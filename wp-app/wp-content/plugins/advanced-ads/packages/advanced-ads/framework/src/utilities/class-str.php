<?php
/**
 * String utilities
 *
 * @package AdvancedAds\Framework\Utilities
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Str class.
 */
class Str {

	/**
	 * Capitalizes a string.
	 *
	 * @param string $str The string to be capitalized.
	 *
	 * @return string The capitalized string.
	 */
	public static function capitalize( $str ) {
		return ucwords( str_replace( '_', ' ', $str ) );
	}

	/**
	 * Check if the string contains the given value.
	 *
	 * @param string $needle      The sub-string to search for.
	 * @param string $haystack    The string to search.
	 * @param bool   $ignore_case Whether to ignore the case.
	 *
	 * @return bool
	 */
	public static function contains( $needle, $haystack, $ignore_case = false ): bool {
		if ( $ignore_case ) {
			$haystack = self::to_lower( $haystack );
		}

		return '' !== $needle && mb_strpos( $haystack, $needle ) !== false
			? true : false;
	}

	/**
	 * Validates whether the passed variable is a empty string.
	 *
	 * @param mixed $str The variable to validate.
	 *
	 * @return bool Whether or not the passed value is a non-empty string.
	 */
	public static function is_empty( $str ): bool {
		return ! is_string( $str ) || empty( $str );
	}

	/**
	 * Validates whether the passed variable is a non-empty string.
	 *
	 * @param mixed $str The variable to validate.
	 *
	 * @return bool Whether or not the passed value is a non-empty string.
	 */
	public static function is_non_empty( $str ): bool {
		return is_string( $str ) && '' !== $str;
	}

	/**
	 * Check if the string end with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function ends_with( $needle, $haystack ): bool {
		return '' !== $needle && mb_substr( $haystack, -self::length( $needle ) ) === (string) $needle
			? true : false;
	}

	/**
	 * Return the length of the given string.
	 *
	 * @param string      $value    The string to get the length of.
	 * @param string|null $encoding The encoding to use.
	 *
	 * @return int
	 */
	public static function length( $value, $encoding = null ): int {
		if ( $encoding ) {
			return mb_strlen( $value, $encoding );
		}

		return mb_strlen( $value );
	}

	/**
	 * Check if the string begins with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function starts_with( $needle, $haystack ): bool {
		return '' !== $needle && mb_substr( $haystack, 0, self::length( $needle ) ) === (string) $needle
			? true : false;
	}

	/**
	 * Wrapper for mb_strtoupper which see's if supported first.
	 *
	 * @param string      $str      String to format.
	 * @param string|null $encoding Encoding to use.
	 *
	 * @return string
	 */
	public static function to_upper( $str, $encoding = 'UTF-8' ) {
		$str = $str ?? '';
		if ( function_exists( 'mb_strtoupper' ) ) {
			return $encoding ? mb_strtoupper( $str, $encoding ) : mb_strtoupper( $str );
		}

		return strtoupper( $str );
	}

	/**
	 * Make a string lowercase.
	 * Try to use mb_strtolower() when available.
	 *
	 * @param string      $str      String to format.
	 * @param string|null $encoding Encoding to use.
	 *
	 * @return string
	 */
	public static function to_lower( $str, $encoding = 'UTF-8' ) {
		$str = $str ?? '';
		if ( function_exists( 'mb_strtolower' ) ) {
			return $encoding ? mb_strtolower( $str, $encoding ) : mb_strtolower( $str );
		}

		return strtolower( $str );
	}
}
