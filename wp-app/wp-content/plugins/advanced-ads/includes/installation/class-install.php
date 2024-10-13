<?php
/**
 * The class provides plugin installation routines.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Installation;

use AdvancedAds\Framework\Installation\Install as Base;

defined( 'ABSPATH' ) || exit;

/**
 * Install.
 */
class Install extends Base {

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->base_file = ADVADS_FILE;
		parent::initialize();
	}

	/**
	 * Plugin activation callback.
	 *
	 * @return void
	 */
	protected function activate(): void {
		// TODO: inform modules.
		Capabilities::get_instance()->create_capabilities();
	}

	/**
	 * Plugin deactivation callback.
	 *
	 * @return void
	 */
	protected function deactivate(): void {
		// TODO: inform modules.
		Capabilities::get_instance()->remove_capabilities();
	}

	/**
	 * Plugin uninstall callback.
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		( new Uninstall() )->initialize();
	}
}
