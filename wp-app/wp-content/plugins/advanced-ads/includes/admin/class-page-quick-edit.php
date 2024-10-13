<?php
/**
 * Admin Page Quick Edit.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Framework\Interfaces\Integration_Interface;
use AdvancedAds\Framework\Utilities\Formatting;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Page Quick Edit.
 */
class Page_Quick_Edit implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'quick_edit_custom_box', [ $this, 'add_quick_edit_fields' ], 10, 2 );
		add_action( 'bulk_edit_custom_box', [ $this, 'add_bulk_edit_fields' ], 10, 2 );
		add_action( 'save_post', [ $this, 'save_quick_edit_fields' ] );
		add_action( 'save_post', [ $this, 'save_bulk_edit_fields' ] );

		add_action( 'wp_ajax_advads_get_disabled_ads', [ $this, 'get_disable_ads_status' ] );
	}

	/**
	 * Get disable ads and content injection status for one post/page
	 *
	 * @return void
	 */
	public function get_disable_ads_status() {
		$nonce = sanitize_text_field( wp_unslash( Params::post( 'nonce' ) ) );
		if ( ! wp_verify_nonce( $nonce, 'advads-post-quick-edit' ) ) {
			wp_send_json_error( 'Not authorized', 401 );
		}
		$id = (int) Params::post( 'id', 0, FILTER_VALIDATE_INT );
		wp_send_json_success( (array) get_post_meta( $id, '_advads_ad_settings', true ), 200 );
	}

	/**
	 * Print quick edit fields.
	 *
	 * @param string $column    the column name.
	 * @param string $post_type current post type.
	 *
	 * @return void
	 */
	public function add_quick_edit_fields( $column, $post_type ) {
		if ( ! in_array( $post_type, [ 'post', 'page' ], true ) ) {
			return;
		}
		if ( 'ad-status' === $column ) {
			require ADVADS_ABSPATH . 'views/admin/page-quick-edit.php';
		}
	}

	/**
	 * Print bulk edit fields
	 *
	 * @param string $column_name the column name.
	 * @param string $post_type   current post type.
	 *
	 * @return void
	 */
	public function add_bulk_edit_fields( $column_name, $post_type ) {
		if ( ! in_array( $post_type, [ 'post', 'page' ], true ) ) {
			return;
		}
		require ADVADS_ABSPATH . 'views/admin/page-bulk-edit.php';
	}

	/**
	 * Save quick edit changes
	 *
	 * @param int $post_id the post id.
	 *
	 * @return void
	 */
	public function save_quick_edit_fields( $post_id ) {
		// Not inline edit, or no permission.
		if (
			! wp_verify_nonce( sanitize_key( Params::post( '_inline_edit' ) ), 'inlineeditnonce' ) ||
			! current_user_can( 'edit_post', $post_id )
		) {
			return;
		}
		$meta = [ 'disable_ads' => Params::post( 'advads-disable-ads', 0, FILTER_VALIDATE_INT ) ];
		if ( defined( 'AAP_VERSION' ) ) {
			$meta['disable_the_content'] = Params::post( 'advads-disable-the-content', 0 );
		}
		update_post_meta( $post_id, '_advads_ad_settings', $meta );
	}

	/**
	 * Save bulk changes
	 *
	 * @return void
	 */
	public function save_bulk_edit_fields() {
		// Not bulk edit, not post/page or not enough permissions.
		if (
			! wp_verify_nonce( sanitize_key( Params::get( '_wpnonce', '', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ), 'bulk-posts' )
			|| ! in_array( sanitize_key( Params::get( 'post_type' ) ), [ 'post', 'page' ], true )
			|| ! current_user_can( 'edit_posts' )
		) {
			return;
		}
		$disable_ads         = Params::get( 'advads_disable_ads', '', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$disable_the_content = Params::get( 'advads_disable_the_content', '', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( empty( $disable_ads ) && empty( $disable_the_content ) ) {
			return;
		}

		$ids = Params::get( 'post', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		foreach ( $ids as $id ) {
			$meta = get_post_meta( (int) $id, '_advads_ad_settings', true );
			if ( ! empty( $disable_ads ) ) {
				$meta['disable_ads'] = Formatting::string_to_bool( $disable_ads ) ? 1 : 0;
			}
			if ( ! empty( $disable_the_content ) ) {
				$meta['disable_the_content'] = Formatting::string_to_bool( $disable_the_content ) ? 1 : 0;
			}
			update_post_meta( (int) $id, '_advads_ad_settings', $meta );
		}
	}
}
