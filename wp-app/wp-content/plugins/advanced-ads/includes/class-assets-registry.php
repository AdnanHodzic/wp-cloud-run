<?php
/**
 * Assets registry handles the registration of stylesheets and scripts required for plugin functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds;

use AdvancedAds\Framework;

defined( 'ABSPATH' ) || exit;

/**
 * Assets Registry.
 */
class Assets_Registry extends Framework\Assets_Registry {

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
		$this->register_style( 'ui', 'admin/assets/css/ui.css' );
		$this->register_style( 'admin', 'admin/assets/css/admin.css' );
		$this->register_style( 'ad-positioning', 'modules/ad-positioning/assets/css/ad-positioning.css', [ self::prefix_it( 'admin' ) ] );

		// New CSS files.
		$this->register_style( 'common', 'assets/css/admin/common.css' );
		$this->register_style( 'notifications', 'assets/css/admin/notifications.css' );
		$this->register_style( 'screen-ads-editing', 'assets/css/admin/screen-ads-editing.css', [ self::prefix_it( 'common' ) ] );
		$this->register_style( 'screen-ads-listing', 'assets/css/admin/screen-ads-listing.css', [] );
		$this->register_style( 'screen-dashboard', 'assets/css/admin/screen-dashboard.css', [ self::prefix_it( 'common' ), 'wp-components' ] );
		$this->register_style( 'screen-groups-listing', 'assets/css/admin/screen-groups-listing.css' );
		$this->register_style( 'screen-onboarding', 'assets/css/admin/screen-onboarding.css' );
		$this->register_style( 'screen-placements-listing', 'assets/css/admin/screen-placements-listing.css' );
		$this->register_style( 'screen-settings', 'assets/css/admin/screen-settings.css', [ self::prefix_it( 'common' ) ] );
		$this->register_style( 'screen-tools', 'assets/css/admin/screen-status.css', [ self::prefix_it( 'common' ) ] );
		$this->register_style( 'wp-dashboard', 'assets/css/admin/wp-dashboard.css', [ self::prefix_it( 'common' ) ] );
	}

	/**
	 * Register scripts
	 *
	 * @return void
	 */
	public function register_scripts(): void {
		$this->register_script( 'admin-global', 'admin/assets/js/admin-global.js', [ 'jquery' ], false, true );
		$this->register_script( 'find-adblocker', 'admin/assets/js/advertisement.js' );
		$this->register_script( 'ui', 'admin/assets/js/ui.js', [ 'jquery' ] );
		$this->register_script( 'conditions', 'admin/assets/js/conditions.js', [ 'jquery', self::prefix_it( 'ui' ) ] );
		$this->register_script( 'wizard', 'admin/assets/js/wizard.js', [ 'jquery' ] );
		$this->register_script( 'inline-edit-group-ads', 'admin/assets/js/inline-edit-group-ads.js', [ 'jquery' ], false, false );
		$this->register_script( 'ad-positioning', '/modules/ad-positioning/assets/js/ad-positioning.js', [], false, true );
		$this->register_script( 'admin', 'admin/assets/js/admin.min.js', [ 'jquery', self::prefix_it( 'ui' ), 'jquery-ui-autocomplete', 'wp-util' ], false, false );
		$this->register_script( 'groups', 'admin/assets/js/groups.js', [ 'jquery' ], false, true );
		$this->register_script( 'adblocker-image-data', 'admin/assets/js/adblocker-image-data.js', [ 'jquery' ] );

		// New JS files.
		$this->register_script( 'admin-common', 'assets/js/admin/admin-common.js', [ 'jquery' ], false, true );
		$this->register_script( 'screen-ads-listing', 'assets/js/admin/screen-ads-listing.js', [ 'jquery', 'inline-edit-post', 'wp-util', 'wp-api-fetch', self::prefix_it( 'admin-common' ) ], false, true );
		$this->register_script( 'screen-ads-editing', 'assets/js/admin/screen-ads-editing.js', [], false, true );
		$this->register_script( 'screen-dashboard', 'assets/js/admin/screen-dashboard.js', [ self::prefix_it( 'admin-common' ) ], false, true );
		$this->register_script( 'screen-groups-listing', 'assets/js/admin/screen-groups-listing.js', [ 'wp-api-fetch', self::prefix_it( 'admin-common' ) ], false, true );
		$this->register_script( 'screen-placements-listing', 'assets/js/admin/screen-placements-listing.js', [ 'wp-util', 'wp-api-fetch', self::prefix_it( 'admin-global' ), self::prefix_it( 'admin-common' ) ], false, true );
		$this->register_script( 'screen-settings', 'assets/js/admin/screen-settings.js', [], false, true );
		$this->register_script( 'screen-tools', 'assets/js/admin/screen-tools.js', [], false, true );
		$this->register_script( 'wp-dashboard', 'assets/js/admin/wp-dashboard.js', [ 'jquery' ], false, true );
		$this->register_script( 'notifications-center', 'assets/js/admin/notifications.js', [ 'jquery' ], false, true );
		$onboarding_deps = [
			'jquery',
			'lodash',
			'moment',
			'wp-data',
			'wp-compose',
			'wp-components',
			'wp-api-fetch',
		];
		$this->register_script( 'screen-onboarding', 'assets/js/screen-onboarding.js', $onboarding_deps, false, true );
		$this->register_script( 'page-quick-edit', 'assets/js/admin/page-quick-edit.js', [ 'wp-api-fetch' ], false, true );

		// OneClick.
		$deps = [
			'jquery',
			'wp-dom-ready',
			'wp-components',
			'wp-notices',
			'wp-element',
			'wp-html-entities',
		];
		$this->register_script( 'oneclick-onboarding', 'assets/js/admin/oneclick-onboarding.js', $deps, false, true );
	}
}
