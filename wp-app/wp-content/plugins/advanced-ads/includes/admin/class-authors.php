<?php
/**
 * The class manages the ad authors.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads;
use AdvancedAds\Constants;
use AdvancedAds\Framework\Interfaces\Integration_Interface;
use WP_Role;
use WP_User_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Control Ad Authors.
 */
class Authors implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'wp_dropdown_users_args', [ $this, 'filter_ad_authors' ] );
		add_action( 'pre_post_update', [ $this, 'sanitize_author_saving' ], 10, 2 );
		add_filter( 'map_meta_cap', [ $this, 'filter_editable_posts' ], 10, 4 );
	}

	/**
	 * Ensure that users cannot assign ads to users with unfiltered_html if they don't have the capability themselves.
	 *
	 * @param array $query_args WP_User_Query args.
	 *
	 * @return array
	 */
	public function filter_ad_authors( $query_args ) {
		$screen = get_current_screen();

		if ( ! $screen || Constants::POST_TYPE_AD !== $screen->post_type ) {
			return $query_args;
		}

		if ( is_multisite() ) {
			return $this->multisite_filter_ad_authors( $query_args );
		}

		$user_roles_to_display  = $this->filtered_user_roles();
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
		$allowed_roles = $options['allow-unfiltered-html'] ?? [];

		// if the current user can unfiltered_html, return the default args.
		if ( ! empty( array_intersect( wp_get_current_user()->roles, $allowed_roles ) ) ) {
			return $query_args;
		}

		// if the current user can't use unfiltered_html, they should not be able to assign the ad to a user that can.
		$user_roles_to_display = array_filter(
			wp_roles()->role_objects,
			function ( WP_Role $role ) use ( $allowed_roles ) {
				return ! in_array( $role->name, $allowed_roles, true ) && $role->has_cap( 'advanced_ads_edit_ads' );
			}
		);

		$query_args['role__in'] = wp_list_pluck( $user_roles_to_display, 'name' );

		// Exclude super-admins from the author dropdown.
		$query_args['exclude'] = array_map(
			function ( $login ) {
				return get_user_by( 'login', $login )->ID;
			},
			get_super_admins()
		);

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
			get_post_type( $post_id ) !== Constants::POST_TYPE_AD ||
			get_current_user_id() === (int) $data['post_author'] ||
			(int) get_post_field( 'post_author', $post_id ) === (int) $data['post_author']
		) {
			return;
		}

		$user_query = new WP_User_Query( $this->filter_ad_authors( [ 'fields' => 'ID' ] ) );
		$user_query = array_map( 'absint', $user_query->get_results() );

		if ( ! in_array( (int) $data['post_author'], $user_query, true ) ) {
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
		if ( 'advanced_ads_edit_ads' !== $cap || empty( $args ) ) {
			return $caps;
		}

		$post_id = (int) $args[0];
		if ( empty( $post_id ) ) {
			return $caps;
		}

		$ad = wp_advads_get_ad( $post_id );
		if ( $ad && ! $ad->is_type( 'plain' ) ) {
			return $caps;
		}

		$author_id = (int) get_post_field( 'post_author', $post_id );
		$author    = get_userdata( $author_id );

		if ( false === $author ) {
			$author_id = $user_id;
		}

		if ( $author_id !== $user_id && ! user_can( $author, $cap, $post_id ) ) {
			return [ 'do_not_allow' ];
		}

		static $users;

		if ( null === $users ) {
			$user_query = new WP_User_Query( $this->filter_ad_authors( [ 'fields' => 'ID' ] ) );
			$users      = array_map( 'absint', $user_query->get_results() );
		}

		if ( ! in_array( $author_id, $users, true ) ) {
			return [ 'do_not_allow' ];
		}

		return $caps;
	}

	/**
	 * Get the user roles that are allowed to edit ads.
	 *
	 * @return array
	 */
	private function filtered_user_roles(): array {
		$current_user_has_unfiltered_html = current_user_can( 'unfiltered_html' );
		return array_filter(
			wp_roles()->role_objects,
			function ( \WP_Role $role ) use ( $current_user_has_unfiltered_html ) {
				if ( $current_user_has_unfiltered_html ) {
					return $role->has_cap( 'advanced_ads_edit_ads' );
				}

				return ! $role->has_cap( 'unfiltered_html' ) && $role->has_cap( 'advanced_ads_edit_ads' );
			}
		);
	}
}
