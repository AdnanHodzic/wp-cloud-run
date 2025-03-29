<?php
/**
 * AAWP Compatibility.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Compatibility;

use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Interfaces\Ad_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * AAWP Ad.
 */
class AAWP_Ad extends Ad implements Ad_Interface {

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string {
		$display_variant = $this->get_prop( 'display_variant' );
		if ( empty( $display_variant ) ) {
			return '';
		}

		switch ( $display_variant ) {
			case 'box':
				$next_input = $this->get_prop( 'asin' );
				break;

			case 'bestseller':
			case 'new':
				$next_input = $this->get_prop( 'keywords' );
				break;

			default:
				$next_input = '';
		}

		$template = ! empty( $this->get_prop( 'template' ) ) ? $this->get_prop( 'template' ) : 'default';

		$shortcode = '[' . aawp_get_shortcode() . ' ' . $display_variant . '="' . $next_input . '"';

		if ( 'bestseller' === $display_variant || 'new' === $display_variant ) {
			$shortcode = $shortcode . ' items="' . $this->get_prop( 'items' ) . '"';
		}

		$shortcode = $shortcode . ' template="' . $template . '"]';

		return do_shortcode( $shortcode );
	}
}
