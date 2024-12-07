<?php

namespace SPC\Utils;

class Helpers {

	private const BYPASS_CACHE_REASON_HEADER = 'X-WP-CF-Super-Cache-Disabled-Reason';

	/**
	 * Check if the request is a cacheable request
	 *
	 * @param string $reason The reason why the request is not cacheable.
	 *
	 * @return void
	 */
	public static function bypass_reason_header( $reason = '' ) {
		if ( ! is_string( $reason ) || empty( $reason ) ) {
			return;
		}

		header( sprintf( '%s: %s', self::BYPASS_CACHE_REASON_HEADER, $reason ) );
	}

	/**
	 * Checks if we have the cache bypass reason header.
	 *
	 * @return bool
	 */
	public static function has_cache_bypass_reason_header() {
		$headers = array_map(
			function ( $header ) {
				return strstr( $header, ':', true );
			},
			headers_list() 
		);

		return in_array( self::BYPASS_CACHE_REASON_HEADER, $headers );
	}
}
