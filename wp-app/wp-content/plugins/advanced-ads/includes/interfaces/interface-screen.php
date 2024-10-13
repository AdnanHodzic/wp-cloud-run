<?php
/**
 * Screen interface.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Screen.
 */
interface Screen_Interface {

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void;

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void;
}
