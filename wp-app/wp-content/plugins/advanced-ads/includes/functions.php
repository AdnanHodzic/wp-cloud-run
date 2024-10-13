<?php

/*
 * functions that are directly available in WordPress themes (and plugins)
 */

/**
 * Return ad content
 *
 * @since 1.0.0
 * @param int $id id of the ad (post)
 * @param arr $args additional arguments
 */
function get_ad($id = 0, $args = []){
	if ( defined( 'ADVANCED_ADS_DISABLE_CHANGE' ) && ADVANCED_ADS_DISABLE_CHANGE ) {
		$args = [];
	}

	return Advanced_Ads_Select::get_instance()->get_ad_by_method( $id, 'id', $args );
}

/**
 * Echo an ad
 *
 * @since 1.0.0
 * @param int $id id of the ad (post)
 * @param arr $args additional arguments
 */
function the_ad($id = 0, $args = []){
	echo get_ad( $id, $args );
}

/**
 * Return an ad from an ad group based on ad weight
 *
 * @since 1.0.0
 * @param int $id id of the ad group (taxonomy)
 *
 */
function get_ad_group( $id = 0, $args = [] ) {
	if ( defined( 'ADVANCED_ADS_DISABLE_CHANGE' ) && ADVANCED_ADS_DISABLE_CHANGE ) {
		$args = [];
	}
	return Advanced_Ads_Select::get_instance()->get_ad_by_method( $id, 'group', $args );
}

/**
 * Echo an ad from an ad group
 *
 * @since 1.0.0
 * @param int $id id of the ad (post)
 */
function the_ad_group($id = 0){
	echo get_ad_group( $id );
}

/**
 * Return content of an ad placement
 *
 * @since 1.1.0
 * @param string $id slug of the ad placement
 *
 */
function get_ad_placement( $id = '', $args = [] ) {
	if ( defined( 'ADVANCED_ADS_DISABLE_CHANGE' ) && ADVANCED_ADS_DISABLE_CHANGE ) {
		$args = [];
	}
	return Advanced_Ads_Select::get_instance()->get_ad_by_method( $id, 'placement', $args );
}

/**
 * Return content of an ad placement
 *
 * @since 1.1.0
 * @param string $id slug of the ad placement
 */
function the_ad_placement($id = ''){
	echo get_ad_placement( $id );
}

/**
 * Return true if ads can be displayed
 *
 * @since 1.4.9
 * @return bool, true if ads can be displayed
 */
function advads_can_display_ads(){
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
	if ( is_admin()
		|| is_embed()
		|| is_feed()
		|| ( isset( $pagenow ) && in_array( $pagenow, [ 'wp-login.php', 'wp-signup.php', 'wp-activate.php' ], true ) )
		|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		|| ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
	) {
		return false;
	}

	if ( ! did_action( 'wp' ) ) {
		return false;
	}

	return ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() )
	|| ( function_exists( 'is_wp_amp' ) && is_wp_amp() )
	|| ( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() )
	|| ( function_exists( 'is_penci_amp' ) && is_penci_amp() )
	|| isset( $_GET [ 'wpamp' ] );
}

/**
 * Test if a placement has ads.
 *
 * @return bool
 */
function placement_has_ads( $id = '' ) {
	$args = [
		'global_output' => false,
		'cache-busting' => 'ignore',
	];
	return Advanced_Ads_Select::get_instance()->get_ad_by_method( $id, 'placement', $args ) != '';

}

/**
 * Test if a group has ads.
 *
 * @return bool
 */
function group_has_ads( $id = '' ) {
	$args = [
		'global_output' => false,
		'cache-busting' => 'ignore',
	];
	return Advanced_Ads_Select::get_instance()->get_ad_by_method( $id, 'group', $args ) != '';
}
