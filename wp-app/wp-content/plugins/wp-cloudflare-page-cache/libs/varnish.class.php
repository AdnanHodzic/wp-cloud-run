<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Varnish {



	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance       = null;
	private $objects             = false;
	private $hostname            = 'localhost';
	private $port                = 6081;
	private $single_purge_method = 'PURGE';
	private $whole_purge_method  = 'PURGE';
	private $provider            = '';

	function __construct( $main_instance ) {

		$this->main_instance = $main_instance;

		$this->hostname            = $this->main_instance->get_single_config( 'cf_varnish_hostname', 'localhost' );
		$this->port                = $this->main_instance->get_single_config( 'cf_varnish_port', 6081 );
		$this->single_purge_method = $this->main_instance->get_single_config( 'cf_varnish_purge_method', 'PURGE' );
		$this->whole_purge_method  = $this->main_instance->get_single_config( 'cf_varnish_purge_all_method', 'PURGE' );

		if ( $this->main_instance->get_single_config( 'cf_varnish_cw', 0 ) > 0 ) {
			$this->provider = 'cloudways';
		}

		$this->actions();

	}


	function actions() {

		// Ajax clear whole fallback cache
		add_action( 'wp_ajax_swcfpc_purge_varnish_cache', [ $this, 'ajax_purge_whole_varnish_cache' ] );

	}


	function purge_urls( $urls ) {

		$error = '';

		if ( is_array( $urls ) && count( $urls ) > 0 ) {

			foreach ( $urls as $single_url ) {
				$this->purge_single_url_cache( $single_url, $error );
			}       
		}

	}


	function purge_single_url_cache( $url, &$error, $purge_all = false ) {

		$this->objects = $this->main_instance->get_modules();

		if ( $this->hostname == null || $this->port == null ) {
			$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', 'Invalid hostname or port' );
			$error = __( 'Invalid hostname or port', 'wp-cloudflare-page-cache' );
			return false;
		}

		// Varnish purge request on Cloudways
		if ( $this->provider == 'cloudways' ) {

			$this->single_purge_method = 'URLPURGE';
			$this->whole_purge_method  = 'PURGE';

		}

		$parseUrl = $purge_all ? parse_url( site_url() ) : parse_url( $url );

		// Determine the schema
		$schema = 'http://';
		if ( isset( $parseUrl['scheme'] ) ) {
			$schema = "{$parseUrl['scheme']}://";
		}

		if ( $purge_all ) {

			if ( $this->provider == 'cloudways' ) {
				$finalURL = sprintf( '%s%s%s', $schema, $this->hostname, '/.*' );
			} else {
				$finalURL = sprintf( '%s%s:%d%s', $schema, $this->hostname, $this->port, '/*' );
			}       
		} else {

			// Determine the path
			$path = '';
			if ( isset( $parseUrl['path'] ) ) {
				$path = $parseUrl['path'];
			}

			if ( $this->provider == 'cloudways' ) {
				$finalURL = sprintf( '%s%s%s', $schema, $this->hostname, $path );
			} else {
				$finalURL = sprintf( '%s%s:%d%s', $schema, $this->hostname, $this->port, $path );
			}

			if ( ! empty( $parseUrl['query'] ) ) {
				$finalURL .= "?{$parseUrl['query']}";
			}       
		}

		$request_args = [
			'method'    => $purge_all ? $this->whole_purge_method : $this->single_purge_method,
			'headers'   => [
				'Host'       => $parseUrl['host'],
				'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
			],
			'sslverify' => false,
		];

		$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', "Send purging request to {$finalURL}" );

		if ( $this->objects['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', 'Request args ' . print_r( $request_args, true ) );
		}

		// Send purge request to Varnish
		$response = wp_remote_request( $finalURL, $request_args );

		if ( $this->objects['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', 'Response: ' . print_r( $response, true ) );
		}

		if ( is_wp_error( $response ) || $response['response']['code'] != '200' ) {

			if ( $schema === 'https://' ) {
				$schema = 'http://';
			} else {
				$schema = 'https://';
			}

			if ( is_wp_error( $response ) ) {
				$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', 'Error: ' . $response->get_error_message() . " - Retry using {$schema}" );
			} else {
				$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', "Response code {$response['response']['code']} - Retry using {$schema}" );
			}

			if ( $purge_all ) {

				if ( $this->provider == 'cloudways' ) {
					$finalURL = sprintf( '%s%s%s', $schema, $this->hostname, '/.*' );
				} else {
					$finalURL = sprintf( '%s%s:%d%s', $schema, $this->hostname, $this->port, '/*' );
				}           
			} else {

				if ( $this->provider == 'cloudways' ) {
					$finalURL = sprintf( '%s%s%s', $schema, $this->hostname, $path );
				} else {
					$finalURL = sprintf( '%s%s:%d%s', $schema, $this->hostname, $this->port, $path );
				}

				if ( ! empty( $parseUrl['query'] ) ) {
					$finalURL .= "?{$parseUrl['query']}";
				}           
			}

			$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', "Send purging request to {$finalURL}" );

			if ( $this->objects['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', 'Request args ' . print_r( $request_args, true ) );
			}

			// Send new purge request to Varnish
			$response = wp_remote_request( $finalURL, $request_args );

			if ( $this->objects['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', 'Response: ' . print_r( $response, true ) );
			}

			if ( is_wp_error( $response ) ) {
				$error = $response->get_error_message();
				return false;
			}       
		}

		$this->objects['logs']->add_log( 'varnish::purge_single_url_cache', "Cache purged for URL {$url}" );

		return true;

	}


	function purge_whole_cache( &$error ) {

		$error = '';

		return $this->purge_single_url_cache( '', $error, true );

	}


	function ajax_purge_whole_varnish_cache() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$return_array = [ 'status' => 'ok' ];
		$error        = '';

		if ( ! $this->purge_whole_cache( $error ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = $error;
			die( json_encode( $return_array ) );
		}

		$this->objects['logs']->add_log( 'varnish::ajax_purge_whole_varnish_cache', 'Purge whole Varnish cache' );

		$return_array['success_msg'] = __( 'Varnish cache purged successfully!', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );

	}

}
