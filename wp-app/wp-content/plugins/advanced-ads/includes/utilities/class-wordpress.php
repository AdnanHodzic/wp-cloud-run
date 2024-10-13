<?php
/**
 * The class provides utility functions related to WordPress.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Utilities WordPress.
 */
class WordPress {

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	public static function current_action() {
		$action = Params::request( 'action' );
		if ( '-1' !== $action ) {
			return sanitize_key( $action );
		}

		$action = Params::request( 'action2' );
		if ( '-1' !== $action ) {
			return sanitize_key( $action );
		}

		return false;
	}

	/**
	 * Returns whether the current user has the specified capability.
	 *
	 * @param string $capability Capability name.
	 *
	 * @return bool
	 */
	public static function user_can( $capability = 'manage_options' ): bool {
		// Admins can do everything.
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return current_user_can(
			apply_filters( 'advanced-ads-capability', $capability )
		);
	}

	/**
	 * Returns the capability needed to perform an action
	 *
	 * @param string $capability A capability to check, can be internal to Advanced Ads.
	 *
	 * @return string
	 */
	public static function user_cap( $capability = 'manage_options' ) {
		// Admins can do everything.
		if ( current_user_can( 'manage_options' ) ) {
			return 'manage_options';
		}

		return apply_filters( 'advanced-ads-capability', $capability );
	}

	/**
	 * Get site domain
	 *
	 * @param string $part Part of domain.
	 *
	 * @return string
	 */
	public static function get_site_domain( $part = 'host' ): string {
		$domain = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

		if ( 'name' === $part ) {
			$domain = explode( '.', $domain );
			$domain = count( $domain ) > 2 ? $domain[1] : $domain[0];
		}

		return $domain;
	}

	/**
	 * Returns true if the current request is a REST request.
	 *
	 * @return bool
	 */
	public static function is_rest_request(): bool {
		$request = Params::server( 'REQUEST_URI' );
		if ( empty( $request ) ) {
			return false;
		}

		return false !== strpos( $request, trailingslashit( rest_get_url_prefix() ) );
	}

	/**
	 * Returns true if a REST request has an Advanced Ads endpoint.
	 *
	 * @return bool
	 */
	public static function is_gutenberg_writing_request(): bool {
		global $wp;
		$rest_route = $wp->query_vars['rest_route'] ?? '';

		$is_writing   = in_array( Params::server( 'REQUEST_METHOD' ), [ 'POST', 'PUT' ], true );
		$is_gutenberg = strpos( $rest_route, '/wp/v2/posts' ) !== false || strpos( $rest_route, '/wp/v2/pages' ) !== false;

		return $is_gutenberg && $is_writing;
	}

	/**
	 * Unserializes data only if it was serialized.
	 *
	 * @link https://patchstack.com/articles/unauthenticated-php-object-injection-in-flatsome-theme-3-17-5/
	 *
	 * @param string $data Data that might be unserialized.
	 *
	 * @return mixed Unserialized data can be any type.
	 */
	public static function maybe_unserialize( $data ) {
		if ( is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
			return @unserialize( trim( $data ), [ 'allowed_classes' => false ] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		}

		return $data;
	}
}
