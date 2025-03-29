<?php
/**
 * This class is responsible to model content ads.
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
 * Content ad.
 */
class Ad_Content extends Ad implements Ad_Interface {

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string {
		$output = $this->get_content();

		if ( isset( $GLOBALS['wp_embed'] ) ) {
			$old_post        = $GLOBALS['post'];
			$GLOBALS['post'] = $this->get_id(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			$output = $GLOBALS['wp_embed']->run_shortcode( $output );
			$output = $GLOBALS['wp_embed']->autoembed( $output );

			$GLOBALS['post'] = $old_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		$output = wptexturize( $output );
		$output = convert_smilies( $output );
		$output = convert_chars( $output );
		$output = wpautop( $output );
		$output = shortcode_unautop( $output );
		$output = $this->do_shortcode( $output );

		if ( defined( 'ADVADS_DISABLE_RESPONSIVE_IMAGES' ) && ADVADS_DISABLE_RESPONSIVE_IMAGES ) {
			return $output;
		}

		// Make included images responsive, since WordPress 4.4, before WordPress 5.5.
		if ( function_exists( 'wp_make_content_images_responsive' ) && ! function_exists( 'wp_filter_content_tags' ) ) {
			return function_exists( 'wp_filter_content_tags' )
				? wp_filter_content_tags( $output )
				: wp_make_content_images_responsive( $output ); // phpcs:ignore WordPress.WP.DeprecatedFunctions.wp_make_content_images_responsiveFound
		}

		// Function wp_make_content_images_responsive has been deprecated with WordPress 5.5.
		if ( function_exists( 'wp_filter_content_tags' ) ) {
			return wp_filter_content_tags( $output );
		}

		return $output;
	}
}
