<?php
/**
 * This class is responsible to model group ads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads;

use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Interfaces\Ad_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Group ad.
 */
class Ad_Group extends Ad implements Ad_Interface {

	/**
	 * Get the group id for the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_group_id( $context = 'view' ): int {
		return $this->get_prop( 'group_id', $context ) ?? 0;
	}

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string {
		if ( ! $this->get_group_id() ) {
			return '';
		}

		return get_the_group( $this->get_group_id(), '', $this->get_prop( 'ad_args' ) ?? [] );
	}
}
