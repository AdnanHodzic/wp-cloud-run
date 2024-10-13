<?php
/**
 * Ads screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use Advanced_Ads;
use AdvancedAds\Entities;
use AdvancedAds\Interfaces\Screen_Interface;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Ads.
 */
class Ads implements Screen_Interface {

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$has_ads = Advanced_Ads::get_number_of_ads( [ 'any', 'trash' ] );

		// Forward Ads link to new-ad page when there is no ad existing yet.
		add_submenu_page(
			ADVADS_SLUG,
			__( 'Ads', 'advanced-ads' ),
			__( 'Ads', 'advanced-ads' ),
			WordPress::user_cap( 'advanced_ads_edit_ads' ),
			! $has_ads ? 'post-new.php?post_type=' . Entities::POST_TYPE_AD . '&new=new' : 'edit.php?post_type=' . Entities::POST_TYPE_AD
		);
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {}
}
