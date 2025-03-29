<?php
/**
 * Admin Compatibility.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Compatibility.
 */
class Compatibility implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'quads_meta_box_post_types', [ $this, 'fix_wpquadspro_issue' ], 11 );
	}

	/**
	 * Fixes a WP QUADS PRO compatibility issue
	 * they inject their ad optimization meta box into our ad page, even though it is not a public post type
	 * using they filter, we remove AA from the list of post types they inject this box into
	 *
	 * @param array $allowed_post_types Array of allowed post types.
	 *
	 * @return array
	 */
	public function fix_wpquadspro_issue( $allowed_post_types ): array {
		unset( $allowed_post_types['advanced_ads'] );

		return $allowed_post_types;
	}
}
