<?php // phpcs:ignore WordPress.Files.FileName
/**
 * AdSense Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2013-2022 Thomas Maier, Advanced Ads GmbH
 */

use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Interfaces\Ad_Interface;

/**
 * Adsense ad type
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */
class Advanced_Ads_Ad_Type_Adsense extends Ad implements Ad_Interface {

	/**
	 * Return an array with AdSense ad type keys and readable labels
	 *
	 * @return array
	 */
	public static function get_ad_types() {
		return [
			'normal'          => __( 'Normal', 'advanced-ads' ),
			'responsive'      => __( 'Responsive', 'advanced-ads' ),
			'matched-content' => __( 'Multiplex', 'advanced-ads' ),
			'link'            => __( 'Link ads', 'advanced-ads' ),
			'link-responsive' => __( 'Link ads (Responsive)', 'advanced-ads' ),
			'in-article'      => __( 'In-article', 'advanced-ads' ),
			'in-feed'         => __( 'In-feed', 'advanced-ads' ),
		];
	}

	/**
	 * Get readable names for each AdSense ad type
	 *
	 * @param string $ad_type ad type key.
	 * @return string
	 */
	public static function get_ad_type_label( $ad_type ) {
		$ad_types = self::get_ad_types();
		return $ad_types[ $ad_type ] ?? __( 'Normal', 'advanced-ads' );
	}

	/**
	 * Output for the ad parameters metabox
	 * this will be loaded using ajax when changing the ad type radio buttons
	 * echo the output right away here
	 * name parameters must be in the "advanced_ads" array
	 *
	 * @param object $ad ad object.
	 *
	 * @since 1.4
	 */
	public function render_parameters( $ad ) {
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
		$content      = (string) $ad->get_content();
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
				$layout        = $content->layout ?? '';
				$layout_key    = $content->layout_key ?? '';

				if ( 'responsive' !== $content->unitType && 'link-responsive' !== $content->unitType && 'matched-content' !== $content->unitType ) {
					// Normal ad unit.
					$unit_width  = $ad->get_width();
					$unit_height = $ad->get_height();
				} else {
					// Responsive && multiplex ads.
					$unit_resize = $content->resize ?? 'auto';
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
	}

	/**
	 * Render icon on the ad overview list
	 *
	 * @param Ad $ad Ad instance.
	 */
	public function render_icon( Ad $ad ) {
		$image = 'adsense-display.svg';

		$content = json_decode( wp_unslash( $ad->get_content() ), true );
		if ( isset( $content['unitType'] ) ) {
			switch ( $content['unitType'] ) {
				case 'matched-content':
					$image = 'adsense-multiplex.svg';
					break;
				case 'in-article':
					$image = 'adsense-in-article.svg';
					break;
				case 'in-feed':
					$image = 'adsense-in-feed.svg';
					break;
			}
		}

		echo '<img src="' . esc_url( ADVADS_BASE_URL ) . '/modules/gadsense/admin/assets/img/' . esc_attr( $image ) . '" width="50" height="50">';
	}

	/**
	 * Render additional information in the ad type tooltip on the ad overview page
	 *
	 * @param Ad $ad Ad instance.
	 */
	public function render_ad_type_tooltip( Ad $ad ) {
		$content = json_decode( stripslashes( $ad->get_content() ), true );
		if ( isset( $content['unitType'] ) ) {
			echo esc_html( self::get_ad_type_label( $content['unitType'] ) );
		}
	}

	/**
	 * Sanitize content field on save
	 *
	 * @param string $content ad content.
	 *
	 * @return string $content sanitized ad content
	 * @since 1.0.0
	 */
	public function sanitize_content( $content = '' ) {
		$content = wp_unslash( $content );
		$ad_unit = json_decode( $content, true );
		if ( empty( $ad_unit ) ) {
			$ad_unit = [];
		}

		// Remove this slotId from unsupported_ads.
		$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();
		if ( array_key_exists( 'slotId', $ad_unit ) && array_key_exists( $ad_unit['slotId'], $mapi_options['unsupported_units'] ) ) {
			unset( $mapi_options['unsupported_units'][ $ad_unit['slotId'] ] );
			update_option( Advanced_Ads_AdSense_MAPI::OPTION_KEY, $mapi_options );
		}

		return $content;
	}

	/**
	 * Prepare output for frontend.
	 *
	 * @return string
	 */
	public function prepare_frontend_output(): string {
		global $gadsense;

		$ad_args = $this->get_prop( 'ad_args' );
		$content = json_decode( stripslashes( $this->get_content() ) );

		if (
			isset( $ad_args['wp_the_query']['is_404'] ) &&
			$ad_args['wp_the_query']['is_404'] &&
			! defined( 'ADVADS_ALLOW_ADSENSE_ON_404' )
		) {
			return '';
		}

		$output = '';
		$db     = Advanced_Ads_AdSense_Data::get_instance();
		$pub_id = $db->get_adsense_id( $this );

		if ( ! isset( $content->unitType ) || empty( $pub_id ) ) {
			return $output;
		}

		// deprecated since the adsbygoogle.js file is now always loaded.
		if ( ! isset( $gadsense['google_loaded'] ) || ! $gadsense['google_loaded'] ) {
			$gadsense['google_loaded'] = true;
		}

		// check if passive cb is used.
		if ( isset( $gadsense['adsense_count'] ) ) {
			++$gadsense['adsense_count'];
		} else {
			$gadsense['adsense_count'] = 1;
		}

		// "link" was a static format until AdSense stopped filling them in March 2021. Their responsive format serves as a fallback recommended by AdSense
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$is_static_normal_content = ! in_array( $content->unitType, [ 'responsive', 'link', 'link-responsive', 'matched-content', 'in-article', 'in-feed' ], true );

		$output = apply_filters( 'advanced-ads-gadsense-output', false, $this, $pub_id, $content );
		if ( false !== $output ) {
			return $output;
		}

		// Prevent output on AMP pages.
		if ( advads_is_amp() ) {
			return '';
		}

		$output = '';

		// Add notice when a link unit is used.
		if ( in_array( $content->unitType, [ 'link', 'link-responsive' ], true ) ) {
			Advanced_Ads_Ad_Health_Notices::get_instance()->add( 'adsense_link_units_deprecated' );
		}

		// build static normal content ads first.
		if ( $is_static_normal_content ) {
			$output .= $this->get_script_tag( $pub_id );
			$output .= '<ins class="adsbygoogle" ';
			$output .= 'style="display:inline-block;width:' . $this->get_width() . 'px;height:' . $this->get_height() . 'px;" ' . "\n";
			$output .= 'data-ad-client="ca-' . $pub_id . '" ' . "\n";
			$output .= 'data-ad-slot="' . $content->slotId . '"';
			// ad type for static link unit.
			if ( 'link' === $content->unitType ) {
				$output .= "\n" . 'data-ad-format="link"';
			}
			$output .= '></ins> ' . "\n";
			$output .= '<script> ' . "\n";
			$output .= '(adsbygoogle = window.adsbygoogle || []).push({}); ' . "\n";
			$output .= '</script>' . "\n";
		} else {
			/**
			 * The value of $ad->content->resize should be tested to format the output correctly
			 */
			$unmodified = $output;
			$output     = apply_filters( 'advanced-ads-gadsense-responsive-output', $output, $this, $pub_id );
			if ( $unmodified === $output ) {
				/**
				 * If the output has not been modified, perform a default responsive output.
				 * A simple did_action check isn't sufficient, some hooks may be attached and fired but didn't touch the output
				 */
				$this->append_defaut_responsive_content( $output, $pub_id, $content );

				// Remove float setting if this is a responsive ad unit without custom sizes.
				unset( $this->wrapper['style']['float'] );
			}
		}

		return $output;
	}

	/**
	 * Check if a string looks like an AdSense ad code.
	 *
	 * @param string $content The string that need to be checked.
	 *
	 * @return boolean
	 */
	public static function content_is_adsense( $content = '' ) {
		return false !== stripos( $content, 'googlesyndication.com' ) &&
			( false !== stripos( $content, 'google_ad_client' ) || false !== stripos( $content, 'data-ad-client' ) );
	}

	/**
	 * Build AdSense script tag.
	 *
	 * @param string $pub_id AdSense publisher ID.
	 *
	 * @return string
	 */
	protected function get_script_tag( $pub_id ) {
		return sprintf(
			// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- don't allow any changes on Google AdSense code.
			'<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js%s" crossorigin="anonymous"></script>',
			/**
			 * Filter the output of the publisher ID appended to the AdSense JavaScript Code.
			 *
			 * @param boolean
			 */
			apply_filters( 'advanced-ads-adsense-publisher-id', true ) ? '?client=ca-' . $pub_id : ''
		);
	}

	/**
	 * Append responsive content
	 *
	 * @param string $output Current ad unit code.
	 * @param string $pub_id AdSense publisher ID.
	 * @param object $content Ad unit content with all parameters.
	 */
	protected function append_defaut_responsive_content( &$output, $pub_id, $content ) {
		$format = '';
		$style  = 'display:block;';
		switch ( $content->unitType ) {
			case 'matched-content':
				$format = 'autorelaxed';
				break;
			case 'link-responsive':
			case 'link':
				$format = 'link';
				break;
			case 'in-feed':
				$format     = 'fluid';
				$layout_key = $content->layout_key;
				break;
			case 'in-article':
				$format = 'fluid';
				$layout = 'in-article';
				$style  = 'display:block; text-align:center;';
				break;
			default:
				$format = 'auto';
		}

		$output .= $this->get_script_tag( $pub_id );
		$output .= '<ins class="adsbygoogle" ';
		$output .= 'style="' . $style . '" ';
		$output .= 'data-ad-client="ca-' . $pub_id . '" ' . "\n";
		$output .= 'data-ad-slot="' . $content->slotId . '" ' . "\n";
		$output .= isset( $layout ) ? 'data-ad-layout="' . $layout . '"' . "\n" : '';
		$output .= isset( $layout_key ) ? 'data-ad-layout-key="' . $layout_key . '"' . "\n" : '';
		$output .= 'data-ad-format="';
		$output .= $format;

		$options = Advanced_Ads_AdSense_Data::get_instance()->get_options();
		$fw      = ! empty( $options['fullwidth-ads'] ) ? $options['fullwidth-ads'] : 'default';
		if ( 'default' !== $fw ) {
			$output .= 'enable' === $fw ? '" data-full-width-responsive="true' : '" data-full-width-responsive="false';
		}

		$output .= '"></ins>' . "\n";
		$output .= '<script> ' . "\n";
		$output .= apply_filters( 'advanced-ads-gadsense-responsive-adsbygoogle', '(adsbygoogle = window.adsbygoogle || []).push({}); ' . "\n" );
		$output .= '</script>' . "\n";
	}
}
