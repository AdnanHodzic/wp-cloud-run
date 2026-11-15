<?php
/**
 * Plugin Name: Cloud Run Local Loopback Router
 * Description: Routes same-site WordPress loopback HTTP(S) requests (WP-Cron, Action Scheduler, Site Health, REST API) directly to the local container Apache server on 127.0.0.1 instead of exiting through external CDN/WAF challenges.
 * Author: Adnan Hodzic
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return lowercase hostnames belonging to this WordPress instance.
 *
 * @return string[]
 */
function wcr_get_local_site_hosts(): array {
	$hosts = array();

	foreach ( array( home_url(), site_url() ) as $site_url ) {
		$host = wp_parse_url( $site_url, PHP_URL_HOST );
		if ( is_string( $host ) && '' !== $host ) {
			$host = strtolower( $host );
			if ( ! in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true ) ) {
				$hosts[ $host ] = true;
			}
		}
	}

	return array_keys( $hosts );
}

/**
 * Parse a URL if it targets this WordPress site on a standard HTTP(S) port.
 *
 * @param string $url Request URL.
 * @return array|null Parsed URL components when eligible for local loopback, otherwise null.
 */
function wcr_parse_loopback_target( string $url ): ?array {
	$parsed = wp_parse_url( $url );
	if ( ! is_array( $parsed ) || empty( $parsed['scheme'] ) || empty( $parsed['host'] ) ) {
		return null;
	}

	$scheme = strtolower( $parsed['scheme'] );
	if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
		return null;
	}

	$port = isset( $parsed['port'] ) ? (int) $parsed['port'] : null;
	if ( null !== $port && ! in_array( $port, array( 80, 443 ), true ) ) {
		return null;
	}

	$host = strtolower( $parsed['host'] );
	if ( ! in_array( $host, wcr_get_local_site_hosts(), true ) ) {
		return null;
	}

	$parsed['scheme'] = $scheme;
	$parsed['host']   = $host;

	return $parsed;
}

/**
 * Case-insensitively check whether a header key is present.
 *
 * @param array  $headers Request headers.
 * @param string $name    Header name.
 * @return bool
 */
function wcr_headers_have( array $headers, string $name ): bool {
	$needle = strtolower( $name );
	foreach ( array_keys( $headers ) as $key ) {
		if ( strtolower( (string) $key ) === $needle ) {
			return true;
		}
	}
	return false;
}

/**
 * Preserve original Host and HTTPS forwarding headers before WP_Http dispatches a same-site request.
 * Keeping $url unchanged here ensures cookie domain/secure matching and wp_http_validate_url() pass natively.
 */
add_filter(
	'http_request_args',
	function ( array $parsed_args, string $url ): array {
		$target = wcr_parse_loopback_target( $url );
		if ( null === $target ) {
			return $parsed_args;
		}

		if ( empty( $parsed_args['headers'] ) ) {
			$parsed_args['headers'] = array();
		} elseif ( is_string( $parsed_args['headers'] ) && class_exists( 'WP_Http' ) ) {
			$processed              = WP_Http::processHeaders( $parsed_args['headers'] );
			$parsed_args['headers'] = is_array( $processed['headers'] ?? null ) ? $processed['headers'] : array();
		}

		if ( ! is_array( $parsed_args['headers'] ) ) {
			return $parsed_args;
		}

		if ( ! wcr_headers_have( $parsed_args['headers'], 'Host' ) ) {
			$parsed_args['headers']['Host'] = $target['host'];
		}

		if ( 'https' === $target['scheme'] && ! wcr_headers_have( $parsed_args['headers'], 'X-Forwarded-Proto' ) ) {
			$parsed_args['headers']['X-Forwarded-Proto'] = 'https';
		}

		if ( ! wcr_headers_have( $parsed_args['headers'], 'X-Forwarded-Host' ) ) {
			$parsed_args['headers']['X-Forwarded-Host'] = $target['host'];
		}

		return $parsed_args;
	},
	10,
	2
);

/**
 * If a local request redirects to an external host (e.g., GCS media bucket), strip local Host/Forwarded headers.
 */
add_action(
	'requests-requests.before_redirect',
	function ( string &$location, array &$req_headers ): void {
		if ( null !== wcr_parse_loopback_target( $location ) ) {
			return;
		}

		foreach ( array_keys( $req_headers ) as $key ) {
			if ( in_array( strtolower( (string) $key ), array( 'host', 'x-forwarded-proto', 'x-forwarded-host' ), true ) ) {
				unset( $req_headers[ $key ] );
			}
		}
	},
	10,
	2
);

/**
 * Rewrite cURL transport URL to container-local 127.0.0.1 HTTP port right before sending.
 */
add_action(
	'http_api_curl',
	function ( $handle, array $parsed_args, string $url ): void {
		$effective_url = curl_getinfo( $handle, CURLINFO_EFFECTIVE_URL );
		$check_url     = ( is_string( $effective_url ) && '' !== $effective_url ) ? $effective_url : $url;

		$target = wcr_parse_loopback_target( $check_url );
		if ( null === $target ) {
			return;
		}

		$port = (int) ( getenv( 'PORT' ) ?: 80 );
		if ( $port <= 0 || $port > 65535 ) {
			$port = 80;
		}

		$path      = isset( $target['path'] ) && '' !== $target['path'] ? $target['path'] : '/';
		$query     = isset( $target['query'] ) && '' !== $target['query'] ? '?' . $target['query'] : '';
		$local_url = sprintf( 'http://127.0.0.1:%d%s%s', $port, $path, $query );

		curl_setopt( $handle, CURLOPT_URL, $local_url );
	},
	10,
	3
);
