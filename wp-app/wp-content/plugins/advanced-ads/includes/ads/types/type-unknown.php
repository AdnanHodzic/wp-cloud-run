<?php
/**
 * This class represents the "Unknown" ad type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads\Types;

use AdvancedAds\Ads\Ad_Dummy;
use AdvancedAds\Interfaces\Ad_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Unknown.
 */
class Unknown implements Ad_Type {
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
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
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
		return $this->data['classname'] ?? Ad_Dummy::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return $this->data['title'] ?? __( 'Unknown type', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return $this->data['description'] ?? __( 'No description', 'advanced-ads' );
	}

	/**
	 * Check if this ad type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return boolval( $this->data['is_upgrade'] ?? $this->data['is_premium'] ?? true );
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_upgrade_url(): string {
		return $this->data['upgrade_url'] ?? '';
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_image(): string {
		if ( isset( $this->data['icon'] ) && ! empty( $this->data['icon'] ) ) {
			return $this->data['icon'];
		}

		$icon_path = sprintf( 'assets/img/ad-types/%s.svg', $this->get_id() );
		if ( ! file_exists( ADVADS_ABSPATH . $icon_path ) ) {
			$icon_path = 'assets/img/ad-types/empty.svg';
		}

		return ADVADS_BASE_URL . $icon_path;
	}

	/**
	 * Check if this ad type has size parameters.
	 *
	 * @return bool True if has size parameters; otherwise, false.
	 */
	public function has_size(): bool {
		return true;
	}

	/**
	 * Output for the ad parameters metabox
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ): void {
		if ( isset( $this->data['render_parameters'] ) && is_callable( $this->data['render_parameters'] ) ) {
			$this->data['render_parameters']( $ad );
		}
	}
}
