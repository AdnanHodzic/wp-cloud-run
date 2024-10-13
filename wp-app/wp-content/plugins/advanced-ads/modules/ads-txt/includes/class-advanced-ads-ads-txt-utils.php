<?php
/**
 * User interface for managing the 'ads.txt' file.
 */
class Advanced_Ads_Ads_Txt_Utils {
	private static $location;

	/**
	 * Get file info.
	 *
	 * @param string $url Url to retrieve the file.
	 * @return array/WP_Error An array containing 'exists', 'is_third_party'.
	 *                        A WP_Error upon error.
	 */
	public static function get_file_info( $url = null ) {
		$url = $url ? $url : home_url( '/' );

		// Disable ssl verification to prevent errors on servers that are not properly configured with its https certificates.
		/** This filter is documented in wp-includes/class-wp-http-streams.php */
		$sslverify    = apply_filters( 'https_local_ssl_verify', false );
		$response     = wp_remote_get(
			trailingslashit( $url ) . 'ads.txt',
			[
				'timeout'   => 3,
				'sslverify' => $sslverify,
				'headers'   => [
					'Cache-Control' => 'no-cache',
				],
			]
		);
		$code         = wp_remote_retrieve_response_code( $response );
		$content      = wp_remote_retrieve_body( $response );
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$file_exists = ! is_wp_error( $response )
			&& 404 !== $code
			&& ( false !== stripos( $content_type, 'text/plain' ) );
		$header_exists = false !== strpos( $content, Advanced_Ads_Ads_Txt_Public::TOP );

		$r = [
			'exists'      => $file_exists && $header_exists,
			'is_third_party' => $file_exists && ! $header_exists
		];

		return $r;
	}



	/**
	 * Check if the another 'ads.txt' file should be hosted on the root domain.
	 *
	 * @return bool
	 */
	public static function need_file_on_root_domain( $url = null ) {
		$url = $url ? $url : home_url( '/' );


		$parsed_url = wp_parse_url( $url );
		if ( ! isset( $parsed_url['host'] ) ) {
			return false;
		}

		$host = $parsed_url['host'];

		if ( WP_Http::is_ip_address( $host ) ) {
			return false;
		}

		$host_parts = explode( '.', $host );
		$count      = count( $host_parts );
		if ( $count < 3 ) {
			return false;
		}

		if ( 3 === $count ) {
			// Example: `http://one.{net/org/gov/edu/co}.two`.
			$suffixes = [ 'net', 'org', 'gov', 'edu', 'co'  ];
			if ( in_array( $host_parts[ $count - 2 ], $suffixes, true ) ) {
				return false;
			}

			// Example: `one.com.au'.
			$suffix_and_tld = implode( '.', array_slice( $host_parts, 1 ) );
			if ( in_array( $suffix_and_tld, [ 'com.au', 'com.br', 'com.pl' ] ) ) {
				return false;
			}

			// `http://www.one.two` will only be crawled if `http://one.two` redirects to it.
			// Check if such redirect exists.
			if ( 'www' === $host_parts[0] ) {
				/*
				 * Do not append `/ads.txt` because otherwise the redirect will not happen.
				 */
				$no_www_url = $parsed_url['scheme'] . '://' . trailingslashit( $host_parts[1] . '.' . $host_parts[2] );

				add_action( 'requests-requests.before_redirect', [ __CLASS__, 'collect_locations' ] );
				wp_remote_get( $no_www_url, [ 'timeout' => 5, 'redirection' => 3 ] );
				remove_action( 'requests-requests.before_redirect', [ __CLASS__, 'collect_locations' ] );

				$no_www_url_parsed = wp_parse_url( self::$location );
				if ( isset( $no_www_url_parsed['host'] ) && $no_www_url_parsed['host'] === $host ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Collect last location.
	 *
	 * @return string $location An URL.
	 */
	public static function collect_locations( $location ) {
		self::$location = $location;
	}

	/**
	 * Check if the site is in a subdirectory, for example 'http://one.two/three'.
	 *
	 * @return bool
	 */
	public static function is_subdir( $url = null ) {
		$url = $url ? $url : home_url( '/' );

		$parsed_url = wp_parse_url( $url );
		if ( ! empty( $parsed_url['path'] ) && '/' !== $parsed_url['path'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Remove duplicate lines.
	 *
	 * @param array $blog_data Array of arrays of blog options, keyed by by blog IDs.
	 * @param array $options {
	 *     Options.
	 *
	 *     @type string    $to_comments    Whether to convert duplicate records to comments.
	 * }
	 * @return array $blog_data Array of arrays of blog options, keyed by by blog IDs.
	 */
	public static function remove_duplicate_lines( $blog_data, $options = [] ) {
		$to_comments = ! empty( $options['to_comments'] );

		$added_records = [];
		foreach ( $blog_data as $blog_id => &$blog_options ) {
			foreach ( $blog_options['networks'] as $id => $data ) {
				// Convert to comments or remove duplicate records that are not comments.
				if ( ! empty( $data['rec'] ) && '#' !== substr( $data['rec'], 0, 1 ) && in_array( $data['rec'], $added_records, true ) ) {
					if ( $to_comments ) {
						$blog_options['networks'][ $id ]['rec'] = '# ' . $blog_options['networks'][ $id ]['rec'];
					} else {
						unset( $blog_options['networks'][ $id ] );
					}
					continue;
				}
				$added_records[] = $data['rec'];
			}

			$blog_options['custom'] = explode( "\n", $blog_options['custom'] );
			$blog_options['custom'] = array_map( 'trim', $blog_options['custom'] );

			foreach ( $blog_options['custom'] as $id => $rec ) {
				// Convert to comments or remove duplicate records that are not comments.
				if ( ! empty( $rec ) && '#' !== substr( $rec, 0, 1 ) && in_array( $rec, $added_records, true ) ) {
					if ( $to_comments ) {
						$blog_options['custom'][ $id ] = '# ' . $blog_options['custom'][ $id ];
					} else {
						unset( $blog_options['custom'][ $id ] );
					}
					continue;
				}
				$added_records[] = $rec;
			}
			$blog_options['custom'] = implode( "\n", $blog_options['custom'] );

		}
		return $blog_data;
	}
}
