<?php
/**
 * HTML formatting utilities
 *
 * @package AdvancedAds\Framework\Utilities
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * HTML class.
 */
class HTML {

	/**
	 * Implode and escape HTML attributes for output.
	 *
	 * @param array $raw_attributes Attribute name value pairs.
	 *
	 * @return string
	 */
	public static function build_attributes( $raw_attributes ) {
		$attributes = [];
		foreach ( $raw_attributes as $name => $value ) {
			$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}

		return implode( ' ', $attributes );
	}

	/**
	 * Generate classnames from arguments
	 *
	 * @return string
	 */
	public static function classnames(): string {
		$args    = func_get_args();
		$data    = array_reduce( $args, [ __CLASS__, 'classnames_reduce' ], [] );
		$classes = array_map(
			[ __CLASS__, 'classnames_mapper' ],
			array_keys( $data ),
			array_values( $data )
		);
		$classes = array_filter( $classes );

		return implode( ' ', $classes );
	}

	/**
	 * Classnames helper function
	 *
	 * @param mixed $carry Holds the return value of the previous iteration.
	 * @param mixed $item  Holds the value of the current iteration.
	 *
	 * @return mixed
	 */
	private static function classnames_reduce( $carry, $item ) {
		if ( is_array( $item ) ) {
			return array_merge( $carry, $item );
		}

		$carry[] = $item;

		return $carry;
	}

	/**
	 * Classnames helper function
	 *
	 * @param mixed $key   Key of array item.
	 * @param mixed $value Value of array item.
	 *
	 * @return array
	 */
	private static function classnames_mapper( $key, $value ) {
		$condition = $value;
		$return    = $key;

		if ( is_int( $key ) ) {
			$condition = null;
			$return    = $value;
		}

		$is_array             = is_array( $return );
		$is_object            = is_object( $return );
		$is_stringable_type   = ! $is_array && ! $is_object;
		$is_stringable_object = $is_object && method_exists( $return, '__toString' );

		if ( ! $is_stringable_type && ! $is_stringable_object ) {
			return null;
		}

		if ( null === $condition ) {
			return $return;
		}

		return $condition ? $return : null;
	}
}
