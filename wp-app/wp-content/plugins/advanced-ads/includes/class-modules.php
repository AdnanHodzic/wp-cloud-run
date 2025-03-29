<?php
/**
 * Modules.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds;

use AdvancedAds\Interfaces\Module_Interface;
use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Modules.
 */
class Modules implements Initializer_Interface {
	/**
	 * Modules.
	 *
	 * @var array
	 */
	private $modules = [];

	/**
	 * Running modules.
	 *
	 * @var array
	 */
	private $running = [];

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		add_action( 'init', [ $this, 'load_modules' ], 0 );
	}

	/**
	 * Register a module.
	 *
	 * @param string $module Module class name.
	 *
	 * @return void
	 */
	public function register_module( string $module ): void {
		$module = new $module();
		$name   = $module->get_name();

		$this->modules[ $name ] = $module;
	}

	/**
	 * Load modules.
	 *
	 * @return void
	 */
	public function load_modules(): void {
		foreach ( $this->modules as $module ) {
			if ( $this->can_load( $module ) ) {
				$module->load();
			}
		}
	}

	/**
	 * Check if a module can be loaded.
	 *
	 * @param Module_Interface $module Module object.
	 *
	 * @return bool
	 */
	private function can_load( Module_Interface $module ): bool {
		$check = apply_filters( 'advanced-ads-can-load-module', true, $module );
		if ( ! $check ) {
			return false;
		}

		if ( in_array( $module->get_name(), $this->running, true ) ) {
			return false;
		}

		$this->running[] = $module->get_name();

		return true;
	}
}
