<?php
/**
 * Ad settings metabox.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use Advanced_Ads;
use AdvancedAds\Constants;
use AdvancedAds\Utilities\Validation;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Ad settings metabox.
 */
class Metabox_Ad_Settings implements Integration_Interface {

	/**
	 * Ad setting post meta key
	 *
	 * @var string
	 */
	const SETTING_METAKEY = '_advads_ad_settings';


	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_settings' ], 10, 2 );
		add_action( 'set_object_terms', [ $this, 'set_group_terms' ], 10, 6 );
	}

	/**
	 * Add a meta box to post type edit screens with ad settings
	 *
	 * @param string $post_type current post type.
	 *
	 * @return void
	 */
	public function add_meta_box( $post_type = '' ): void {
		// Early bail!!
		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) || ! is_post_type_viewable( $post_type ) ) {
			return;
		}

		$options             = Advanced_Ads::get_instance()->options();
		$disabled_post_types = $options['pro']['general']['disable-by-post-types'] ?? [];
		$render_what         = in_array( $post_type, $disabled_post_types, true ) ? 'display_disable_notice' : 'display_settings';

		add_meta_box(
			'advads-ad-settings',
			__( 'Ad Settings', 'advanced-ads' ),
			[ $this, $render_what ],
			$post_type,
			'side',
			'low'
		);
	}

	/**
	 * Render meta box for ad settings notice when ads disabled for post type
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function display_disable_notice( $post ): void {
		$labels = get_post_type_object( $post->post_type )->labels;
		include ADVADS_ABSPATH . 'views/notices/ad-disable-post-type.php';
	}

	/**
	 * Render meta box for ad settings on a per post basis
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function display_settings( $post ): void {
		$values = get_post_meta( $post->ID, self::SETTING_METAKEY, true );

		include ADVADS_ABSPATH . 'views/admin/metaboxes/ads/post-ad-settings.php';
	}

	/**
	 * Save the ad settings when the post is saved.
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object.
	 *
	 * @return void
	 */
	public function save_settings( $post_id, $post ): void {
		$post_id = absint( $post_id );

		// Check the nonce.
		$nonce = Params::post( 'advads_post_meta_box_nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'advads_post_meta_box' ) ) {
			return;
		}

		// Don’t display for non admins.
		if (
			! Conditional::user_can( 'advanced_ads_edit_ads' ) ||
			! Validation::check_save_post( $post_id, $post )
		) {
			return;
		}

		// Check user has permission to edit.
		$perm = 'page' === get_post_type( $post_id ) ? 'edit_page' : 'edit_post';
		if ( ! current_user_can( $perm, $post_id ) ) {
			return;
		}

		$data['disable_ads'] = absint( $_POST['advanced_ads']['disable_ads'] ?? 0 );

		$data = apply_filters( 'advanced_ads_save_post_meta_box', $data );

		update_post_meta( $post_id, self::SETTING_METAKEY, $data );
	}

	/**
	 * Sets the group terms for an ad.
	 *
	 * Handles the removed and added groups accordingly.
	 *
	 * @param int    $ad_id      The ID of the ad.
	 * @param array  $terms      The terms to set for the ad.
	 * @param array  $tt_ids     The term taxonomy IDs to set for the ad.
	 * @param string $taxonomy   The taxonomy to set the terms for.
	 * @param bool   $append     Whether to append the terms or replace them.
	 * @param array  $old_tt_ids The old term taxonomy IDs for the ad.
	 * @return void
	 */
	public function set_group_terms( $ad_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if ( Constants::TAXONOMY_GROUP === $taxonomy ) {
			$removed_terms = array_diff( $old_tt_ids, $tt_ids );
			$added_terms   = array_diff( $tt_ids, $old_tt_ids );

			$this->handle_removed_terms( $removed_terms, $ad_id );
			$this->handle_added_terms( $added_terms, $ad_id );
		}
	}

	/**
	 * Handles the removed terms for an ad.
	 *
	 * @param array $removed_terms An array of term IDs that have been removed.
	 * @param int   $ad_id         The ID of the ad.
	 * @return void
	 */
	private function handle_removed_terms( $removed_terms, $ad_id ) {
		foreach ( $removed_terms as $group_id ) {
			$group = wp_advads_get_group( $group_id );
			if ( ! $group ) {
				continue;
			}

			$weights = $group->get_ad_weights();
			if ( isset( $weights[ $ad_id ] ) ) {
				unset( $weights[ $ad_id ] );
				$group->set_ad_weights( $weights );
				$group->save();
			}
		}
	}

	/**
	 * Handles the added terms for an ad.
	 *
	 * @param array $added_terms An array of term IDs that have been added.
	 * @param int   $ad_id       The ID of the ad.
	 * @return void
	 */
	private function handle_added_terms( $added_terms, $ad_id ) {
		foreach ( $added_terms as $group_id ) {
			$group = wp_advads_get_group( $group_id );
			if ( ! $group ) {
				continue;
			}

			$weights = $group->get_ad_weights();
			if ( ! isset( $weights[ $ad_id ] ) ) {
				$weights[ $ad_id ] = Constants::GROUP_AD_DEFAULT_WEIGHT;
				$group->set_ad_weights( $weights );
				$group->save();
			}
		}
	}
}
