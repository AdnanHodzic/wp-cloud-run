<?php

use AdvancedAds\Entities;

/**
 * Control Ad Authors.
 */
class Advanced_Ads_Ad_Authors {
	/**
	 * Singleton instance of this class.
	 *
	 * @var Advanced_Ads_Ad_Authors
	 */
	private static $instance;

	/**
	 * Attach callbacks to hooks and filters.
	 */
	private function __construct() {
		add_filter( 'wp_dropdown_users_args', [ $this, 'filter_ad_authors' ] );
		add_action( 'pre_post_update', [ $this, 'sanitize_author_saving' ], 10, 2 );
		add_filter( 'map_meta_cap', [ $this, 'filter_editable_posts' ], 10, 4 );
	}

	/**
	 * Singleton.
	 *
	 * @return Advanced_Ads_Ad_Authors
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Ensure that users cannot assign ads to users with unfiltered_html if they don't have the capability themselves.
	 *
	 * @param array $query_args WP_User_Query args.
	 *
	 * @return array
	 */
	public function filter_ad_authors( $query_args ) {
		if ( get_current_screen()->post_type !== Entities::POST_TYPE_AD ) {
			return $query_args;
		}

		if ( is_multisite() ) {
			return $this->multisite_filter_ad_authors( $query_args );
		}

		$current_user_has_unfiltered_html = current_user_can( 'unfiltered_html' );
		$user_roles_to_display            = array_filter( wp_roles()->role_objects, static function( WP_Role $role ) use ( $current_user_has_unfiltered_html ) {
			if ( $current_user_has_unfiltered_html ) {
				return $role->has_cap( 'advanced_ads_edit_ads' );
			}

			return ! $role->has_cap( 'unfiltered_html' ) && $role->has_cap( 'advanced_ads_edit_ads' );
		} );

		$query_args['role__in'] = wp_list_pluck( $user_roles_to_display, 'name' );

		return $query_args;
	}

	/**
	 * Ensure that users cannot assign ads to users who have more rights on multisite.
	 *
	 * @param array $query_args WP_User_Query args.
	 *
	 * @return array
	 */
	private function multisite_filter_ad_authors( $query_args ) {
		if ( is_super_admin() ) {
			return $query_args;
		}

		$options       = Advanced_Ads::get_instance()->options();
		$allowed_roles = isset( $options['allow-unfiltered-html'] ) ? $options['allow-unfiltered-html'] : [];

		// if the current user can unfiltered_html, return the default args.
		if ( ! empty( array_intersect( wp_get_current_user()->roles, $allowed_roles ) ) ) {
			return $query_args;
		}

		// if the current user can't use unfiltered_html, they should not be able to assign the ad to a user that can.
		$user_roles_to_display = array_filter( wp_roles()->role_objects, static function( WP_Role $role ) use ( $allowed_roles ) {
			return ! in_array( $role->name, $allowed_roles, true ) && $role->has_cap( 'advanced_ads_edit_ads' );
		} );

		$query_args['role__in'] = wp_list_pluck( $user_roles_to_display, 'name' );
		// exclude super-admins from the author dropdown.
		$query_args['exclude'] = array_map( static function( $login ) {
			return get_user_by( 'login', $login )->ID;
		}, get_super_admins() );

		return $query_args;
	}

	/**
	 * Prevent users from editing the form data and assign ads to users they're not allowed to.
	 * Wp_die() if tampering detected.
	 *
	 * @param int   $post_id The current post id.
	 * @param array $data    The post data to be saved.
	 *
	 * @return void
	 */
	public function sanitize_author_saving( $post_id, $data ) {
		if (
			get_post_type( $post_id ) !== Entities::POST_TYPE_AD
			|| (int) $data['post_author'] === get_current_user_id()
			|| (int) $data['post_author'] === (int) get_post_field( 'post_author', $post_id )
		) {
			return;
		}

		$user_query = new WP_User_Query( $this->filter_ad_authors( [ 'fields' => 'ID' ] ) );
		if ( ! in_array( (int) $data['post_author'], array_map( function($value) { return (int)$value; }, $user_query->get_results() ), true ) ) {
			wp_die( esc_html__( 'Sorry, you\'re not allowed to assign this user.', 'advanced-ads' ) );
		}
	}

	/**
	 * Prevent users from editing posts of users with more rights than themselves.
	 *
	 * @param array  $caps    Needed capabilities.
	 * @param string $cap     Requested capability.
	 * @param int    $user_id The user_id for the cap check.
	 * @param array  $args    Arguments array for checking primitive capabilities.
	 *
	 * @return array
	 */
	public function filter_editable_posts( $caps, $cap, $user_id, $args ) {
		if ( $cap !== 'advanced_ads_edit_ads' || empty( $args ) ) {
			return $caps;
		}

		$post_id = (int) $args[0];
		if ( empty( $post_id ) ) {
			return $caps;
		}

		$ad = \Advanced_Ads\Ad_Repository::get( $post_id );
		if ( $ad->type !== 'plain' ) {
			return $caps;
		}

		$author_id = (int) get_post_field( 'post_author', $post_id );
		$author    = get_userdata( $author_id );
		if ( $author === false || ( $author_id !== $user_id && ! user_can( $author, $cap, $post_id ) ) ) {
			$author_id = $user_id;
		}

		static $user_query;
		if ( $user_query === null ) {
			$user_query = new WP_User_Query( $this->filter_ad_authors( [ 'fields' => 'ID' ] ) );
		}

		if ( ! in_array( $author_id, array_map( function($value) { return (int)$value; }, $user_query->get_results() ), true ) ) {
			$caps[] = 'do_not_allow';
		}

		return $caps;
	}
}
