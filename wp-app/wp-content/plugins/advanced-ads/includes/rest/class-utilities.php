<?php
/**
 * Rest Utilities.
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
 * Rest Utilities.
 */
class Utilities implements Routes_Interface {

	/**
	 * Registers routes with WordPress.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			Constants::REST_BASE,
			'/user-email',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_user_email' ],
				'permission_callback' => function () {
					return Conditional::user_can( 'advanced_ads_edit_ads' );
				},
			]
		);
	}

	/**
	 * Retrieves the user email address.
	 *
	 * @return string Loggedin user email address.
	 */
	public function get_user_email() {
		return wp_get_current_user()->user_email;
	}
}
