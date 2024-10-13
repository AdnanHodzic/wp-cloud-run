<?php
/**
 * Abstracts Types.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Abstracts;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Abstracts Types.
 */
abstract class Types implements Integration_Interface {

	/**
	 * Hold types.
	 *
	 * @var array
	 */
	private $types = [];

	/**
	 * Hook to filter types.
	 *
	 * @var string
	 */
	protected $hook = 'advanced-ads-none-types';

	/**
	 * Class for unknown type.
	 *
	 * @var string
	 */
	protected $type_unknown = '';

	/**
	 * Type interface to check.
	 *
	 * @var string
	 */
	protected $type_interface = '';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		$this->register_types();
	}

	/**
	 * Register custom type.
	 *
	 * @param string $classname Type class name.
	 *
	 * @return void
	 */
	public function register_type( $classname ): void {
		$type                           = new $classname();
		$this->types[ $type->get_id() ] = $type;
	}

	/**
	 * Register custom types.
	 *
	 * @return void
	 */
	public function register_types(): void {
		$this->register_default_types();

		/**
		 * Developers can add new types using this filter
		*/
		$this->types = apply_filters( $this->hook, $this->types );
		$this->validate_types();
	}

	/**
	 * Has type.
	 *
	 * @param string $type Type to check.
	 *
	 * @return bool
	 */
	public function has_type( $type ) {
		return array_key_exists( $type, $this->types );
	}

	/**
	 * Get the registered type.
	 *
	 * @param string $type Type to get.
	 *
	 * @return mixed
	 */
	public function get_type( $type ) {
		return $this->types[ $type ] ?? false;
	}

	/**
	 * Get the registered types.
	 *
	 * @return array
	 */
	public function get_types(): array {
		return $this->types;
	}

	/**
	 * Register default types.
	 *
	 * @return void
	 */
	protected function register_default_types(): void {
	}

	/**
	 * Validate types by type interface
	 *
	 * @return void
	 */
	private function validate_types(): void {
		// Early bail!!
		if ( empty( $this->type_unknown ) || empty( $this->type_interface ) ) {
			return;
		}

		foreach ( $this->types as $slug => $type ) {
			if ( is_a( $type, $this->type_interface ) ) {
				continue;
			}

			$type['id']           = $slug;
			$this->types[ $slug ] = new $this->type_unknown( $type );
		}
	}
}
