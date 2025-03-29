<?php
/**
 * This class is responsible to model slider groups.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Groups;

use AdvancedAds\Abstracts\Group;
use AdvancedAds\Interfaces\Group_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Slider group.
 */
class Group_Slider extends Group implements Group_Interface {

	/**
	 * Get delay.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_delay( $context = 'view' ): int {
		return $this->get_prop( 'delay', $context );
	}

	/**
	 * Is grid display random.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function is_random( $context = 'view' ): bool {
		return $this->get_prop( 'random', $context );
	}
}
