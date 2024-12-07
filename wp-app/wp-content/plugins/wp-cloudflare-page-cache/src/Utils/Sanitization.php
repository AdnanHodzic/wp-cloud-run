<?php

namespace SPC\Utils;

class Sanitization {
	/**
	 * Sanitize excluded URLs.
	 *
	 * @param string $value the initial value to be sanitized.
	 * @param array $other_settings the other incoming settings.
	 *
	 * @return string
	 */
	public static function sanitize_excluded_urls( $value, $other_settings = [] ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = explode( "\n", $value );

		if ( isset( $other_settings['swcfpc_cf_bypass_woo_checkout_page'] ) && ( (int) $other_settings['swcfpc_cf_bypass_woo_checkout_page'] > 0 && function_exists( 'wc_get_checkout_url' ) ) ) {
			$value[] = wc_get_checkout_url() . '*';
		}

		if ( isset( $other_settings['swcfpc_cf_bypass_woo_cart_page'] ) && ( (int) $other_settings['swcfpc_cf_bypass_woo_cart_page'] ) > 0 && function_exists( 'wc_get_cart_url' ) ) {
			$value[] = wc_get_cart_url() . '*';
		}

		if ( isset( $other_settings['swcfpc_cf_bypass_edd_checkout_page'] ) && ( (int) $other_settings['swcfpc_cf_bypass_edd_checkout_page'] ) > 0 && function_exists( 'edd_get_checkout_uri' ) ) {
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
	 * @param string $value the initial value to be sanitized.
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

					if ( isset( $parsed_url['query'] ) ) {
						$uri .= "?{$parsed_url['query']}";
					}

					$sitemap_urls[] = $uri;
				}
			}
		}

		return join( "\n", $sitemap_urls );
	}
}
