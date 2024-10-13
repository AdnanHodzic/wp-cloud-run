<?php
/**
 * Group Repository class.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

namespace Advanced_Ads;

use WP_Term;

/**
 * Group Repository/Factory class.
 * Ensures every ad is only set-up once and the same instance is re-used within one request.
 */
class Group_Repository extends Abstract_Repository {
	/**
	 * Array to hold the \Advanced_Ads_Group objects, indexed by the term.
	 *
	 * @var array
	 */
	protected static $repo = [];

	/**
	 * Get the ad object from the repository. Create and add it, if it doesn't exist.
	 * If the passed id is not an ad, return the created ad object without adding it to the repository.
	 * This behavior prevents breaking changes.
	 *
	 * @param int|WP_Term $term The term to look for.
	 *
	 * @return \Advanced_Ads_Group
	 */
	public static function get( $term ): \Advanced_Ads_Group {
		$id = $term->term_id ?? $term;
		if ( ! self::has( $id ) ) {
			$group = new \Advanced_Ads_Group( $term );
			if ( ! $group->is_group ) {
				return $group;
			}
			self::$repo[ $id ] = $group;
		}

		return self::$repo[ $id ];
	}
}
