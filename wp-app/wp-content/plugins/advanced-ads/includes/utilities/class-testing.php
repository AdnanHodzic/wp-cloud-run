<?php
/**
 * The class is responsible to allow split testing websites based on their URL.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Utilities;

use Advanced_Ads;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Utilities Testing.
 */
class Testing {
	/**
	 * Show stuff to new users only.
	 *
	 * @param integer $timestamp time after which to show whatever.
	 * @param string  $group optional group.
	 *
	 * @return bool true if user enabled after given timestamp.
	 */
	public static function show_to_new_users( $timestamp, $group = 'a' ) {
		return self::get_group_by_url( null, $group ) && self::is_new_user( $timestamp );
	}

	/**
	 * Check if user started after a given date
	 *
	 * @param integer $timestamp time stamp.
	 *
	 * @return bool true if user is added after timestamp.
	 */
	public static function is_new_user( $timestamp = 0 ) {
		// Allow admins to see version for new users in any case.
		if ( Conditional::user_can( 'advanced_ads_manage_options' ) && Params::request( 'advads-ignore-timestamp' ) ) {
			return true;
		}

		$timestamp = absint( $timestamp );

		$options   = Advanced_Ads::get_instance()->internal_options();
		$installed = isset( $options['installed'] ) ? $options['installed'] : 0;

		return $installed >= $timestamp;
	}

	/**
	 * Create a random group
	 *
	 * @param string $url optional parameter.
	 * @param string $ex group.
	 *
	 * @return bool
	 */
	public static function get_group_by_url( $url = '', $ex = 'a' ) {
		$url  = self::get_short_url( $url );
		$code = (int) substr( md5( $url ), - 1 );

		switch ( $ex ) {
			case 'b':
				return ( $code & 2 ) >> 1; // returns 1 or 0.
			case 'c':
				return ( $code & 4 ) >> 2; // returns 1 or 0.
			case 'd':
				return ( $code & 8 ) >> 3; // returns 1 or 0.
			default:
				return $code & 1; // returns 1 or 0.
		}
	}

	/**
	 * Get short version of home_url() by removing protocol, www and slash
	 *
	 * @param string $url URL to be shortened.
	 *
	 * @return string
	 */
	public static function get_short_url( $url = '' ) {
		$url = empty( $url ) ? home_url() : $url;

		// Strip protocols.
		if ( preg_match( '/^(\w[\w\d]*:\/\/)?(www\.)?(.*)$/', trim( $url ), $matches ) ) {
			$url = $matches[3];
		}

		// Strip slashes.
		return trim( $url, '/' );
	}
}
