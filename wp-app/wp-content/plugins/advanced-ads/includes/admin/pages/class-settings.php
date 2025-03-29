<?php
/**
 * Admin Pages Settings.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Abstracts\Screen;
use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Pages Settings.
 */
class Settings extends Screen {

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'settings';
	}

	/**
	 * Get the order number of the screen.
	 *
	 * @return int
	 */
	public function get_order(): int {
		return 20;
	}

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$hook = add_submenu_page(
			ADVADS_SLUG,
			__( 'Advanced Ads Settings', 'advanced-ads' ),
			__( 'Settings', 'advanced-ads' ),
			Conditional::user_cap( 'advanced_ads_manage_options' ),
			ADVADS_SLUG . '-settings',
			[ $this, 'display' ]
		);

		$this->set_hook( $hook );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_advads()->registry->enqueue_style( 'screen-settings' );
		wp_advads()->registry->enqueue_script( 'screen-settings' );
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		include_once ADVADS_ABSPATH . 'views/admin/screens/settings.php';
	}
}
