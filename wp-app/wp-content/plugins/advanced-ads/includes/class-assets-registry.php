<?php
/**
 * Assets registry handles the registration of stylesheets and scripts required for plugin functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds;

defined( 'ABSPATH' ) || exit;

/**
 * Assets Registry.
 */
class Assets_Registry extends \AdvancedAds\Framework\Assets_Registry {

	/**
	 * Version for plugin local assets.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return ADVADS_VERSION;
	}

	/**
	 * Prefix to use in handle to make it unique.
	 *
	 * @return string
	 */
	public function get_prefix(): string {
		return ADVADS_SLUG;
	}

	/**
	 * Base URL for plugin local assets.
	 *
	 * @return string
	 */
	public function get_base_url(): string {
		return ADVADS_BASE_URL;
	}

	/**
	 * Register styles
	 *
	 * @return void
	 */
	public function register_styles(): void {
		if ( ! is_admin() ) {
			return;
		}

		$this->register_style( 'ui', 'admin/assets/css/ui.css' );
		$this->register_style( 'admin', 'admin/assets/css/admin.css' );
		if ( 'toplevel_page_advanced-ads' === ( get_current_screen() )->id ) {
			$this->register_style( 'app', 'assets/css/app.css' );
		}
		$this->register_style( 'ad-positioning', 'modules/ad-positioning/assets/css/ad-positioning.css', [ $this->prefix_it( 'admin' ) ] );
	}

	/**
	 * Register scripts
	 *
	 * @return void
	 */
	public function register_scripts(): void {
		$this->register_script( 'ad-positioning', '/modules/ad-positioning/assets/js/ad-positioning.js', [], false, true );
		$this->register_script( 'wp-widget-adsense', 'modules/gadsense/admin/assets/js/wp-widget.js', [ 'jquery' ], false, true );
		$this->register_script( 'app', 'assets/js/app.js', [ 'jquery' ], false, true );
		$this->register_script( 'find-adblocker', 'admin/assets/js/advertisement.js' );

		if ( ! is_admin() ) {
			return;
		}

		// Backend JS files go here.
		$this->register_script( 'conditions', 'admin/assets/js/conditions.js', [ 'jquery', $this->prefix_it( 'ui' ) ] );
		$this->register_script( 'wizard', 'admin/assets/js/wizard.js', [ 'jquery' ] );
		$this->register_script( 'inline-edit-group-ads', 'admin/assets/js/inline-edit-group-ads.js', [ 'jquery' ] );
		$this->register_script( 'admin', 'admin/assets/js/admin.min.js', [ 'jquery', $this->prefix_it( 'ui' ), 'jquery-ui-autocomplete', 'wp-util' ] );
		$this->register_script( 'ui', 'admin/assets/js/ui.js', [ 'jquery' ] );
		$this->register_script( 'admin-global', 'admin/assets/js/admin-global.js', [ 'jquery' ], false, true );
		$this->register_script( 'page-quick-edit', 'assets/js/admin/page-quick-edit.js', [], false, true );
	}
}
