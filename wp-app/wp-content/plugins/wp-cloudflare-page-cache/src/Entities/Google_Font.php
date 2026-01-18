<?php

namespace SPC\Entities;

/**
 * Google Fonts Parser.
 *
 * Utility class to parse Google Fonts URLs and get the families.
 *
 * @link https://developers.google.com/fonts/docs/css2
 * @link https://developers.google.com/fonts/docs/css
 */
class Google_Font {
	public const API_V1 = 'v1';
	public const API_V2 = 'v2';

	/**
	 * The API version.
	 *
	 * @var self::API_V1|self::API_V2
	 */
	private $api_version;

	/**
	 * The original URL.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Constructor.
	 *
	 * @param string $url
	 */
	public function __construct( $url ) {
		$this->url         = $url;
		$this->api_version = strpos( $url, 'css2' ) !== false ? self::API_V2 : self::API_V1;
	}

	/**
	 * Get the API version.
	 *
	 * @return string
	 */
	public function get_api_version() {
		return $this->api_version;
	}

	/**
	 * Get the original URL.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get the families from the Google Font URL.
	 *
	 * In v2 API, multiple families use `&family=` multiple times, so we need to
	 * make sure we get all of them.
	 *
	 * @link https://developers.google.com/fonts/docs/css2#multiple_families
	 *
	 * @return array
	 */
	public function get_families() {
		$parsed = parse_url( $this->url );

		if ( $this->api_version === self::API_V1 ) {
			parse_str( $parsed['query'] ?? '', $query_params );

			if ( ! isset( $query_params['family'] ) ) {
				return [];
			}

			if ( is_array( $query_params['family'] ) ) {
				return $query_params['family'];
			}

			return array_filter( array_unique( explode( '|', $query_params['family'] ) ) );
		}

		$as_array = explode( '&', $parsed['query'] );

		$families = [];

		foreach ( $as_array as $item ) {
			if ( strpos( $item, 'family=' ) === false ) {
				continue;
			}

			$families[] = explode( '=', $item )[1];
		}

		return array_filter( array_unique( $families ) );
	}

	/**
	 * Check if the URL is a Google Font URL.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool
	 */
	public static function is_google_font_url( $url ) {
		return strpos( $url, 'fonts.googleapis.com/css' ) !== false;
	}
}
