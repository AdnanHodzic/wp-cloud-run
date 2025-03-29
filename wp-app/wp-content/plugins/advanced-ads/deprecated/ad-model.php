<?php // phpcs:ignoreFileName

/**
 * Advanced Ads Model
 *
 * @deprecated 2.0.0
 */
class Advanced_Ads_Model {

	/**
	 * Get the array with ad placements
	 *
	 * @since 1.1.0
	 * @deprecated 2.0.0 Use `wp_advads_get_all_placements()` instead
	 *
	 * @return array $ad_placements
	 */
	public function get_ad_placements_array() {
		return wp_advads_get_all_placements();
	}
}
