<?php

use SPC\Constants;
use SPC\Modules\Settings_Manager;
use SPC\Services\Cloudflare_Client;
use SPC\Services\Settings_Store;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Cloudflare {

	/**
	 * The main plugin class.
	 *
	 * @var \SW_CLOUDFLARE_PAGECACHE
	 */
	private $main_instance         = null;
	private $api_key               = '';
	private $email                 = '';
	private $api_token             = '';
	private $auth_mode             = 0;
	private $api_token_domain      = '';
	private $cache_ruleset_id      = ''; // Ruleset related to `http_request_cache_settings` phase.
	private $cache_ruleset_rule_id = '';

	/**
	 * @var Cloudflare_Client
	 */
	private $client;

	/**
	 * SWCFPC_Cloudflare constructor.
	 *
	 * @param \SW_CLOUDFLARE_PAGECACHE $main_instance Instance of the main plugin class.
	 */
	public function __construct( $main_instance ) {
		$this->main_instance         = $main_instance;
		$this->auth_mode             = $this->main_instance->get_single_config( 'cf_auth_mode' );
		$this->api_key               = $this->main_instance->get_cloudflare_api_key();
		$this->email                 = $this->main_instance->get_cloudflare_api_email();
		$this->api_token             = $this->main_instance->get_cloudflare_api_token();
		$this->cache_ruleset_id      = $this->main_instance->get_single_config( 'cf_cache_settings_ruleset_id', '' );
		$this->cache_ruleset_rule_id = $this->main_instance->get_single_config( 'cf_cache_settings_ruleset_rule_id', '' );
		$this->client                = new Cloudflare_Client( $this->main_instance );
	}

	/**
	 * Check if the Cloudflare API is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (
			$this->main_instance->has_cloudflare_api_zone_id() &&
			(
				! empty( $this->email ) && ! empty( $this->api_key ) ||
				! empty( $this->api_token )
			)
		);
	}

	/**
	 * Check if the cache rule is set.
	 *
	 * @return bool
	 */
	public function has_cache_rule() {
		return ! empty( $this->cache_ruleset_id ) && ! empty( $this->cache_ruleset_rule_id );
	}

	/**
	 * Set the authentication mode locally in this class.
	 *
	 * @param int $auth_mode The authentication mode.
	 *
	 * @return void
	 */
	public function set_auth_mode( $auth_mode ) {
		$this->auth_mode = $auth_mode;
	}

	/**
	 * Set the Cloudflare API key locally in this class.
	 *
	 * @param string $api_key The Cloudflare API key.
	 *
	 * @return void
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Set the Cloudflare API email locally in this class.
	 *
	 * @param string $email The email associated to the Cloudflare account.
	 *
	 * @return void
	 */
	public function set_api_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Set the Cloudflare API token locally in this class.
	 *
	 * @param string $api_token The Cloudflare API token.
	 *
	 * @return void
	 */
	public function set_api_token( $api_token ) {
		$this->api_token = $api_token;
	}

	/**
	 * Set the domain when using API token locally in this class.
	 *
	 * @param string $api_token_domain The domain for the API token.
	 *
	 * @return void
	 */
	public function set_api_token_domain( $api_token_domain ) {
		$this->api_token_domain = $api_token_domain;
	}

	/**
	 * Get the zone ID list.
	 *
	 * @param string $error The error message.
	 *
	 * @return array|false
	 */
	public function get_zone_id_list( &$error ) {
		return $this->client->get_zone_id_list($error);
	}

	/**
	 * Change the browser TTL.
	 *
	 * @param number $ttl The new TTL value.
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function change_browser_cache_ttl( $ttl, &$error ) {
		return $this->client->change_browser_cache_ttl( $ttl, $error );
	}

	/**
	 * Delete the Page Rule
	 *
	 * @param string $page_rule_id The page rule id to delete.
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function delete_page_rule( $page_rule_id, &$error ) {
		return $this->client->delete_page_rule( $page_rule_id, $error );
	}

	/**
	 * Purge the Cloudflare cache
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function purge_cache( &$error ) {
		$purge = $this->client->purge_cache( $error );

		if ( $purge ) {
			$this->main_instance->get_logger()->add_log( 'cloudflare::purge_cache', 'Cache purged successfully.' );

			if ( $this->main_instance->get_single_config( 'cf_preloader', 1 ) > 0 ) {
				$this->main_instance->get_cache_controller()->start_preloader_for_all_urls();
			}
		}

		return $purge;
	}

	/**
	 * Purge specific URLs from Cloudflare Cache
	 *
	 * @param array $urls The URLs to purge.
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function purge_cache_urls( $urls, &$error ) {
		return $this->client->purge_cache_urls( $urls, $error );
	}

	/**
	 * Get the current account ID.
	 *
	 * @param string $error The error message.
	 *
	 * @return string
	 */
	public function get_current_account_id( &$error ) {
		return $this->client->get_account_id( $error );
	}

	/**
	 * Disable page cache.
	 *
	 * @param string $error The error message.
	 * @param bool $disable_cache If true, it will disable the cache.
	 *
	 * @return bool
	 */
	public function disable_page_cache( &$error = '', $disable_cache = true ) {
		$logger = $this->main_instance->get_logger();

		// Reset old browser cache TTL
		if ( $this->main_instance->get_single_config( 'cf_old_bc_ttl', 0 ) != 0 ) {
			$this->change_browser_cache_ttl( $this->main_instance->get_single_config( 'cf_old_bc_ttl', 0 ), $error );
		}

		// Delete page rules
		if ($this->main_instance->get_single_config('cf_page_rule_id', '') != '' && ! $this->delete_page_rule($this->main_instance->get_single_config('cf_page_rule_id', ''), $error)) {
			return false;
		} else {
			$this->main_instance->set_single_config( 'cf_page_rule_id', '' );
		}

		if ($this->main_instance->get_single_config('cf_bypass_backend_page_rule_id', '') != '' && ! $this->delete_page_rule($this->main_instance->get_single_config('cf_bypass_backend_page_rule_id', ''), $error)) {
			return false;
		} else {
			$this->main_instance->set_single_config( 'cf_bypass_backend_page_rule_id', '' );
		}

		$this->delete_cache_rule( $error );
		$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', '' );
		$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_id', '' );
		$this->purge_cache( $error );

		$this->main_instance->get_cache_controller()->reset_htaccess();

		$this->main_instance->set_single_config( 'cf_woker_route_id', '' );
		if ( $disable_cache ) {
			$this->main_instance->set_single_config( 'cf_cache_enabled', 0 );
		}
		$this->main_instance->update_config();

		return true;
	}

	/**
	 * Delete the legacy page rules.
	 *
	 * @param string $error The error message.
	 *
	 * @return void
	 */
	public function delete_legacy_page_rules( &$error ) {
		// Delete page rule.
		$page_rule_id = $this->main_instance->get_single_config( 'cf_page_rule_id', '' );
		if ( ! empty( $page_rule_id ) && $this->delete_page_rule( $page_rule_id, $error ) ) {
			$this->main_instance->set_single_config( 'cf_page_rule_id', '' );
		}

		// Delete the legacy backend bypass page rule.
		$legacy_page_rule_id = $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule_id', '' );
		if ( ! empty( $legacy_page_rule_id ) && $this->delete_page_rule( $legacy_page_rule_id, $error ) ) {
			$this->main_instance->set_single_config( 'cf_bypass_backend_page_rule_id', '' );
		}
	}

	/**
	 * Enable page cache.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function enable_page_cache( &$error ) {
		$current_cf_browser_ttl = $this->client->get_current_browser_cache_ttl( $error );

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
			$this->cache_ruleset_id = $this->client->get_ruleset_id( $error );
			$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_id', $this->cache_ruleset_id );
		}

		// Step 3a - create a new cache ruleset if it does not exist.
		if (empty($this->cache_ruleset_id)) {
			$this->cache_ruleset_id = $this->client->create_ruleset($error);
			$this->main_instance->set_single_config('cf_cache_settings_ruleset_id', $this->cache_ruleset_id);
		}

		// If we still haven't got the cache ruleset id, then we can't proceed.
		if (empty($this->cache_ruleset_id)) {
			return false;
		}

		// Setp 3b - create a standard rule for the cache ruleset.
		$this->cache_ruleset_rule_id = $this->create_cache_rule($error);
		$this->main_instance->set_single_config('cf_cache_settings_ruleset_rule_id', $this->cache_ruleset_rule_id);

		// If we still haven't got the cache ruleset rule id, then we can't proceed.
		if (empty($this->cache_ruleset_rule_id)) {
			return false;
		}

		// Update config data
		$this->main_instance->update_config();

		// Step 4 - purge cache
		$this->purge_cache( $error );

		$this->main_instance->set_single_config( 'cf_cache_enabled', 1 );
		$this->main_instance->update_config();
		$this->main_instance->get_cache_controller()->write_htaccess( $error );

		return true;
	}

	/**
	 * Create a cache rule in the Cloudflare API.
	 *
	 * @param string $error The error message.
	 *
	 * @return string The created rule ID. Empty string if the rule was not created.
	 *
	 */
	public function create_cache_rule( &$error ) {
		$this->cache_ruleset_rule_id = $this->client->create_rule( $error );

		if ( ! empty( $this->cache_ruleset_rule_id ) ) {
			$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', $this->cache_ruleset_rule_id );
			$this->main_instance->update_config();
		}

		do_action( 'swcfpc_after_create_cache_rule' );

		return $this->cache_ruleset_rule_id;
	}

	/**
	 * Delete the cache rule.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function delete_cache_rule( &$error = '' ) {
		$was_deleted = $this->client->delete_rule( $error );

		// Update the settings to reflect this deletion.
		if ( $was_deleted === true ) {
			$this->cache_ruleset_rule_id = '';
			$this->main_instance->set_single_config( 'cf_cache_settings_ruleset_rule_id', '' );
			$this->main_instance->update_config();
		}

		do_action( 'swcfpc_after_delete_cache_rule' );

		return $was_deleted;
	}

	/**
	 * Update cache rule with the default values.
	 *
	 * @return string
	 */
	public function sync_cache_rule( &$error ) {
		$rule_id = $this->client->update_rule( $error );

		if ( ! empty( $rule_id ) ) {
			$this->main_instance->get_logger()->add_log( 'cloudflare::sync_cache_rule', sprintf( 'Cache %s rule updated successfully.', $rule_id ) );

			$this->purge_cache( $error );

			return $rule_id;
		}

		$this->main_instance->get_logger()->add_log( 'cloudflare::sync_cache_rule', 'Failed to update cache rule.' );

		update_option( Constants::KEY_RULE_UPDATE_FAILED, true );

		return '';
	}

	/**
	 * Pull the existing cache rule from the Cloudflare API if it is not set.
	 *
	 * @param bool $auto_save If true, it will save the cache rule ID. But not commit the changes.
	 *
	 * @return void
	 */
	public function pull_existing_cache_rule( $auto_save = true ) {
		if ( ! $this->main_instance->has_cloudflare_api_zone_id() ) {
			return;
		}

		$this->cache_ruleset_id = $this->client->get_ruleset_id();

		if ( empty( $this->cache_ruleset_id ) ) {
			return;
		}

		$settings = Settings_Store::get_instance();

		if ( $auto_save ) {
			$settings->set(Constants::RULESET_ID_CACHE, $this->cache_ruleset_id);
		}

		$this->cache_ruleset_rule_id = $this->client->get_rule_id();

		if ( empty( $this->cache_ruleset_rule_id ) ) {
			return;
		}

		if ( $auto_save ) {
			$settings->set(Constants::RULE_ID_CACHE, $this->cache_ruleset_rule_id);

			$settings->save();
		}
	}

	/**
	 * Reset the Cloudflare cache rule.
	 *
	 * @param string $error The error message.
	 *
	 * @return bool
	 */
	public function reset_cf_rule( &$error = '' ) {
		$disable = $this->disable_page_cache( $error );
		$enable  = $this->enable_page_cache( $error );

		return $disable && $enable;
	}

	/**
	 * Update the cache rule if it's different from the one we build.
	 *
	 * @return void
	 */
	public function update_cache_rule_if_diff( &$error = '' ) {
		$logger = $this->main_instance->get_logger();

		if ( ! $this->is_enabled() || ! $this->has_cache_rule() ) {
			$logger->add_log( 'cloudflare::update_cache_rule_if_diff', 'Cloudflare API is not enabled or cache rule is not set. Enabled: ' . $this->is_enabled() . ' Rule set: ' . $this->has_cache_rule() );

			return;
		}

		$logger->add_log( 'cloudflare::update_cache_rule_if_diff', 'Start cache rule upgrade.' );

		do_action( 'swcfpc_before_cache_rule_sync_start' );

		$existing_rule = $this->client->get_rule();

		$logger->add_log( 'cloudflare::update_cache_rule_if_diff', 'Existing rule: ' . print_r( $existing_rule, true ) );

		if ( ! isset( $existing_rule['expression'] ) ) {
			return;
		}

		$new_rule_expression = $this->client->get_rule_expression();

		if ( $existing_rule['expression'] === $new_rule_expression ) {
			$logger->add_log( 'cloudflare::update_cache_rule_if_diff', 'Cache rule is up to date.' );

			return;
		}

		$this->sync_cache_rule( $error );
	}

	/**
	 * Disconnect from Cloudflare.
	 *
	 * @param string $error The error message.
	 *
	 * @return void
	 */
	public function disconnect( &$error = '' ) {
		$logger = $this->main_instance->get_logger();

		$logger->add_log( 'cloudflare::disconnect', 'Disconnecting from Cloudflare.' );
		$this->disable_page_cache( $error, false );

		$this->main_instance->update_config();
		$logger->add_log( 'cloudflare::disconnect', 'Disconnected from Cloudflare.' );
	}
}
