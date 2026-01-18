<?php
/**
 * Manage group and ad relationship.
 *
 * @since   2.0.7
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

namespace AdvancedAds\Groups;

use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Group;

defined( 'ABSPATH' ) || exit;

/**
 * Group ad relation class.
 */
class Group_Ad_Relation {

	/**
	 * Hold ads.
	 *
	 * @var array
	 */
	private $ads = [];

	/**
	 * Create ad group relation.
	 *
	 * @param Group $group Group object.
	 *
	 * @return void
	 */
	public function relate( &$group ): void {
		$data    = $group->get_data();
		$changes = $group->get_changes();
		$old_ads = $data['ad_weights'] ? array_keys( $data['ad_weights'] ) : [];
		$new_ads = $changes['ad_weights'] ? array_keys( $changes['ad_weights'] ) : [];

		$removed_ads = array_diff( $old_ads, $new_ads );
		$added_ads   = array_diff( $new_ads, $old_ads );

		$this->handle_removed_ads( $removed_ads, $group->get_id() );
		$this->handle_added_ads( $added_ads, $group );
	}

	/**
	 * Handles the removed ads for a group.
	 *
	 * @param array $removed_ads An array of ad IDs that have been removed.
	 * @param int   $group_id    The ID of the group.
	 *
	 * @return void
	 */
	private function handle_removed_ads( $removed_ads, $group_id ): void {
		foreach ( $removed_ads as $ad_id ) {
			$terms = wp_get_object_terms( $ad_id, Constants::TAXONOMY_GROUP );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$term_ids = wp_list_pluck( $terms, 'term_id' );
				$term_ids = array_diff( $term_ids, [ $group_id ] );
				wp_set_object_terms( $ad_id, $term_ids, Constants::TAXONOMY_GROUP );
				update_post_meta( $ad_id, Constants::AD_META_GROUP_IDS, $term_ids );
			}
		}
	}

	/**
	 * Handles the added ads for a group.
	 *
	 * @param array $added_ads An array of ad IDs that have been added.
	 * @param Group $group     Group instance.
	 *
	 * @return void
	 */
	private function handle_added_ads( $added_ads, &$group ): void {
		$changes       = $group->get_changes();
		$new_ads_final = $changes['ad_weights'] ?? [];
		$new_ads       = $changes['ad_weights'] ? array_keys( $changes['ad_weights'] ) : [];

		foreach ( $new_ads as $ad_id ) {
			/**
			 * Check if this ad is representing the current group and remove it in this case
			 * could cause an infinite loop otherwise
			 */
			if ( $this->is_ad_type_group( $ad_id, $group ) ) {
				unset( $new_ads_final[ $ad_id ] );
				continue;
			}

			$terms = wp_get_object_terms( $ad_id, Constants::TAXONOMY_GROUP );

			if ( ! is_wp_error( $terms ) ) {
				$term_ids   = wp_list_pluck( $terms, 'term_id' );
				$term_ids[] = $group->get_id();
				$term_ids   = array_unique( $term_ids );

				wp_set_object_terms( $ad_id, $term_ids, Constants::TAXONOMY_GROUP );
				update_post_meta( $ad_id, Constants::AD_META_GROUP_IDS, $term_ids );
			}
		}

		$group->set_ad_weights( $new_ads_final );
	}

	/**
	 * Check if ad is of type 'group' and belongs to the current group.
	 *
	 * @param int   $ad_id Ad id.
	 * @param Group $group Group instance.
	 *
	 * @return bool
	 */
	private function is_ad_type_group( int $ad_id, Group $group ): bool {
		if ( ! isset( $this->ads[ $ad_id ] ) ) {
			$this->ads[ $ad_id ] = wp_advads_get_ad( $ad_id );
		}

		$ad = $this->ads[ $ad_id ];
		return $ad && $ad->is_type( 'group' ) && $ad->get_group_id() === $group->get_id();
	}
}
