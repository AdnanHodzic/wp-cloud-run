<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_WP_CLI extends WP_CLI_Command {
	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance = null;

	function __construct( $main_instance ) {
		$this->main_instance = $main_instance;
	}


	/**
	 * Show current Super Page Cache version
	 *
	 * @when after_wp_load
	 */
	function version() {
		WP_CLI::line( sprintf( 'Super Page Cache %s', get_option( 'swcfpc_version', false ) ) );
	}


	/**
	 * Purge whole caches
	 *
	 * @when after_wp_load
	 */
	function purge_cache() {
		if ( $this->main_instance->get_cache_controller()->purge_all() ) {
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
		$error = '';

		if ( ! $this->main_instance->get_cloudflare_handler()->purge_cache( $error ) ) {
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
		$error = '';

		if ( ! $this->main_instance->get_varnish_handler()->purge_whole_cache( $error ) ) {
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
		$this->main_instance->get_cache_controller()->purge_opcache();

		WP_CLI::success( __( 'Cache purged successfully', 'wp-cloudflare-page-cache' ) );
	}


	/**
	 * Purge the whole Fallback cache only
	 *
	 * @when after_wp_load
	 */
	function purge_fallback_cache() {
		$this->main_instance->get_fallback_cache_handler()->fallback_cache_purge_all();

		WP_CLI::success( __( 'Cache purged successfully', 'wp-cloudflare-page-cache' ) );
	}


	/**
	 * Enable Cloudflare page cache
	 *
	 * @when after_wp_load
	 */
	function enable_cf_cache() {
		$error = '';

		if ( ! $this->main_instance->get_cloudflare_handler()->enable_page_cache( $error ) ) {
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
		$error = '';

		if ( ! $this->main_instance->get_cloudflare_handler()->disable_page_cache( $error ) ) {
			WP_CLI::error( $error );
		}

		WP_CLI::success( __( 'Cache disabled successfully', 'wp-cloudflare-page-cache' ) );
	}


	/**
	 * Test Cloudflare page cache
	 *
	 * @when after_wp_load
	 */
	function test_cf_cache() {
		$error_dynamic = '';
		$error_static  = '';

		$url_static_resource  = SWCFPC_PLUGIN_URL . 'assets/testcache.html';
		$url_dynamic_resource = home_url();

		$return_array['static_resource_url']  = $url_static_resource;
		$return_array['dynamic_resource_url'] = $url_dynamic_resource;

		$headers_dyamic_resource = $this->page_cache_test( $url_dynamic_resource, $error_dynamic );

		if ( ! $headers_dyamic_resource ) {

			$headers_static_resource = $this->page_cache_test( $url_static_resource, $error_static, true );
			$error                   = '';

			// Error on both dynamic and static test
			if ( ! $headers_static_resource ) {

				$error .= __( 'Page caching seems not working for both dynamic and static pages.', 'wp-cloudflare-page-cache' );
				$error .= '<br/><br/>';
				// translators: %1$s is the dynamic resource URL, %2$s is the error message.
				$error .= sprintf( __( 'Error on dynamic page (%1$s): %2$s', 'wp-cloudflare-page-cache' ), $url_dynamic_resource, $error_dynamic );
				$error .= '<br/><br/>';
				// translators: %1$s is the static resource URL, %2$s is the error message.
				$error .= sprintf( __( 'Error on static resource (%1$s): %2$s', 'wp-cloudflare-page-cache' ), $url_static_resource, $error_static );

			} else {
				// Error on dynamic test only
				// translators: %s is the static resource URL.
				$error .= sprintf( __( 'Page caching is working for static page (%s) but seems not working for dynamic pages.', 'wp-cloudflare-page-cache' ), $url_static_resource );
				$error .= '<br/><br/>';
				// translators: %1$s is the dynamic resource URL, %2$s is the error message.
				$error .= sprintf( __( 'Error on dynamic page (%1$s): %2$s', 'wp-cloudflare-page-cache' ), $url_dynamic_resource, $error_dynamic );

			}

			WP_CLI::error( $error );

		}

		WP_CLI::success( __( 'Page caching is working properly', 'wp-cloudflare-page-cache' ) );

	}


	private function page_cache_test( $url, &$error, $test_static = false ) {
		$logger = $this->main_instance->get_logger();

		$args = [
			'timeout'    => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
			'sslverify'  => false,
			'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0',
			'headers'    => [
				'Accept' => 'text/html',
			],
		];

		$logger->add_log( 'cloudflare::page_cache_test', "Start test to {$url} with headers " . print_r( $args, true ) );

		// First test - Home URL
		$response = wp_remote_get( esc_url_raw( $url ), $args );

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$logger->add_log( 'cloudflare::page_cache_test', "Error wp_remote_get: {$error}" );

			return false;
		}

		$headers = wp_remote_retrieve_headers( $response );

		if ( is_object( $logger ) ) {
			$logger->add_log( 'cloudflare::page_cache_test', 'Response Headers: ' . var_export( $headers, true ) );
		}

		if ( ! $test_static && ! isset( $headers['X-WP-CF-Super-Cache'] ) ) {
			$error = __( 'The plugin is not detected on your home page. If you have activated other caching systems, please disable them and retry the test.', 'wp-cloudflare-page-cache' );

			return false;
		}

		if ( ! $test_static && $headers['X-WP-CF-Super-Cache'] == 'no-cache' ) {
			$error = __( 'The cache is not enabled on your home page. It\'s not possible to verify if the page caching is working properly.', 'wp-cloudflare-page-cache' );

			return false;
		}

		if ( ! isset( $headers['CF-Cache-Status'] ) ) {
			$error = __( 'Seem that your website is not behind Cloudflare. If you have recently enabled the cache or it is your first test, wait about 30 seconds and try again because the changes take a few seconds for Cloudflare to propagate them on the web. If the error persists, request support for a detailed check.', 'wp-cloudflare-page-cache' );

			return false;
		}

		if ( ! isset( $headers['Cache-Control'] ) ) {
			$error = __( 'Unable to find the Cache-Control response header.', 'wp-cloudflare-page-cache' );

			return false;
		}

		if ( ! $test_static && ! isset( $headers['X-WP-CF-Super-Cache-Cache-Control'] ) ) {
			$error = __( 'Unable to find the X-WP-CF-Super-Cache-Cache-Control response header.', 'wp-cloudflare-page-cache' );

			return false;
		}

		if ( strcasecmp( $headers['Cache-Control'], '{resp:x-wp-cf-super-cache-cache-control}' ) == 0 ) {
			$error = __( 'Invalid Cache-Control response header. If you are using Litespeed Server, please disable the option <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong>, purge the cache and retry.', 'wp-cloudflare-page-cache' );

			return false;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'HIT' ) == 0 || strcasecmp( $headers['CF-Cache-Status'], 'MISS' ) == 0 || strcasecmp( $headers['CF-Cache-Status'], 'EXPIRED' ) == 0 ) {
			return true;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'REVALIDATED' ) == 0 ) {
			// translators: %s is the CF-Cache-Status header value.
			$error = sprintf( __( 'Cache status: %s - The resource is served from cache but is stale. The resource was revalidated by either an If-Modified-Since header or an If-None-Match header.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );

			return false;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'UPDATING' ) == 0 ) {
			// translators: %s is the CF-Cache-Status header value.
			$error = sprintf( __( 'Cache status: %s - The resource was served from cache but is expired. The resource is currently being updated by the origin web server. UPDATING is typically seen only for very popular cached resources.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );

			return false;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'BYPASS' ) == 0 ) {
			// translators: %s is the CF-Cache-Status header value.
			$error = sprintf( __( 'Cache status: %s - Cloudflare has been instructed to not cache this asset. It has been served directly from the origin.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );

			return false;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'DYNAMIC' ) == 0 ) {

			$cookies = wp_remote_retrieve_cookies( $response );

			if ( ! empty( $cookies ) && count( $cookies ) > 1 ) {
				// translators: %s is the CF-Cache-Status header value.
				$error = sprintf( __( 'Cache status: %s - The resource was not cached by default and your current Cloudflare caching configuration doesn\'t instruct Cloudflare to cache the resource. Try to enable the <strong>Strip response cookies on pages that should be cached</strong> option and retry.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );
			} else {
				// translators: %s is the CF-Cache-Status header value.
				$error = sprintf( __( 'Cache status: %s - The resource was not cached by default and your current Cloudflare caching configuration doesn\'t instruct Cloudflare to cache the resource.  Instead, the resource was requested from the origin web server.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );
			}

			return false;

		}

		$error = __( 'Undefined error', 'wp-cloudflare-page-cache' );

		return false;

	}

}
