<?php
/**
 * Frontend Manager.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Frontend;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Manager.
 */
class Manager implements Integration_Interface {

	/**
	 * Page display condition object.
	 *
	 * @var Ad_Display_Condition
	 */
	private $page_display;

	/**
	 * Magic method to handle dynamic method calls.
	 *
	 * @param string $name      The name of the method being called.
	 * @param array  $arguments The arguments passed to the method.
	 *
	 * @return mixed The result of the method call, if the method exists. Otherwise, null is returned.
	 */
	public function __call( $name, $arguments ) {
		if ( method_exists( $this->page_display, $name ) ) {
			return call_user_func_array( [ $this->page_display, $name ], $arguments );
		}
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		$this->page_display = new Ad_Display_Condition();

		add_action( 'rest_api_init', [ $this, 'run_checks' ] );
		add_action( 'template_redirect', [ $this, 'run_checks' ], 11 );
	}

	/**
	 * Run the check.
	 *
	 * @return void
	 */
	public function run_checks(): void {
		$this->page_display->run_checks();

		if ( ! $this->page_display->is_ad_disabled() ) {
			do_action( 'advanced-ads-frontend' );
		}
	}
}
