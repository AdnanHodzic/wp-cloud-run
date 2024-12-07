<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( class_exists( 'WP_Background_Process' ) ) {

	class SWCFPC_Preloader_Process extends \WP_Background_Process {
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
		 * Action name.
		 *
		 * @var string
		 */
		protected $action = 'swcfpc_cache_preloader_background_process';

		function __construct( $main_instance ) {
			$this->main_instance = $main_instance;
			$this->logger        = $this->main_instance->get_logger();
			parent::__construct();
		}

		protected function task( $item ) {
			if ( ! isset( $item['url'] ) ) {
				$this->logger->add_log( 'preloader::task', 'Unable to find a valid URL to preload. Exit.' );

				return false;
			}

			$this->logger->add_log( 'preloader::task', 'Preloading URL ' . esc_url_raw( $item['url'] ) );

			$args = [
				'timeout'    => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
				'blocking'   => true,
				'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0',
				'sslverify'  => false,
				'headers'    => [
					'Accept' => 'text/html',
				],
			];

			$response = wp_remote_get( esc_url_raw( $item['url'] ), $args );

			$status = wp_remote_retrieve_response_code( $response );

			if ( $status !== 200 ) {
				$this->logger->add_log( 'preloader::task', 'Error preloading URL ' . esc_url_raw( $item['url'] ) . '. Status: ' . $status );
			} else {
				$this->logger->add_log( 'preloader::task', 'URL ' . esc_url_raw( $item['url'] ) . ' preloaded successfully.' );
			}

			// Sleep 2 seconds before to remove the item from queue and preload next url
			sleep( 2 );

			// Return false to remove item from the queue. If not, the process enter in loop
			return false;
		}

		protected function complete() {
			// Unlock preloader
			$this->main_instance->get_cache_controller()->unlock_preloader();

			// Log preloading complete
			$this->logger->add_log( 'preloader::task', 'Preloading complete' );

			parent::complete();
		}

		protected function maybe_wp_die( $return = null ) {
			/**
			 * Should wp_die be used?
			 *
			 * @return bool
			 */
			if ( apply_filters( $this->identifier . '_wp_die', true ) ) {
				wp_die();
			}

			return $return;
		}
	}
}
