<?php
/**
 * Update routine
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.5
 */

/**
 * Add default options for some 'advanced-ads-adblocker' option
 *
 * @since 1.48.5
 *
 * @return void
 */
function advads_upgrade_1_48_5(): void {
	// Get the current options.
	$eab_option = get_option( 'advanced-ads-adblocker' );

	// Check if the 'advanced-ads' option exists and has the required elements.
	if ( is_array( $eab_option ) ) {
		$eab_option['method']                    = 'nothing';
		$eab_option['overlay']['content']        = '<h2 style="text-align: center;">Uh-oh! It looks like you\'re using an ad blocker.</h2><p style="text-align: center;">Our website relies on ads to provide free content and sustain our operations. By turning off your ad blocker, you help support us and ensure we can continue offering valuable content without any cost to you.</p><p style="text-align: center;">We truly appreciate your understanding and support. Thank you for considering disabling your ad blocker for this website</p>';
		$eab_option['overlay']['time_frequency'] = 'everytime';

		// Update the 'advanced-ads-adblocker' option.
		update_option( 'advanced-ads-adblocker', $eab_option );
	}
}

advads_upgrade_1_48_5();
