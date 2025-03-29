<?php
/**
 * Ad Adsense.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin\Metaboxes;

use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use Advanced_Ads_AdSense_Data;
use Advanced_Ads_Network_Adsense;

defined( 'ABSPATH' ) || exit;

/**
 * Ad Adsense.
 */
class Ad_Adsense {

	/**
	 * Get metabox id
	 *
	 * @return string
	 */
	public function get_box_id(): string {
		return 'advads-gadsense-box';
	}

	/**
	 * Hook into WordPress.
	 *
	 * TODO: move to module to its right place.
	 *
	 * @param Metabox_Ad $manager Manager instance.
	 *
	 * @return void
	 */
	public function register( $manager ): void {
		global $post;

		if (
			$post->ID &&
			Advanced_Ads_AdSense_Data::get_instance()->is_setup() &&
			! Advanced_Ads_AdSense_Data::get_instance()->is_hide_stats()
		) {
			$ad_unit = Advanced_Ads_Network_Adsense::get_instance()->get_ad_unit( $post->ID );

			if ( $ad_unit ) {
				add_meta_box(
					$this->get_box_id(),
					sprintf(
						/* translators: 1: Name of ad unit */
						esc_html__( 'Earnings of  %1$s', 'advanced-ads' ),
						esc_html( $ad_unit->name )
					),
					[ $manager, 'display' ],
					Constants::POST_TYPE_AD,
					'normal',
					'high'
				);
			}
		}
	}

	/**
	 * Get metaboxe view file
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return string
	 */
	public function get_view( $ad ): string {
		$unit_code = null;
		if ( $ad->is_type( 'adsense' ) && ! empty( $ad->get_content() ) ) {
			$json_content = json_decode( $ad->get_content() );
			// phpcs:disable
			if ( isset( $json_content->slotId ) ) {
				$unit_code = $json_content->slotId;
			}
			// phpcs:enable
		}

		$report_type   = 'unit';
		$report_filter = $unit_code;

		include ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-gadsense-dashboard.php';

		return '';
	}

	/**
	 * Return manual link
	 *
	 * @return array|string
	 */
	public function get_handle_link() {
		return '<a href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ) . '" target="_blank">' . __( 'Disable', 'advanced-ads' ) . '</a>';
	}
}
