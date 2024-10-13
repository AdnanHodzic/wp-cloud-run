<?php
/**
 * The class is responsible for configuring the TinyMCE editor to allow unsafe link targets.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * TinyMCE.
 */
class TinyMCE implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'tiny_mce_before_init', [ $this, 'allow_unsafe_link_target' ] );
	}

	/**
	 * Configure TinyMCE to allow unsafe link target.
	 *
	 * @param bool $mce_init the tinyMce initialization array.
	 *
	 * @return bool|[]
	 */
	public function allow_unsafe_link_target( $mce_init ) {
		// Early bail!!
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $mce_init;
		}

		$screen = get_current_screen();
		if ( 'advanced_ads' === ( $screen->id ?? '' ) ) {
			$mce_init['allow_unsafe_link_target'] = true;
		}

		return $mce_init;
	}
}
