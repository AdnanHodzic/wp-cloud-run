<?php
/**
 * Plugin Name: WordPress Security Hardening
 * Description: Disables XML-RPC password authentication and pingback DDoS vectors (while preserving Jetpack HMAC XML-RPC), hides WordPress version disclosure, and disables the admin file editor.
 * Author: Adnan Hodzic
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Disable the built-in theme/plugin PHP file editor in WP Admin.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Disable core XML-RPC username/password authentication (blocks brute-force via xmlrpc.php)
// while keeping token-signed integrations such as Jetpack (?for=jetpack) functional.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Remove XML-RPC pingback methods used for DDoS amplification.
add_filter(
	'xmlrpc_methods',
	function ( array $methods ): array {
		unset(
			$methods['pingback.ping'],
			$methods['pingback.extensions.getPingbacks']
		);
		return $methods;
	},
	9999
);

// Strip X-Pingback HTTP header and close pingbacks site-wide.
add_filter(
	'wp_headers',
	function ( array $headers ): array {
		unset( $headers['X-Pingback'], $headers['x-pingback'] );
		return $headers;
	},
	9999
);
add_filter( 'pings_open', '__return_false', 9999 );

// Hide WordPress version disclosure from HTML head and RSS/Atom feeds,
// and remove legacy RSD / Windows Live Writer discovery tags.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
add_filter( 'the_generator', '__return_empty_string' );
