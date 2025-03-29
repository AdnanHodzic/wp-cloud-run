<?php
/**
 * The interface to provide a contract for Ad.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interfaces Ad.
 */
interface Ad_Interface {

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string;
}
