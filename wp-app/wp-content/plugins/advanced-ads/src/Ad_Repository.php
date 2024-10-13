<?php

namespace Advanced_Ads;

/**
 * Ad Repository/Factory class.
 * Ensures every ad is only set-up once and the same instance is re-used within one request.
 */
class Ad_Repository extends Abstract_Repository {
	/**
	 * Array to hold the \Advanced_Ads_Ad objects, indexed by id.
	 *
	 * @var array
	 */
	protected static $repo = [];

	/**
	 * Get the ad object from the repository. Create and add it, if it doesn't exist.
	 * If the passed id is not an ad, return the created ad object without adding it to the repository.
	 * This behavior prevents breaking changes.
	 *
	 * @param int $id The ad id to look for.
	 *
	 * @return \Advanced_Ads_Ad
	 */
	public static function get( int $id ): \Advanced_Ads_Ad {
		if ( ! self::has( $id ) ) {
			$ad = new \Advanced_Ads_Ad( $id );
			if ( ! $ad->is_ad ) {
				return $ad;
			}
			self::$repo[ $id ] = $ad;
		}

		return self::$repo[ $id ];
	}
}
