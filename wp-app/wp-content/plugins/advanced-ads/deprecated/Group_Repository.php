<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Group Repository class.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

namespace Advanced_Ads;

use AdvancedAds\Abstracts\Group;

/**
 * Group Repository/Factory class.
 *
 * @deprecated 2.0.0
 */
class Group_Repository {

	/**
	 * Get the ad object from the repository. Create and add it, if it doesn't exist.
	 * If the passed id is not an ad, return the created ad object without adding it to the repository.
	 * This behavior prevents breaking changes.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int|WP_Term $term The term to look for.
	 *
	 * @return Group|bool
	 */
	public static function get( $term ) {
		_deprecated_function( __METHOD__, '2.0.0', 'wp_advads_get_group()' );
		return wp_advads_get_group( $term );
	}
}
