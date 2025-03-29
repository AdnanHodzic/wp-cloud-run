<?php
/**
 * This class is responsible to model image ads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads;

use Advanced_Ads;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Interfaces\Ad_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Image ad.
 */
class Ad_Image extends Ad implements Ad_Interface {

	/**
	 * Get the image id for the ad.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_image_id( $context = 'view' ): int {
		return $this->get_prop( 'image_id', $context ) ?? 0;
	}

	/**
	 * Set the image id.
	 *
	 * @param int|string $image_id Image id for ad.
	 */
	public function set_image_id( $image_id ) {
		$this->set_prop( 'image_id', absint( $image_id ) );
	}

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string {
		$url      = $this->get_url();
		$image_id = $this->get_image_id();

		ob_start();
		$this->get_type_object()->create_image_tag( $image_id, $this );
		$img = ob_get_clean();

		if ( ! defined( 'AAT_VERSION' ) && $url ) {
			$alt          = trim( esc_textarea( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) );
			$aria_label   = ! empty( $alt ) ? $alt : wp_basename( get_the_title( $image_id ) );
			$options      = Advanced_Ads::get_instance()->options();
			$target_blank = ! empty( $options['target-blank'] ) ? ' target="_blank"' : '';
			$img          = sprintf( '<a href="%s"%s aria-label="%s">%s</a>', esc_url( $url ), $target_blank, $aria_label, $img );
		}

		return $img;
	}

	/**
	 * Pre save
	 *
	 * @param array $post_data Post data.
	 *
	 * @return void
	 */
	public function pre_save( $post_data ): void {
		$image_id = absint( $post_data['image_id'] ?? 0 );
		if ( $image_id ) {
			$attachment = get_post( $image_id );
			if ( $attachment && 0 === $attachment->post_parent ) {
				wp_update_post(
					[
						'ID'          => $image_id,
						'post_parent' => $this->get_id(),
					]
				);
			}
		}
	}
}
