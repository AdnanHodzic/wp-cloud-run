<?php
/**
 * Groups screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Screen;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Admin\Groups_List_Table;

defined( 'ABSPATH' ) || exit;

/**
 * Groups.
 */
class Groups extends Screen {

	/**
	 * Hold table object.
	 *
	 * @var null|Groups_List_Table
	 */
	private $list_table = null;

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'groups';
	}

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$hook = add_submenu_page(
			ADVADS_SLUG,
			__( 'Ad Groups & Rotations', 'advanced-ads' ),
			__( 'Groups & Rotation', 'advanced-ads' ),
			Conditional::user_cap( 'advanced_ads_edit_ads' ),
			ADVADS_SLUG . '-groups',
			[ $this, 'display' ]
		);

		$this->set_hook( $hook );
		add_action( 'in_admin_header', [ $this, 'get_list_table' ] );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		wp_advads()->registry->enqueue_style( 'screen-groups-listing' );
		wp_advads()->registry->enqueue_script( 'screen-groups-listing' );
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		$wp_list_table = $this->get_list_table();

		include_once ADVADS_ABSPATH . 'views/admin/screens/groups.php';
	}

	/**
	 * Get list table object
	 *
	 * @return null|Groups_List_Table
	 */
	public function get_list_table() {
		$screen = get_current_screen();
		if ( 'advanced-ads_page_advanced-ads-groups' === $screen->id && null === $this->list_table ) {
			wp_advads()->registry->enqueue_script( 'groups' );
			$screen->taxonomy  = Constants::TAXONOMY_GROUP;
			$screen->post_type = Constants::POST_TYPE_AD;
			$this->list_table  = new Groups_List_Table();
		}

		return $this->list_table;
	}
}
