<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Cloudflare {


	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance    = null;
	private $modules          = false;
	private $api_key          = '';
	private $email            = '';
	private $api_token        = '';
	private $auth_mode        = 0;
	private $zone_id          = '';
	private $zone_domain_name = '';
	// private $subdomain           = '';
	private $api_token_domain = '';
	private $worker_mode      = false;
	private $worker_content   = '';
	private $worker_id        = '';
	private $worker_route_id  = '';
	private $account_id_list  = [];

	private $cache_ruleset_id                     = ''; // Ruleset related to `http_request_cache_settings` phase.
	private $cache_ruleset_options                = [];
	private $cache_ruleset_rule_id                = '';
	private $cache_rule_description               = '';
	private $last_cache_ruleset_options_retrieved = [];

	/**
	 * SWCFPC_Cloudflare constructor.
	 *
	 * @param \SW_CLOUDFLARE_PAGECACHE $main_instance Instance of the main plugin class.
	 */
	function __construct( $main_instance ) {

		$this->main_instance    = $main_instance;
		$this->auth_mode        = $this->main_instance->get_single_config( 'cf_auth_mode' );
		$this->api_key          = $this->main_instance->get_cloudflare_api_key();
		$this->email            = $this->main_instance->get_cloudflare_api_email();
		$this->api_token        = $this->main_instance->get_cloudflare_api_token();
		$this->zone_id          = $this->main_instance->get_cloudflare_api_zone_id();
		$this->zone_domain_name = $this->main_instance->get_cloudflare_api_zone_domain_name( $this->zone_id );
		$this->worker_mode      = $this->main_instance->get_cloudflare_worker_mode();
		$this->worker_content   = $this->main_instance->get_cloudflare_worker_content();
		$this->worker_id        = $this->main_instance->get_cloudflare_worker_id();
		$this->worker_route_id  = $this->main_instance->get_cloudflare_worker_route_id();

		$this->cache_ruleset_id       = $this->main_instance->get_single_config( 'cf_cache_settings_ruleset_id', '' );
		$this->cache_ruleset_rule_id  = $this->main_instance->get_single_config( 'cf_cache_settings_ruleset_rule_id', '' );
		$this->cache_rule_description = 'WP Super Page Cache Plugin rules for ' . $this->zone_domain_name;

		$this->cache_ruleset_options = [
			'name'        => 'WP Cloudflare Super Page Cache Plugin',
			'description' => 'Ruleset made with WP Cloudflare Super Page Cache Plugin',
			'kind'        => 'zone',
			'phase'       => 'http_request_cache_settings',
			'rules'       => [],
		];

		$this->actions();
	}


	function actions() {

	}

	/**
	 * Check if the Cloudflare API is enabled.
	 *
	 * @return bool
	 */
	function is_enabled() {
		return (
			! empty( $this->zone_id ) &&
			! empty( $this->email ) &&
			(
				! empty( $this->api_token ) ||
				! empty( $this->api_key )
			)
		);
	}


	function set_auth_mode( $auth_mode ) {
		$this->auth_mode = $auth_mode;
	}


	function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}


	function set_api_email( $email ) {
		$this->email = $email;
	}


	function set_api_token( $api_token ) {
		$this->api_token = $api_token;
	}


	function set_api_token_domain( $api_token_domain ) {
		$this->api_token_domain = $api_token_domain;
	}


	function set_worker_id( $worker_id ) {
		$this->worker_id = $worker_id;
	}


	function set_worker_route_id( $worker_route_id ) {
		$this->worker_route_id = $worker_route_id;
	}


	function enable_worker_mode( $worker_content ) {
		$this->worker_mode    = true;
		$this->worker_content = $worker_content;
	}


	/**
	 * Get the request arguments for the Cloudflare API authorization.
	 *
	 * @param bool $use_standard_curl_format Format the request arguments for standard cURL.
	 * @return array|int[]
	 */
	function get_api_auth_args( $use_standard_curl_format = false ) {

		$cf_request_args = [
			'timeout' => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
			'headers' => [],
		];

		$headers = [
			'Content-Type' => 'application/json',
		];

		if ( $this->auth_mode == SWCFPC_AUTH_MODE_API_TOKEN ) {
			$headers['Authorization'] = "Bearer {$this->api_token}";
		} else {
			$headers['X-Auth-Email'] = $this->email;
			$headers['X-Auth-Key']   = $this->api_key;
		}

		if ( $use_standard_curl_format ) {
			foreach ( $headers as $key => $value ) {
				$cf_request_args['headers'][] = "{$key}: {$value}";
			}
		} else {
			$cf_request_args['headers'] = $headers;
		}

		return $cf_request_args;
	}


	function get_zone_id_list( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$zone_id_list = [];
		$per_page     = 50;
		$current_page = 1;
		$pagination   = false;
		$cf_headers   = $this->get_api_auth_args();

		do {

			if ( $this->auth_mode == SWCFPC_AUTH_MODE_API_TOKEN && $this->api_token_domain != '' ) {

				if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
					$this->modules['logs']->add_log( 'cloudflare::cloudflare_get_zone_ids', "Request for page {$current_page} - URL: " . esc_url_raw( "https://api.cloudflare.com/client/v4/zones?name={$this->api_token_domain}" ) );
				}

				$response = wp_remote_get(
					esc_url_raw( "https://api.cloudflare.com/client/v4/zones?name={$this->api_token_domain}" ),
					$cf_headers
				);

			} else {

				if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
					$this->modules['logs']->add_log( 'cloudflare::cloudflare_get_zone_ids', "Request for page {$current_page} - URL: " . esc_url_raw( "https://api.cloudflare.com/client/v4/zones?page={$current_page}&per_page={$per_page}" ) );
				}

				$response = wp_remote_get(
					esc_url_raw( "https://api.cloudflare.com/client/v4/zones?page={$current_page}&per_page={$per_page}" ),
					$cf_headers
				);

			}

			if ( is_wp_error( $response ) ) {
				$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
				$this->modules['logs']->add_log( 'cloudflare::get_zone_id_list', "Error wp_remote_get: {$error}" );
				return false;
			}

			$response_body = wp_remote_retrieve_body( $response );

			if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->modules['logs']->add_log( 'cloudflare::cloudflare_get_zone_ids', "Response for page {$current_page}: {$response_body}" );
			}

			$json = json_decode( $response_body, true );

			if ( $json['success'] == false ) {

				$error = [];

				foreach ( $json['errors'] as $single_error ) {
					$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
				}

				$error = implode( ' - ', $error );

				return false;

			}

			if ( isset( $json['result_info'] ) && is_array( $json['result_info'] ) ) {

				if ( isset( $json['result_info']['total_pages'] ) && (int) $json['result_info']['total_pages'] > $current_page ) {
					$pagination = true;
					$current_page++;
				} else {
					$pagination = false;
				}           
			} else {

				if ( $pagination ) {
					$pagination = false;
				}           
			}

			if ( isset( $json['result'] ) && is_array( $json['result'] ) ) {

				foreach ( $json['result'] as $domain_data ) {

					if ( ! isset( $domain_data['name'] ) || ! isset( $domain_data['id'] ) ) {
						$error = __( 'Unable to retrive zone id due to invalid response data', 'wp-cloudflare-page-cache' );
						return false;
					}

					$zone_id_list[ $domain_data['name'] ] = $domain_data['id'];

				}           
			}       
		} while ( $pagination );


		if ( ! count( $zone_id_list ) ) {
			$error = __( 'Unable to find domains configured on Cloudflare', 'wp-cloudflare-page-cache' );
			return false;
		}

		return $zone_id_list;

	}


	function get_current_browser_cache_ttl( &$error ) {

		$this->modules = $this->main_instance->get_modules();
		$cf_headers    = $this->get_api_auth_args();

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::cloudflare_get_browser_cache_ttl', 'Request ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/settings/browser_cache_ttl" ) );
		}

		$response = wp_remote_get(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/settings/browser_cache_ttl" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::get_current_browser_cache_ttl', "Error wp_remote_get: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::cloudflare_get_browser_cache_ttl', "Response {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) && isset( $json['result']['value'] ) ) {
			return $json['result']['value'];
		}

		$error = __( 'Unable to find Browser Cache TTL settings ', 'wp-cloudflare-page-cache' );
		return false;

	}


	function change_browser_cache_ttl( $ttl, &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$cf_headers           = $this->get_api_auth_args();
		$cf_headers['method'] = 'PATCH';
		$cf_headers['body']   = json_encode( [ 'value' => $ttl ] );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::cloudflare_set_browser_cache_ttl', 'Request URL: ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/settings/browser_cache_ttl" ) );
			$this->modules['logs']->add_log( 'cloudflare::cloudflare_set_browser_cache_ttl', 'Request body: ' . json_encode( [ 'value' => $ttl ] ) );
		}

		$response = wp_remote_post(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/settings/browser_cache_ttl" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::change_browser_cache_ttl', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::cloudflare_set_browser_cache_ttl', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		return true;

	}

	/**
	 * Delete the Page Rule
	 *
	 * @param string $page_rule_id The page rule id to delete.
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	function delete_page_rule( $page_rule_id, &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$cf_headers           = $this->get_api_auth_args();
		$cf_headers['method'] = 'DELETE';

		if ( $page_rule_id == '' ) {
			// $error = __('There is not page rule to delete', 'wp-cloudflare-page-cache');
			return false;
		}

		if ( $this->zone_id == '' ) {
			$error = __( 'There is not zone id to use', 'wp-cloudflare-page-cache' );
			return false;
		}

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::cloudflare_delete_page_rule', 'Request: ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/pagerules/{$page_rule_id}" ) );
		}

		$response = wp_remote_post(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/pagerules/{$page_rule_id}" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::delete_page_rule', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::cloudflare_delete_page_rule', 'Response: ' . wp_remote_retrieve_body( $response ) );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		return true;

	}

	/**
	 * Add a Page Rule to cache everything
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 *
	 * @deprecated Use the new Cache Rules API.
	 */
	function add_cache_everything_page_rule( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$cf_headers = $this->get_api_auth_args();
		$url        = $this->main_instance->home_url( '/*' );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::add_cache_everything_page_rule', 'Request URL: ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/pagerules" ) );
			$this->modules['logs']->add_log(
				'cloudflare::add_cache_everything_page_rule',
				'Request Body: ' . json_encode(
					[
						'targets'  => [
							[
								'target'     => 'url',
								'constraint' => [
									'operator' => 'matches',
									'value'    => $url, 
								], 
							],
						],
						'actions'  => [
							[
								'id'    => 'cache_level',
								'value' => 'cache_everything', 
							],
						],
						'priority' => 1,
						'status'   => 'active',
					] 
				) 
			);
		}

		$cf_headers['method'] = 'POST';
		$cf_headers['body']   = json_encode(
			[
				'targets'  => [
					[
						'target'     => 'url',
						'constraint' => [
							'operator' => 'matches',
							'value'    => $url, 
						], 
					],
				],
				'actions'  => [
					[
						'id'    => 'cache_level',
						'value' => 'cache_everything', 
					],
				],
				'priority' => 1,
				'status'   => 'active',
			] 
		);

		$response = wp_remote_post(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/pagerules" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::add_cache_everything_page_rule', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::add_cache_everything_page_rule', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) && isset( $json['result']['id'] ) ) {
			return $json['result']['id'];
		}

		return false;

	}

	/**
	 * Add a Page Rule to bypass cache for backend (admin pages)
	 *
	 * @return bool
	 * @deprecated Use the new Cache Rules API.
	 */
	function add_bypass_cache_backend_page_rule( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$cf_headers = $this->get_api_auth_args();
		$url        = admin_url( '/*' );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::add_bypass_cache_backend_page_rule', 'Request URL: ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/pagerules" ) );
			$this->modules['logs']->add_log(
				'cloudflare::add_bypass_cache_backend_page_rule',
				'Request Body: ' . json_encode(
					[
						'targets'  => [
							[
								'target'     => 'url',
								'constraint' => [
									'operator' => 'matches',
									'value'    => $url, 
								], 
							],
						],
						'actions'  => [
							[
								'id'    => 'cache_level',
								'value' => 'bypass', 
							],
						],
						'priority' => 1,
						'status'   => 'active',
					] 
				) 
			);
		}

		$cf_headers['method'] = 'POST';
		$cf_headers['body']   = json_encode(
			[
				'targets'  => [
					[
						'target'     => 'url',
						'constraint' => [
							'operator' => 'matches',
							'value'    => $url, 
						], 
					],
				],
				'actions'  => [
					[
						'id'    => 'cache_level',
						'value' => 'bypass', 
					],
				],
				'priority' => 1,
				'status'   => 'active',
			] 
		);

		$response = wp_remote_post(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/pagerules" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::add_bypass_cache_backend_page_rule', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::add_bypass_cache_backend_page_rule', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) && isset( $json['result']['id'] ) ) {
			return $json['result']['id'];
		}

		return false;

	}


	function purge_cache( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		do_action( 'swcfpc_cf_purge_whole_cache_before' );

		$cf_headers           = $this->get_api_auth_args();
		$cf_headers['method'] = 'POST';
		$cf_headers['body']   = json_encode( [ 'purge_everything' => true ] );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::purge_cache', 'Request URL: ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache" ) );
			$this->modules['logs']->add_log( 'cloudflare::purge_cache', 'Request Body: ' . json_encode( [ 'purge_everything' => true ] ) );
		}

		$response = wp_remote_post(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::purge_cache', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::purge_cache', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		do_action( 'swcfpc_cf_purge_whole_cache_after' );

		return true;

	}


	private function purge_cache_urls_async( $urls ) {

		$this->modules = $this->main_instance->get_modules();

		$cf_headers = $this->get_api_auth_args( true );

		$chunks = array_chunk( $urls, 30 );

		$multi_curl = curl_multi_init();
		$curl_array = [];
		$curl_index = 0;

		foreach ( $chunks as $single_chunk ) {

			$curl_array[ $curl_index ] = curl_init();

			curl_setopt_array(
				$curl_array[ $curl_index ],
				[
					CURLOPT_URL            => "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache",
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => $cf_headers['timeout'],
					CURLOPT_CUSTOMREQUEST  => 'POST',
					CURLOPT_POST           => 1,
					CURLOPT_HTTPHEADER     => $cf_headers['headers'],
					CURLOPT_POSTFIELDS     => json_encode( [ 'files' => array_values( $single_chunk ) ] ),
				]
			);

			curl_multi_add_handle( $multi_curl, $curl_array[ $curl_index ] );

			$curl_index++;

		}

		// execute the multi handle
		$active = null;

		do {

			$status = curl_multi_exec( $multi_curl, $active );

			if ( $active ) {
				// Wait a short time for more activity
				curl_multi_select( $multi_curl );
			}       
		} while ( $active && $status == CURLM_OK );

		// close the handles
		for ( $i = 0; $i < $curl_index; $i++ ) {

			// Get the content of cURL request $curl_array[$i]
			if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->modules['logs']->add_log( 'cloudflare::purge_cache_urls_async', "Response for request {$i}: " . curl_multi_getcontent( $curl_array[ $i ] ) );
			}

			curl_multi_remove_handle( $multi_curl, $curl_array[ $i ] );

		}

		curl_multi_close( $multi_curl );

		// free up additional memory resources
		for ( $i = 0; $i < $curl_index; $i++ ) {
			curl_close( $curl_array[ $i ] );
		}

		return true;

	}


	function purge_cache_urls( $urls, &$error, $async = true ) {

		$this->modules = $this->main_instance->get_modules();

		do_action( 'swcfpc_cf_purge_cache_by_urls_before', $urls );

		$cf_headers           = $this->get_api_auth_args();
		$cf_headers['method'] = 'POST';

		if ( count( $urls ) > 30 ) {

			$this->purge_cache_urls_async( $urls );

			/*
			$chunks = array_chunk($urls, 30);

			foreach ($chunks as $single_chunk) {

				$cf_headers['body'] = json_encode(array('files' => array_values($single_chunk)));

				if ( is_object($this->objects['logs']) && $this->objects['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
					$this->objects['logs']->add_log('cloudflare::purge_cache_urls', 'Request URL: ' . esc_url_raw("https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache"));
					$this->objects['logs']->add_log('cloudflare::purge_cache_urls', 'Request Body: ' . json_encode(array('files' => $single_chunk)));
				}

				$response = wp_remote_post(
					esc_url_raw("https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache"),
					$cf_headers
				);

				if (is_wp_error($response)) {
					$error = __('Connection error: ', 'wp-cloudflare-page-cache') . $response->get_error_message();
					$this->objects['logs']->add_log('cloudflare::purge_cache_urls', "Error wp_remote_post: {$error}");
					return false;
				}

				$response_body = wp_remote_retrieve_body($response);

				if ( is_object($this->objects['logs']) && $this->objects['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
					$this->objects['logs']->add_log('cloudflare::purge_cache_urls', "Response: {$response_body}");
				}

				$json = json_decode($response_body, true);

				if ($json['success'] == false) {

					$error = array();

					foreach ($json['errors'] as $single_error) {
						$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
					}

					$error = implode(' - ', $error);

					return false;

				}

			}
			*/

		} else {

			$cf_headers['body'] = json_encode( [ 'files' => array_values( $urls ) ] );

			if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->modules['logs']->add_log( 'cloudflare::purge_cache_urls', 'Request URL: ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache" ) );
				$this->modules['logs']->add_log( 'cloudflare::purge_cache_urls', 'Request Body: ' . json_encode( [ 'files' => $urls ] ) );
			}

			$response = wp_remote_post(
				esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache" ),
				$cf_headers
			);

			if ( is_wp_error( $response ) ) {
				$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
				$this->modules['logs']->add_log( 'cloudflare::purge_cache_urls', "Error wp_remote_post: {$error}" );
				return false;
			}

			$response_body = wp_remote_retrieve_body( $response );

			if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
				$this->modules['logs']->add_log( 'cloudflare::purge_cache_urls', "Response: {$response_body}" );
			}

			$json = json_decode( $response_body, true );

			if ( $json['success'] == false ) {

				$error = [];

				foreach ( $json['errors'] as $single_error ) {
					$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
				}

				$error = implode( ' - ', $error );

				return false;

			}       
		}

		do_action( 'swcfpc_cf_purge_cache_by_urls_after', $urls );

		return true;

	}


	function get_account_ids( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$this->account_id_list = [];
		$cf_headers            = $this->get_api_auth_args();

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::get_account_ids', 'Request ' . esc_url_raw( 'https://api.cloudflare.com/client/v4/accounts?page=1&per_page=20&direction=desc' ) );
		}

		$response = wp_remote_get(
			esc_url_raw( 'https://api.cloudflare.com/client/v4/accounts?page=1&per_page=20&direction=desc' ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::get_account_ids', "Error wp_remote_get: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::get_account_ids', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) ) {

			foreach ( $json['result'] as $account_data ) {

				if ( ! isset( $account_data['id'] ) ) {
					$error = __( 'Unable to retrive account ID', 'wp-cloudflare-page-cache' );
					return false;
				}

				$this->account_id_list[] = [
					'id'   => $account_data['id'],
					'name' => $account_data['name'],
				];

			}       
		}

		return $this->account_id_list;

	}


	function get_current_account_id( &$error ) {

		$account_id = '';

		if ( count( $this->account_id_list ) == 0 ) {
			$this->get_account_ids( $error );
		}

		if ( count( $this->account_id_list ) == 0 ) {
			$this->modules['logs']->add_log( 'cloudflare::get_current_account_id', "Unable to retrive an account ID: {$error}" );
			return false;
		}

		if ( count( $this->account_id_list ) > 1 ) {

			foreach ( $this->account_id_list as $account_data ) {

				if ( strstr( strtolower( $account_data['name'] ), strtolower( $this->email ) ) !== false ) {
					$account_id = $account_data['id'];
					break;
				}           
			}       
		} else {
			$account_id = $this->account_id_list[0]['id'];
		}

		if ( $account_id == '' ) {
			$error = __( 'Unable to find a valid account ID.', 'wp-cloudflare-page-cache' );
			return false;
		}

		return $account_id;

	}


	function worker_get_list( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$workers_id_list = [];
		$cf_headers      = $this->get_api_auth_args();
		$account_id      = $this->get_current_account_id( $error );

		$this->modules['logs']->add_log( 'cloudflare::worker_get_list', "I'm using the account ID: {$account_id}" );

		$cloudflare_request_url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}/workers/scripts";

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_get_list', 'Request ' . esc_url_raw( $cloudflare_request_url ) );
		}

		$response = wp_remote_get(
			esc_url_raw( $cloudflare_request_url ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::worker_get_list', "Error wp_remote_get: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_get_list', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			$this->modules['logs']->add_log( 'cloudflare::worker_get_list', "Error: {$error}" );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) ) {

			foreach ( $json['result'] as $worker_data ) {

				if ( isset( $worker_data['id'] ) ) {
					$workers_id_list[] = $worker_data['id'];
				}           
			}       
		}

		return $workers_id_list;

	}


	function worker_upload( &$error ) {

		$this->modules = $this->main_instance->get_modules();
		$account_id    = $this->get_current_account_id( $error );

		$cf_headers                            = $this->get_api_auth_args();
		$cf_headers['method']                  = 'PUT';
		$cf_headers['headers']['Content-Type'] = 'application/javascript';
		$cf_headers['body']                    = $this->worker_content;

		$this->modules['logs']->add_log( 'cloudflare::worker_upload', "I'm using the account ID: {$account_id}" );

		$cloudflare_request_url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}/workers/scripts/{$this->worker_id}";

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_upload', 'Request ' . esc_url_raw( $cloudflare_request_url ) );
		}

		$response = wp_remote_post(
			esc_url_raw( $cloudflare_request_url ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::worker_upload', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_upload', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) && isset( $json['result']['id'] ) && $json['result']['id'] == $this->worker_id ) {
			return true;
		}

		return false;

	}


	function worker_delete( &$error ) {

		$this->modules = $this->main_instance->get_modules();
		$account_id    = $this->get_current_account_id( $error );

		$cf_headers           = $this->get_api_auth_args();
		$cf_headers['method'] = 'DELETE';

		$this->modules['logs']->add_log( 'cloudflare::worker_delete', "I'm using the account ID: {$account_id}" );

		$cloudflare_request_url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}/workers/scripts/{$this->worker_id}";

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_delete', 'Request ' . esc_url_raw( $cloudflare_request_url ) );
		}

		$response = wp_remote_post(
			esc_url_raw( $cloudflare_request_url ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::worker_delete', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_delete', "Response {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		return true;

	}


	function worker_route_create( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$cf_headers = $this->get_api_auth_args();
		$url        = $this->main_instance->home_url( '/*' );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_route_create', 'Request URL: ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/workers/routes" ) );
		}

		$cf_headers['method'] = 'POST';
		$cf_headers['body']   = json_encode(
			[
				'pattern' => $url,
				'script'  => $this->worker_id,
			] 
		);

		$response = wp_remote_post(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/workers/routes" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::worker_route_create', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_route_create', "Response: {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) && isset( $json['result']['id'] ) ) {
			return $json['result']['id'];
		}

		return false;

	}


	function worker_route_get_list( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$routes_list = [];
		$cf_headers  = $this->get_api_auth_args();

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_route_get_list', 'Request ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/workers/routes" ) );
		}

		$response = wp_remote_get(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/workers/routes" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::worker_route_get_list', "Error wp_remote_get: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_route_get_list', "Response {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			$this->modules['logs']->add_log( 'cloudflare::worker_route_get_list', "Error: {$error}" );

			return false;

		}

		if ( isset( $json['result'] ) && is_array( $json['result'] ) ) {

			foreach ( $json['result'] as $route_data ) {

				if ( isset( $route_data['id'] ) ) {
					$routes_list[ $route_data['id'] ] = [
						'pattern' => $route_data['pattern'],
						'script'  => $route_data['script'],
					];
				}           
			}       
		}

		return $routes_list;

	}


	function worker_route_delete( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$cf_headers           = $this->get_api_auth_args();
		$cf_headers['method'] = 'DELETE';

		if ( $this->worker_route_id == '' ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_route_delete', 'No route to delete' );
			return false;
		}

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_route_delete', 'Request ' . esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/workers/routes/{$this->worker_route_id}" ) );
		}

		$response = wp_remote_post(
			esc_url_raw( "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/workers/routes/{$this->worker_route_id}" ),
			$cf_headers
		);

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::worker_route_delete', "Error wp_remote_post: {$error}" );
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );

		if ( is_object( $this->modules['logs'] ) && $this->modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$this->modules['logs']->add_log( 'cloudflare::worker_route_delete', "Response {$response_body}" );
		}

		$json = json_decode( $response_body, true );

		if ( $json['success'] == false ) {

			$error = [];

			foreach ( $json['errors'] as $single_error ) {
				$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
			}

			$error = implode( ' - ', $error );

			return false;

		}

		$this->worker_route_id = '';

		return true;

	}


	function page_cache_test( $url, &$error, $test_static = false ) {

		$this->modules = $this->main_instance->get_modules();

		$args = [
			'timeout'    => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
			'sslverify'  => false,
			'user-agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:59.0) Gecko/20100101 Firefox/59.0',
			'headers'    => [
				'Accept' => 'text/html',
			],
		];

		$this->modules['logs']->add_log( 'cloudflare::page_cache_test', "Start test to {$url} with headers " . print_r( $args, true ) );

		// First test - Home URL
		$response = wp_remote_get( esc_url_raw( $url ), $args );

		if ( is_wp_error( $response ) ) {
			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();
			$this->modules['logs']->add_log( 'cloudflare::page_cache_test', "Error wp_remote_get: {$error}" );
			return false;
		}

		$headers = wp_remote_retrieve_headers( $response );

		if ( is_object( $this->modules['logs'] ) ) {
			$this->modules['logs']->add_log( 'cloudflare::page_cache_test', 'Response Headers: ' . var_export( $headers, true ) );
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

		if ( $this->worker_mode == true && ! isset( $headers['x-wp-cf-super-cache-worker-status'] ) ) {
			$error = __( 'Unable to find the X-WP-CF-Super-Cache-Worker-Status response header. Worker mode seems not working correctly.', 'wp-cloudflare-page-cache' );
			return false;
		}

		if ( $this->worker_mode == true && ( strcasecmp( $headers['x-wp-cf-super-cache-worker-status'], 'hit' ) == 0 || strcasecmp( $headers['x-wp-cf-super-cache-worker-status'], 'miss' ) == 0 ) ) {
			return true;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'HIT' ) == 0 || strcasecmp( $headers['CF-Cache-Status'], 'MISS' ) == 0 || strcasecmp( $headers['CF-Cache-Status'], 'EXPIRED' ) == 0 ) {
			return true;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'REVALIDATED' ) == 0 ) {
			$error = sprintf( __( 'Cache status: %s - The resource is served from cache but is stale. The resource was revalidated by either an If-Modified-Since header or an If-None-Match header.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );
			return false;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'UPDATING' ) == 0 ) {
			$error = sprintf( __( 'Cache status: %s - The resource was served from cache but is expired. The resource is currently being updated by the origin web server. UPDATING is typically seen only for very popular cached resources.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );
			return false;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'BYPASS' ) == 0 ) {
			$error = sprintf( __( 'Cache status: %s - Cloudflare has been instructed to not cache this asset. It has been served directly from the origin.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );
			return false;
		}

		if ( strcasecmp( $headers['CF-Cache-Status'], 'DYNAMIC' ) == 0 ) {

			$cookies = wp_remote_retrieve_cookies( $response );

			if ( ! empty( $cookies ) && count( $cookies ) > 1 ) {
				$error = sprintf( __( 'Cache status: %s - The resource was not cached by default and your current Cloudflare caching configuration doesn\'t instruct Cloudflare to cache the resource. Try to enable the <strong>Strip response cookies on pages that should be cached</strong> option and retry.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );
			} else {
				$error = sprintf( __( 'Cache status: %s - The resource was not cached by default and your current Cloudflare caching configuration doesn\'t instruct Cloudflare to cache the resource.  Instead, the resource was requested from the origin web server.', 'wp-cloudflare-page-cache' ), $headers['CF-Cache-Status'] );
			}

			return false;

		}

		$error = __( 'Undefined error', 'wp-cloudflare-page-cache' );

		return false;

	}


	function disable_page_cache( &$error ) {

		$error = '';

		$this->modules = $this->main_instance->get_modules();

		// Reset old browser cache TTL
		if ( $this->main_instance->get_single_config( 'cf_old_bc_ttl', 0 ) != 0 ) {
			$this->change_browser_cache_ttl( $this->main_instance->get_single_config( 'cf_old_bc_ttl', 0 ), $error );
		}

		if ( $this->worker_mode == true ) {

			$worker_route_ids = $this->worker_route_get_list( $error );

			if ( $worker_route_ids === false || ! is_array( $worker_route_ids ) ) {
				$this->modules['logs']->add_log( 'cloudflare::disable_page_cache', 'Unable to retrieve the worker routes list' );
				return false;
			}

			if ( isset( $worker_route_ids[ $this->worker_route_id ] ) ) {

				// Delete worker route
				if ( ! $this->worker_route_delete( $error ) ) {
					return false;
				}           
			} else {

				$this->modules['logs']->add_log( 'cloudflare::disable_page_cache', "Unable to find the route ID {$this->worker_route_id} in Cloudflare routes list, so I don't delete it: " . print_r( $worker_route_ids, true ) );

			}

			$worker_ids = $this->worker_get_list( $error );

			if ( $worker_ids && is_array( $worker_ids ) && in_array( $this->worker_id, $worker_ids ) ) {

				// Delete worker script
				if ( ! $this->worker_delete( $error ) ) {
					return false;
				}           
			} else {

				if ( is_array( $worker_ids ) ) {
					$this->modules['logs']->add_log( 'cloudflare::disable_page_cache', "Unable to find the worker ID {$this->worker_id} in Cloudflare workers list, so I don't delete it: " . print_r( $worker_ids, true ) );
				} else {
					$this->modules['logs']->add_log( 'cloudflare::disable_page_cache', 'Unable to find the worker ID to delete' );
				}           
			}       
		}


		// Delete page rules
		if ( $this->worker_mode == false && $this->main_instance->get_single_config( 'cf_page_rule_id', '' ) != '' && ! $this->delete_page_rule( $this->main_instance->get_single_config( 'cf_page_rule_id', '' ), $error ) ) {
			return false;
		} else {
			$this->main_instance->set_single_config( 'cf_page_rule_id', '' );
		}

		if ( $this->worker_mode == false && $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule_id', '' ) != '' && ! $this->delete_page_rule( $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule_id', '' ), $error ) ) {
			return false;
		} else {
			$this->main_instance->set_single_config( 'cf_bypass_backend_page_rule_id', '' );
		}

		if ( $this->delete_cache_rule() ) {
			$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', '' );
		}
		$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_id', '' );

		// Purge cache
		$this->purge_cache( $error );

		// Reset htaccess
		$this->modules['cache_controller']->reset_htaccess();

		$this->main_instance->set_single_config( 'cf_woker_route_id', '' );
		$this->main_instance->set_single_config( 'cf_cache_enabled', 0 );
		$this->main_instance->update_config();

		return true;
	}

	function delete_legacy_page_rules( &$error ) {
		// Delete page rule.
		$page_rule_id = $this->main_instance->get_single_config( 'cf_page_rule_id', '' );
		if ( ! empty( $page_rule_id ) && $this->delete_page_rule( $page_rule_id, $error_msg ) ) {
			$this->main_instance->set_single_config( 'cf_page_rule_id', '' );
		}

		// Delete the legacy backend bypass page rule.
		$legacy_page_rule_id = $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule_id', '' );
		if ( ! empty( $legacy_page_rule_id ) && $this->delete_page_rule( $legacy_page_rule_id, $error_msg ) ) {
			$this->main_instance->set_single_config( 'cf_bypass_backend_page_rule_id', '' );
		}
	}

	function enable_page_cache( &$error ) {

		$this->modules = $this->main_instance->get_modules();

		$current_cf_browser_ttl = $this->get_current_browser_cache_ttl( $error );

		if ( $current_cf_browser_ttl !== false ) {
			$this->main_instance->set_single_config( 'cf_old_bc_ttl', $current_cf_browser_ttl );
		}

		// Step 1 - set browser cache ttl to zero (Respect Existing Headers)
		if ( ! $this->change_browser_cache_ttl( 0, $error ) ) {
			$this->main_instance->set_single_config( 'cf_cache_enabled', 0 );
			$this->main_instance->update_config();
			return false;
		}

		// Step 2 - Delete the current cache configuration and page rule.
		$this->delete_legacy_page_rules( $error );


		// Get existing cache ruleset id.
		if ( empty( $this->cache_ruleset_id ) ) {
			$this->cache_ruleset_id = $this->get_ruleset_id_from_api();
			if ( $this->cache_ruleset_id ) {
				$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_id', $this->cache_ruleset_id );
			}
		}

		// Delete the existing cache rule (stored or fresly retrieved from Ruleset API)
		if ( $this->delete_cache_rule() ) {
			$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', '' );
		}

		if ( $this->worker_mode == true ) {

			$worker_route_ids = $this->worker_route_get_list( $error );

			if ( $worker_route_ids === false || ! is_array( $worker_route_ids ) ) {
				$this->modules['logs']->add_log( 'cloudflare::enable_page_cache', 'Unable to retrieve the worker routes list' );
				return false;
			}

			$worker_ids = $this->worker_get_list( $error );

			// Delete existing route
			if ( isset( $worker_route_ids[ $this->worker_route_id ] ) ) {

				$this->modules['logs']->add_log( 'cloudflare::enable_page_cache', "I'm deleting existing route ID {$this->worker_route_id}" );

				if ( ! $this->worker_route_delete( $error ) ) {
					return false;
				}           
			}

			// Delete existing worker
			if ( $worker_ids && is_array( $worker_ids ) && in_array( $this->worker_id, $worker_ids ) ) {

				$this->modules['logs']->add_log( 'cloudflare::enable_page_cache', "I'm deleting existing worker ID {$this->worker_id}" );

				// Delete worker script
				if ( ! $this->worker_delete( $error ) ) {
					return false;
				}           
			}


			// Step 3a - upload worker
			if ( ! $this->worker_upload( $error ) ) {

				$this->main_instance->set_single_config( 'cf_cache_enabled', 0 );

				$return_array['status'] = 'error';
				$return_array['error']  = $error;
				die( json_encode( $return_array ) );

			}

			// Step 3b - create route
			$this->worker_route_id = $this->worker_route_create( $error );

			if ( ! $this->worker_route_id ) {

				$this->worker_delete( $error );

				$this->main_instance->set_single_config( 'cf_cache_enabled', 0 );
				$this->main_instance->update_config();

				return false;

			}

			$this->main_instance->set_single_config( 'cf_woker_id', $this->worker_id );
			$this->main_instance->set_single_config( 'cf_woker_route_id', $this->worker_route_id );

		} else {

			// Step 3a - create a new cache ruleset if it does not exist.
			if ( empty( $this->cache_ruleset_id ) ) {
				$this->cache_ruleset_id = $this->create_ruleset_id( $this->zone_id );
				if ( ! empty( $this->cache_ruleset_id ) ) {
					$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_id', $this->cache_ruleset_id );
				}
			}

			// Setp 3b - create a standard rule for the cache ruleset.
			if ( ! empty( $this->cache_ruleset_id ) ) {
				$this->cache_ruleset_rule_id = $this->apply_standard_rules();
				$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', $this->cache_ruleset_rule_id );
			}
		}

		// Update config data
		$this->main_instance->update_config();

		// Step 4 - purge cache
		$this->purge_cache( $error );

		$this->main_instance->set_single_config( 'cf_cache_enabled', 1 );
		$this->main_instance->update_config();

		$this->modules['cache_controller']->write_htaccess( $error );

		return true;

	}

	/**
	 * Get the existing ruleset ID for the current zone.
	 *
	 * @return mixed|string
	 *
	 * @see https://developers.cloudflare.com/api/operations/getZoneEntrypointRuleset
	 */
	function get_ruleset_id_from_api() {
		if ( empty( $this->zone_id ) ) {
			return '';
		}

		$modules      = $this->main_instance->get_modules();
		$url          = 'https://api.cloudflare.com/client/v4/zones/' . $this->zone_id . '/rulesets/phases/http_request_cache_settings/entrypoint';
		$request_args = $this->get_api_auth_args();
		$response     = wp_remote_get( $url, $request_args );

		if ( is_wp_error( $response ) ) {
			if ( is_object( $modules['logs'] ) ) {
				$modules['logs']->add_log( 'cloudflare::get_ruleset_id_from_api', 'Connection error: ' . $response->get_error_message() );
			}
			return '';
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );


		if ( isset( $response['success'] ) && ! $response['success'] ) {

			if ( is_object( $modules['logs'] ) ) {
				$modules['logs']->add_log( 'cloudflare::get_ruleset_id_from_api', "Could NOT retrieve rulesets ID for zone {$this->zone_id} - URL: " . esc_url_raw( $url ) );
			}
			$this->try_log_error( $response );

			return '';
		}

		if ( is_object( $modules['logs'] ) && $modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$modules['logs']->add_log( 'cloudflare::get_ruleset_id_from_api', "Retrieved rulesets ID for zone {$this->zone_id} - URL: " . esc_url_raw( $url ) );
		}

		if ( isset( $response['result']['id'] ) ) {
			$this->last_cache_ruleset_options_retrieved = $response['result'];
			return $response['result']['id'];
		}

		return '';
	}

	/**
	 * Create a new ruleset ID for the current zone.
	 *
	 * @param string $zone_id The zone ID.
	 * @return mixed|string
	 *
	 * @see https://developers.cloudflare.com/api/operations/createZoneRuleset
	 */
	function create_ruleset_id( $zone_id ) {

		if ( empty( $zone_id ) ) {
			return '';
		}

		$modules              = $this->main_instance->get_modules();
		$url                  = 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/rulesets';
		$request_args         = $this->get_api_auth_args();
		$request_args['body'] = json_encode( $this->cache_ruleset_options );

		$response = wp_remote_post( $url, $request_args );

		if ( is_wp_error( $response ) ) {
			if ( is_object( $modules['logs'] ) ) {
				$modules['logs']->add_log( 'cloudflare::create_ruleset_id', 'Connection error: ' . $response->get_error_message() );
			}
			return '';
		}

		$response   = json_decode( wp_remote_retrieve_body( $response ), true );
		$is_success = isset( $response['success'] ) && $response['success'];

		if ( ! $is_success ) {

			if ( is_object( $modules['logs'] ) ) {
				$modules['logs']->add_log( 'cloudflare::create_ruleset_id', "Could NOT create rulesets ID for zone {$zone_id} - URL: " . esc_url_raw( $url ) );
			}
			$this->try_log_error( $response );

			return '';
		}

		if ( is_object( $modules['logs'] ) && $modules['logs']->get_verbosity() === SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$modules['logs']->add_log( 'cloudflare::create_ruleset_id', "Created the rulesets ID for zone {$zone_id} - URL: " . esc_url_raw( $url ) );
		}

		if ( isset( $response['result']['id'] ) ) {
			$this->last_cache_ruleset_options_retrieved = $response['result'];
			return $response['result']['id'];
		}

		return '';
	}

	/**
	 * Apply the standard WP rules to the ruleset.
	 *
	 * @return string The created rule ID. Empty string if the rule was not created.
	 */
	function apply_standard_rules() {
		if ( empty( $this->zone_domain_name ) ) {
			return '';
		}

		$modules = $this->main_instance->get_modules();

		if ( is_object( $modules['logs'] ) && $modules['logs']->get_verbosity() == SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$modules['logs']->add_log( 'cloudflare::apply_standard_rules', 'Trying to apply on ' . $this->zone_domain_name . ' the standard rules.' );
		}

		return $this->create_cache_rule( $this->get_cache_rule_expression() );
	}

	/**
	 * Get the cache rule expression.
	 *
	 * @return string
	 */
	function get_cache_rule_expression() {
		$url = preg_replace( '#^(https?://)?#', '', $this->main_instance->home_url() );

		// Rule expression reference: https://gist.github.com/isaumya/af10e4855ac83156cc210b7148135fa2
		$expression = 'http.host wildcard "' . $url . '*"';

		// Clean up the expression so that the Cloudflare Expression Builder UI can understand it.
		$expression = str_replace( [ "\n", "\r", "\t" ], '', $expression ); // Remove new lines, tabs.
		$expression = preg_replace( '/\s+/', ' ', $expression ); // Remove multiple spaces.
		$expression = '(' . trim( $expression ) . ')';

		return $expression;
	}

	/**
	 * Create a cache rule in the Cloudflare API.
	 *
	 * @param string $expression The expression for the rule.
	 *
	 * @return string The created rule ID. Empty string if the rule was not created.
	 *
	 * @see https://developers.cloudflare.com/api/operations/createZoneRulesetRule
	 */
	function create_cache_rule( $expression ) {
		if ( empty( $this->zone_id ) || empty( $this->cache_ruleset_id ) ) {
			return false;
		}

		$rule = [
			'action'            => 'set_cache_settings',
			'action_parameters' => [
				'cache' => true,
			],
			'description'       => $this->cache_rule_description,
			'enabled'           => true,
			'expression'        => $expression,
		];

		$url                  = 'https://api.cloudflare.com/client/v4/zones/' . $this->zone_id . '/rulesets/' . $this->cache_ruleset_id . '/rules';
		$request_args         = $this->get_api_auth_args();
		$request_args['body'] = json_encode( $rule );

		$response = wp_remote_post( $url, $request_args );

		$modules = $this->main_instance->get_modules();

		if ( is_wp_error( $response ) ) {
			if ( is_object( $modules['logs'] ) ) {
				$modules['logs']->add_log( 'cloudflare::create_cache_rule', 'Error while creating the rule for ' . $this->zone_domain_name . ': ' . $response->get_error_message() );
			}
			return '';
		}

		$response   = json_decode( wp_remote_retrieve_body( $response ), true );
		$is_success = isset( $response['success'] ) && $response['success'] && isset( $response['result'] ) && is_array( $response['result'] ) && ! empty( $response['result'] );

		if ( is_object( $modules['logs'] ) ) {
			if ( $is_success ) {
				$modules['logs']->add_log( 'cloudflare::create_cache_rule', 'Rule created for ' . $this->zone_domain_name . ': ' . json_encode( $response ) );
			} else {
				$modules['logs']->add_log( 'cloudflare::create_cache_rule', 'Could not create the rule for ' . $this->zone_domain_name . ' on ruleset ' . $this->cache_ruleset_id );
				$this->try_log_error( $response );
			}
		}

		if ( $is_success ) {
			$this->last_cache_ruleset_options_retrieved = $response['result'];

			$available_rule = $this->find_cache_rule_by_description( $this->last_cache_ruleset_options_retrieved['rules'], $this->cache_rule_description );
			if ( $available_rule ) {
				return $available_rule['id'];
			}
		}

		return '';
	}

	/**
	 * Find a cache rule by its description.
	 *
	 * @param array  $rules       The ruleset rules.
	 * @param string $description The description to search for.
	 *
	 * @return array|bool The rule if found, false otherwise.
	 */
	function find_cache_rule_by_description( $rules, $description ) {
		if ( empty( $rules ) || empty( $description ) ) {
			return false;
		}

		foreach ( $rules as $rule ) {
			if ( strpos( $rule['description'], $description ) !== false ) {
				return $rule;
			}
		}
		return false;
	}

	/**
	 * Delete a cache rule in the Cloudflare API.
	 *
	 * If it is empty, it will try to use the last retrieved cache ruleset options.
	 *
	 * @return bool
	 *
	 * @see https://developers.cloudflare.com/api/operations/deleteZoneRulesetRule
	 */
	function delete_cache_rule() {
		if ( empty( $this->zone_id ) || empty( $this->cache_ruleset_id ) ) {
			return false;
		}

		if (
			empty( $this->cache_ruleset_rule_id ) &&
			! empty( $this->last_cache_ruleset_options_retrieved ) &&
			! empty( $this->last_cache_ruleset_options_retrieved['rules'] )
		) {
			$available_rule = $this->find_cache_rule_by_description( $this->last_cache_ruleset_options_retrieved['rules'], $this->cache_rule_description );
			if ( $available_rule ) {
				$this->cache_ruleset_rule_id = $available_rule['id'];
			}
		}

		if ( empty( $this->cache_ruleset_rule_id ) ) {
			return false;
		}

		$url                    = 'https://api.cloudflare.com/client/v4/zones/' . $this->zone_id . '/rulesets/' . $this->cache_ruleset_id . '/rules/' . $this->cache_ruleset_rule_id;
		$request_args           = $this->get_api_auth_args();
		$request_args['method'] = 'DELETE';

		$response = wp_remote_request( $url, $request_args );

		$modules = $this->main_instance->get_modules();

		if ( is_wp_error( $response ) ) {
			if ( is_object( $modules['logs'] ) ) {
				$modules['logs']->add_log( 'cloudflare::delete_cache_rule', 'Error while deleting the rule for ' . $this->zone_domain_name . ': ' . $response->get_error_message() );
			}
			return false;
		}

		$is_success = wp_remote_retrieve_response_code( $response ) == 204;

		if ( is_object( $modules['logs'] ) ) {
			if ( $is_success ) {
				$modules['logs']->add_log( 'cloudflare::delete_cache_rule', 'Rule ' . $this->cache_ruleset_rule_id . ' deleted for ' . $this->zone_domain_name . ': ' . json_encode( $response ) );
			} else {
				$modules['logs']->add_log( 'cloudflare::delete_cache_rule', 'Could NOT delete the rule ' . $this->cache_ruleset_rule_id . ' for ' . $this->zone_domain_name . ' on ruleset ' . $this->cache_ruleset_id );
			}
		}

		if ( $is_success ) {
			$this->cache_ruleset_rule_id = '';
		}

		return $is_success;
	}

	/**
	 * Delete the page rule in the Cloudflare API.
	 *
	 * @return void
	 */
	public function disable_page_cache_rule() {
		$this->delete_cache_rule();
		$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', '' );
	}

	/**
	 * Try to log out the error message from the Cloudflare API response.
	 *
	 * @param array $response_body The response body of Cloudflare API.
	 * @return void
	 */
	function try_log_error( $response_body ) {
		if (
			( isset( $response_body['success'] ) && $response_body['success'] ) ||
			! isset( $response_body['errors'] ) ||
			! is_array( $response_body['errors'] ) ||
			! is_object( $this->modules['logs'] )
		) {
			return;
		}

		$error = [];
		foreach ( $response_body['errors'] as $single_error ) {
			$error[] = "{$single_error['message']} (err code: {$single_error['code']})";
		}
		$error = implode( ' - ', $error );

		$this->modules['logs']->add_log( 'cloudflare::try_log_error', "Cloudflare API Errors: {$error}" );
	}

	/**
	 * Pull the existing cache rule from the Cloudflare API if it is not set.
	 *
	 * @param bool $auto_save If true, it will save the cache rule ID. But not commit the changes.
	 *
	 * @return void
	 */
	public function pull_existing_cache_rule( $auto_save = true ) {
		if ( empty( $this->zone_id ) ) {
			return;
		}

		$this->cache_ruleset_id = empty( $this->cache_ruleset_id ) ? $this->get_ruleset_id_from_api() : $this->cache_ruleset_id;

		if (
			! empty( $this->cache_ruleset_rule_id ) ||
			empty( $this->last_cache_ruleset_options_retrieved ) ||
			empty( $this->last_cache_ruleset_options_retrieved['rules'] )
		) {
			return;
		}

		if ( $auto_save ) {
			$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_id', $this->cache_ruleset_id );
		}

		$available_rule = $this->find_cache_rule_by_description( $this->last_cache_ruleset_options_retrieved['rules'], $this->cache_rule_description );
		if ( empty( $available_rule ) ) {
			return;
		}

		$this->cache_ruleset_rule_id = $available_rule['id'];
		if ( $auto_save ) {
			$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', $this->cache_ruleset_rule_id );
		}
	}
}
