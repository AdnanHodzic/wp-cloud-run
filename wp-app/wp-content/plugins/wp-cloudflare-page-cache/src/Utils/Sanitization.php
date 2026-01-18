<?php

namespace SPC\Utils;

use SPC\Modules\Database_Optimization;
use SPC\Modules\Frontend;
use SPC\Modules\Third_Party;

class Sanitization {
	/**
	 * Sanitize excluded URLs.
	 *
	 * @param mixed $value the initial value to be sanitized.
	 * @param array $other_settings the other incoming settings.
	 *
	 * @return string
	 */
	public static function sanitize_excluded_urls( $value, $other_settings = [] ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = explode( "\n", $value );

		if ( isset( $other_settings[ Third_Party::SETTING_WOO_BYPASS_CHECKOUT_PAGE ] ) && (bool) $other_settings[ Third_Party::SETTING_WOO_BYPASS_CHECKOUT_PAGE ] && function_exists( 'wc_get_checkout_url' ) ) {
			$value[] = wc_get_checkout_url() . '*';
		}

		if ( isset( $other_settings[ Third_Party::SETTING_WOO_BYPASS_CART_PAGE ] ) && (bool) $other_settings[ Third_Party::SETTING_WOO_BYPASS_CART_PAGE ] && function_exists( 'wc_get_cart_url' ) ) {
			$value[] = wc_get_cart_url() . '*';
		}

		if ( isset( $other_settings[ Third_Party::SETTING_EDD_BYPASS_CHECKOUT_PAGE ] ) && (bool) $other_settings[ Third_Party::SETTING_EDD_BYPASS_CHECKOUT_PAGE ] && function_exists( 'edd_get_checkout_uri' ) ) {
			$value[] = edd_get_checkout_uri() . '*';
		}

		$filtered_urls = array_unique(
			array_filter(
				array_map(
					function ( $url ) {
						$url = trim( $url );

						$parsed = parse_url( str_replace( [ "\r", "\n" ], '', $url ) );

						if ( $parsed && isset( $parsed['path'] ) ) {
							$uri = $parsed['path'];

							// Force trailing slash
							if ( strlen( $uri ) > 1 && $uri[ strlen( $uri ) - 1 ] !== '/' && $uri[ strlen( $uri ) - 1 ] !== '*' ) {
								$uri .= '/';
							}

							if ( isset( $parsed['query'] ) ) {
								$uri .= "?{$parsed['query']}";
							}

							return $uri;
						}

						return '';
					},
					$value
				)
			)
		);

		return join( "\n", $filtered_urls );
	}

	/**
	 * Sanitize preloaded sitemap URLs.
	 *
	 * @param mixed $value the initial value to be sanitized.
	 * @param array $other_settings the other incoming settings.
	 *
	 * @return string
	 */
	public static function sanitize_preloaded_sitemap_urls( $value, $other_settings = [] ) {
		if ( ! is_string( $value ) ) {
			return '';
		}
		$sitemap_urls        = [];
		$parsed_sitemap_urls = explode( "\n", $value );

		foreach ( $parsed_sitemap_urls as $single_sitemap_url ) {

			$parsed_sitemap_url = parse_url( str_replace( [ "\r", "\n" ], '', $single_sitemap_url ) );

			if ( $parsed_sitemap_url && isset( $parsed_sitemap_url['path'] ) ) {

				$uri = $parsed_sitemap_url['path'];

				if ( strtolower( substr( $uri, - 3 ) ) === 'xml' ) {

					if ( isset( $parsed_sitemap_url['query'] ) ) {
						$uri .= "?{$parsed_sitemap_url['query']}";
					}

					$sitemap_urls[] = $uri;
				}
			}
		}

		return join( "\n", $sitemap_urls );
	}

	/**
	 * Sanitize Log Verbosity.
	 *
	 * @param mixed $value the initial value to be sanitized.
	 *
	 * @return int
	 */
	public static function sanitize_log_verbosity( $value, $other_settings ) {
		$value = (int) $value;

		if ( ! in_array( $value, [ SWCFPC_LOGS_STANDARD_VERBOSITY, SWCFPC_LOGS_HIGH_VERBOSITY ], true ) ) {
			return SWCFPC_LOGS_STANDARD_VERBOSITY;
		}

		return $value;
	}

	/**
	 * Sanitize database optimization interval.
	 *
	 * @param mixed $value the initial value to be sanitized.
	 * @param array $other_settings the other incoming settings.
	 * @return string
	 */
	public static function sanitize_database_optimization_interval( $value, $other_settings ) {
		if ( ! isset( Database_Optimization::get_schedule_options()[ $value ] ) ) {
			return Database_Optimization::NEVER;
		}

		return $value;
	}
	public static function sanitize_lazy_load_behaviour( $value, $other_settings ) {

		if ( ! isset( Frontend::get_lazyload_behaviours()[ $value ] ) ) {
			return Frontend::LAZY_LOAD_BEHAVIOUR_ALL;
		}

		return $value;
	}
}
