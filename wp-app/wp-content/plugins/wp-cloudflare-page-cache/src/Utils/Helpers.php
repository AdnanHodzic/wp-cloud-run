<?php

namespace SPC\Utils;

use SPC\Modules\Dashboard;

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
		if ( empty( $reason ) ) {
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

		return in_array( self::BYPASS_CACHE_REASON_HEADER, $headers, true );
	}

	/**
	 * Get the second level domain of the site.
	 *
	 * @return string
	 */
	public static function get_second_level_domain() {
		$site_hostname = parse_url( home_url(), PHP_URL_HOST );

		if ( is_null( $site_hostname ) ) {
			return '';
		}

		// get the domain name from the hostname
		$site_domain = preg_replace( '/^www\./', '', $site_hostname );

		return $site_domain;
	}

	/**
	 * Get the menu icon.
	 *
	 * @param string $fill The fill color of the icon.
	 *
	 * @return string
	 */
	public static function get_menu_icon( $fill = '#a7aaad' ) {
		$svg = '<svg width="185" height="229" viewBox="0 0 185 229" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#spc-clip-path)"><path d="M12.322 180.443L160.356 210.236L159.512 114.191H166.264L144.489 88.8715V189.305L35.7846 165.167C31.7335 164.492 29.286 158.078 29.286 153.942V128.623V36.8825L139.003 60.5139V43.0435L12.4064 15.6143L12.322 180.443Z" fill="' . esc_attr( $fill ) . '"/><path d="M134.446 124.992V161.114C134.469 162.372 134.21 163.618 133.69 164.763C133.169 165.907 132.399 166.921 131.437 167.73C130.474 168.539 129.343 169.122 128.126 169.438C126.909 169.754 125.637 169.793 124.402 169.554L42.1988 150.733L38.3165 143.475L42.1988 142.884L36.0377 131.913L115.962 149.89V131.322L46.0811 116.721C44.1149 116.362 42.3402 115.316 41.0729 113.771C39.8055 112.225 39.1278 110.28 39.1604 108.281V69.7116C39.1289 68.4282 39.3906 67.1545 39.9255 65.9875C40.4605 64.8204 41.2546 63.7908 42.2475 62.977C43.2403 62.1631 44.4058 61.5865 45.6551 61.291C46.9044 60.9956 48.2047 60.989 49.457 61.2718L133.855 78.9953L124.74 87.4351L128.875 87.9415L122.039 96.9721L55.7868 83.1308V101.192L128.031 116.721C129.875 117.177 131.511 118.241 132.675 119.742C133.839 121.243 134.463 123.093 134.446 124.992Z" fill="' . esc_attr( $fill ) . '"/><path d="M166.686 121.026V219.097L6.49863 184.325V8.35538L138.919 36.3755V29.1172L0 0L0.75958 190.992L173.438 228.38L173.016 138.581H185L166.686 121.026Z" fill="' . esc_attr( $fill ) . '"/></g><defs><clipPath id="spc-clip-path"><rect width="185" height="228.38" fill="white"/></clipPath></defs></svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Check if the current page is a SPC admin page.
	 *
	 * @return bool
	 */
	public static function is_spc_admin_page() {
		if ( ! is_admin() ) {
			return false;
		}

		if ( ! isset( $_GET['page'] ) ) {
			return false;
		}

		// About page is not a SPC page.
		if ( strpos( $_GET['page'], 'ti-about' ) === 0 ) {
			return false;
		}

		if ( strpos( $_GET['page'], Dashboard::PAGE_SLUG ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the plugin content directory path.
	 *
	 * @return string
	 */
	public static function get_plugin_content_dir() {
		$parts = parse_url( home_url() );

		return WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$parts['host']}";
	}

	/**
	 * Get the plugin content directory url.
	 *
	 * @return string
	 */
	public static function get_plugin_content_dir_url() {
		$parts = parse_url( home_url() );

		return content_url( "wp-cloudflare-super-page-cache/{$parts['host']}" );
	}

	/**
	 * Get the current url.
	 *
	 * @return string
	 */
	public static function get_current_url() {
		return function_exists( 'swcfpc_normalize_url' ) ? swcfpc_normalize_url( null ) : null;
	}
	/**
	 * Get the current full url.
	 *
	 * @return string
	 */
	public static function get_current_absolute_url() {
		$current_url = self::get_current_url();
		$host        = $_SERVER['HTTP_HOST'] ?? '';
		if ( empty( $host ) ) {
			return $current_url;
		}
		/**
		 * If the current url does not start with the host, add the host to the current url.
		 * The scheme is just to normalize the url.
		 */
		return strpos( $current_url, $host ) === 0 ? "http://{$current_url}" : "http://{$host}/{$current_url}";
	}
	/**
	 * Get the url id.
	 *
	 * @param string $url The url.
	 *
	 * @return int
	 */
	public static function get_url_id( $url ) {
		srand( crc32( $url ) );
		$random_id = rand();
		srand();
		return $random_id;
	}

	/**
	 * Get the wp-config.php path.
	 *
	 * wp-config.php can be placed one level up from the root directory.
	 *
	 * @return string
	 */
	public static function get_wp_config_path() {
		if ( is_file( ABSPATH . 'wp-config.php' ) ) {
			return ABSPATH . 'wp-config.php';
		}

		if ( is_file( dirname( ABSPATH ) . '/wp-config.php' ) ) {
			return dirname( ABSPATH ) . '/wp-config.php';
		}

		return ABSPATH . 'wp-config.php';
	}
}
