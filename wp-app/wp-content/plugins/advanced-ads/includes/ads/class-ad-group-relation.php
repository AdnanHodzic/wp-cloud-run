<?php
/**
 * Manage ad and group relationship.
 *
 * @since   2.0.7
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

namespace AdvancedAds\Ads;

use AdvancedAds\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Ad group relation class.
 */
class Ad_Group_Relation {

	/**
	 * Hold groups.
	 *
	 * @var array
	 */
	private $groups = [];

	/**
	 * Create ad group relation.
	 *
	 * @param Ad $ad Ad object.
	 *
	 * @return void
	 */
	public function relate( $ad ): void {
		$old_groups = get_post_meta( $ad->get_id(), Constants::AD_META_GROUP_IDS, true );
		$new_groups = wp_get_object_terms( $ad->get_id(), Constants::TAXONOMY_GROUP, [ 'fields' => 'ids' ] );

		if ( empty( $old_groups ) || ! is_array( $old_groups ) ) {
			$old_groups = [];
		}

		$removed_terms = array_diff( $old_groups, $new_groups );
		$added_terms   = array_diff( $new_groups, $old_groups );

		$this->handle_removed_terms( $removed_terms, $ad->get_id() );
		$this->handle_added_terms( $added_terms, $ad->get_id() );

		update_post_meta( $ad->get_id(), Constants::AD_META_GROUP_IDS, $new_groups );
	}

	/**
	 * Handles the removed terms for an ad.
	 *
	 * @param array $removed_terms An array of term IDs that have been removed.
	 * @param int   $ad_id         The ID of the ad.
	 *
	 * @return void
	 */
	private function handle_removed_terms( $removed_terms, $ad_id ): void {
		foreach ( $removed_terms as $group_id ) {
			$group = $this->get_group( $group_id );
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
	 *
	 * @return void
	 */
	private function handle_added_terms( $added_terms, $ad_id ): void {
		foreach ( $added_terms as $group_id ) {
			$group = $this->get_group( $group_id );
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

	/**
	 * Get group by id and cache them.
	 *
	 * @param int $group_id Group id.
	 *
	 * @return Group|bool Group object or false if the group cannot be loaded.
	 */
	private function get_group( $group_id ) {
		if ( ! isset( $this->groups[ $group_id ] ) ) {
			$this->groups[ $group_id ] = wp_advads_get_group( $group_id );
		}

		return $this->groups[ $group_id ];
	}
}
