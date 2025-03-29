<?php
/**
 * This class is responsible to model standard groups.
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
 * Standard group.
 */
class Group_Standard extends Group implements Group_Interface {

	/**
	 * Get interval.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_interval( $context = 'view' ): int {
		return $this->get_prop( 'interval', $context );
	}

	/**
	 * Is refresh enabled.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function is_refresh( $context = 'view' ): bool {
		$value = $this->get_prop( 'enabled', $context );
		return $value ?? false;
	}
}
