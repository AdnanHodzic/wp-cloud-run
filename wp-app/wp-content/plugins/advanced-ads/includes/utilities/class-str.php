<?php
/**
 * The class provides utility functions related to String.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * String.
 */
class Str {

	/**
	 * Determine if a string contains a given substring
	 *
	 * @param string $needle   The substring to search for in the haystack.
	 * @param string $haystack The string to search in.
	 *
	 * @return bool
	 */
	public static function str_contains( $needle, $haystack ): bool {
		return false !== strpos( $haystack, $needle );
	}
}
