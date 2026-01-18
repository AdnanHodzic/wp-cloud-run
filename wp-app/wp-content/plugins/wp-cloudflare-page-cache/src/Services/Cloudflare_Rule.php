<?php

namespace SPC\Services;

use SPC\Constants;
use SW_CLOUDFLARE_PAGECACHE;

/**
 * Cloudflare rule manager abstract class.
 */
abstract class Cloudflare_Rule {
	/**
	 * The rule types.
	 */
	public const RULE_TYPE_CACHE     = 'cache';
	public const RULE_TYPE_TRANSFORM = 'transform';

	/**
	 * The Cloudflare phases according to the rule type.
	 */
	private const PHASES = [
		self::RULE_TYPE_CACHE     => 'http_request_cache_settings',
		self::RULE_TYPE_TRANSFORM => 'http_request_transform',
	];

	protected const RULE_DESCRIPTION         = '[DO NOT EDIT] WP Super Page Cache Plugin rules for';
	protected const LEGACY_RULE_DESCRIPTIONS = [
		'WP Super Page Cache Plugin rules for ',
	];

	/**
	 * @var string $rule_type The type of rule to manage.
	 */
	private $rule_type = self::RULE_TYPE_CACHE;

	/**
	 * The cached ruleset. Used when the ruleset is already retrieved.
	 *
	 * @var array
	 */
	protected $cached_ruleset = [];

	/**
	 * Plugin instance.
	 *
	 * @var SW_CLOUDFLARE_PAGECACHE
	 */
	protected $plugin;

	/**
	 * The ruleset ID.
	 *
	 * @var string
	 */
	protected $ruleset_id;

	/**
	 * The rule ID.
	 *
	 * @var string
	 */
	protected $rule_id;

	/**
	 * Cloudflare_Rule_Manager constructor.
	 *
	 * @param SW_CLOUDFLARE_PAGECACHE $plugin Plugin instance.
	 */
	public function __construct( SW_CLOUDFLARE_PAGECACHE $plugin ) {
		$this->plugin     = $plugin;
		$this->ruleset_id = $this->plugin->get_single_config( $this->get_ruleset_id_setting_slug(), '' );
		$this->rule_id    = $this->plugin->get_single_config( $this->get_rule_id_setting_slug(), '' );
	}

	/**
	 * Get the ruleset ID setting slug.
	 *
	 * @return string
	 */
	abstract public function get_ruleset_id_setting_slug(): string;

	/**
	 * Get the rule ID setting slug.
	 *
	 * @return string
	 */
	abstract public function get_rule_id_setting_slug(): string;

	/**
	 * Get the rule args that will be passed onto the rule creation or update.
	 *
	 * @return array
	 */
	abstract protected function get_rule_args(): array;

	/**
	 * Create a new ruleset ID for the current zone.
	 *
	 * @return string
	 *
	 * @see https://developers.cloudflare.com/api/operations/createZoneRuleset
	 */
	public function create_ruleset( &$error = '' ) {
		if ( ! $this->plugin->has_cloudflare_api_zone_id() ) {
			return '';
		}

		$zone_id = $this->plugin->get_cloudflare_api_zone_id();

		if ( empty( $zone_id ) ) {
			return '';
		}

		$url          = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/rulesets', $zone_id );
		$args         = $this->get_api_auth_args();
		$args['body'] = wp_json_encode( $this->get_ruleset_args() );

		$response = wp_remote_post( $url, $args );

		if ( ! $this->is_success_api_response( $response, 'create_ruleset', $error ) ) {
			$this->log( 'create_ruleset', sprintf( 'Could NOT create ruleset ID for zone %s - URL: %s', $zone_id, $url ) );

			return '';
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $response_body ) || ! isset( $response_body['result']['id'] ) ) {
			return '';
		}

		$this->log( 'create_ruleset', sprintf( 'Ruleset ID created for zone %s: %s', $zone_id, $response_body['result']['id'] ) );

		$this->cached_ruleset = $response_body['result'];
		$this->ruleset_id     = $response_body['result']['id'];

		return $this->ruleset_id;
	}

	/**
	 * Set the rule type to manage by this class instance.
	 *
	 * The rule type can be either 'cache' or 'transform'
	 *
	 * @see self::RULE_TYPE_CACHE, self::RULE_TYPE_TRANSFORM
	 *
	 * @param string $type The type of rule to manage.
	 *
	 * @return void
	 */
	public function set_rule_type( $type ) {
		if ( ! in_array( $type, [ 'cache', 'transform' ], true ) ) {
			return;
		}

		$this->rule_type = $type;
	}

	/**
	 * Get the existing ruleset ID for the current zone.
	 *
	 * @return array
	 *
	 * @see https://developers.cloudflare.com/api/operations/getZoneEntrypointRuleset
	 */
	public function get_ruleset( &$error = '' ) {
		if ( ! $this->plugin->has_cloudflare_api_zone_id() ) {
			$error = __( 'There is not zone id to use', 'wp-cloudflare-page-cache' );

			return [];
		}

		if ( ! empty( $this->cached_ruleset ) ) {
			return $this->cached_ruleset;
		}

		$url      = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/rulesets/phases/%s/entrypoint', $this->plugin->get_cloudflare_api_zone_id(), $this->rule_type === self::RULE_TYPE_CACHE ? 'http_request_cache_settings' : 'http_request_transform' );
		$args     = $this->get_api_auth_args();
		$response = wp_safe_remote_get( $url, $args );

		if ( ! $this->is_success_api_response( $response, 'get_ruleset', $error ) ) {
			return [];
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $response_body ) ) {
			return [];
		}

		$this->cached_ruleset = $response_body['result'];

		return isset( $response_body['result'] ) ? $response_body['result'] : [];
	}

	/**
	 * Get the ruleset ID.
	 *
	 * @return string
	 */
	public function get_ruleset_id( &$error = '' ) {
		if ( ! $this->plugin->has_cloudflare_api_zone_id() ) {
			$error = __( 'There is not zone id to use', 'wp-cloudflare-page-cache' );

			return '';
		}

		if ( ! empty( $this->ruleset_id ) ) {
			return $this->ruleset_id;
		}

		$ruleset = $this->get_ruleset( $error );

		if ( ! isset( $ruleset['id'] ) ) {
			return '';
		}

		$this->ruleset_id = $ruleset['id'];

		return $ruleset['id'];
	}

	/**
	 * Get the rule as array.
	 *
	 * @return array
	 */
	public function get_rule() {
		if ( ! $this->plugin->has_cloudflare_api_zone_id() ) {
			return [];
		}

		$ruleset = $this->get_ruleset();

		if ( empty( $ruleset['rules'] ) ) {
			return [];
		}

		foreach ( $ruleset['rules'] as $rule ) {
			if ( $this->is_spc_rule( $rule ) ) {
				return $rule;
			}
		}

		return [];
	}

	/**
	 * Get the rule ID.
	 *
	 * @return string
	 */
	public function get_rule_id() {
		if ( ! $this->plugin->has_cloudflare_api_zone_id() ) {
			return '';
		}

		if ( ! empty( $this->rule_id ) ) {
			return $this->rule_id;
		}

		$rule = $this->get_rule();

		if ( ! isset( $rule['id'] ) ) {
			return '';
		}

		$this->rule_id = $rule['id'];

		return $rule['id'];
	}

	/**
	 * Update the existing cache rule in the Cloudflare API.
	 *
	 * @param string $error The error message.
	 *
	 * @return string The updated rule ID. Empty string if the rule was not updated.
	 */
	public function update_rule( &$error = '' ) {
		if ( ! $this->plugin->has_cloudflare_api_zone_id() ) {
			return '';
		}

		$url                    = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/rulesets/%s/rules/%s', $this->plugin->get_cloudflare_api_zone_id(), $this->get_ruleset_id(), $this->get_rule_id() );
		$request_args           = $this->get_api_auth_args();
		$request_args['method'] = 'PATCH';
		$request_args['body']   = wp_json_encode( $this->get_rule_args() );
		$response               = wp_remote_request( $url, $request_args );

		if ( ! $this->is_success_api_response( $response, 'update_rule', $error ) ) {
			return '';
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $response_body ) ) {
			return '';
		}

		$this->cached_ruleset = [];

		return isset( $response_body['result'], $response_body['result']['id'] ) ? $response_body['result']['id'] : '';
	}

	/**
	 * Create a cache rule in the Cloudflare API.
	 *
	 * @param string $error The error message.
	 *
	 * @return string The created rule ID. Empty string if the rule was not created.
	 *
	 * @see https://developers.cloudflare.com/api/operations/createZoneRulesetRule
	 */
	public function create_rule( &$error = '' ) {
		if ( $this->get_rule_id() ) {
			$rule = $this->get_rule();

			if ( isset( $rule['id'] ) ) {
				return $rule['id'];
			}
		}

		$zone_id = $this->plugin->get_cloudflare_api_zone_id();

		$url                  = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/rulesets/%s/rules', $zone_id, $this->get_ruleset_id() );
		$request_args         = $this->get_api_auth_args();
		$request_args['body'] = wp_json_encode( $this->get_rule_args() );
		$response             = wp_remote_post( $url, $request_args );

		if ( ! $this->is_success_api_response( $response, 'create_rule', $error ) ) {
			return '';
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $response_body )
			|| ! isset( $response_body['result']['rules'] )
			|| ! is_array( $response_body['result']['rules'] )
		) {
			return '';
		}

		foreach ( $response_body['result']['rules'] as $rule ) {
			if ( $this->is_spc_rule( $rule ) ) {
				$this->rule_id = $rule['id'];
				break;
			}
		}

		if ( empty( $this->rule_id ) ) {
			$this->log( 'create_rule', 'Could NOT create rule.' );
		}

		$this->cached_ruleset = [];
		$this->log( 'create_rule', sprintf( 'Rule ID created for zone %s: %s', $zone_id, $this->rule_id ) );

		return $this->rule_id;
	}

	/**
	 * Delete the rule.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 *
	 * @see https://developers.cloudflare.com/api/operations/deleteZoneRulesetRule
	 */
	public function delete_rule( &$error = '' ) {
		if ( ! Settings_Store::get_instance()->is_cloudflare_connected() ) {
			return false;
		}

		$rule    = $this->get_rule_id();
		$ruleset = $this->get_ruleset_id();

		if ( empty( $rule ) || empty( $ruleset ) ) {
			$this->log( 'delete_cache_rule', 'Could NOT delete rule. No ruleset or rule defined.' );

			return false;
		}

		$url            = sprintf( 'https://api.cloudflare.com/client/v4/zones/%s/rulesets/%s/rules/%s', $this->plugin->get_cloudflare_api_zone_id(), $ruleset, $rule );
		$args           = $this->get_api_auth_args();
		$args['method'] = 'DELETE';

		$response = wp_remote_request( $url, $args );

		$this->log( 'delete_rule', sprintf( 'Request URL: %s', esc_url_raw( $url ) ) );

		if ( ! $this->is_success_api_response( $response, 'delete_rule', $error ) ) {
			if ( wp_remote_retrieve_response_code( $response ) === 404 ) {
				$this->rule_id = '';

				return true;
			}

			return false;
		}

		$status = wp_remote_retrieve_response_code( $response ) == 200;

		$this->rule_id        = '';
		$this->cached_ruleset = [];

		return $status;
	}

	/**
	 * Get the ruleset args. Used when creating the ruleset.
	 *
	 * @return array
	 */
	private function get_ruleset_args() {
		return [
			'name'        => 'WP Cloudflare Super Page Cache Plugin',
			'description' => 'Ruleset made with WP Cloudflare Super Page Cache Plugin',
			'kind'        => 'zone',
			'phase'       => self::PHASES[ $this->rule_type ],
			'rules'       => [],
		];
	}

	/**
	 * Get the authentication arguments for the Cloudflare API
	 *
	 * @param bool $use_curl Whether to return headers as a string or an array
	 *
	 * @return array
	 */
	protected function get_api_auth_args( $use_curl = false ) {
		$settings = Settings_Store::get_instance();

		$req_args = [
			'timeout' => defined( 'SWCFPC_CURL_TIMEOUT' ) ? SWCFPC_CURL_TIMEOUT : 10,
			'headers' => [],
		];

		$headers = [
			'Content-Type' => 'application/json',
		];

		if ( $this->is_token_auth() ) {
			$headers['Authorization'] = "Bearer {$settings->get(Constants::SETTING_CF_API_TOKEN)}";
		} else {
			$headers['X-Auth-Email'] = $settings->get( Constants::SETTING_CF_EMAIL );
			$headers['X-Auth-Key']   = $settings->get( Constants::SETTING_CF_API_KEY );
		}

		if ( $use_curl ) {
			foreach ( $headers as $key => $value ) {
				$req_args['headers'][] = "{$key}: {$value}";
			}
		} else {
			$req_args['headers'] = $headers;
		}

		return $req_args;
	}

	/**
	 * Check if the API response is successful
	 *
	 * @param array| \WP_Error $response The incoming response from the API.
	 * @param string $origin The origin of the request (function name).
	 *
	 * @return bool
	 */
	protected function is_success_api_response( $response, $origin = 'request_success_check', &$error = '' ) {
		if ( is_wp_error( $response ) ) {
			$this->log( $origin, sprintf( 'Error <code>wp_remote_get</code>: %s', $response->get_error_message() ) );

			$error = __( 'Connection error: ', 'wp-cloudflare-page-cache' ) . $response->get_error_message();

			return false;
		}

		try {
			$response_body = wp_remote_retrieve_body( $response );

			$this->log( $origin, sprintf( 'Response: %s', var_export( $response_body, true ) ), true );

			$json = json_decode( $response_body, true );

			if ( ! is_array( $json ) ) {
				$error = __( 'Invalid response data', 'wp-cloudflare-page-cache' );

				return false;
			}

			if ( isset( $json['errors'] ) && is_array( $json['errors'] ) ) {
				$errors = [];

				foreach ( $json['errors'] as $single_error ) {
					$single_err = "{$single_error['message']}";

					if ( isset( $single_error['code'] ) ) {
						$single_err .= " (err code: {$single_error['code']})";
					}

					$errors[] = $single_err;
				}

				$error = implode( ' - ', $errors );
			}

			return isset( $json['success'] ) && $json['success'] === true;
		} catch ( \Exception $e ) {
			$this->log( $origin, sprintf( 'Error: %s', $e->getMessage() ) );

			return false;
		}
	}

	/**
	 * Log a message.
	 *
	 * @param string $identifier The identifier for the log.
	 * @param string $message The message to log.
	 * @param bool $only_log_if_verbose Whether to log the message only if the verbose mode is enabled.
	 *
	 * @return void
	 */
	protected function log( $identifier, $message, $only_log_if_verbose = false ) {
		$this->plugin->get_logger()->add_log( sprintf( 'cloudflare::%s_%s', $identifier, $this->rule_type ), $message, $only_log_if_verbose );
	}

	/**
	 * Check if the authentication mode is API Token.
	 *
	 * @return bool
	 */
	protected function is_token_auth() {
		return SWCFPC_AUTH_MODE_API_TOKEN === (int) Settings_Store::get_instance()->get( Constants::SETTING_AUTH_MODE );
	}

	/**
	 * Check if the rule is a rule created by the plugin.
	 *
	 * We do this check by looking at the description of the rule.
	 *
	 * @param array $rule The rule to check.
	 *
	 * @return bool
	 */
	protected function is_spc_rule( $rule ) {
		if ( ! isset( $rule['description'] ) ) {
			return false;
		}

		foreach ( self::LEGACY_RULE_DESCRIPTIONS as $description ) {
			if ( strpos( $rule['description'], $description ) !== false ) {

				return true;
			}
		}

		return strpos( $rule['description'], $this->build_rule_description() ) !== false;
	}

	/**
	 * Build the rule description.
	 *
	 * @return string
	 */
	protected function build_rule_description() {
		return self::RULE_DESCRIPTION . ' ' . $this->plugin->get_home_url();
	}
}
