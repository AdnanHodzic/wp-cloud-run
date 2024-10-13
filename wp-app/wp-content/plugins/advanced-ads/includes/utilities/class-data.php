<?php
/**
 * The class provides utility functions for retrieving and managing plugin data and choices.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use AdvancedAds\Framework\Interfaces\WordPress_Integration;

defined( 'ABSPATH' ) || exit;

/**
 * Data and Choices.
 */
class Data {

	/**
	 * Get the admin screen ids.
	 *
	 * @return array
	 */
	public static function get_admin_screen_ids(): array {
		return apply_filters(
			'advanced-ads-dashboard-screens',
			[
				'advanced_ads',
				'edit-advanced_ads',
				'toplevel_page_advanced-ads',
				'admin_page_advanced-ads-debug',
				'admin_page_advanced-ads-import-export',
				'advanced-ads_page_advanced-ads-groups',
				'advanced-ads_page_advanced-ads-placements',
				'advanced-ads_page_advanced-ads-settings',
			]
		);
	}
}
