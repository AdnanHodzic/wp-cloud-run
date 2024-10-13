<?php
/**
 * Admin Pages Settings.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Interfaces\Screen_Interface;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Pages Settings.
 */
class Settings implements Screen_Interface {

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		add_submenu_page(
			ADVADS_SLUG,
			__( 'Advanced Ads Settings', 'advanced-ads' ),
			__( 'Settings', 'advanced-ads' ),
			WordPress::user_cap( 'advanced_ads_manage_options' ),
			ADVADS_SLUG . '-settings',
			[ $this, 'display' ]
		);
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		include ADVADS_ABSPATH . 'views/admin/screens/settings.php';
	}
}
