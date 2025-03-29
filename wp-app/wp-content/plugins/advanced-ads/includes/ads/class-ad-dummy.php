<?php
/**
 * This class is responsible to model dummy ads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads;

use Advanced_Ads;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Interfaces\Ad_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Dummy ad.
 */
class Ad_Dummy extends Ad implements Ad_Interface {

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string {
		$style = '';
		if ( strpos( $this->get_position(), 'center' ) === 0 ) {
			$style .= 'display: inline-block;';
		}

		$style = '' !== $style ? 'style="' . $style . '"' : '';
		$img   = sprintf(
			'<img src="%s" width="300" height="250" %s />',
			esc_url( ADVADS_BASE_URL . 'public/assets/img/dummy.jpg' ),
			$style
		);

		$url = $this->get_url();
		if ( ! defined( 'AAT_VERSION' ) && $url ) {
			$options      = Advanced_Ads::get_instance()->options();
			$target_blank = ! empty( $options['target-blank'] ) ? ' target="_blank"' : '';
			$img          = sprintf( '<a href="%s"%s aria-label="dummy">%s</a>', esc_url( $url ), $target_blank, $img );
		}

		// Add 'loading' attribute if applicable, available from WP 5.5.
		if ( function_exists( 'wp_lazy_loading_enabled' ) && wp_lazy_loading_enabled( 'img', 'the_content' ) ) {
			// Optimize image HTML tag with loading attributes based on WordPress filter context.
			$img = WordPress::img_tag_add_loading_attr( $img, 'the_content' );
		}

		return $img;
	}
}
