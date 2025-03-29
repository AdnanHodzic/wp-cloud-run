<?php
/**
 * Conditional functions.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0.0
 */

use AdvancedAds\Framework\Utilities\Params;

/**
 * Return true if ads can be displayed
 *
 * @since 1.4.9
 *
 * @return bool, true if ads can be displayed
 */
function advads_can_display_ads() {
	return Advanced_Ads::get_instance()->can_display_ads();
}

/**
 * Are we currently on an AMP URL?
 * Will always return `false` and show PHP Notice if called before the `wp` hook.
 *
 * @return bool true if amp url, false otherwise
 */
function advads_is_amp() {
	global $pagenow;

	if (
		is_admin() ||
		is_embed() ||
		is_feed() ||
		( isset( $pagenow ) && in_array( $pagenow, [ 'wp-login.php', 'wp-signup.php', 'wp-activate.php' ], true ) ) ||
		( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
		( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
	) {
		return false;
	}

	if ( ! did_action( 'wp' ) ) {
		return false;
	}

	return ( function_exists( 'is_amp_endpoint' ) && \is_amp_endpoint() ) ||
		( function_exists( 'is_wp_amp' ) && \is_wp_amp() ) ||
		( function_exists( 'ampforwp_is_amp_endpoint' ) && \ampforwp_is_amp_endpoint() ) ||
		( function_exists( 'is_penci_amp' ) && \is_penci_amp() ) ||
		Params::get( 'wpamp' );
}

/**
 * Test if a placement has ads.
 *
 * @param int $id Id of the placement.
 *
 * @return bool
 */
function placement_has_ads( $id = '' ) {
	$args = [
		'global_output' => false,
		'cache-busting' => 'ignore',
	];

	return get_the_placement( $id, '', $args ) !== '';
}

/**
 * Test if a group has ads.
 *
 * @param int $id Id of the placement.
 *
 * @return bool
 */
function group_has_ads( $id = '' ) {
	$args = [
		'global_output' => false,
		'cache-busting' => 'ignore',
	];

	return get_the_group( $id, '', $args ) !== '';
}
