<?php
/**
 * Data formatting utilities
 *
 * @package AdvancedAds\Framework\Utilities
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Formatting class.
 */
class Formatting {

	/**
	 * Converts a string (e.g. 'yes' or 'no') to a bool.
	 *
	 * @param string|bool $str String to convert. If a bool is passed it will be returned as-is.
	 *
	 * @return bool
	 */
	public static function string_to_bool( $str ): bool {
		$str = $str ?? '';
		return is_bool( $str ) ? $str : ( 'yes' === strtolower( $str ) || 1 === $str || 'true' === strtolower( $str ) || '1' === $str || 'on' === strtolower( $str ) );
	}

	/**
	 * Converts a bool to a 'yes' or 'no'.
	 *
	 * @param bool|string $boolval Bool to convert. If a string is passed it will first be converted to a bool.
	 *
	 * @return string
	 */
	public static function bool_to_string( $boolval ): string {
		if ( ! is_bool( $boolval ) ) {
			$boolval = self::string_to_bool( $boolval );
		}

		return true === $boolval ? 'yes' : 'no';
	}

	/**
	 * Explode a string into an array by $delimiter and remove empty values.
	 *
	 * @param string $str       String to convert.
	 * @param string $delimiter Delimiter, defaults to ','.
	 *
	 * @return array
	 */
	public static function string_to_array( $str, $delimiter = ',' ): array {
		$str = $str ?? '';
		return is_array( $str ) ? $str : array_filter( explode( $delimiter, $str ) );
	}

	/**
	 * Convert a float to a string without locale formatting which PHP adds when changing floats to strings.
	 *
	 * @param float $floatval Float value to format.
	 *
	 * @return string
	 */
	public static function float_to_string( $floatval ): string {
		if ( ! is_float( $floatval ) ) {
			return $floatval;
		}

		$locale = localeconv();
		$string = strval( $floatval );
		$string = str_replace( $locale['decimal_point'], '.', $string );

		return $string;
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $value Data to sanitize.
	 *
	 * @return string|array
	 */
	public static function clean( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ self::class, 'clean' ], $value );
		}

		return is_scalar( $value ) ? sanitize_text_field( $value ) : $value;
	}

	/**
	 * Function wp_check_invalid_utf8 with recursive array support.
	 *
	 * @param string|array $value Data to sanitize.
	 *
	 * @return string|array
	 */
	public static function check_invalid_utf8( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ self::class, 'check_invalid_utf8' ], $value );
		}

		return wp_check_invalid_utf8( $value );
	}

	/**
	 * Run clean over posted textarea but maintain line breaks.
	 *
	 * @param string $value Data to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_textarea( $value ) {
		return implode( "\n", array_map( 'clean', explode( "\n", $value ?? '' ) ) );
	}

	/**
	 * Sanitize a string destined to be a tooltip.
	 *
	 * @param string $value Data to sanitize.
	 *
	 * @return string
	 */
	public static function sanitize_tooltip( $value ) {
		return htmlspecialchars(
			wp_kses(
				html_entity_decode( $value ?? '' ),
				[
					'br'     => [],
					'em'     => [],
					'strong' => [],
					'small'  => [],
					'span'   => [],
					'ul'     => [],
					'li'     => [],
					'ol'     => [],
					'p'      => [],
				]
			)
		);
	}
}
