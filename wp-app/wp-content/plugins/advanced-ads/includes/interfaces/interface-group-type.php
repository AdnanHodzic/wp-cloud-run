<?php
/**
 * This interface defines a contract for implementing different group types within plugin.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface for Group Type.
 */
interface Group_Type {

	/**
	 * Get the unique identifier (ID) of the group type.
	 *
	 * @return string The unique ID of the group type.
	 */
	public function get_id(): string;

	/**
	 * Get the title or name of the group type.
	 *
	 * @return string The title of the group type.
	 */
	public function get_title(): string;

	/**
	 * Get a description of the group type.
	 *
	 * @return string The description of the group type.
	 */
	public function get_description(): string;

	/**
	 * Check if this group type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool;

	/**
	 * Get the URL for upgrading to this group type.
	 *
	 * @return string The upgrade URL for the group type.
	 */
	public function get_image(): string;
}
