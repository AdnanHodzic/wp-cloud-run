<?php
/**
 * JSON manger class
 *
 * It handles json output for use on backend and frontend.
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/**
 * JSON class
 */
class JSON {

	/**
	 * JSON Holder.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Default Object name.
	 *
	 * @var string
	 */
	private $default_object_name = null;

	/**
	 * The constructor
	 *
	 * @param string $object_name Object name to be used.
	 */
	public function __construct( $object_name ) {
		$this->default_object_name = $object_name;
	}

	/**
	 * Bind all hooks.
	 *
	 * @since 1.0.0
	 *
	 * @throws InvalidArgumentException When object name not defined.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if ( empty( $this->default_object_name ) ) {
			throw new InvalidArgumentException( 'Please set default object name to be used when printing JSON.' );
		}

		$hook = is_admin() ? 'admin_footer' : 'wp_footer';
		add_action( $hook, [ $this, 'output' ], 0 );
	}

	/**
	 * Add to JSON object.
	 *
	 * @since  1.0.0
	 *
	 * @param mixed ...$args Arguments.
	 *
	 * Parameters can be
	 * 1. array|string Unique identifier or array<key, value>.
	 * 2. array|string The data itself can be either a scalar or an array.
	 *                 In Case of first param an array this can be object_name.
	 * 3. string Name for the JavaScript object.
	 *           Passed directly, so it should be qualified JS variable.
	 *
	 * @return JSON
	 */
	public function add( ...$args ): JSON {
		list( $key, $value, $object_name ) = $this->get_add_data( $args );

		// Early Bail!!
		if ( empty( $key ) ) {
			return $this;
		}

		// If array is passed.
		if ( is_array( $key ) ) {
			foreach ( $key as $arr_key => $arr_value ) {
				$this->add_to_storage( $arr_key, $arr_value, $object_name );
			}

			return $this;
		}

		$this->add_to_storage( $key, $value, $object_name );

		return $this;
	}

	/**
	 * Add to storage.
	 *
	 * @since  1.0.0
	 *
	 * @param string $key         Unique identifier.
	 * @param mixed  $value       The data itself can be either a single or an array.
	 * @param string $object_name Name for the JavaScript object.
	 *                            Passed directly, so it should be qualified JS variable.
	 * @return void
	 */
	private function add_to_storage( $key, $value, $object_name ): void {
		// If key doesn't exists.
		if ( ! isset( $this->data[ $object_name ][ $key ] ) ) {
			$this->data[ $object_name ][ $key ] = $value;
			return;
		}

		// If key already exists.
		$old_value = $this->data[ $object_name ][ $key ];
		$is_array  = is_array( $old_value ) && is_array( $value );

		$this->data[ $object_name ][ $key ] = $is_array ? array_merge( $old_value, $value ) : $value;
	}

	/**
	 * Remove from JSON object.
	 *
	 * @since  1.0.0
	 *
	 * @param string $key         Unique identifier.
	 * @param string $object_name Name for the JavaScript object.
	 *                            Passed directly, so it should be qualified JS variable.
	 * @return JSON
	 */
	public function remove( $key, $object_name = false ): JSON {
		// Early Bail!!
		if ( empty( $key ) ) {
			return $this;
		}

		if ( empty( $object_name ) ) {
			$object_name = $this->default_object_name;
		}

		if ( isset( $this->data[ $object_name ][ $key ] ) ) {
			unset( $this->data[ $object_name ][ $key ] );
		}

		return $this;
	}

	/**
	 * Clear all data.
	 *
	 * @since 1.0.0
	 *
	 * @return JSON
	 */
	public function clear_all(): JSON {
		$this->data                               = [];
		$this->data[ $this->default_object_name ] = [];

		return $this;
	}

	/**
	 * Print data.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function output(): void {
		$script = $this->encode();
		if ( ! $script ) {
			return;
		}

		echo "<script type='text/javascript'>\n"; // CDATA and type='text/javascript' is not needed for HTML 5.
		echo "/* <![CDATA[ */\n";
		echo "$script\n"; // phpcs:ignore
		echo "/* ]]> */\n";
		echo "</script>\n";
	}

	/**
	 * Get encoded string.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	private function encode(): string {
		$script = '';
		foreach ( $this->data as $object_name => $object_data ) {
			$script .= $this->single_object( $object_name, $object_data );
		}

		return $script;
	}

	/**
	 * Encode single object.
	 *
	 * @since  1.0.0
	 *
	 * @param string $object_name Object name to use as JS variable.
	 * @param array  $object_data Object data to json encode.
	 *
	 * @return string
	 */
	private function single_object( $object_name, $object_data ): string {
		if ( empty( $object_data ) ) {
			return '';
		}

		foreach ( (array) $object_data as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$object_data[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}

		return "var $object_name = " . wp_json_encode( $object_data ) . ';' . PHP_EOL;
	}

	/**
	 * Normalize add arguments
	 *
	 * @param array $args Arguments array.
	 *
	 * @return array
	 */
	private function get_add_data( $args ): array {
		$key         = $args[0] ?? false;
		$value       = $args[1] ?? false;
		$object_name = $args[2] ?? $this->default_object_name;

		if ( is_array( $key ) && ! empty( $value ) ) {
			$object_name = $value;
			$value       = false;
		}

		return [
			$key,
			$value,
			$object_name,
		];
	}
}
