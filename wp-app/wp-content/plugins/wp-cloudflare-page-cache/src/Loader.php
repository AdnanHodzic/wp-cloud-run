<?php

namespace SPC;

use SPC\Modules\Admin;
use SPC\Modules\Assets_Manager;
use SPC\Modules\Dashboard;
use SPC\Modules\Font_Optimizer;
use SPC\Modules\Database_Optimization;
use SPC\Modules\Frontend;
use SPC\Modules\HTML_Modifier;
use SPC\Modules\Module_Interface;
use SPC\Modules\Rest_Server;
use SPC\Modules\Settings_Manager;
use SPC\Modules\Third_Party;
use SPC\Services\Settings_Store;
use SPC\Modules\Metrics_Cleanup;

class Loader {
	public const PRODUCT_KEY = 'wp_cloudflare_page_cache';

	/**
	 * Modules.
	 *
	 * @var array $modules Pro modules.
	 */
	private $modules = [];

	/**
	 * Loader constructor.
	 */
	public function __construct() {
		$this->modules['frontend']         = new Frontend();
		$this->modules['html_modifier']    = new HTML_Modifier();
		$this->modules['settings_manager'] = new Settings_Manager();
		$this->modules['admin']            = new Admin();
		$this->modules['dashboard']        = new Dashboard();
		$this->modules['rest_server']      = new Rest_Server();
		$this->modules['third_party']      = new Third_Party();
		$this->modules['metrics_cleanup']  = new Metrics_Cleanup();
		$this->modules['assets_manager']   = new Assets_Manager();

		if ( Settings_Store::get_instance()->get( Constants::SETTING_ENABLE_DATABASE_OPTIMIZATION ) ) {
			$this->modules['database_optimization'] = new Database_Optimization();
		}
		$this->modules['font_optimizer'] = new Font_Optimizer();
	}

	/**
	 * Get the modules.
	 *
	 * @return array<string, Module_Interface>
	 */
	public function get_modules() {
		return $this->modules;
	}

	/**
	 * Get the frontend module.
	 *
	 * @return Frontend
	 */
	public function get_frontend_module() {
		return $this->modules['frontend'];
	}
	/**
	 * Load the modules.
	 *
	 * @return void
	 */
	public function load_modules() {
		$modules = apply_filters( 'spc_loaded_modules', $this->modules );

		$valid_modules = array_filter(
			$modules,
			function ( $module ) {
				return $module instanceof Module_Interface;
			}
		);

		array_walk(
			$valid_modules,
			function ( $module ) {
				$module->init();
			}
		);
	}

	/**
	 * Check if we should load the HTML modifier.
	 *
	 * @return bool
	 */
	public static function can_process_html() {
		return version_compare( get_bloginfo( 'version' ), '6.2', '>=' ) && class_exists( 'WP_HTML_Tag_Processor' );
	}

	/**
	 * Check if the current page is cached.
	 *
	 * @return bool
	 */
	public static function is_cached_page() {
		return function_exists( 'swcfpc_is_this_page_cachable' ) && \swcfpc_is_this_page_cachable();
	}
}
