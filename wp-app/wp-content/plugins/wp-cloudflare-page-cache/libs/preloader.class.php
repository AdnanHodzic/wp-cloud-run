<?php

use SPC\Constants;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Preloader_Process {
	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance = null;

	/**
	 * Logger instance
	 *
	 * @var SWCFPC_Logs
	 */
	private $logger = null;

	/**
	 * Job items.
	 *
	 * @var items
	 */
	private $items = array();

	/**
	 * Action name.
	 *
	 * @var string
	 */
	protected $action = 'swcfpc_cache_preloader_background_process';

	function __construct( $main_instance ) {
		$this->main_instance = $main_instance;
		$this->logger        = $this->main_instance->get_logger();

		add_action( 'spc_preloader_job', array( $this, 'preloader_jobs' ) );
		add_action( 'spc_preloader_completed', array( $this, 'preloader_completed' ) );
	}

	public function push_to_queue( $item ) {
		$this->items[] = $item;
	}

	public function save() {
		foreach ( $this->items as $item ) {
			as_enqueue_async_action('spc_preloader_job', $item, Constants::ACTION_SCHEDULER_GROUP);
		}

		as_schedule_single_action(time() + 60, 'spc_preloader_completed', array(), Constants::ACTION_SCHEDULER_GROUP);
	}

	public function preloader_jobs( $item ) {
		if ( empty( $item ) ) {
			$this->logger->add_log( 'preloader::preloader_jobs', 'Unable to find a valid URL to preload. Exit.' );
			
			return false;
		}

		$this->logger->add_log( 'preloader::preloader_jobs', 'Preloading URL ' . esc_url_raw( $item ) );

		$args = [
			'timeout'    => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
			'blocking'   => true,
			'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0',
			'sslverify'  => false,
			'headers'    => [
				'Accept' => 'text/html',
			],
		];

		$response = wp_remote_get( esc_url_raw( $item ), $args );

		$status = wp_remote_retrieve_response_code( $response );

		if ( $status !== 200 ) {
			$this->logger->add_log( 'preloader::preloader_jobs', 'Error preloading URL ' . esc_url_raw( $item ) . '. Status: ' . $status );
		} else {
			$this->logger->add_log( 'preloader::preloader_jobs', 'URL ' . esc_url_raw( $item ) . ' preloaded successfully.' );
		}

		// Sleep 2 seconds before to remove the item from queue and preload next url
		sleep( 2 );

		// Return false to remove item from the queue. If not, the process enter in loop
		return false;
	}

	public function preloader_completed() {
		// Unlock preloader
		$this->main_instance->get_cache_controller()->unlock_preloader();

		// Log preloading complete
		$this->logger->add_log( 'preloader::preloader_completed', 'Preloading complete' );
	}
}
