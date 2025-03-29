<?php
/**
 * This class represents the "Unknown" group type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Groups\Types;

use AdvancedAds\Groups\Group_Standard;
use AdvancedAds\Interfaces\Group_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Unknown.
 */
class Unknown implements Group_Type {
	/**
	 * Hold type data.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * The constructor.
	 *
	 * @param array $data Array of type data.
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * Get the unique identifier (ID) of the group type.
	 *
	 * @return string The unique ID of the group type.
	 */
	public function get_id(): string {
		return $this->data['id'] ?? 'default';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return $this->data['classname'] ?? Group_Standard::class;
	}

	/**
	 * Get the title or name of the group type.
	 *
	 * @return string The title of the group type.
	 */
	public function get_title(): string {
		return $this->data['title'] ?? __( 'Unknown type', 'advanced-ads' );
	}

	/**
	 * Get a description of the group type.
	 *
	 * @return string The description of the group type.
	 */
	public function get_description(): string {
		return $this->data['description'] ?? __( 'No description', 'advanced-ads' );
	}

	/**
	 * Check if this group type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return boolval( $this->data['is_premium'] ?? true );
	}

	/**
	 * Get the URL for upgrading to this group type.
	 *
	 * @return string The upgrade URL for the group type.
	 */
	public function get_image(): string {
		$fallback = ADVADS_BASE_URL . 'admin/assets/img/placements/manual.png';

		return $this->data['image'] ?? $fallback;
	}
}
