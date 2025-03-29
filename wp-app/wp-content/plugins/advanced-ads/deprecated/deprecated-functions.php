<?php
/**
 * The functions graveyard.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0.0
 */

/**
 * Return ad content
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int   $id   Id of the ad (post).
 * @param array $args Additional arguments.
 */
function get_ad( $id = 0, $args = [] ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'get_the_ad' );

	return get_the_ad( $id, '', $args );
}

/**
 * Return an ad from an ad group based on ad weight
 *
 * @since 1.0.0
 * @deprecated 2.0.0
 *
 * @param int   $id   Id of the ad group (taxonomy).
 * @param array $args Additional arguments.
 */
function get_ad_group( $id = 0, $args = [] ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'get_the_group' );

	return get_the_group( $id, '', $args );
}

/**
 * Return content of an ad placement
 *
 * @since 1.1.0
 * @deprecated 2.0.0
 *
 * @param string $id   Slug of the ad placement.
 * @param array  $args Additional arguments.
 */
function get_ad_placement( $id = '', $args = [] ) {
	_deprecated_function( __FUNCTION__, '2.0.0', 'get_the_placement' );
	return get_the_placement( $id, '', $args );
}
