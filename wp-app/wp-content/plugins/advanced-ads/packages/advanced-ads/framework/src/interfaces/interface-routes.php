<?php
/**
 * An interface for registering routes with WordPress.
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Routes interface.
 */
interface Routes_Interface {

	/**
	 * Registers routes with WordPress.
	 *
	 * @return void
	 */
	public function register_routes();
}
