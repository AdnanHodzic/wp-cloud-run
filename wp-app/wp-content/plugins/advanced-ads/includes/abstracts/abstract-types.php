<?php
/**
 * Abstracts Types.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Abstracts;

use AdvancedAds\Framework\Utilities\Str;
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
	 * Check if has premium types.
	 *
	 * @var bool
	 */
	private $has_premium = null;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'init', [ $this, 'register_types' ], 25 );
	}

	/**
	 * Create missing type
	 *
	 * @param string $type Missing type.
	 *
	 * @return mixed
	 */
	public function create_missing( $type ) {
		$this->types[ $type ] = new $this->type_unknown(
			[
				'id'    => $type,
				'title' => Str::capitalize( $type ),
			]
		);

		return $this->types[ $type ];
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
		do_action( $this->hook . '-manager', $this );
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
	public function has_type( $type ): bool {
		return array_key_exists( $type, $this->types );
	}

	/**
	 * Get the registered type.
	 *
	 * @param string $type Type to get.
	 *
	 * @return mixed|bool
	 */
	public function get_type( $type ) {
		return $this->types[ $type ] ?? false;
	}

	/**
	 * Get the registered types.
	 *
	 * @param bool $with_unknown Include unknown type placements.
	 *
	 * @return array
	 */
	public function get_types( $with_unknown = true ): array {
		return $with_unknown ? $this->types : array_filter( $this->types, fn( $type ) => ! is_a( $type, $this->type_unknown ) );
	}

	/**
	 * Check if has premium types.
	 *
	 * @return bool
	 */
	public function has_premium(): bool {
		if ( null !== $this->has_premium ) {
			return $this->has_premium;
		}

		$this->has_premium = false;

		foreach ( $this->get_types() as $type ) {
			if ( $type->is_premium() ) {
				$this->has_premium = true;
				break;
			}
		}

		return $this->has_premium;
	}

	/**
	 * Register default types.
	 *
	 * @return void
	 */
	abstract protected function register_default_types(): void;

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
			if ( ! is_array( $type ) || is_a( $type, $this->type_interface ) ) {
				continue;
			}

			$type['id']           = $slug;
			$this->types[ $slug ] = new $this->type_unknown( $type );
		}
	}
}
