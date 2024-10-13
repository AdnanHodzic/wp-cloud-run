<?php

use AdvancedAds\Utilities\WordPress;

/**
 * Provide public ajax interface.
 *
 * @since 1.5.0
 */
class Advanced_Ads_Ajax {

	/**
	 * Advanced_Ads_Ajax constructor.
	 */
	private function __construct() {
		add_action( 'wp_ajax_advads_ad_select', [ $this, 'advads_ajax_ad_select' ] );
		add_action( 'wp_ajax_nopriv_advads_ad_select', [ $this, 'advads_ajax_ad_select' ] );
		add_action( 'wp_ajax_advads-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );
		add_action( 'wp_ajax_nopriv_advads-ad-health-notice-push', [ $this, 'ad_health_notice_push' ] );
	}

	/**
	 * Instance of Advanced_Ads_Ajax
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Instance getter
	 *
	 * @return Advanced_Ads_Ajax
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Simple wp ajax interface for ad selection.
	 */
	public function advads_ajax_ad_select() {
		add_filter( 'advanced-ads-output-inline-css', '__return_false' );
		// Set proper header.
		header( 'Content-Type: application/json; charset: utf-8' );

		// Allow modules / add ons to test (this is rather late but should happen before anything important is called).
		do_action( 'advanced-ads-ajax-ad-select-init' );

		$ad_ids = isset( $_REQUEST['ad_ids'] ) ? $_REQUEST['ad_ids'] : null;
		if ( is_string( $ad_ids ) ) {
			$ad_ids = json_decode( $ad_ids, true );
		}
		if ( is_array( $ad_ids ) ) { // Ads loaded previously and passed by query.
			Advanced_Ads::get_instance()->current_ads += $ad_ids;
		}

		$defered_ads = isset( $_REQUEST['deferedAds'] ) ? $_REQUEST['deferedAds'] : null;
		if ( $defered_ads ) { // Load all ajax ads with a single request.
			$response = [];

			$requests_by_blog = [];
			foreach ( (array) $defered_ads as $request ) {
				$blog_id                        = isset( $request['blog_id'] ) ? $request['blog_id'] : get_current_blog_id();
				$requests_by_blog[ $blog_id ][] = $request;
			}
			foreach ( $requests_by_blog as $blog_id => $requests ) {
				if ( get_current_blog_id() !== $blog_id ) {
					Advanced_Ads::get_instance()->switch_to_blog( $blog_id );
				}

				foreach ( $requests as $request ) {
					$result              = $this->select_one( $request );
					$result['elementId'] = ! empty( $request['elementId'] ) ? $request['elementId'] : null;
					$response[]          = $result;
				}

				if ( get_current_blog_id() !== $blog_id ) {
					Advanced_Ads::get_instance()->restore_current_blog();
				}
			}

			echo wp_json_encode( $response );
			die();
		}

		$response = $this->select_one( $_REQUEST );
		echo wp_json_encode( $response );
		die();
	}

	/**
	 * Provides a single ad (ad, group, placement) given ID and selection method.
	 *
	 * @param array $request request.
	 *
	 * @return array
	 */
	private function select_one( $request ) {
		// Init handlers.
		$selector  = Advanced_Ads_Select::get_instance();
		$methods   = $selector->get_methods();
		$method    = isset( $request['ad_method'] ) ? (string) $request['ad_method'] : null;
		$id        = isset( $request['ad_id'] ) ? (string) $request['ad_id'] : null;
		$arguments = isset( $request['ad_args'] ) ? $request['ad_args'] : [];
		if ( is_string( $arguments ) ) {
			$arguments = stripslashes( $arguments );
			$arguments = json_decode( $arguments, true );
		}

		if ( ! empty( $request['elementId'] ) ) {
			$arguments['cache_busting_elementid'] = $request['elementId'];
		}

		if ( ! array_key_exists( $method, $methods ) || empty( $id ) ) {
			// Report error.
			return [
				'status'  => 'error',
				'message' => 'No valid ID or METHOD found.',
			];
		}

		/**
		 * Filters the received arguments before passing them to to ads/groups/placements.
		 *
		 * @param array $arguments Existing arguments.
		 * @param array $request Request data.
		 */
		$arguments = apply_filters( 'advanced-ads-ajax-ad-select-arguments', $arguments, $request );

		$advads       = Advanced_Ads::get_instance();
		$previous_ads = $advads->current_ads;

		add_filter( 'advanced-ads-can-display', [ $this, 'can_display_by_consent' ], 10, 2 );
		$content = $selector->get_ad_by_method( $id, $method, $arguments );

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
			'ads'     => array_slice( $advads->current_ads, count( $previous_ads ) ),
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
	 * Push an Ad Health notice to the queue in the backend
	 */
	public function ad_health_notice_push() {

		check_ajax_referer( 'advanced-ads-ad-health-ajax-nonce', 'nonce' );

		if ( ! WordPress::user_can( 'advanced_ads_edit_ads' ) ) {
			return;
		}

		$key  = ( ! empty( $_REQUEST['key'] ) ) ? esc_attr( $_REQUEST['key'] ) : false;
		$attr = ( ! empty( $_REQUEST['attr'] ) && is_array( $_REQUEST['attr'] ) ) ? $_REQUEST['attr'] : [];

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
	 * @param bool            $can_display Whether this ad can be displayed.
	 * @param Advanced_Ads_Ad $ad          The ad object.
	 *
	 * @return bool
	 */
	public function can_display_by_consent( $can_display, $ad ) {
		// already false, honor this.
		if ( ! $can_display ) {
			return $can_display;
		}

		// If consent is overridden for the ad.
		if ( ! empty( $ad->options()['privacy']['ignore-consent'] ) ) {
			return true;
		}

		// if privacy module is not active, we can display.
		$privacy         = Advanced_Ads_Privacy::get_instance();
		$privacy_options = $privacy->options();
		if ( empty( $privacy_options['enabled'] ) ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce should be verified in ajax handler.
		$consent_state = sanitize_text_field( $_REQUEST['consent'] );

		// Consent is either given or not needed.
		if ( in_array( $consent_state, [ 'not_needed', 'accepted' ], true ) ) {
			return true;
		}

		// If there is custom code, don't display the ad (unless it's a group).
		if ( class_exists( 'Advanced_Ads_Pro' ) && ! empty( Advanced_Ads_Pro::get_instance()->get_custom_code( $ad ) ) && $ad->type !== 'group' ) {
			return false;
		}

		// See if this ad type needs consent.
		return ! $privacy->ad_type_needs_consent( $ad->type );
	}
}
