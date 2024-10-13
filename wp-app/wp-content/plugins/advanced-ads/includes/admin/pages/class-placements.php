<?php
/**
 * Placements screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use Advanced_Ads;
use Advanced_Ads_Admin;
use Advanced_Ads_Placements;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Interfaces\Screen_Interface;
use AdvancedAds\Utilities\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Placements.
 */
class Placements implements Screen_Interface {

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		add_submenu_page(
			ADVADS_SLUG,
			__( 'Ad Placements', 'advanced-ads' ),
			__( 'Placements', 'advanced-ads' ),
			WordPress::user_cap( 'advanced_ads_manage_placements' ),
			ADVADS_SLUG . '-placements',
			[ $this, 'display' ]
		);
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		$placement_types = Advanced_Ads_Placements::get_placement_types();
		$placements      = Advanced_Ads::get_ad_placements_array(); // -TODO use model
		$orderby         = $this->get_field_to_order_placement();
		$has_placements  = isset( $placements ) && is_array( $placements ) && count( $placements );

		// display view.
		include ADVADS_ABSPATH . 'views/admin/screens/placements.php';
	}

	/**
	 * Get order placement.
	 *
	 * @return string
	 */
	private function get_field_to_order_placement(): string {
		$settings = Advanced_Ads_Admin::get_admin_settings();
		$default  = $settings['placement-orderby'] ?? 'type';
		$current  = Params::get( 'orderby', $default );

		if ( ! in_array( $current, [ 'name', 'type' ], true ) ) {
			$current = 'type';
		}

		$settings['placement-orderby'] = $current;
		Advanced_Ads_Admin::update_admin_setttings( $settings );

		return $current;
	}

	/**
	 * Render order data
	 *
	 * @param array $placement_types Types of placements.
	 * @param array $placement       Placement instance.
	 *
	 * @return void
	 */
	public static function render_order_data( $placement_types, $placement ): void {
		printf(
			" data-order='%s'",
			wp_json_encode(
				[
					'order'                 => absint( $placement_types[ $placement['type'] ]['order'] ?? 100 ),
					'name'                  => esc_html( $placement['name'] ),
					'type'                  => esc_html( $placement['type'] ),
					'words-between-repeats' => ! empty( $placement['options']['words_between_repeats'] ) ? 1 : 0,
					'post-content-index'    => absint( $placement['options']['index'] ?? 0 ),
				]
			)
		);
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	private function get_url_for_content_placement_picker(): string {
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

		return $location ? $location : home_url();
	}
}
