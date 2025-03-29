<?php
/**
 * Rest Page Quick Edit.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Rest;

use AdvancedAds\Constants;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Interfaces\Routes_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Rest Page Quick Edit.
 */
class Page_Quick_Edit implements Routes_Interface {
	/**
	 * Register rest route for disabled ads status
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Constants::REST_BASE,
			'/page_quick_edit',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_disable_ads' ],
				'permission_callback' => function () {
					return Conditional::user_can( 'edit_posts' );
				},
			]
		);
	}

	/**
	 * Endpoint callback
	 *
	 * @param \WP_REST_Request $request the request.
	 *
	 * @return array
	 */
	public function get_disable_ads( $request ) {
		$params = $request->get_params();
		$nonce  = sanitize_text_field( $params['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'advads-post-quick-edit' ) ) {
			return [];
		}

		$id = absint( $params['id'] );

		return (array) get_post_meta( $id, '_advads_ad_settings', true );
	}
}
