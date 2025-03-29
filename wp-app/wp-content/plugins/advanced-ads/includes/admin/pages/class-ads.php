<?php
/**
 * Ads screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use WP_Screen;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Screen;
use AdvancedAds\Admin\Ad_List_Table;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Ads.
 */
class Ads extends Screen {

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'ads';
	}

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$has_ads = WordPress::get_count_ads();

		// Forward Ads link to new-ad page when there is no ad existing yet.
		add_submenu_page(
			ADVADS_SLUG,
			__( 'Ads', 'advanced-ads' ),
			__( 'Ads', 'advanced-ads' ),
			Conditional::user_cap( 'advanced_ads_edit_ads' ),
			! $has_ads ? 'post-new.php?post_type=' . Constants::POST_TYPE_AD . '&new=new' : 'edit.php?post_type=' . Constants::POST_TYPE_AD
		);

		$this->set_hook( 'edit-' . Constants::POST_TYPE_AD );
		add_action( 'current_screen', [ $this, 'load_placement_ui' ] );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_advads()->registry->enqueue_style( 'screen-ads-listing' );
		wp_advads()->registry->enqueue_script( 'screen-ads-listing' );
	}

	/**
	 * Load list table
	 *
	 * @param WP_Screen $screen Current screen instance.
	 *
	 * @return void
	 */
	public function load_placement_ui( WP_Screen $screen ): void {
		if ( 'edit-' . Constants::POST_TYPE_AD === $screen->id ) {
			( new Ad_List_Table() )->hooks();
		}
	}
}
