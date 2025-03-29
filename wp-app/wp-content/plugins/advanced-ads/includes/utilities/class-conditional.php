<?php
/**
 * The class provides utility functions related to check condition related to plugin.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use Advanced_Ads;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Conditional.
 */
class Conditional {

	/**
	 * Check if the author of the ad can use unfiltered_html.
	 *
	 * @param int $author_id User ID of the ad author.
	 *
	 * @return bool
	 */
	public static function can_author_unfiltered_html( $author_id ): bool {
		if ( defined( 'DISALLOW_UNFILTERED_HTML' ) && DISALLOW_UNFILTERED_HTML ) {
			return false;
		}

		$unfiltered_allowed = user_can( $author_id, 'unfiltered_html' );
		if ( $unfiltered_allowed || ! is_multisite() ) {
			return $unfiltered_allowed;
		}

		$options = Advanced_Ads::get_instance()->options();
		if ( ! isset( $options['allow-unfiltered-html'] ) ) {
			$options['allow-unfiltered-html'] = [];
		}
		$allowed_roles = $options['allow-unfiltered-html'];
		$user          = get_user_by( 'id', $author_id );

		if ( ! $user ) {
			// In case the author was removed from the site.
			$user = wp_get_current_user();
		}

		return ! empty( array_intersect( $user->roles, $allowed_roles ) );
	}

	/**
	 * Checks if the current request is an AJAX request.
	 * It can be a request to `admin-ajax.php` or to `ajax-handler.php`.
	 *
	 * @return bool
	 */
	public static function doing_ajax(): bool {
		return wp_doing_ajax() || 'XMLHttpRequest' === Params::server( 'HTTP_X_REQUESTED_WITH' );
	}

	/**
	 * Determines whether the current request is an autosave
	 *
	 * @return bool
	 */
	public static function doing_autosave(): bool {
		return defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE;
	}

	/**
	 * Check if we have cache enabled on site
	 *
	 * @return bool
	 */
	public static function has_cache_plugins(): bool {
		return ( defined( 'WP_CACHE' ) && WP_CACHE ) // General cache constant.
			|| defined( 'W3TC' ) // W3 Total Cache.
			|| function_exists( 'wp_super_cache_text_domain' ) // WP Super Cache.
			|| defined( 'WP_ROCKET_SLUG' ) // WP Rocket.
			|| defined( 'WPFC_WP_CONTENT_DIR' ) // WP Fastest Cache.
			|| class_exists( 'HyperCache', false ) // Hyper Cache.
			|| defined( 'CE_CACHE_DIR' ); // Cache Enabler.
	}

	/**
	 * Check if the current screen uses a search or filter.
	 *
	 * @return bool
	 */
	public static function has_filter_or_search(): bool {
		$ad_params        = [ 's', 'adtype', 'adsize', 'adgroup', 'addate', 'ad_author', 'ad_debugmode', 'ad_displayonce', 'ad_privacyignore' ];
		$group_params     = [ 's', 'group_type' ];
		$placement_params = [ 's', 'placement-type' ];

		$params = array_merge( $ad_params, $group_params, $placement_params );

		foreach ( $params as $param ) {
			if ( ! empty( Params::get( $param ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the current user has a role on this site.
	 *
	 * @return bool
	 */
	public static function has_user_role_on_site(): bool {
		return in_array(
			get_current_blog_id(),
			wp_list_pluck( get_blogs_of_user( get_current_user_id() ), 'userblog_id' ),
			true
		);
	}

	/**
	 * Check if any add-on is activated
	 *
	 * @return bool true if there is any add-on activated
	 */
	public static function is_any_addon_activated() {
		return has_action( 'advanced-ads-loaded' );
	}

	/**
	 * Check if the current user agent is given or a bot
	 *
	 * @return bool
	 */
	public static function is_ua_bot(): bool {
		// show ads on AMP version also for bots in order to allow Google (and maybe others) to cache the page.
		if ( advads_is_amp() ) {
			return false;
		}

		$user_agent = Params::server( 'HTTP_USER_AGENT', '' );
		if ( empty( $user_agent ) ) {
			return true;
		}

		// Make sure delimiters in regex are escaped.
		$bots = implode( '|', Data::get_bots() );
		$bots = preg_replace( '/(.*?)(?<!\\\)' . preg_quote( '/', '/' ) . '(.*?)/', '$1\\/$2', $bots );

		return preg_match( sprintf( '/%s/i', $bots ), wp_unslash( $user_agent ) );
	}

	/**
	 * Returns true if a REST request has an Advanced Ads endpoint.
	 *
	 * @return bool
	 */
	public static function is_gutenberg_writing_request(): bool {
		global $wp;

		$rest_route   = $wp->query_vars['rest_route'] ?? '';
		$is_writing   = in_array( Params::server( 'REQUEST_METHOD' ), [ 'POST', 'PUT' ], true );
		$is_gutenberg = mb_strpos( $rest_route, '/wp/v2/posts' ) !== false || mb_strpos( $rest_route, '/wp/v2/pages' ) !== false;

		return $is_gutenberg && $is_writing;
	}

	/**
	 * Check if php execution is globally forbidden
	 *
	 * @return bool
	 */
	public static function is_php_allowed(): bool {
		return ! ( defined( 'ADVANCED_ADS_DISALLOW_PHP' ) && ADVANCED_ADS_DISALLOW_PHP )
			&& ! ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT );
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * TODO: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
	 *
	 * @return bool
	 */
	public static function is_rest_request() {
		$request = Params::server( 'REQUEST_URI' );
		if ( empty( $request ) ) {
			return false;
		}

		return false !== strpos( $request, trailingslashit( rest_get_url_prefix() ) );
	}

	/**
	 * Check if we are on desired screen.
	 *
	 * @param array|string $screen_id Screen id.
	 *
	 * @return bool
	 */
	public static function is_screen( $screen_id ): bool {
		static $advads_current_screen;

		if ( ! is_admin() ) {
			return false;
		}

		if ( null === $advads_current_screen ) {
			$advads_current_screen = get_current_screen()->id;
			$advads_current_screen = explode( '_page_', $advads_current_screen );
			$advads_current_screen = array_pop( $advads_current_screen );
			$advads_current_screen = str_replace( 'advanced-ads-', '', $advads_current_screen );
		}

		return is_array( $screen_id )
			? in_array( $advads_current_screen, $screen_id, true )
			: $advads_current_screen === $screen_id;
	}

	/**
	 * Check if the current screen belongs to Advanced Ads
	 *
	 * @return bool
	 */
	public static function is_screen_advanced_ads(): bool {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! isset( $screen->id ) ) {
			return false;
		}

		return in_array( $screen->id, Data::get_admin_screen_ids(), true );
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
	 * Check if the user can subscribe to a notice.
	 *
	 * @param string $notice Notice ID.
	 *
	 * @return bool
	 */
	public static function user_can_subscribe( $notice ) {
		$current_user = wp_get_current_user();
		// Early bail!!
		if ( empty( $current_user->ID ) || empty( $current_user->user_email ) ) {
			return false;
		}

		$subscribed_notices = get_user_meta( $current_user->ID, 'advanced-ads-subscribed', true );
		if ( ! is_array( $subscribed_notices ) ) {
			$subscribed_notices = [];
		}

		// secureserver.net email address belong to GoDaddy (?) and have very, very low open rates. Seems like only temporary setup.
		return ( ! isset( $subscribed_notices[ $notice ] ) && is_email( $current_user->user_email ) && false === strpos( $current_user->user_email, 'secureserver.net' ) )
			? true : false;
	}
}
