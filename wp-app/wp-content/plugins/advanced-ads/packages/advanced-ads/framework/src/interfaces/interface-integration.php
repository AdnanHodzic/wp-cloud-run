<?php
/**
 * An interface for registering integrations with WordPress.
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Integration interface.
 */
interface Integration_Interface {

	/**
	 * Hook into WordPress.
	 */
	public function hooks();
}
