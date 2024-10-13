<?php
/**
 * The class provides utility functions related to check condition related to plugin.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Conditional.
 */
class Conditional {

	/**
	 * Check if the current screen belongs to Advanced Ads
	 *
	 * @return bool
	 */
	public static function is_screen_advanced_ads(): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! isset( $screen->id ) ) {
			return false;
		}

		return in_array( $screen->id, Data::get_admin_screen_ids(), true );
	}

	/**
	 * Check if the current screen uses a search or filter.
	 *
	 * @return bool
	 */
	public static function has_filter_or_search(): bool {
		$params = [ 's', 'adtype', 'adsize', 'adgroup' ];

		foreach ( $params as $param ) {
			if ( ! empty( Params::get( $param ) ) ) {
				return true;
			}
		}

		return false;
	}
}
