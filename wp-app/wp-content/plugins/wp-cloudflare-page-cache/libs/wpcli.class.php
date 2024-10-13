<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_WP_CLI extends WP_CLI_Command {



	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance = null;
	private $objects       = false;

	function __construct( $main_instance ) {

		$this->main_instance = $main_instance;

	}


	/**
	 * Show current Super Page Cache version
	 *
	 * @when after_wp_load
	 */
	function version() {
		WP_CLI::line( 'Super Page Cache' . get_option( 'swcfpc_version', false ) );
	}


	/**
	 * Purge whole caches
	 *
	 * @when after_wp_load
	 */
	function purge_cache() {

		$this->objects = $this->main_instance->get_modules();

		if ( $this->objects['cache_controller']->purge_all() ) {
			WP_CLI::success( __( 'Cache purged successfully', 'wp-cloudflare-page-cache' ) );
		} else {
			WP_CLI::error( __( 'An error occurred while purging the cache', 'wp-cloudflare-page-cache' ) );
		}

	}


	/**
	 * Purge the whole Cloudflare cache only
	 *
	 * @when after_wp_load
	 */
	function purge_cf_cache() {

		$this->objects = $this->main_instance->get_modules();
		$error         = '';

		if ( ! $this->objects['cloudflare']->purge_cache( $error ) ) {
			WP_CLI::error( $error );
		}

		WP_CLI::success( __( 'Cache purged successfully', 'wp-cloudflare-page-cache' ) );

	}


	/**
	 * Purge the whole Varnish cache only
	 *
	 * @when after_wp_load
	 */
	function purge_varnish_cache() {

		$this->objects = $this->main_instance->get_modules();
		$error         = '';

		if ( ! $this->objects['varnish']->purge_whole_cache( $error ) ) {
			WP_CLI::error( $error );
		}

		WP_CLI::success( __( 'Cache purged successfully', 'wp-cloudflare-page-cache' ) );

	}


	/**
	 * Purge the whole OPcache cache only
	 *
	 * @when after_wp_load
	 */
	function purge_opcache_cache() {

		$this->objects = $this->main_instance->get_modules();
		$this->objects['cache_controller']->purge_opcache();

		WP_CLI::success( __( 'Cache purged successfully', 'wp-cloudflare-page-cache' ) );

	}


	/**
	 * Purge the whole Fallback cache only
	 *
	 * @when after_wp_load
	 */
	function purge_fallback_cache() {

		$this->objects = $this->main_instance->get_modules();
		$this->objects['fallback_cache']->fallback_cache_purge_all();

		WP_CLI::success( __( 'Cache purged successfully', 'wp-cloudflare-page-cache' ) );

	}


	/**
	 * Enable Cloudflare page cache
	 *
	 * @when after_wp_load
	 */
	function enable_cf_cache() {

		$this->objects = $this->main_instance->get_modules();
		$error         = '';

		if ( ! $this->objects['cloudflare']->enable_page_cache( $error ) ) {
			WP_CLI::error( $error );
		}

		WP_CLI::success( __( 'Cache enabled successfully', 'wp-cloudflare-page-cache' ) );

	}


	/**
	 * Disable Cloudflare page cache
	 *
	 * @when after_wp_load
	 */
	function disable_cf_cache() {

		$this->objects = $this->main_instance->get_modules();
		$error         = '';

		if ( ! $this->objects['cloudflare']->disable_page_cache( $error ) ) {
			WP_CLI::error( $error );
		}

		WP_CLI::success( __( 'Cache enabled successfully', 'wp-cloudflare-page-cache' ) );

	}


	/**
	 * Test Cloudflare page cache
	 *
	 * @when after_wp_load
	 */
	function test_cf_cache() {

		$this->objects = $this->main_instance->get_modules();
		$error_dynamic = '';
		$error_static  = '';

		$url_static_resource  = SWCFPC_PLUGIN_URL . 'assets/testcache.html';
		$url_dynamic_resource = home_url();

		$return_array['static_resource_url']  = $url_static_resource;
		$return_array['dynamic_resource_url'] = $url_dynamic_resource;

		$headers_dyamic_resource = $this->objects['cloudflare']->page_cache_test( $url_dynamic_resource, $error_dynamic );

		if ( ! $headers_dyamic_resource ) {

			$headers_static_resource = $this->objects['cloudflare']->page_cache_test( $url_static_resource, $error_static, true );
			$error                   = '';

			// Error on both dynamic and static test
			if ( ! $headers_static_resource ) {

				$error .= __( 'Page caching seems not working for both dynamic and static pages.', 'wp-cloudflare-page-cache' );
				$error .= '<br/><br/>';
				$error .= sprintf( __( 'Error on dynamic page (%1$s): %2$s', 'wp-cloudflare-page-cache' ), $url_dynamic_resource, $error_dynamic );
				$error .= '<br/><br/>';
				$error .= sprintf( __( 'Error on static resource (%1$s): %2$s', 'wp-cloudflare-page-cache' ), $url_static_resource, $error_static );

			} else {
				// Error on dynamic test only
				$error .= sprintf( __( 'Page caching is working for static page (%s) but seems not working for dynamic pages.', 'wp-cloudflare-page-cache' ), $url_static_resource );
				$error .= '<br/><br/>';
				$error .= sprintf( __( 'Error on dynamic page (%1$s): %2$s', 'wp-cloudflare-page-cache' ), $url_dynamic_resource, $error_dynamic );

			}

			WP_CLI::error( $error );

		}

		WP_CLI::success( __( 'Page caching is working properly', 'wp-cloudflare-page-cache' ) );

	}

}
