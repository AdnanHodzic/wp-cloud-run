<?php
/**
 * Admin Placement Quick Edit.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Constants;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Placement Quick Edit.
 */
class Placement_Quick_Edit implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if ( ! Conditional::user_can( 'advanced_ads_manage_placements' ) ) {
			return;
		}
		add_action( 'quick_edit_custom_box', [ $this, 'add_quick_edit_fields' ], 10, 2 );
		add_action( 'save_post', [ $this, 'save_quick_edits' ], 100 );
		add_action( 'admin_init', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Add inline script
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		wp_advads()->json->add(
			'placements',
			[
				'read_nonce' => wp_create_nonce( 'advads-read-placement' ),
				'draft'      => __( 'Draft', 'advanced-ads' ),
			]
		);
	}

	/**
	 * Add fields to the quick edit form
	 *
	 * @param string $column currently processed column.
	 * @param string $post_type the post type.
	 *
	 * @return void
	 */
	public function add_quick_edit_fields( $column, $post_type ): void {
		if ( Constants::POST_TYPE_PLACEMENT !== $post_type || 'type' !== $column ) {
			return;
		}
		include plugin_dir_path( ADVADS_FILE ) . 'views/admin/placements/quick-edit.php';
	}

	/**
	 * Save quick edit data
	 *
	 * @param int $id the placement id.
	 *
	 * @return void
	 */
	public function save_quick_edits( $id ): void {
		// Not inline edit, or no permission.
		if ( ! wp_verify_nonce( sanitize_key( Params::post( '_inline_edit' ) ), 'inlineeditnonce' ) ) {
			return;
		}

		$placement = wp_advads_get_placement( $id );

		if ( ! $placement ) {
			return;
		}
		$placement->set_status( Params::post( 'status', '', FILTER_UNSAFE_RAW ) );
		$placement->save();
		( new Placement_List_Table() )->hooks();
	}
}
