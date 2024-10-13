<?php
/**
 * Array utilities
 *
 * @package AdvancedAds\Framework\Utilities
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Utilities;

use ArrayAccess;

defined( 'ABSPATH' ) || exit;

/**
 * Arr class.
 */
class Arr {

	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	public static function accessible( $value ) {
		return is_array( $value ) || $value instanceof ArrayAccess;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param ArrayAccess|array $arr Array to check key in.
	 * @param string|int        $key Key to check for.
	 *
	 * @return bool
	 */
	public static function exists( $arr, $key ) {
		if ( $arr instanceof ArrayAccess ) {
			return $arr->offsetExists( $key );
		}

		return array_key_exists( $key, $arr );
	}

	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param \ArrayAccess|array $array   Array to get from.
	 * @param string|int|null    $key     Key to get.
	 * @param mixed              $default Default value to return if key does not exist.
	 *
	 * @return mixed
	 */
	public static function get( $array, $key, $default = null ) {
		if ( ! static::accessible( $array ) ) {
			return $default;
		}

		if ( null === $key ) {
			return $array;
		}

		if ( static::exists( $array, $key ) ) {
			return $array[ $key ];
		}

		if ( ! str_contains( $key, '.' ) ) {
			return $array[ $key ] ?? $default;
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( static::accessible( $array ) && static::exists( $array, $segment ) ) {
				$array = $array[ $segment ];
			} else {
				return $default;
			}
		}

		return $array;
	}

	/**
	 * Check if an item or items exist in an array using "dot" notation.
	 *
	 * @param \ArrayAccess|array $array Array to check in.
	 * @param string|array       $keys  Key or keys to check for.
	 *
	 * @return bool
	 */
	public static function has( $array, $keys ): bool {
		$keys = (array) $keys;

		if ( ! $array || [] === $keys ) {
			return false;
		}

		foreach ( $keys as $key ) {
			$subkey_array = $array;

			if ( static::exists( $array, $key ) ) {
				continue;
			}

			foreach ( explode( '.', $key ) as $segment ) {
				if ( static::accessible( $subkey_array ) && static::exists( $subkey_array, $segment ) ) {
					$subkey_array = $subkey_array[ $segment ];
				} else {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Determine if any of the keys exist in an array using "dot" notation.
	 *
	 * @param \ArrayAccess|array $array Array to check in.
	 * @param string|array       $keys  Key or keys to check for.
	 *
	 * @return bool
	 */
	public static function has_any( $array, $keys ): bool {
		if ( null === $keys ) {
			return false;
		}

		$keys = (array) $keys;

		if ( ! $array ) {
			return false;
		}

		if ( [] === $keys ) {
			return false;
		}

		foreach ( $keys as $key ) {
			if ( static::has( $array, $key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Insert a single array item inside another array at a set position
	 *
	 * @param array $arr      Array to modify. Is passed by reference, and no return is needed.
	 * @param array $new      New array to insert.
	 * @param int   $position Position in the main array to insert the new array.
	 */
	public static function insert( &$arr, $new, $position ) {
		$before = array_slice( $arr, 0, $position - 1 );
		$after  = array_diff_key( $arr, $before );
		$arr    = array_merge( $before, $new, $after );
	}

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param array           $array Array to set value in.
	 * @param string|int|null $key   Key to set.
	 * @param mixed           $value Value to set.
	 *
	 * @return array
	 */
	public static function set( &$array, $key, $value ): array {
		if ( null === $key ) {
			$array = $value;
			return $array;
		}

		$keys = explode( '.', $key );

		foreach ( $keys as $i => $key ) {
			if ( 1 === count( $keys ) ) {
				break;
			}

			unset( $keys[ $i ] );

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $array[ $key ] ) || ! is_array( $array[ $key ] ) ) {
				$array[ $key ] = [];
			}

			$array = &$array[ $key ];
		}

		$array[ array_shift( $keys ) ] = $value;

		return $array;
	}

	/**
	 * Filter the array using the given callback.
	 *
	 * @param array    $array    Array to filter.
	 * @param callable $callback Callback to use for filtering.
	 *
	 * @return array
	 */
	public static function where( $array, callable $callback ): array {
		return array_filter( $array, $callback, ARRAY_FILTER_USE_BOTH );
	}

	/**
	 * Filter items where the value is not null.
	 *
	 * @param array $array Array to filter.
	 *
	 * @return array
	 */
	public static function where_not_null( $array ): array {
		return static::where( $array, fn ( $value ) => null !== $value );
	}

	/**
	 * If the given value is not an array and not null, wrap it in one.
	 *
	 * @param mixed $value Value to wrap.
	 *
	 * @return array
	 */
	public static function wrap( $value ): array {
		if ( null === $value ) {
			return [];
		}

		return is_array( $value ) ? $value : [ $value ];
	}
}
