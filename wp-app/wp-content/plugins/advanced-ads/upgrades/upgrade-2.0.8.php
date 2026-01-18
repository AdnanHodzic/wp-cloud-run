<?php
/**
 * Update routine
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0.8
 */

use AdvancedAds\Constants;

/**
 * Save group ids into ad options.
 *
 * @since 2.0.8
 *
 * @return void
 */
function advads_upgrade_2_0_8_save_group_ids(): void {
	$ads = wp_advads_get_ads_dropdown();

	foreach ( $ads as $ad_id => $ad ) {
		$groups = wp_get_object_terms( $ad_id, Constants::TAXONOMY_GROUP, [ 'fields' => 'ids' ] );

		// If no groups are found, skip.
		if ( is_wp_error( $groups ) || empty( $groups ) ) {
			continue;
		}

		// If groups are already saved, skip.
		if ( get_post_meta( $ad_id, Constants::AD_META_GROUP_IDS, true ) ) {
			continue;
		}

		update_post_meta( $ad_id, Constants::AD_META_GROUP_IDS, $groups );
	}
}

advads_upgrade_2_0_8_save_group_ids();
