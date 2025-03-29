<?php
/**
 * Placements screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use WP_Screen;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Screen;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Admin\Placement_List_Table;
use AdvancedAds\Admin\Placement_Create_Modal;

defined( 'ABSPATH' ) || exit;

/**
 * Placements.
 */
class Placements extends Screen {

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'placements';
	}

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$hook = add_submenu_page(
			ADVADS_SLUG,
			__( 'Ad Placements', 'advanced-ads' ),
			__( 'Placements', 'advanced-ads' ),
			Conditional::user_cap( 'advanced_ads_manage_placements' ),
			'edit.php?post_type=' . Constants::POST_TYPE_PLACEMENT
		);

		// Keep the manual placements page around, but redirect it to the custom post type.
		$old_placements_hook = add_submenu_page(
			'',
			'',
			'',
			Conditional::user_cap( 'advanced_ads_manage_placements' ),
			ADVADS_SLUG . '-placements',
			'__return_true'
		);
		$this->set_hook( 'edit-' . Constants::POST_TYPE_PLACEMENT );
		add_action( 'current_screen', [ $this, 'load_placement_ui' ] );
		add_action( 'load-' . $old_placements_hook, [ $this, 'redirect_to_post_type' ] );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_advads()->registry->enqueue_style( 'screen-placements-listing' );
		wp_advads()->registry->enqueue_script( 'screen-placements-listing' );

		wp_advads_json_add( 'content_placement_picker_url', $this->get_content_placement_picker_url() );
	}

	/**
	 * Redirect old placement page to custom post type.
	 *
	 * @return void
	 */
	public function redirect_to_post_type(): void {
		wp_safe_redirect( 'edit.php?post_type=' . Constants::POST_TYPE_PLACEMENT );
	}

	/**
	 * Load list table
	 *
	 * @param WP_Screen $screen Current screen instance.
	 *
	 * @return void
	 */
	public function load_placement_ui( WP_Screen $screen ): void {
		if ( 'edit-' . Constants::POST_TYPE_PLACEMENT === $screen->id ) {
			( new Placement_List_Table() )->hooks();
			( new Placement_Create_Modal() )->hooks();
		}
	}

	/**
	 * Get the URL where the user is redirected after activating the frontend picker for a "Content" placement.
	 *
	 * @return string
	 */
	private function get_content_placement_picker_url() {
		$location = false;

		if ( get_option( 'show_on_front' ) === 'posts' ) {
			$recent_posts = wp_get_recent_posts(
				[
					'numberposts' => 1,
					'post_type'   => 'post',
					'post_status' => 'publish',
				],
				'OBJECT'
			);

			if ( $recent_posts ) {
				$location = get_permalink( $recent_posts[0] );
			}
		}

		return $location ?? home_url();
	}
}
