<?php
/**
 * The class is responsible for rendering a branded header on plugin pages in the WordPress admin area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Entities;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Header.
 */
class Header implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'in_admin_header', [ $this, 'render' ] );
	}

	/**
	 * Add an Advanced Ads branded header to plugin pages
	 */
	public function render() {
		// Early bail!!
		if ( ! Conditional::is_screen_advanced_ads() ) {
			return;
		}

		$screen              = get_current_screen();
		$manual_url          = 'https://wpadvancedads.com/manual/';
		$new_button_id       = '';
		$new_button_label    = '';
		$new_button_href     = '';
		$show_filter_button  = false;
		$reset_href          = '';
		$filter_disabled     = $screen->get_option( 'show-filters' ) ? 'disabled' : '';
		$show_screen_options = false;
		$title               = get_admin_page_title();
		$tooltip             = '';

		switch ( $screen->id ) {
			case 'advanced_ads':
				$new_button_label = __( 'New Ad', 'advanced-ads' );
				$new_button_href  = admin_url( 'post-new.php?post_type=advanced_ads' );
				$manual_url       = 'https://wpadvancedads.com/manual/first-ad/';
				break;
			case 'edit-advanced_ads':
				$title               = __( 'Your Ads', 'advanced-ads' );
				$new_button_label    = __( 'New Ad', 'advanced-ads' );
				$new_button_href     = admin_url( 'post-new.php?post_type=advanced_ads' );
				$manual_url          = 'https://wpadvancedads.com/manual/first-ad/';
				$show_filter_button  = ! Conditional::has_filter_or_search();
				$reset_href          = ! $show_filter_button ? esc_url( admin_url( 'edit.php?post_type=' . Entities::POST_TYPE_AD ) ) : '';
				$show_screen_options = true;
				break;
			case 'advanced-ads_page_advanced-ads-groups':
				$title               = __( 'Your Groups', 'advanced-ads' );
				$new_button_label    = __( 'New Ad Group', 'advanced-ads' );
				$new_button_href     = '#modal-group-new';
				$new_button_id       = 'advads-new-ad-group-link';
				$manual_url          = 'https://wpadvancedads.com/manual/ad-groups/';
				$show_filter_button  = ! Conditional::has_filter_or_search();
				$reset_href          = ! $show_filter_button ? esc_url( admin_url( 'admin.php?page=advanced-ads-groups' ) ) : '';
				$tooltip             = Entities::get_group_description();
				$show_screen_options = true;
				break;
			case 'advanced-ads_page_advanced-ads-placements':
				$title              = __( 'Your Placements', 'advanced-ads' );
				$new_button_label   = __( 'New Placement', 'advanced-ads' );
				$new_button_href    = '#modal-placement-new';
				$manual_url         = 'https://wpadvancedads.com/manual/placements/';
				$show_filter_button = true;
				$tooltip            = Entities::get_placement_description();
				break;
			case 'advanced-ads_page_advanced-ads-settings':
				$title = __( 'Advanced Ads Settings', 'advanced-ads' );
				break;
		}

		$manual_url = apply_filters( 'advanced-ads-admin-header-manual-url', $manual_url, $screen->id );

		include ADVADS_ABSPATH . 'views/admin/header.php';
	}
}
