<?php
/**
 * This class represents the "Grid" group type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Groups\Types;

use AdvancedAds\Interfaces\Group_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Grid.
 */
class Grid implements Group_Type {

	/**
	 * Get the unique identifier (ID) of the group type.
	 *
	 * @return string The unique ID of the group type.
	 */
	public function get_id(): string {
		return 'grid';
	}

	/**
	 * Get the title or name of the group type.
	 *
	 * @return string The title of the group type.
	 */
	public function get_title(): string {
		return __( 'Grid', 'advanced-ads' );
	}

	/**
	 * Get a description of the group type.
	 *
	 * @return string The description of the group type.
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Check if this group type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return true;
	}

	/**
	 * Get the URL for upgrading to this group type.
	 *
	 * @return string The upgrade URL for the group type.
	 */
	public function get_image(): string {
		return ADVADS_BASE_URL . 'admin/assets/img/groups/grid.svg';
	}
}
