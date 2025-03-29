<?php
/**
 * Update routine
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.4
 */

/**
 * Migrate some 'advanced-ads' options to 'advanced-ads-adblocker'
 *
 * @since 1.48.4
 *
 * @return void
 */
function advads_upgrade_1_48_4(): void {
	// Get the current options.
	$advanced_ads = get_option( 'advanced-ads' );

	// Check if the 'advanced-ads' option exists and has the required elements.
	if ( is_array( $advanced_ads ) ) {
		$adblocker_settings = [];

		if ( isset( $advanced_ads['ga-UID'] ) ) {
			$adblocker_settings['ga-UID'] = $advanced_ads['ga-UID'];
			unset( $advanced_ads['ga-UID'] );
		}

		if ( isset( $advanced_ads['use-adblocker'] ) ) {
			$adblocker_settings['use-adblocker'] = $advanced_ads['use-adblocker'];
			unset( $advanced_ads['use-adblocker'] );
		}

		// Update the 'advanced-ads' option.
		update_option( 'advanced-ads', $advanced_ads );

		// Update the 'advanced-ads-adblocker' option.
		update_option( 'advanced-ads-adblocker', $adblocker_settings );
	}
}

advads_upgrade_1_48_4();
