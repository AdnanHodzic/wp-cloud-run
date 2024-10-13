<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Utility class for checking if the cache is active by checking the headers.
 */
class SWCFPC_Test_Cache {

	/**
	 * URL to test.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Errors found during the test.
	 *
	 * @var string[]
	 */
	protected $errors;

	/**
	 * Saved response of the tested URL.
	 *
	 * @var array
	 */
	private $saved_response;

	public function __construct( $url ) {
		$this->url = $url;
	}

	/**
	 * Check if the response headers indicate that the page was served from Cloudflare.
	 *
	 * @param bool $check_worker_mode - Check if the page was served by the Worker.
	 *
	 * @return bool - True if the cache is active, false otherwise. On false, check if errors are present.
	 */
	public function check_cloudflare_cache( $check_worker_mode = false ) {
		$this->errors = [];

		$retrieved_headers = $this->fetch_headers();

		if ( empty( $retrieved_headers ) ) {
			return false;
		}

		// +-------- Invalid Cache Status --------+
		if ( ! isset( $retrieved_headers['CF-Cache-Status'] ) ) {
			$this->errors[] = __( 'Seem that your website is not behind Cloudflare. If you have recently enabled the cache or it is your first test, wait about 30 seconds and try again because the changes take a few seconds for Cloudflare to propagate them on the web. If the error persists, request support for a detailed check.', 'wp-cloudflare-page-cache' );

			return false;
		}

		if ( 0 === strcasecmp( $retrieved_headers['CF-Cache-Status'], 'REVALIDATED' ) ) {
			$this->errors[] = sprintf( __( 'Cache status: %s - The resource is served from cache but is stale. The resource was revalidated by either an If-Modified-Since header or an If-None-Match header.', 'wp-cloudflare-page-cache' ), $retrieved_headers['CF-Cache-Status'] );

			return false;
		}

		if ( 0 === strcasecmp( $retrieved_headers['CF-Cache-Status'], 'UPDATING' ) ) {
			$this->errors[] = sprintf( __( 'Cache status: %s - The resource was served from cache but is expired. The resource is currently being updated by the origin web server. UPDATING is typically seen only for very popular cached resources.', 'wp-cloudflare-page-cache' ), $retrieved_headers['CF-Cache-Status'] );

			return false;
		}

		if ( 0 === strcasecmp( $retrieved_headers['CF-Cache-Status'], 'BYPASS' ) ) {
			$this->errors[] = sprintf( __( 'Cache status: %s - Cloudflare has been instructed to not cache this asset. It has been served directly from the origin.', 'wp-cloudflare-page-cache' ), $retrieved_headers['CF-Cache-Status'] );

			return false;
		}

		if ( 0 === strcasecmp( $retrieved_headers['CF-Cache-Status'], 'DYNAMIC' ) ) {

			$cookies = wp_remote_retrieve_cookies( $this->saved_response );
			$error   = sprintf( __( 'Cache status: %s - The resource was not cached by default and your current Cloudflare caching configuration doesn\'t instruct Cloudflare to cache the resource.', 'wp-cloudflare-page-cache' ), $retrieved_headers['CF-Cache-Status'] );

			if ( ! empty( $cookies ) && count( $cookies ) > 1 ) {
				$error .= ' ' . __( 'Try to enable the <strong>Strip response cookies on pages that should be cached</strong> option and retry.', 'wp-cloudflare-page-cache' );
			} else {
				$error = ' ' . __( 'Instead, the resource was requested from the origin web server.', 'wp-cloudflare-page-cache' );
			}

			$this->errors[] = $error;
			return false;
		}

		// +-------- Cache Control --------+
		if ( 0 === strcasecmp( $retrieved_headers['Cache-Control'], '{resp:x-wp-cf-super-cache-cache-control}' ) ) {
			$this->errors[] = __( 'Invalid Cache-Control response header. If you are using Litespeed Server, please disable the option <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong>, purge the cache and retry.', 'wp-cloudflare-page-cache' );

			return false;
		}

		// +-------- Cloudflare Worker --------+
		if ( $check_worker_mode ) {
			if ( ! isset( $retrieved_headers['x-wp-cf-super-cache-worker-status'] ) ) {
				$this->errors[] = __( 'Unable to find the X-WP-CF-Super-Cache-Worker-Status response header. Worker mode seems not working correctly.', 'wp-cloudflare-page-cache' );

				return false;
			}

			if ( ! isset( $retrieved_headers['x-wp-cf-super-cache-worker-status'] ) ) {
				$this->errors[] = __( 'Unable to find the X-WP-CF-Super-Cache-Worker-Status response header. Worker mode seems not working correctly.', 'wp-cloudflare-page-cache' );

				return false;
			}
		}

		// +-------- Valid Cache Status --------+
		if (
			0 === strcasecmp( $retrieved_headers['CF-Cache-Status'], 'HIT' ) ||
			0 === strcasecmp( $retrieved_headers['CF-Cache-Status'], 'MISS' ) ||
			0 === strcasecmp( $retrieved_headers['CF-Cache-Status'], 'EXPIRED' ) ||
			(
				$check_worker_mode &&
				(
					0 === strcasecmp( $retrieved_headers['x-wp-cf-super-cache-worker-status'], 'hit' ) ||
					0 === strcasecmp( $retrieved_headers['x-wp-cf-super-cache-worker-status'], 'miss' )
				)
			)
		) {
			return true;
		}

		$this->errors[] = __( 'Undefined error', 'wp-cloudflare-page-cache' );
		return false;
	}

	/**
	 * Return the headers of the endpoint to test.
	 *
	 * The response will be chached after the first succesful request. Reset the saved response to make a new fetch.
	 *
	 * @return \WpOrg\Requests\Utility\CaseInsensitiveDictionary|array The HTTP headers or empty array on error.
	 */
	protected function fetch_headers() {
		if ( empty( $this->url ) ) {
			$this->errors[] = __( 'The testing URL is empty!', 'wp-cloudflare-page-cache' );
			return [];
		}

		if ( empty( $this->saved_response ) ) {
			$response = wp_remote_head(
				$this->url,
				[
					'timeout' => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
				]
			);

			if ( is_wp_error( $response ) ) {
				$this->errors[] = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
				return [];
			}

			$this->saved_response = $response;
		}

		return wp_remote_retrieve_headers( $this->saved_response );
	}

	/**
	 * Check if we have errors.
	 *
	 * @return bool
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Get the errors.
	 *
	 * @return string[]
	 */
	public function get_errors() {
		return $this->errors;
	}
}
