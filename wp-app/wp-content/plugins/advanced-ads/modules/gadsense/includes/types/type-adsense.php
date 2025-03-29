<?php
/**
 * This class represents the "Adsense" ad type.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.4.0
 */

namespace AdvancedAds\Adsense\Types;

use Advanced_Ads_Ad_Type_Adsense;
use Advanced_Ads_AdSense_Data;
use Advanced_Ads_AdSense_MAPI;
use AdvancedAds\Interfaces\Ad_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Type Adsense.
 */
class Adsense implements Ad_Type {
	/**
	 * Get the unique identifier (ID) of the ad type.
	 *
	 * @return string The unique ID of the ad type.
	 */
	public function get_id(): string {
		return 'adsense';
	}

	/**
	 * Get the class name of the object as a string.
	 *
	 * @return string
	 */
	public function get_classname(): string {
		return Advanced_Ads_Ad_Type_Adsense::class;
	}

	/**
	 * Get the title or name of the ad type.
	 *
	 * @return string The title of the ad type.
	 */
	public function get_title(): string {
		return __( 'AdSense ad', 'advanced-ads' );
	}

	/**
	 * Get a description of the ad type.
	 *
	 * @return string The description of the ad type.
	 */
	public function get_description(): string {
		return __( 'Use ads from your Google AdSense account', 'advanced-ads' );
	}

	/**
	 * Check if this ad type requires premium.
	 *
	 * @return bool True if premium is required; otherwise, false.
	 */
	public function is_premium(): bool {
		return false;
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_upgrade_url(): string {
		return '';
	}

	/**
	 * Get the URL for upgrading to this ad type.
	 *
	 * @return string The upgrade URL for the ad type.
	 */
	public function get_image(): string {
		return ADVADS_BASE_URL . 'assets/img/ad-types/adsense.svg';
	}

	/**
	 * Check if this ad type has size parameters.
	 *
	 * @return bool True if has size parameters; otherwise, false.
	 */
	public function has_size(): bool {
		return true;
	}

	/**
	 * Output for the ad parameters metabox
	 *
	 * @since 1.4
	 * @param Ad $ad Ad instance.
	 *
	 * @return void
	 */
	public function render_parameters( $ad ): void {
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		// TODO: THIS IS JUST A QUICK AND DIRTY HACK. Create a dedicated method to handle this properly.
		?>
		<script>
			jQuery( function () {
				<?php
				$mapi_options  = Advanced_Ads_AdSense_MAPI::get_option();
				$json_ad_codes = wp_json_encode( $mapi_options['ad_codes'] );
				?>
				const adsense = new AdvancedAdsNetworkAdsense(<?php echo $json_ad_codes; // phpcs:ignore ?>)
				AdvancedAdsAdmin.AdImporter.setup( adsense )
			} )
		</script>
		<?php

		$options = $ad->get_data();

		$content      = $ad->get_content() ?? '';
		$unit_id      = '';
		$unit_pubid   = '';
		$unit_code    = '';
		$unit_type    = 'responsive';
		$unit_width   = 0;
		$unit_height  = 0;
		$json_content = '';
		$unit_resize  = '';
		$extra_params = [
			'default_width'  => '',
			'default_height' => '',
			'at_media'       => [],
		];

		$db     = Advanced_Ads_AdSense_Data::get_instance();
		$pub_id = trim( $db->get_adsense_id( $ad ) );

		// check pub_id for errors.
		$pub_id_errors = false;
		if ( '' !== $pub_id && 0 !== strpos( $pub_id, 'pub-' ) ) {
			$pub_id_errors = __( 'The Publisher ID has an incorrect format. (must start with "pub-")', 'advanced-ads' );
		}

		global $external_ad_unit_id, $use_dashicons, $closeable;
		$closeable           = true;
		$use_dashicons       = false;
		$external_ad_unit_id = '';
		if ( trim( $content ) !== '' ) {

			$json_content = stripslashes( $content );

			// get json content striped by slashes.
			$content = json_decode( stripslashes( $content ) );

			if ( isset( $content->unitType ) ) {
				$content->json = $json_content;
				$unit_type     = $content->unitType;
				$unit_code     = $content->slotId;
				$unit_pubid    = ! empty( $content->pubId ) ? $content->pubId : $pub_id;
				$layout        = isset( $content->layout ) ? $content->layout : '';
				$layout_key    = isset( $content->layout_key ) ? $content->layout_key : '';

				if ( 'responsive' !== $content->unitType && 'link-responsive' !== $content->unitType && 'matched-content' !== $content->unitType ) {
					// Normal ad unit.
					$unit_width  = $ad->get_width();
					$unit_height = $ad->get_height();
				} else {
					// Responsive && multiplex ads.
					$unit_resize = ( isset( $content->resize ) ) ? $content->resize : 'auto';
					if ( 'auto' !== $unit_resize ) {
						$extra_params = apply_filters( 'advanced-ads-gadsense-ad-param-data', $extra_params, $content, $ad );
					}
				}
				if ( ! empty( $unit_pubid ) ) {
					$unit_id = 'ca-' . $unit_pubid . ':' . $unit_code;
				}
				$external_ad_unit_id = $unit_id;
			}
		}

		if ( '' === trim( $pub_id ) && '' !== trim( $unit_code ) ) {
			$pub_id_errors = __( 'Your AdSense Publisher ID is missing.', 'advanced-ads' );
		}

		$default_template = GADSENSE_BASE_PATH . 'admin/views/adsense-ad-parameters.php';
		/**
		 * Inclusion of other UI template is done here. The content is passed in order to allow the inclusion of different
		 * templates file, depending of the ad. It's up to the developer to verify that $content is not an empty
		 * variable (which is the case for a new ad).
		 *
		 * Inclusion of .js and .css files for the ad creation/editon page are done by another hook. See
		 * 'advanced-ads-gadsense-ad-param-script' and 'advanced-ads-gadsense-ad-param-style' in "../admin/class-gadsense-admin.php".
		 */
		$template = apply_filters( 'advanced-ads-gadsense-ad-param-template', $default_template, $content );

		require $template;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
}
