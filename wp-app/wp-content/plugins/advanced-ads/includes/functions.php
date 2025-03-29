<?php
/**
 * Functions that are directly available in WordPress themes (and plugins)
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

use AdvancedAds\Framework\Utilities\Params;

/**
 * Returns the default arguments for an entity.
 *
 * @param string     $method The method to get the entity.
 * @param int|string $id     The ID of the entity.
 * @param array      $args   Additional arguments for the entity.
 *
 * @return array The default arguments for the entity.
 */
function wp_advads_default_entity_arguments( $method, $id, $args ): array {
	$args = (array) $args;

	$args['previous_id']     = $args['id'] ?? null;
	$args['previous_method'] = $args['method'] ?? null;

	if ( $id || ! isset( $args['id'] ) ) {
		$args['id'] = $id;
	}

	$args['method'] = $method;

	return apply_filters( 'advanced-ads-ad-select-args', $args, $method, $id );
}

/**
 * Sets additional arguments for an entity.
 *
 * @param object $entity The entity object.
 * @param array  $args   The additional arguments to set for the entity.
 *
 * @return void
 */
function wp_advads_set_additional_args( $entity, $args ): void {
	$entity->set_prop_temp( 'ad_args', $args );
}

/**
 * Load ad conditions.
 *
 * @return array
 */
function wp_advads_get_ad_conditions(): array {
	static $ad_conditions;
	if ( null === $ad_conditions ) {
		$ad_conditions = include ADVADS_ABSPATH . 'includes/array_ad_conditions.php';
	}

	return $ad_conditions;
}

/**
 * Get user IP address.
 *
 * @return bool|string IP address or false if not found
 */
function get_user_ip_address() {
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- NO NEED TO SANITIZE HEADERS

	// Define the list of IP headers in the order of priority.
	$ip_headers = [
		'HTTP_CF_CONNECTING_IP',       // Cloudflare.
		'HTTP_CLIENT_IP',              // General.
		'HTTP_X_REAL_IP',              // General.
		'HTTP_X_FORWARDED_FOR',        // General.
		'HTTP_X_FORWARDED',            // General.
		'HTTP_X_CLUSTER_CLIENT_IP',    // General.
		'HTTP_FORWARDED_FOR',          // General.
		'HTTP_FORWARDED',              // General.
		'REMOTE_ADDR',                 // Default server value.
	];

	// Get the server's IP address.
	$server_ip = Params::server( 'SERVER_ADDR', '' );

	foreach ( $ip_headers as $header ) {
		// Check if the header exists and is not empty.
		$data = Params::server( $header, '' );
		if ( ! empty( $data ) ) {
			// Split the header value by comma to handle multiple IP addresses.
			$ip_list = explode( ',', $data );

			foreach ( $ip_list as $ip ) {
				// Trim whitespace and remove any 'for=' prefix from the IP address.
				$ip = trim( str_replace( 'for=', '', $ip ) );

				// Validate the IP address and ensure it's not the server's IP.
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) && $ip !== $server_ip ) {
					return $ip;
				}
			}
		}
	}
	// phpcs:enable
	return false;
}
