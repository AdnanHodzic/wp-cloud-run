<?php
/**
 * AJAX Ads
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads_Pro;
use Advanced_Ads_Privacy;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use Advanced_Ads_Admin_Notices;
use AdvancedAds\Frontend\Stats;
use Advanced_Ads_Admin_Licenses;
use Advanced_Ads_Ad_Blocker_Admin;
use Advanced_Ads_Ad_Health_Notices;
use Advanced_Ads_Display_Conditions;
use Advanced_Ads_Visitor_Conditions;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend AJAX.
 */
class AJAX implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'delete_post', [ $this, 'delete_ad' ] );
		add_action( 'wp_ajax_advads_ad_select', [ $this, 'ad_select' ] );
		add_action( 'wp_ajax_nopriv_advads_ad_select', [ $this, 'ad_select' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );
		add_action( 'wp_ajax_nopriv_advads-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );
		add_action( 'wp_ajax_advads_dismiss_welcome', [ $this, 'dismiss_welcome' ] );
		add_action( 'wp_ajax_advads_newsletter', [ $this, 'subscribe_to_newsletter' ] );
		add_action( 'wp_ajax_advads_activate_addon', [ $this, 'activate_add_on' ] );
		add_action( 'wp_ajax_advads-multiple-subscribe', [ $this, 'multiple_subscribe' ] );

		add_action( 'wp_ajax_load_ad_parameters_metabox', [ $this, 'load_ad_parameters_metabox' ] );
		add_action( 'wp_ajax_load_visitor_conditions_metabox', [ $this, 'load_visitor_condition' ] );
		add_action( 'wp_ajax_load_display_conditions_metabox', [ $this, 'load_display_condition' ] );
		add_action( 'wp_ajax_advads-terms-search', [ $this, 'search_terms' ] );
		add_action( 'wp_ajax_advads-authors-search', [ $this, 'search_authors' ] );
		add_action( 'wp_ajax_advads-close-notice', [ $this, 'close_notice' ] );
		add_action( 'wp_ajax_advads-hide-notice', [ $this, 'hide_notice' ] );
		add_action( 'wp_ajax_advads-subscribe-notice', [ $this, 'subscribe' ] );
		add_action( 'wp_ajax_advads-activate-license', [ $this, 'activate_license' ] );
		add_action( 'wp_ajax_advads-deactivate-license', [ $this, 'deactivate_license' ] );
		add_action( 'wp_ajax_advads-adblock-rebuild-assets', [ $this, 'adblock_rebuild_assets' ] );
		add_action( 'wp_ajax_advads-post-search', [ $this, 'post_search' ] );
		add_action( 'wp_ajax_advads-ad-injection-content', [ $this, 'inject_placement' ] );
		add_action( 'wp_ajax_advads-save-hide-wizard-state', [ $this, 'save_wizard_state' ] );
		add_action( 'wp_ajax_advads-adsense-enable-pla', [ $this, 'adsense_enable_pla' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-display', [ $this, 'ad_health_notice_display' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-push-adminui', [ $this, 'ad_health_notice_push_adminui' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-hide', [ $this, 'ad_health_notice_hide' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-unignore', [ $this, 'ad_health_notice_unignore' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-solved', [ $this, 'ad_health_notice_solved' ] );
		add_action( 'wp_ajax_advads-update-frontend-element', [ $this, 'update_frontend_element' ] );
		add_action( 'wp_ajax_advads-get-block-hints', [ $this, 'get_block_hints' ] );
		add_action( 'wp_ajax_advads-placements-allowed-ads', [ $this, 'get_allowed_ads_for_placement_type' ] );
		add_action( 'wp_ajax_advads-placement-update-item', [ $this, 'placement_update_item' ] );
	}

	/**
	 * Prepare the ad post type to be removed
	 *
	 * @param int $post_id id of the post.
	 *
	 * @return void
	 */
	public function delete_ad( $post_id ): void {
		global $wpdb;

		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		if ( $post_id > 0 ) {
			$post_type = get_post_type( $post_id );
			if ( Constants::POST_TYPE_AD === $post_type ) {
				/**
				 * Images uploaded to an image ad type get the `_advanced-ads_parent_id` meta key from WordPress automatically
				 * the following SQL query removes that meta data from any attachment when the ad is removed.
				 */
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %d", '_advanced-ads_parent_id', $post_id ) ); // phpcs:ignore
			}
		}
	}

	/**
	 * Background plugin activation from the add-on box
	 *
	 * @return void
	 */
	public function activate_add_on(): void {
		wp_ajax_activate_plugin();
	}

	/**
	 * Subscribe to the newsletter
	 *
	 * @return void
	 */
	public function subscribe_to_newsletter(): void {
		if ( ! wp_verify_nonce( sanitize_text_field( Params::post( 'nonce' ), '' ), 'advads-newsletter-subscribe' ) ) {
			wp_send_json_error( 'Not Authorized', 401 );
		}
		if ( ! Conditional::user_can( 'advanced_ads_see_interface' ) ) {
			wp_send_json_error(
				[
					/* translators: %s is a URL. */
					'message' => sprintf( __( 'An error occurred. Please use <a href="%s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), 'http://eepurl.com/bk4z4P' ),
				],
				403
			);
		}

		wp_send_json_success( \Advanced_Ads_Admin_Notices::get_instance()->subscribe( 'nl_free_addons' ), 200 );
	}

	/**
	 * Stop showing the welcome after a click on the dismiss icon
	 *
	 * @return void
	 */
	public function dismiss_welcome(): void {
		Welcome::get()->dismiss();
		wp_send_json_success( 'OK', 200 );
	}

	/**
	 * Simple wp ajax interface for ad selection.
	 *
	 * @return void
	 */
	public function ad_select(): void {
		add_filter( 'advanced-ads-output-inline-css', '__return_false' );

		// Allow modules / add-ons to test (this is rather late but should happen before anything important is called).
		do_action( 'advanced-ads-ajax-ad-select-init' );

		$ad_ids      = Params::request( 'ad_ids', [], FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );
		$defered_ads = Params::request( 'deferedAds', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( is_array( $ad_ids ) ) {
			foreach ( $ad_ids as $ad_id ) {
				Stats::get()->add_entity( 'ad', is_array( $ad_id ) ? $ad_id['id'] : $ad_id, '' );
			}
		}

		if ( $defered_ads ) {
			$response = [];

			$requests_by_blog = [];
			foreach ( $defered_ads as $request ) {
				$blog_id                        = $request['blog_id'] ?? get_current_blog_id();
				$requests_by_blog[ $blog_id ][] = $request;
			}

			foreach ( $requests_by_blog as $blog_id => $requests ) {
				if ( get_current_blog_id() !== $blog_id && is_multisite() ) {
					switch_to_blog( $blog_id );
				}

				foreach ( $requests as $request ) {
					$result              = $this->select_one( $request );
					$result['elementId'] = $request['elementId'] ?? null;
					$response[]          = $result;
				}

				if ( get_current_blog_id() !== $blog_id && is_multisite() ) {
					restore_current_blog();
				}
			}

			wp_send_json( $response );
		}

		$response = $this->select_one( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_send_json( $response );
	}

	/**
	 * Push an Ad Health notice to the queue in the backend
	 *
	 * @return void
	 */
	public function ad_health_notice_push(): void {
		check_ajax_referer( 'advanced-ads-ad-health-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$key  = ! empty( $_REQUEST['key'] ) ? esc_attr( Params::request( 'key' ) ) : false;
		$attr = Params::request( 'attr', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		// Update or new entry?
		if ( isset( $attr['mode'] ) && 'update' === $attr['mode'] ) {
			Advanced_Ads_Ad_Health_Notices::get_instance()->update( $key, $attr );
		} else {
			Advanced_Ads_Ad_Health_Notices::get_instance()->add( $key, $attr );
		}

		die();
	}

	/**
	 * Check if AJAX ad can be displayed, with consent information sent in request.
	 *
	 * @param bool $can_display Whether this ad can be displayed.
	 * @param Ad   $ad          The ad object.
	 *
	 * @return bool
	 */
	public function can_display_by_consent( $can_display, $ad ) {
		// Early bail!!
		if ( ! $can_display ) {
			return $can_display;
		}

		// If consent is overridden for the ad.
		$privacy_props = $ad->get_prop( 'privacy' );
		if ( ! empty( $privacy_props['ignore-consent'] ) ) {
			return true;
		}

		// If privacy module is not active, we can display.
		if ( empty( Advanced_Ads_Privacy::get_instance()->options()['enabled'] ) ) {
			return true;
		}

		$consent_state = Params::request( 'consent', 'not_allowed' );

		// Consent is either given or not needed.
		if ( in_array( $consent_state, [ 'not_needed', 'accepted' ], true ) ) {
			return true;
		}

		// If there is custom code, don't display the ad (unless it's a group).
		if (
			class_exists( 'Advanced_Ads_Pro' ) &&
			! empty( Advanced_Ads_Pro::get_instance()->get_custom_code( $ad ) ) &&
			! $ad->is_type( 'group' )
		) {
			return false;
		}

		// See if this ad type needs consent.
		return ! Advanced_Ads_Privacy::get_instance()->ad_type_needs_consent( $ad->get_type() );
	}

	/**
	 * Subscribe to multiple newsletters
	 */
	public function multiple_subscribe() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		$groups = ! empty( Params::post( 'groups' ) ) ? json_decode( Params::post( 'groups' ), true ) : [];

		if ( ! Conditional::user_can( 'advanced_ads_see_interface' ) || empty( $groups ) ) {
			wp_send_json_error(
				[
					/* translators: %s is a URL. */
					'message' => sprintf( __( 'An error occurred. Please use <a href="%s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), 'http://eepurl.com/bk4z4P' ),
				],
				400
			);
		}

		foreach ( $groups as $group ) {
			$message = Advanced_Ads_Admin_Notices::get_instance()->subscribe( $group );
		}

		wp_send_json_success( [ 'message' => $message ?? '' ] );
	}

	/**
	 * Provides a single ad (ad, group, placement) given ID and selection method.
	 *
	 * @param array $request Request.
	 *
	 * @return array
	 */
	private function select_one( $request ) {
		$method = (string) $request['ad_method'] ?? null;

		if ( 'id' === $method ) {
			$method = 'ad';
		}

		$function  = "get_the_$method";
		$id        = (string) $request['ad_id'] ?? null;
		$arguments = $request['ad_args'] ?? [];

		if ( is_string( $arguments ) ) {
			$arguments = stripslashes( $arguments );
			$arguments = json_decode( $arguments, true );
		}

		if ( ! empty( $request['elementId'] ) ) {
			$arguments['cache_busting_elementid'] = $request['elementId'];
		}

		// Report error.
		if ( empty( $id ) || ! function_exists( $function ) ) {
			return [
				'status'  => 'error',
				'message' => 'No valid ID or METHOD found.',
			];
		}

		/**
		 * Filters the received arguments before passing them to ads/groups/placements.
		 *
		 * @param array $arguments Existing arguments.
		 * @param array $request   Request data.
		 */
		$arguments    = apply_filters( 'advanced-ads-ajax-ad-select-arguments', $arguments, $request );
		$previous_ads = Stats::get()->entities;
		add_filter( 'advanced-ads-can-display-ad', [ $this, 'can_display_by_consent' ], 10, 2 );
		$content = $function( (int) $id, '', $arguments );

		if ( empty( $content ) ) {
			return [
				'status'  => 'error',
				'message' => 'No displayable ad found for privacy settings.',
			];
		}

		$response = [
			'status'  => 'success',
			'item'    => $content,
			'id'      => $id,
			'method'  => $method,
			'ads'     => array_slice( Stats::get()->entities, count( $previous_ads ) ),
			'blog_id' => get_current_blog_id(),
		];

		return apply_filters(
			'advanced-ads-cache-busting-item',
			$response,
			[
				'id'     => $id,
				'method' => $method,
				'args'   => $arguments,
			]
		);
	}

	/**
	 * Load content of the ad parameter metabox
	 *
	 * @since 1.0.0
	 */
	public function load_ad_parameters_metabox() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );
		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$type_string = Params::post( 'ad_type' );
		$ad_id       = Params::post( 'ad_id', 0, FILTER_VALIDATE_INT );
		if ( empty( $ad_id ) ) {
			die();
		}

		if ( wp_advads_has_ad_type( $type_string ) ) {
			$ad      = wp_advads_get_ad( $ad_id, $type_string );
			$ad_type = wp_advads_get_ad_type( $type_string );
			if ( method_exists( $ad_type, 'render_parameters' ) ) {
				$ad_type->render_parameters( $ad );
			}

			if ( $ad_type->has_size() ) {
				include ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-parameters-size.php';
			}

			// Extend the AJAX-loaded parameters form by ad type.
			do_action( "advanced-ads-ad-params-after-{$ad->get_type()}", $ad );
		}

		die();
	}

	/**
	 * Load interface for single visitor condition
	 *
	 * @since 1.5.4
	 */
	public function load_visitor_condition() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		// get visitor condition types.
		$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance()->conditions;
		$condition          = [];
		$condition['type']  = Params::post( 'type', '' );
		$index              = Params::post( 'index', 0, FILTER_VALIDATE_INT );

		$form_name = Params::post( 'form_name', Advanced_Ads_Visitor_Conditions::FORM_NAME );

		if ( ! isset( $visitor_conditions[ $condition['type'] ] ) ) {
			die();
		}

		$metabox = $visitor_conditions[ $condition['type'] ]['metabox'];
		if ( method_exists( $metabox[0], $metabox[1] ) ) {
			call_user_func( [ $metabox[0], $metabox[1] ], $condition, $index, $form_name );
		}

		die();
	}

	/**
	 * Load interface for single display condition
	 *
	 * @since 1.7
	 */
	public function load_display_condition() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		// get display condition types.
		$conditions        = Advanced_Ads_Display_Conditions::get_instance()->conditions;
		$condition         = [];
		$condition['type'] = Params::post( 'type', '' );
		$index             = Params::post( 'index', 0, FILTER_VALIDATE_INT );
		$form_name         = Params::post( 'form_name', Advanced_Ads_Display_Conditions::FORM_NAME );

		if ( ! isset( $conditions[ $condition['type'] ] ) ) {
			die();
		}

		$metabox = $conditions[ $condition['type'] ]['metabox'];
		if ( method_exists( $metabox[0], $metabox[1] ) ) {
			call_user_func( [ $metabox[0], $metabox[1] ], $condition, $index, $form_name );
		}

		die();
	}

	/**
	 * Search terms belonging to a specific taxonomy
	 *
	 * @since 1.4.7
	 */
	public function search_terms() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$args = [
			'taxonomy'   => Params::post( 'tax', '' ),
			'hide_empty' => false,
			'number'     => 20,
		];

		$search = Params::post( 'search', '' );
		if ( '' === $search ) {
			die();
		}

		// if search is an id, search for the term id, else do a full text search.
		if ( 0 !== absint( $search ) && strlen( $search ) === strlen( absint( $search ) ) ) {
			$args['include'] = [ absint( $search ) ];
		} else {
			$args['search'] = $search;
		}

		$results = get_terms( $args );
		echo wp_json_encode( $results );
		echo "\n";
		die();
	}

	/**
	 * Search authors
	 *
	 * @since 1.47.5
	 */
	public function search_authors() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$args['search_columns'] = [ 'ID', 'user_login', 'user_nicename', 'display_name' ];

		if ( version_compare( get_bloginfo( 'version' ), '5.9' ) > -1 ) {
			$args['capability'] = [ 'edit_posts' ];
		} else {
			$args['who'] = 'authors';
		}

		$search = Params::post( 'search', '' );
		if ( '' === $search ) {
			die();
		}

		$args['search'] = '*' . sanitize_text_field( wp_unslash( $search ) ) . '*';

		$results = get_users( $args );

		echo wp_json_encode( $results );
		die();
	}

	/**
	 * Close a notice for good
	 *
	 * @since 1.5.3
	 */
	public function close_notice() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );
		$notice = Params::request( 'notice' );

		if (
			! Conditional::user_can( 'advanced_ads_manage_options' )
			|| empty( $notice )
		) {
			die();
		}

		Advanced_Ads_Admin_Notices::get_instance()->remove_from_queue( $notice );

		// permanent dismissed.
		if ( 'monetize_wizard' === Params::request( 'notice' ) ) {
			update_user_meta( get_current_user_id(), Constants::USER_WIZARD_DISMISS, true );
		}

		$redirect = Params::request( 'redirect' );
		if ( $redirect && wp_safe_redirect( $redirect ) ) {
			exit();
		}

		die();
	}

	/**
	 * Hide a notice for some time (7 days right now)
	 *
	 * @since 1.8.17
	 */
	public function hide_notice() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );
		$notice = Params::request( 'notice' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_options' )
		|| empty( $notice )
		) {
			die();
		}

		Advanced_Ads_Admin_Notices::get_instance()->hide_notice( $notice );
		die();
	}

	/**
	 * Subscribe to newsletter
	 *
	 * @since 1.5.3
	 */
	public function subscribe() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );
		$notice = Params::request( 'notice' );

		if (
			! Conditional::user_can( 'advanced_ads_see_interface' )
			|| empty( $notice )
		) {
			wp_send_json_error(
				[
					/* translators: %s is a URL. */
					'message' => sprintf( __( 'An error occurred. Please use <a href="%s" target="_blank">this form</a> to sign up.', 'advanced-ads' ), 'http://eepurl.com/bk4z4P' ),
				],
				400
			);
		}

		wp_send_json_success( [ 'message' => Advanced_Ads_Admin_Notices::get_instance()->subscribe( $notice ) ] );
	}

	/**
	 * Activate license of an add-on
	 *
	 * @since 1.5.7
	 */
	public function activate_license() {
		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'advads_ajax_license_nonce', 'security' );

		$addon = Params::post( 'addon' );
		if ( '' === $addon ) {
			die();
		}

		// phpcs:disable
		echo Advanced_Ads_Admin_Licenses::get_instance()->activate_license(
			$addon,
			Params::post( 'pluginname' ),
			Params::post( 'optionslug' ),
			Params::post( 'license' )
		);
		// phpcs:enable

		die();
	}

	/**
	 * Deactivate license of an add-on
	 *
	 * @since 1.6.11
	 */
	public function deactivate_license() {
		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'advads_ajax_license_nonce', 'security' );

		$addon = Params::post( 'addon' );
		if ( '' === $addon ) {
			die();
		}

		// phpcs:disable
		echo Advanced_Ads_Admin_Licenses::get_instance()->deactivate_license(
			$addon,
			Params::post( 'pluginname' ),
			Params::post( 'optionslug' )
		);
		// phpcs:enable

		die();
	}

	/**
	 * Rebuild assets for ad-blocker module
	 */
	public function adblock_rebuild_assets() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		Advanced_Ads_Ad_Blocker_Admin::get_instance()->add_asset_rebuild_form();
		die();
	}

	/**
	 * Post search (used in Display conditions)
	 */
	public function post_search() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		add_filter( 'wp_link_query_args', [ 'Advanced_Ads_Display_Conditions', 'modify_post_search' ] );
		add_filter( 'posts_search', [ 'Advanced_Ads_Display_Conditions', 'modify_post_search_sql' ] );

		wp_ajax_wp_link_ajax();
	}

	/**
	 * Inject an ad and a placement
	 *
	 * @since 1.7.3
	 */
	public function inject_placement() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		$ad_id = Params::request( 'ad_id', 0, FILTER_VALIDATE_INT );

		// Early bail!!
		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) || ! $ad_id ) {
			die();
		}

		// use existing placement.
		$placement_id = Params::request( 'placement_id', 0, FILTER_VALIDATE_INT );
		if ( $placement_id ) {
			$placement = wp_advads_get_placement( $placement_id );

			if ( $placement ) {
				$placement->set_item( 'ad_' . $ad_id );
				$placement->save();
				echo esc_attr( $placement_id );
			}

			die();
		}

		$type = esc_attr( Params::request( 'placement_type' ) );
		if ( ! wp_advads_has_placement_type( $type ) ) {
			die();
		}

		$new_placement = wp_advads_create_new_placement( $type );

		$props = [
			'item'  => 'ad_' . $ad_id,
			'title' => wp_advads_get_placement_type( $type )->get_title(),
		];

		// set content specific options.
		$options = Params::request( 'options', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( $new_placement->is_type( 'post_content' ) ) {
			$index             = ! empty( $options['index'] ) ? absint( $options['index'] ) : 1;
			$props['position'] = 'after';
			$props['index']    = $index;
			$props['tag']      = 'p';
		}

		$new_placement->set_props( $props );
		$p_id = $new_placement->save();

		echo $p_id; // phpcs:ignore
	}

	/**
	 * Save ad wizard state for each user individually
	 *
	 * @since 1.7.4
	 */
	public function save_wizard_state() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			die();
		}

		$state = 'true' === Params::request( 'hideWizard' ) ? 'true' : 'false';
		update_user_meta( $user_id, 'advanced-ads-hide-wizard', $state );

		die();
	}

	/**
	 * Enable Adsense Auto ads, previously "Page-Level ads"
	 */
	public function adsense_enable_pla() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		$options                       = get_option( GADSENSE_OPT_NAME, [] );
		$options['page-level-enabled'] = true;
		update_option( GADSENSE_OPT_NAME, $options );
		die();
	}

	/**
	 * Display list of Ad Health notices
	 */
	public function ad_health_notice_display() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		Advanced_Ads_Ad_Health_Notices::get_instance()->render_widget();
		die();
	}

	/**
	 * Push an Ad Health notice to the queue
	 */
	public function ad_health_notice_push_adminui() {

		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		$key  = Params::request( 'key' );
		$attr = Params::request( 'attr', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$attr = ! empty( $attr ) && is_array( $attr ) ? $attr : [];

		// update or new entry?
		if ( isset( $attr['mode'] ) && 'update' === $attr['mode'] ) {
			Advanced_Ads_Ad_Health_Notices::get_instance()->update( $key, $attr );
		} else {
			Advanced_Ads_Ad_Health_Notices::get_instance()->add( $key, $attr );
		}

		die();
	}

	/**
	 * Hide Ad Health notice
	 */
	public function ad_health_notice_hide() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		$notice     = Params::request( 'notice', '' );
		$notice_key = ! empty( $notice ) ? esc_attr( $notice ) : false;

		Advanced_Ads_Ad_Health_Notices::get_instance()->hide( $notice_key );
		die();
	}

	/**
	 * Show all ignored notices of a given type
	 */
	public function ad_health_notice_unignore() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_options' ) ) {
			return;
		}

		Advanced_Ads_Ad_Health_Notices::get_instance()->unignore();
		die();
	}

	/**
	 * After the user has selected a new frontend element, update the corresponding placement.
	 */
	public function update_frontend_element() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		if ( ! Conditional::user_can( 'advanced_ads_manage_placements' ) ) {
			return;
		}

		$return = wp_update_post( $_POST );

		if ( is_wp_error( $return ) ) {
			wp_send_json_error( [ 'error' => $return->get_error_message() ], 400 );
		}

		wp_send_json_success( [ 'id' => $return ] );
	}

	/**
	 * Get hints related to the Gutenberg block.
	 */
	public function get_block_hints() {
		check_ajax_referer( 'advanced-ads-admin-ajax-nonce', 'nonce' );

		$item = Params::post( 'itemID' );
		if ( ! $item || ! Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			die;
		}

		$item = explode( '_', $item );
		if ( ! isset( $item[0] ) || 'group' !== $item[0] ) {
			die;
		}

		$group = wp_advads_get_group( absint( $item[1] ) );
		if ( ! $group ) {
			die;
		}

		wp_send_json_success( $group->get_hints() );
	}

	/**
	 * Get allowed ads per placement.
	 *
	 * @return void
	 */
	public function get_allowed_ads_for_placement_type() {
		check_ajax_referer( sanitize_text_field( Params::post( 'action', '' ) ) );

		$placement_type = wp_advads_get_placement_type( sanitize_text_field( Params::post( 'placement_type' ) ) );

		wp_send_json_success(
			[
				'items' => array_filter(
					$placement_type->get_allowed_items(),
					static function ( $items_group ) {
						return ! empty( $items_group['items'] );
					}
				),
			]
		);
	}

	/**
	 * Update the item for the placement.
	 *
	 * @return void
	 */
	public function placement_update_item(): void {
		$placement     = wp_advads_get_placement( Params::post( 'placement_id', false, FILTER_VALIDATE_INT ) );
		$new_item      = sanitize_text_field( Params::post( 'item_id' ) );
		$new_item_type = 0 === strpos( $new_item, 'ad' ) ? 'ad_' : 'group_';

		try {
			if ( empty( $new_item ) ) {
				$placement->remove_item();
				wp_send_json_success(
					[
						'edit_href'    => '#',
						'placement_id' => $placement->get_id(),
						'item_id'      => '',
					]
				);
			}

			$new_item = $placement->update_item( $new_item );
			wp_send_json_success(
				[
					'edit_href'    => $new_item->get_edit_link(),
					'placement_id' => $placement->get_id(),
					'item_id'      => $new_item_type . $new_item->get_id(),
				]
			);
		} catch ( \RuntimeException $e ) {
			wp_send_json_error(
				[
					'message' => $e->getMessage(),
					'item_id' => $placement->get_item_object() ? $placement->get_item_object()->get_id() : 0,
				],
				400
			);
		}
	}
}
