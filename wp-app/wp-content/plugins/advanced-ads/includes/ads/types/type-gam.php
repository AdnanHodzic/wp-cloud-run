<?php
/**
 * This class represents the "GAM" ad type.
 *
 * @package AdvancedAds\GAM
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Ads\Types;

use AdvancedAds\Ads\Ad_Dummy;
use AdvancedAds\Interfaces\Ad_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type GAM.
 */
class GAM implements Ad_Type {
	/**
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
	 */
	public function get_id(): string {
		return 'gam';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Ad_Dummy::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return __( 'Google Ad Manager', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return __( 'Load ad units directly from your Google Ad Manager account.', 'advanced-ads' );
	}

	/**
	 * Check if this ad type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return true;
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_upgrade_url(): string {
		return 'https://wpadvancedads.com/add-ons/google-ad-manager/';
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_image(): string {
		return ADVADS_BASE_URL . 'assets/img/ad-types/gam.svg';
	}

	/**
	 * Check if this ad type has size parameters.
	 *
	 * @return bool True if has size parameters; otherwise, false.
	 */
	public function has_size(): bool {
		return true;
	}
}
