<?php

namespace SPC\Services;

use SPC\Builders\Cache_Rule;

class Cloudflare_Client extends Cloudflare_Rule {
	/**
	 * Account IDs list.
	 *
	 * @var array
	 */
	private $account_ids = [];

	/**
	 * Get the ruleset ID setting slug.
	 *
	 * @return string
	 */
	public function get_ruleset_id_setting_slug(): string {
		return 'cf_cache_settings_ruleset_id';
	}

	/**
	 * Get the rule ID setting slug.
	 *
	 * @return string
	 */
	public function get_rule_id_setting_slug(): string {
		return 'cf_cache_settings_ruleset_rule_id';
	}

	/**
	 * Get the rule arguments to be used in the API request.
	 *
	 * @return array
	 */
	protected function get_rule_args() : array {
		return [
			'action'            => 'set_cache_settings',
			'action_parameters' => [
				'cache'       => true,
				'browser_ttl' => array(
					'mode' => 'respect_origin',
				),
			],
			'description'       => $this->build_rule_description(),
			'enabled'           => true,
			'expression'        => $this->get_rule_expression(),
		];
	}

	/**
	 * Get the rule expression.
	 *
	 * @return string
	 */
	public function get_rule_expression() {
		$builder = new Cache_Rule( $this->plugin );

		return $builder->exclude_cookies()
					   ->exclude_paths()
					   ->exclude_static_content()
					   ->build();
	}

	/**
	 * Get the current browser cache TTL value from Cloudflare.
	 *
	 * @param string $error The error message.
	 *
	 * @return false|mixed
	 */
	public function get_current_browser_cache_ttl( &$error = '' ) {
		$args     = $this->get_api_auth_args();
		$url      = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/settings/browser_cache_ttl', $this->plugin->get_cloudflare_api_zone_id() );
		$response = wp_remote_get( $url, $args );

		if ( ! $this->is_success_api_response( $response, 'get_current_browser_cache_ttl', $error ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( is_array( $response_body ) && isset( $response_body['result']['value'] ) ) {
			return $response_body['result']['value'];
		}

		$error = __( 'Unable to find Browser Cache TTL settings ', 'wp-cloudflare-page-cache' );

		return false;
	}

	/**
	 * Change the browser cache TTL value.
	 *
	 * @param int $ttl The new TTL value.
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function change_browser_cache_ttl( $ttl, &$error = '' ) {
		$url            = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/settings/browser_cache_ttl', $this->plugin->get_cloudflare_api_zone_id() );
		$args           = $this->get_api_auth_args();
		$args['method'] = 'PATCH';
		$args['body']   = wp_json_encode( [ 'value' => $ttl ] );

		$this->log( 'change_browser_cache_ttl', sprintf( 'Request URL: %s', esc_url_raw( $url ) ) );
		$this->log( 'change_browser_cache_ttl', sprintf( 'Request body: %s', wp_json_encode( [ 'value' => $ttl ] ) ) );

		$response = wp_remote_post( $url, $args );

		return $this->is_success_api_response( $response, 'change_browser_cache_ttl', $error );
	}

	/**
	 * Delete a page rule.
	 *
	 * @param string $id The page rule ID.
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function delete_cache_rule( &$error ) {
		$rule    = $this->get_rule_id();
		$ruleset = $this->get_ruleset_id();

		if ( empty( $rule ) || empty( $ruleset ) ) {
			$this->log( 'delete_cache_rule', 'Could NOT delete cache rule. No ruleset or rule defined.' );

			return false;
		}

		$url            = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/rulesets/%s/rules/%s', $this->plugin->get_cloudflare_api_zone_id(), $ruleset, $rule );
		$args           = $this->get_api_auth_args();
		$args['method'] = 'DELETE';

		$response = wp_remote_request( $url, $args );


		$this->log( 'delete_cache_rule', sprintf( 'Request URL: %s', esc_url_raw( $url ) ) );

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( ! $this->is_success_api_response( $response, 'delete_cache_rule', $error ) ) {
			if ( $response_code === 404 ) {
				$this->rule_id = '';

				return true;
			}

			return false;
		}

		$this->rule_id        = '';
		$this->cached_ruleset = [];

		return 200 === $response_code;
	}

	/**
	 * Delete the Page Rule.
	 * 
	 * @deprecated - The page rule is not used anymore.
	 */
	public function delete_page_rule( $id, &$error = '' ) {
		if ( ! $this->plugin->has_cloudflare_api_zone_id() ) {
			$error = __( 'There is not zone id to use', 'wp-cloudflare-page-cache' );

			return false;
		}

		$url            = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/pagerules/%s', $this->plugin->get_cloudflare_api_zone_id(), $id );
		$args           = $this->get_api_auth_args();
		$args['method'] = 'DELETE';

		$response = wp_remote_post( $url, $args );

		return $this->is_success_api_response( $response, 'delete_page_rule', $error );
	}

	/**
	 * Get the account IDs list.
	 *
	 * @param string $error The error message.
	 *
	 * @return array
	 */
	private function get_account_ids( &$error = '' ) {
		$this->account_ids = [];

		$args = $this->get_api_auth_args();

		$response = wp_remote_get( 'https://api.cloudflare.com/client/v4/accounts?page=1&per_page=20&direction=desc', $args );

		if ( ! $this->is_success_api_response( $response, 'get_account_ids', $error ) ) {
			return [];
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $response_body ) || ! isset( $response_body['result'] ) || ! is_array( $response_body['result'] ) ) {
			$error = __( 'Unable to retrieve account ID', 'wp-cloudflare-page-cache' );

			return [];
		}

		foreach ( $response_body['result'] as $account_data ) {
			if ( ! isset( $account_data['id'] ) ) {
				$error = __( 'Unable to retrieve account ID', 'wp-cloudflare-page-cache' );

				continue;
			}

			$this->account_ids[] = [
				'id'   => $account_data['id'],
				'name' => $account_data['name'],
			];
		}

		return $this->account_ids;
	}

	/**
	 * Get the Cloudflare account ID.
	 *
	 * @param string $error The error message.
	 *
	 * @return string
	 */
	public function get_account_id( &$error = '' ) {
		$account_id = '';

		if ( empty( $this->account_ids ) ) {
			$this->get_account_ids( $error );
		}

		if ( empty( $this->account_ids ) ) {
			$error = __( 'Unable to retrive account ID', 'wp-cloudflare-page-cache' );

			$this->log( 'get_current_account_id', sprintf( 'Unable to retrive an account ID: %s', $error ) );

			return '';
		}


		if ( count( $this->account_ids ) > 1 ) {
			foreach ( $this->account_ids as $account_data ) {
				if ( strstr( strtolower( $account_data['name'] ), strtolower( $this->plugin->get_cloudflare_api_email() ) ) !== false ) {
					$account_id = $account_data['id'];

					break;
				}
			}
		} else {
			$account_id = $this->account_ids[0]['id'];
		}

		if ( empty( $account_id ) ) {
			$error = __( 'Unable to find a valid account ID.', 'wp-cloudflare-page-cache' );

			return '';
		}

		return $account_id;
	}

	/**
	 * Get the zone ID list.
	 *
	 * @param string $error
	 *
	 * @return array | false
	 */
	public function get_zone_id_list( &$error = '', $forced_api_domain = '' ) {
		$list         = [];
		$per_page     = 50;
		$current_page = 1;
		$pagination   = false;
		$args         = $this->get_api_auth_args();

		$api_token_domain = ! empty( $forced_api_domain ) ? $forced_api_domain : $this->plugin->get_second_level_domain();

		do {

			if ( $this->is_token_auth() && ! empty( $api_token_domain ) ) {
				$url = sprintf( 'https://api.cloudflare.com/client/v4/zones?name=%s', $api_token_domain );
			} else {
				$url = sprintf( 'https://api.cloudflare.com/client/v4/zones?page=%s&per_page=%s', $current_page, $per_page );
			}

			$this->log( 'get_zone_id_list', sprintf( 'Request for page %s - URL: %s', $current_page, $url ) );

			$response = wp_remote_get( $url, $args );

			if ( ! $this->is_success_api_response( $response, 'get_zone_id_list', $error ) ) {
				return false;
			}

			$response_body = wp_remote_retrieve_body( $response );

			$this->log( 'get_zone_id_list', sprintf( 'Response for page %s: %s', $current_page, $response_body ) );

			$json = json_decode( $response_body, true );

			if ( ! is_array( $json ) ) {
				$error = __( 'Unable to retrieve zone id due to invalid response data', 'wp-cloudflare-page-cache' );

				return false;
			}

			if ( isset( $json['result_info'] ) && is_array( $json['result_info'] ) ) {

				if ( isset( $json['result_info']['total_pages'] ) && (int) $json['result_info']['total_pages'] > $current_page ) {
					$pagination = true;
					$current_page ++;
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
						$error = __( 'Unable to retrieve zone id due to invalid response data', 'wp-cloudflare-page-cache' );

						return false;
					}

					$list[ $domain_data['name'] ] = $domain_data['id'];

				}
			}
		} while ( $pagination );


		if ( empty( $list ) ) {
			$error = __( 'Unable to find domains configured on Cloudflare', 'wp-cloudflare-page-cache' );

			return false;
		}

		return $list;

	}

	/**
	 * Purge the whole cache.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function purge_cache( &$error = '' ) {
		do_action( 'swcfpc_cf_purge_whole_cache_before' );

		$args           = $this->get_api_auth_args();
		$args['method'] = 'POST';
		$args['body']   = json_encode( [ 'purge_everything' => true ] );
		$url            = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $this->plugin->get_cloudflare_api_zone_id() );
		$response       = wp_remote_post( $url, $args );

		if ( ! $this->is_success_api_response( $response, 'purge_cache', $error ) ) {
			return false;
		}

		do_action( 'swcfpc_cf_purge_whole_cache_after' );

		return true;
	}

	/**
	 * Purge URLs from Cloudflare cache asynchronously.
	 *
	 * @param array $urls URLs to purge.
	 *
	 * @return true
	 */
	public function purge_cache_urls_async( $urls ) {
		$args = $this->get_api_auth_args( true );

		$chunks     = array_chunk( $urls, 30 );
		$multi_curl = curl_multi_init();
		$curl_array = [];
		$curl_index = 0;

		foreach ( $chunks as $single_chunk ) {
			$curl_array[ $curl_index ] = curl_init();

			curl_setopt_array(
				$curl_array[ $curl_index ],
				[
					CURLOPT_URL            => "https://api.cloudflare.com/client/v4/zones/{$this->plugin->get_cloudflare_api_zone_id()}/purge_cache",
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_MAXREDIRS      => 10,
					CURLOPT_TIMEOUT        => $args['timeout'],
					CURLOPT_CUSTOMREQUEST  => 'POST',
					CURLOPT_POST           => 1,
					CURLOPT_HTTPHEADER     => $args['headers'],
					CURLOPT_POSTFIELDS     => json_encode( [ 'files' => array_values( $single_chunk ) ] ),
				]
			);

			curl_multi_add_handle( $multi_curl, $curl_array[ $curl_index ] );

			$curl_index ++;
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
		for ( $i = 0; $i < $curl_index; $i ++ ) {
			// Get the content of cURL request $curl_array[$i]
			$this->log( 'purge_cache_urls_async', "Response for request {$i}: " . curl_multi_getcontent( $curl_array[ $i ] ) );

			curl_multi_remove_handle( $multi_curl, $curl_array[ $i ] );
		}

		curl_multi_close( $multi_curl );

		// free up additional memory resources
		for ( $i = 0; $i < $curl_index; $i ++ ) {
			curl_close( $curl_array[ $i ] );
		}

		return true;
	}

	/**
	 * Purge cache URLs.
	 *
	 * @param array $urls URLs to purge.
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function purge_cache_urls( $urls, &$error = '' ) {
		do_action( 'swcfpc_cf_purge_cache_by_urls_before', $urls );

		if ( count( $urls ) > 30 ) {
			$this->purge_cache_urls_async( $urls );
		} else {
			$url            = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/purge_cache', $this->plugin->get_cloudflare_api_zone_id() );
			$args           = $this->get_api_auth_args();
			$args['method'] = 'POST';
			$args['body']   = json_encode( [ 'files' => array_values( $urls ) ] );

			$this->log( 'purge_cache_urls', sprintf( 'Request URL: %s', $url ) );
			$this->log( 'purge_cache_urls', sprintf( 'Request Body: %s', $args['body'] ) );

			$response = wp_remote_post( $url, $args );

			if ( ! $this->is_success_api_response( $response, 'purge_cache_urls', $error ) ) {
				return false;
			}
		}

		do_action( 'swcfpc_cf_purge_cache_by_urls_after', $urls );

		return true;
	}

	/**
	 * Get the workers IDs list.
	 *
	 * @param $error
	 *
	 * @return array|false
	 */
	public function get_worker_list( &$error = '' ) {
		$account_id = $this->get_account_id( $error );
		$url        = sprintf( 'https://api.cloudflare.com/client/v4/accounts/%s/workers/scripts', $account_id );

		$this->log( 'get_worker_list', sprintf( 'I\'m using the account ID: %s', $account_id ), false );
		$this->log( 'get_worker_list', sprintf( 'Request URL: %s', $url ), false );

		$response = wp_remote_get( esc_url_raw( $url ), $this->get_api_auth_args() );

		if ( ! $this->is_success_api_response( $response, 'get_worker_list', $error ) ) {
			return [];
		}

		$response_body   = json_decode( wp_remote_retrieve_body( $response ), true );
		$workers_id_list = [];

		if ( isset( $response_body['result'] ) && is_array( $response_body['result'] ) ) {
			foreach ( $response_body['result'] as $worker_data ) {
				if ( isset( $worker_data['id'] ) ) {
					$workers_id_list[] = $worker_data['id'];
				}
			}
		}

		return $workers_id_list;
	}

	/**
	 * Upload the worker to Cloudflare.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function upload_worker( &$error = '' ) {
		$account_id = $this->get_account_id( $error );

		$this->log( 'upload_worker', sprintf( 'I\'m using the account ID: %s', $account_id ), false );

		$args                            = $this->get_api_auth_args();
		$args['body']                    = $this->plugin->get_cloudflare_worker_content();
		$args['method']                  = 'PUT';
		$args['headers']['Content-Type'] = 'application/javascript';

		$worker_id = $this->plugin->get_cloudflare_worker_id();
		$url       = sprintf( 'https://api.cloudflare.com/client/v4/accounts/%s/workers/scripts/%s', $account_id, $worker_id );

		$this->log( 'upload_worker', sprintf( 'Request URL: %s', $url ) );

		$response = wp_remote_post( $url, $args );

		if ( ! $this->is_success_api_response( $response, 'upload_worker', $error ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		return ( isset( $response_body['result']['id'] ) && $response_body['result']['id'] === $worker_id );
	}

	/**
	 * Delete Cloudflare worker.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function delete_worker( &$error = '' ) {
		$account_id = $this->get_account_id( $error );

		$this->log( 'delete_worker', sprintf( 'I\'m using the account ID: %s', $account_id ), false );

		$args           = $this->get_api_auth_args();
		$args['method'] = 'DELETE';
		$url            = sprintf( 'https://api.cloudflare.com/client/v4/accounts/%s/workers/scripts/%s', $account_id, $this->plugin->get_cloudflare_worker_id() );

		$response = wp_remote_post( esc_url_raw( $url ), $args );

		return $this->is_success_api_response( $response, 'delete_worker', $error );
	}

	/**
	 * Create the worker route.
	 *
	 * @param string $error The error message.
	 *
	 * @return false|string
	 */
	public function create_worker_route( &$error = '' ) {
		$account_id = $this->get_account_id( $error );

		$this->log( 'create_worker_route', sprintf( 'I\'m using the account ID: %s', $account_id ), false );

		$home_url = preg_replace( '#^(https?://)?#', '', $this->plugin->home_url() );

		$args           = $this->get_api_auth_args();
		$args['method'] = 'POST';
		$args['body']   = json_encode(
			[
				//You can manually override the pattern for testing locally with a remote domain.
				//'pattern' => 'test-domain.com/*',
				'pattern' => $home_url . '/*',
				'script'  => $this->plugin->get_cloudflare_worker_id(),
			]
		);

		$url      = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/workers/routes', $this->plugin->get_cloudflare_api_zone_id() );
		$response = wp_remote_post( esc_url_raw( $url ), $args );

		if ( ! $this->is_success_api_response( $response, 'create_worker_route', $error ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		return is_array( $response_body ) && isset( $response_body['result']['id'] ) ? $response_body['result']['id'] : false;
	}

	/**
	 * Get the worker routes list.
	 *
	 * @param string $error The error message.
	 *
	 * @return array|false
	 */
	public function get_worker_routes_list( &$error = '' ) {
		$args = $this->get_api_auth_args();
		$url  = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/workers/routes', $this->plugin->get_cloudflare_api_zone_id() );

		$response = wp_remote_get( esc_url_raw( $url ), $args );

		if ( ! $this->is_success_api_response( $response, 'get_worker_routes_list', $error ) ) {
			return false;
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		$routes_list   = [];

		if ( ! is_array( $response_body ) || ! isset( $response_body['result'] ) || ! is_array( $response_body['result'] ) ) {
			return false;
		}

		foreach ( $response_body['result'] as $route_data ) {
			if ( ! isset( $route_data['id'], $route_data['pattern'], $route_data['script'] ) ) {
				continue;
			}
			$routes_list[ $route_data['id'] ] = [
				'pattern' => $route_data['pattern'],
				'script'  => $route_data['script'],
			];
		}

		return $routes_list;
	}

	/**
	 * Delete worker route.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function delete_worker_route( &$error = '' ) {
		$route_id = $this->plugin->get_cloudflare_worker_route_id();

		if ( empty( $route_id ) ) {
			$this->log( 'delete_worker_route', 'No route to delete', false );

			return false;
		}

		$args           = $this->get_api_auth_args();
		$args['method'] = 'DELETE';

		$url = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/workers/routes/%s', $this->plugin->get_cloudflare_api_zone_id(), $route_id );

		$response = wp_remote_post( esc_url_raw( $url ), $args );

		return $this->is_success_api_response( $response, 'delete_worker_route', $error );
	}
}
