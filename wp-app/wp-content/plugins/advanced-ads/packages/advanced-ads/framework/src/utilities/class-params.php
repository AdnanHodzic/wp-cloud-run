<?php
/**
 * Params class
 *
 * Easy access to inputs from
 *    INPUT_COOKIE
 *    INPUT_GET
 *    INPUT_POST
 *    INPUT_REQUEST
 *    INPUT_ENV
 *    INPUT_SERVER
 *
 * @package AdvancedAds\Framework\Utilities
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Utilities;

defined( 'ABSPATH' ) || exit;

/**
 * Params class
 */
class Params {

	/**
	 * Get field from input.
	 *
	 * @param string $input   Input to get from.
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	private static function input( $input, $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return filter_has_var( $input, $id ) ? filter_input( $input, $id, $filter, $flag ) : $default;
	}

	/**
	 * Get field from query string.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function get( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_GET, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM post.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function post( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_POST, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM server.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function server( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_SERVER, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM cookie.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function cookie( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_COOKIE, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from FORM env.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function env( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		return self::input( INPUT_ENV, $id, $default, $filter, $flag );
	}

	/**
	 * Get field from request.
	 *
	 * @param string $id      Field id to get.
	 * @param mixed  $default Default value to return if field is not found.
	 * @param int    $filter  The ID of the filter to apply.
	 * @param int    $flag    The ID of the flag to apply.
	 *
	 * @return mixed
	 */
	public static function request( $id, $default = false, $filter = FILTER_DEFAULT, $flag = [] ) {
		$request_filters = [
			'G' => INPUT_GET,
			'P' => INPUT_POST,
			'C' => INPUT_COOKIE,
		];

		// This directive describes the order in which PHP registers GET, POST and Cookie variables into the _REQUEST array. Registration is done from left to right, newer values override older values.
		$request_order = ini_get( 'request_order' ) ? ini_get( 'request_order' ) : 'GP';
		$request_order = str_split( $request_order );
		$request_order = array_reverse( $request_order );

		foreach ( $request_order as $r ) {
			if ( filter_has_var( $request_filters[ $r ], $id ) ) {
				// Return early if found.
				return filter_input( $request_filters[ $r ], $id, $filter, $flag );
			}
		}

		return $default;
	}
}
