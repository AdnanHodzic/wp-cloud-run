<?php

namespace SPC\Services;

use SPC\Constants;
use SPC\Modules\Settings_Manager;

class Settings_Store {
	public const CONFIG_OPTION = 'swcfpc_config';

	public const EXCLUDED_FROM_EXPORT_IMPORT = [
		Constants::SETTING_ENABLE_FALLBACK_CACHE,
		Constants::SETTING_CF_ZONE_ID,
		Constants::ZONE_ID_LIST,
		Constants::SETTING_CF_EMAIL,
		Constants::SETTING_CF_API_TOKEN,
		Constants::SETTING_CF_API_KEY,
		Constants::SETTING_OLD_BC_TTL,
		Constants::RULE_ID_PAGE,
		Constants::RULE_ID_CACHE,
		Constants::RULESET_ID_CACHE,
		Constants::WORKER_ID,
		Constants::SETTING_CF_CACHE_ENABLED,
		Constants::SETTING_PRELOADER_NAV_MENUS,
	];

	/**
	 * @var Settings_Store|null
	 */
	private static $_instance = null;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @var array
	 */
	private $changed_settings = [];

	/**
	 * @var Settings_Manager
	 */
	private $settings_manager;

	/**
	 * Get the instance of the Settings_Store.
	 *
	 * @return Settings_Store
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance                   = new self();
			self::$_instance->settings_manager = new Settings_Manager();
			self::$_instance->refresh();
		}
		return self::$_instance;
	}

	/**
	 * Get a setting value by key.
	 *
	 * @param string $key The setting key.
	 * @param mixed $fallback_default The fallback default value if the key is not set.
	 *
	 * @return mixed The setting value or the fallback default.
	 */
	public function get( string $key, $fallback_default = false ) {
		$default_value = $this->settings_manager->get_default_for_field( $key, $fallback_default );

		if ( ! isset( $this->config[ $key ] ) ) {
			return $default_value;
		}

		return $this->config[ $key ];
	}
	/**
	 * Check if lazyload viewport is enabled.
	 *
	 * @return bool
	 */
	public function is_lazyload_viewport_enabled() {
		return class_exists( 'SPC_Pro\Modules\Frontend' ) && $this->get( Constants::SETTING_LAZY_LOAD_BEHAVIOUR ) === \SPC_Pro\Modules\Frontend::LAZY_LOAD_BEHAVIOUR_VIEWPORT;
	}
	/**
	 * Check if unused CSS is enabled.
	 *
	 * @return bool
	 */
	public function is_unused_css_enabled() {
		return class_exists( 'SPC_Pro\Modules\Frontend' ) && (bool) $this->get( Constants::SETTING_UNUSED_CSS );
	}
	/**
	 * Check if client optimizations are enabled.
	 *
	 * @return bool
	 */
	public function is_client_optimizations_enabled() {
		return $this->is_lazyload_viewport_enabled() || $this->is_unused_css_enabled();
	}
	/**
	 * Check if Cloudflare is connected.
	 *
	 * @return bool
	 */
	public function is_cloudflare_connected() {
		return $this->get( Constants::SETTING_CF_ZONE_ID ) !== '';
	}
	/**
	 * Set a setting value by key.
	 *
	 * @param string $key The setting key.
	 * @param mixed $value The value to set for the key.
	 *
	 * @return $this
	 */
	public function set( string $key, $value ) {
		// Bail out if the value is the same as the current one.
		if ( isset( $this->config[ $key ] ) && $this->config[ $key ] === $value ) {
			return $this;
		}

		$this->config[ $key ]           = $value;
		$this->changed_settings[ $key ] = $value;

		return $this;
	}

	/**
	 * Set multiple settings at once.
	 *
	 * @param array $settings The settings to set.
	 *
	 * @return $this
	 */
	public function set_multiple( array $settings ) {
		foreach ( $settings as $key => $value ) {
			$this->set( $key, $value );
		}

		return $this;
	}

	/**
	 * Get all the settings.
	 *
	 * @param bool $include_defaults Whether to include the default values.
	 *
	 * @return array The settings.
	 */
	public function get_all( $include_defaults = false ) {

		if ( $include_defaults ) {
			return array_merge(
				$this->settings_manager->get_fields( [], 'default' ),
				$this->config
			);
		}

		return $this->config;
	}

	/**
	 * Get the changed settings since the instantiation of this class.
	 */
	public function get_changed_settings() {
		return $this->changed_settings;
	}

	/**
	 * Refresh the settings from the database.
	 */
	public function refresh() {
		$this->config = get_option( self::CONFIG_OPTION, [] );
	}

	/**
	 * Reset the settings.
	 *
	 * @return $this
	 */
	public function reset() {
		$this->config           = [];
		$this->changed_settings = [];

		return $this;
	}

	/**
	 * Save the config to the database.
	 */
	public function save() {
		update_option( self::CONFIG_OPTION, $this->config );
	}

	/**
	 * Import settings from an array.
	 *
	 * @param array $data The settings to import.
	 *
	 * @return bool True if the settings were imported successfully, false otherwise.
	 */
	public function import_settings( array $data ) {

		if ( empty( $data ) ) {
			return false;
		}

		$fields = $this->settings_manager->get_fields();

		$data = array_filter(
			$data,
			function ( $key ) use ( $fields ) {
				return isset( $fields[ $key ] );
			},
			ARRAY_FILTER_USE_KEY
		);

		$data = $this->sanitize_for_import_export( $data );

		if ( empty( $data ) ) {
			return false;
		}

		$this->config = $data;
		$this->save();

		return true;
	}

	/**
	 * Get the settings for export.
	 *
	 * @return array
	 */
	public function get_config_for_export() {
		return $this->sanitize_for_import_export( $this->get_all() );
	}

	/**
	 * Strip excluded settings from the settings array.
	 *
	 * @param array $settings The array of settings to be stripped.
	 *
	 * @return array
	 */
	private function sanitize_for_import_export( array $settings ) {
		foreach ( self::EXCLUDED_FROM_EXPORT_IMPORT as $key ) {
			unset( $settings[ $key ] );
		}

		return $settings;
	}
}
