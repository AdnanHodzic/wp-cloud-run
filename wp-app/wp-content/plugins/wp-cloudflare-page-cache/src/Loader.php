<?php

namespace SPC;

use SPC\Modules\Admin;
use SPC\Modules\Frontend;
use SPC\Modules\HTML_Modifier;
use SPC\Modules\Module_Interface;
use SPC\Modules\Settings_Manager;

class Loader {
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
