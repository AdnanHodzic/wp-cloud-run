<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Advanced Ads main admin class
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 *
 * Admin class.
 *
 * @deprecated 2.0.0
 */
class Advanced_Ads_Admin {
	/**
	 * Check if the current screen belongs to Advanced Ads
	 *
	 * @deprecated 1.47.0
	 *
	 * @return bool
	 */
	public static function screen_belongs_to_advanced_ads() {
		_deprecated_function( __METHOD__, '1.47.0', '\AdvancedAds\Utilities\Conditional::is_screen_advanced_ads()' );
		return Conditional::is_screen_advanced_ads();
	}

	/**
	 * Get DateTimeZone object for the WP installation
	 *
	 * @return DateTimeZone object set in WP settings.
	 * @see        Advanced_Ads_Utils::get_wp_timezone()
	 *
	 * @deprecated This is also used outside of admin as well as other plugins.
	 */
	public static function get_wp_timezone() {
		return Advanced_Ads_Utils::get_wp_timezone();
	}
}
