<?php
/**
 * Rest Quick Edit.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Rest;

use Advanced_Ads_Privacy;
use AdvancedAds\Constants;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Routes_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Rest Quick Edit.
 */
class Quick_Edit implements Routes_Interface {

	/**
	 * Registers routes with WordPress.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Constants::REST_BASE,
			'/quick_edit_data',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'format_ad_data' ],
				'permission_callback' => function () {
					return Conditional::user_can( 'advanced_ads_edit_ads' );
				},
			]
		);
	}

	/**
	 * Return ad data for quick edit
	 *
	 * @param \WP_REST_Request $request the request.
	 *
	 * @return []
	 */
	public function format_ad_data( $request ) {
		$id = $request->get_param( 'id' );
		$ad = wp_advads_get_ad( $id );

		if ( ! $ad ) {
			return [ 'error' => __( 'Ad not found', 'advanced-ads' ) ];
		}

		$expiry = $ad->get_expiry_date();

		if ( $expiry ) {
			$expiry_date = array_combine(
				[ 'year', 'month', 'day', 'hour', 'minutes' ],
				explode( '-', wp_date( 'Y-m-d-H-i', $expiry ) )
			);
		}

		$ad_data = [
			'debug_mode' => $ad->is_debug_mode(),
			'expiry'     => $expiry
				? [
					'expires'     => true,
					'expiry_date' => $expiry_date,
				]
				: [
					'expires' => false,
				],
			'ad_label'   => $ad->get_prop( 'ad_label' ),
		];

		if ( isset( Advanced_Ads_Privacy::get_instance()->options()['enabled'] ) ) {
			$ad_data['ignore_privacy'] = isset( $ad->get_data()['privacy']['ignore-consent'] );
		}

		/**
		 * Allow add-ons to add more ad data fields.
		 *
		 * @param array $ad_data the fields to be sent back to the browser.
		 * @param       $ad      Ad the ad being currently edited.
		 */
		$ad_data = apply_filters( 'advanced-ads-quick-edit-ad-data', $ad_data, $ad );

		return $ad_data;
	}
}
