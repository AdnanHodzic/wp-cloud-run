<?php
/**
 * An interface for registering initializer with WordPress.
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Initializer interface.
 */
interface Initializer_Interface {

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize();
}
