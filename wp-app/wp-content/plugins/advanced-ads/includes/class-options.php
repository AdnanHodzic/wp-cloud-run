<?php
/**
 * Get and Set Options.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds;

use AdvancedAds\Framework\Utilities\Arr;

defined( 'ABSPATH' ) || exit;

/**
 * This class is used to get and set plugin options.
 */
class Options {

	/**
	 * Hold plugin options.
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Original DB option values.
	 *
	 * @var array
	 */
	private $raw_options = [];

	/**
	 * Map of key to their respective option name.
	 *
	 * @var array
	 */
	private $options_map = [
		'advanced-ads' => ADVADS_SLUG,
		'adsense'      => ADVADS_SLUG . '-adsense',
		'adblocker'    => ADVADS_SLUG . '-adblocker',
		'ads-txt'      => 'advanced_ads_ads_txt',
		'notices'      => ADVADS_SLUG . '-notices',
		'privacy'      => ADVADS_SLUG . '-privacy',
		'pro'          => ADVADS_SLUG . '-pro',
		'tracking'     => ADVADS_SLUG . '-tracking',
	];

	/**
	 * Retrieves the instance of the Options class.
	 *
	 * @return Options
	 */
	public static function instance() {
		static $instance;

		if ( null === $instance ) {
			$instance = new Options();
		}

		return $instance;
	}

	/**
	 * Gets the value of a specific option.
	 *
	 * @param string $key Option suffix (advanced-ads-XXX).
	 * @param mixed  $default_val Default value to return if the option is not set.
	 *
	 * @return mixed Array of option values, or the default value.
	 */
	public function get( $key, $default_val = false ) {
		$keys        = $this->normalize_key( $key );
		$option_name = array_shift( $keys );
		$values      = $this->get_option( $option_name );

		foreach ( $keys as $key ) {
			$values = Arr::get( $values, $key, null );
		}

		return null === $values ? $default_val : $values;
	}

	/**
	 * Sets the value of a specific option.
	 *
	 * It checks if the method to set the option exists in the instance of the class and calls it if it does.
	 *
	 * @param string $key    Option suffix (advanced-ads-XXX).
	 * @param array  $value  The value to set.
	 *
	 * @return array The new value of the option.
	 */
	public function set( $key, $value ): array {
		$keys        = $this->normalize_key( $key );
		$option_name = array_shift( $keys );

		$this->get_option( $option_name ); // Make sure the option is loaded.

		$values  = $this->raw_options[ $option_name ] ?? [];
		$current = &$values;

		foreach ( $keys as $key ) {
			if ( ! Arr::has( $current, $key ) ) {
				Arr::set( $current, $key, [] );
			}
			$current = &$current[ $key ];
		}
		$current = $value;

		return $this->set_option( $option_name, $values );
	}

	/**
	 * Retrieves the value of an option identified by the given key.
	 *
	 * @param string $key Option name.
	 *
	 * @return array
	 */
	private function get_option( $key ): array {
		// Early bail!!
		if ( isset( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
		}

		// Check for getter method.
		$method = 'get_' . $key;
		if ( method_exists( $this, $method ) ) {
			$this->options[ $key ] = $this->$method();
		}

		// Check in key map.
		if ( isset( $this->options_map[ $key ] ) ) {
			$this->options[ $key ] = get_option( $this->options_map[ $key ], [] );
		}

		$this->raw_options[ $key ] = $this->options[ $key ];
		$this->options[ $key ]     = $this->normalize_option( $this->options[ $key ] );

		return $this->options[ $key ];
	}

	/**
	 * Sets the value of an option and updates the corresponding database entry.
	 *
	 * @param string $key   Option name.
	 * @param mixed  $value Option value.
	 *
	 * @return array The normalized option value.
	 */
	private function set_option( $key, $value ): array {
		// Check for setter method.
		$method = 'set_' . $key;
		if ( method_exists( $this, $method ) ) {
			$this->$method( $value );
		}

		// Check in key map.
		if ( isset( $this->options_map[ $key ] ) ) {
			update_option( $this->options_map[ $key ], $value );
		}

		$this->raw_options[ $key ] = $value;
		$this->options[ $key ]     = $this->normalize_option( $value );

		return $this->options[ $key ];
	}

	/**
	 * Normalizes the given options array recursively.
	 *
	 * @param array $options Options array to be normalized.
	 *
	 * @return array The normalized options array.
	 */
	private function normalize_option( $options ): array {
		if ( ! is_array( $options ) || empty( $options ) ) {
			return [];
		}

		foreach ( $options as $key => $value ) {
			$option[ $key ] = is_array( $value )
				? $this->normalize_option( $value )
				: $this->normalize_value( $value );
		}

		return $option;
	}

	/**
	 * Normalizes a given value.
	 *
	 * @param mixed $value Value to be normalized.
	 *
	 * @return mixed The normalized value.
	 */
	private function normalize_value( $value ) {
		if ( 'true' === $value || 'on' === $value ) {
			$value = true;
		} elseif ( 'false' === $value || 'off' === $value ) {
			$value = false;
		} elseif ( '0' === $value || '1' === $value ) {
			$value = intval( $value );
		}

		return $value;
	}

	/**
	 * Normalizes a given key by removing leading and trailing dots and splitting it into an array.
	 *
	 * @param string $key Key to be normalized.
	 *
	 * @return array The normalized key as an array.
	 */
	private function normalize_key( $key ) {
		$key = trim( $key, '.' );
		return explode( '.', $key );
	}
}
