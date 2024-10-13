<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( class_exists( 'WP_Background_Process' ) ) {

	class SWCFPC_Preloader_Process extends WP_Background_Process {
	


		/**
		 * The main plugin class.
		 *
		 * @var \SW_CLOUDFLARE_PAGECACHE
		 */
		private $main_instance = null;
		protected $action      = 'swcfpc_cache_preloader_background_process';

		function __construct( $main_instance ) {

			$this->main_instance = $main_instance;

			parent::__construct();

		}

		protected function task( $item ) {

			$objects = $this->main_instance->get_modules();

			if ( ! isset( $item['url'] ) ) {
				$objects['logs']->add_log( 'preloader::task', 'Unable to find a valid URL to preload. Exit.' );
				return false;
			}

			$objects['logs']->add_log( 'preloader::task', 'Preloading URL ' . esc_url_raw( $item['url'] ) );

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

			// $objects['logs']->add_log( 'preloader::task', 'Response headers for URL '.esc_url_raw( $item['url'] ).': '.print_r( wp_remote_retrieve_headers($response), true) );

			// Sleep 2 seconds before to remove the item from queue and preload next url
			sleep( 2 );

			// Return false to remove item from the queue. If not, the process enter in loop
			return false;

		}

		protected function complete() {

			$objects = $this->main_instance->get_modules();

			// Unlock preloader
			$objects['cache_controller']->unlock_preloader();

			// Log preloading complete
			$objects['logs']->add_log( 'preloader::task', 'Preloading complete' );

			parent::complete();

		}

		public function is_process_running() {
			return parent::is_process_running();
		}

	}


}
