<?php
/**
 * This class represents the "Unknown" placement type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Placements\Types;

use AdvancedAds\Abstracts\Placement_Type as Base;
use AdvancedAds\Interfaces\Placement_Type;
use AdvancedAds\Placements\Placement_Standard;

defined( 'ABSPATH' ) || exit;

/**
 * Placements Types Unknown.
 */
class Unknown extends Base implements Placement_Type {

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
	 * Get the unique identifier (ID) of the placement type.
	 *
	 * @return string The unique ID of the placement type.
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
		return $this->data['classname'] ?? Placement_Standard::class;
	}

	/**
	 * Get the title or name of the placement type.
	 *
	 * @return string The title of the placement type.
	 */
	public function get_title(): string {
		return $this->data['title'] ?? __( 'Unknown type', 'advanced-ads' );
	}

	/**
	 * Get a description of the placement type.
	 *
	 * @return string The description of the placement type.
	 */
	public function get_description(): string {
		return $this->data['description'] ?? __( 'No description', 'advanced-ads' );
	}

	/**
	 * Check if this placement type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return boolval( $this->data['is_premium'] ?? true );
	}

	/**
	 * Get order number for this placement type.
	 *
	 * @return int The order number.
	 */
	public function get_order(): int {
		return $this->data['order'] ?? 100;
	}

	/**
	 * Get options for this placement type.
	 *
	 * @return array The options array.
	 */
	public function get_options(): array {
		return $this->apply_filter_on_options( $this->data['options'] ?? [] );
	}

	/**
	 * Get the URL for upgrading to this placement type.
	 *
	 * @return string The upgrade URL for the placement type.
	 */
	public function get_image(): string {
		$fallback = ADVADS_BASE_URL . 'admin/assets/img/placements/manual.png';

		return $this->data['image'] ?? $fallback;
	}
}
