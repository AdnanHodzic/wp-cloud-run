<?php
/**
 * Bulk edit for placement
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 */

namespace AdvancedAds\Admin\Placement;

use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Placement;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Placement Bulk Edit.
 */
class Bulk_Edit implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'bulk_edit_custom_box', [ $this, 'add_bulk_edit_fields' ], 10, 2 );
		add_action( 'save_post', [ $this, 'save_bulk_edit' ], 100 );
	}

	/**
	 * Add the bulk edit inputs
	 *
	 * @param string $column_name the current column.
	 * @param string $post_type   the current post type.
	 *
	 * @return void
	 */
	public function add_bulk_edit_fields( $column_name, $post_type ) {
		// Early bail!!
		if ( Constants::POST_TYPE_PLACEMENT !== $post_type || 'type' !== $column_name ) {
			return;
		}

		include ADVADS_ABSPATH . 'views/admin/placements/bulk-edit.php';

		/**
		 * Allow add-ons to add more fields.
		 */
		do_action( 'advanced-ads-placement-bulk-edit-fields' );
	}

	/**
	 * Save changes made during bulk edit
	 *
	 * @return void
	 */
	public function save_bulk_edit() {
		// not placement or not enough permissions.
		if ( Constants::POST_TYPE_PLACEMENT !== sanitize_key( Params::get( 'post_type' ) ) || ! current_user_can( 'advanced_ads_edit_ads' )
		) {
			return;
		}

		check_admin_referer( 'bulk-posts' );

		$ad_label = Params::get( 'ad_label' );

		$has_change = ! empty( $ad_label );

		/**
		 * Filter to determine if there are changes to be saved during bulk edit.
		 *
		 * @param bool $has_change Indicates if there are changes to be saved.
		 */
		$has_change = apply_filters( 'advanced-ads-placement-bulk-edit-has-change', $has_change );

		// No changes, bail out.
		if ( ! $has_change ) {
			return;
		}

		$placements = array_map(
			function ( $placement ) {
				return wp_advads_get_placement( absint( $placement ) );
			},
			wp_unslash( Params::get( 'post', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) )
		);

		foreach ( $placements as $placement ) {
			if ( ! empty( $ad_label ) ) {
				$placement->set_prop( 'ad_label', $ad_label );
			}

			/**
			 * Allow add-on to bulk save placements.
			 *
			 * @param Placement $placement current placement being saved.
			 */
			$placement = apply_filters( 'advanced-ads-placement-bulk-edit-save', $placement );

			$placement->save();
		}
	}
}
