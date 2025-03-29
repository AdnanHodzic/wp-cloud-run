<?php // phpcs:ignore WordPress.Files.FileName

namespace Advanced_Ads;

use AdvancedAds\Abstracts\Ad;

/**
 * Ad Repository/Factory class.
 *
 * @deprecated 2.0.0
 */
class Ad_Repository {
	/**
	 * Get the ad object from the repository. Create and add it, if it doesn't exist.
	 * If the passed id is not an ad, return the created ad object without adding it to the repository.
	 * This behavior prevents breaking changes.
	 *
	 * @deprecated 2.0.0
	 *
	 * @param int $id The ad id to look for.
	 *
	 * @return Ad|bool
	 */
	public static function get( int $id ) {
		return wp_advads_get_ad( $id );
	}
}
