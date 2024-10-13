<?php

/**
 * Advanced Ads AdSense Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2013-2022 Thomas Maier, Advanced Ads GmbH
 *
 * Class containing information about the adsense ad type
 *
 * see also includes/class-ad-type-abstract.php for basic object
 */
class Advanced_Ads_Ad_Type_Adsense extends Advanced_Ads_Ad_Type_Abstract {

	/**
	 * ID - internal type of the ad type
	 *
	 * must be static so set your own ad type ID here
	 * use slug like format, only lower case, underscores and hyphens
	 *
	 * @since 1.4
	 */
	public $ID = 'adsense';

	/**
	 * Set basic attributes
	 *
	 * @since 1.4
	 */
	public function __construct() {
		$this->title       = __( 'AdSense ad', 'advanced-ads' );
		$this->description = __( 'Use ads from your Google AdSense account', 'advanced-ads' );
		$this->parameters  = [
			'content' => '',
		];
	}

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
		return isset( $ad_types[ $ad_type ] ) ? $ad_types[ $ad_type ] : __( 'Normal', 'advanced-ads' );
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
		{
			// TODO: THIS IS JUST A QUICK AND DIRTY HACK. Create a dedicated method to handle this properly.
			?>
			<script>
				jQuery( function () {
					<?php
					$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();
					$json_ad_codes = json_encode( $mapi_options['ad_codes'] );
					?>
					const adsense = new AdvancedAdsNetworkAdsense(<?php echo $json_ad_codes?>)
					AdvancedAdsAdmin.AdImporter.setup( adsense )
				} )
			</script>
			<?php
		}

		$options = $ad->options();

		$content      = (string) ( isset( $ad->content ) ? $ad->content : '' );
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

		// check pub_id for errors
		$pub_id_errors = false;
		if ( $pub_id !== '' && 0 !== strpos( $pub_id, 'pub-' ) ) {
			$pub_id_errors = __( 'The Publisher ID has an incorrect format. (must start with "pub-")', 'advanced-ads' );
		}

		global $external_ad_unit_id, $use_dashicons, $closeable;
		$closeable           = true;
		$use_dashicons       = false;
		$external_ad_unit_id = "";
		if ( trim( $content ) !== '' ) {

			$json_content = stripslashes( $content );

			// get json content striped by slashes
			$content = json_decode( stripslashes( $content ) );

			if ( isset( $content->unitType ) ) {
				$content->json = $json_content;
				$unit_type     = $content->unitType;
				$unit_code     = $content->slotId;
				$unit_pubid    = ! empty( $content->pubId ) ? $content->pubId : $pub_id;
				$layout        = isset( $content->layout ) ? $content->layout : '';
				$layout_key    = isset( $content->layout_key ) ? $content->layout_key : '';

				if ( 'responsive' != $content->unitType && 'link-responsive' != $content->unitType && 'matched-content' != $content->unitType ) {
					// Normal ad unit
					$unit_width  = $ad->width;
					$unit_height = $ad->height;
				} else {
					// Responsive && multiplex ads
					$unit_resize = ( isset( $content->resize ) ) ? $content->resize : 'auto';
					if ( 'auto' != $unit_resize ) {
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
	 * @param Advanced_Ads_Ad $ad ad object.
	 */
	public function render_icon( Advanced_Ads_Ad $ad ) {
		$image = 'adsense-display.svg';

		$content = json_decode( wp_unslash( $ad->content ), true );
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
	 * @param Advanced_Ads_Ad $ad ad object.
	 */
	public function render_ad_type_tooltip( Advanced_Ads_Ad $ad ) {
		$content = json_decode( stripslashes( $ad->content ), true );
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
		// remove this slotId from unsupported_ads
		$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();
		if ( array_key_exists( 'slotId', $ad_unit ) && array_key_exists( $ad_unit['slotId'], $mapi_options['unsupported_units'] ) ) {
			unset( $mapi_options['unsupported_units'][ $ad_unit['slotId'] ] );
			update_option( Advanced_Ads_AdSense_MAPI::OPTNAME, $mapi_options );
		}

		return $content;
	}

	/**
	 * Prepare the ads frontend output.
	 *
	 * @param object $ad ad object.
	 *
	 * @return string $content ad content prepared for frontend output
	 * @since 1.0.0
	 */
	public function prepare_output( $ad ) {
		global $gadsense;

		$content = json_decode( stripslashes( $ad->content ) );

		if ( isset( $ad->args['wp_the_query']['is_404'] )
		     && $ad->args['wp_the_query']['is_404']
		     && ! defined( 'ADVADS_ALLOW_ADSENSE_ON_404' ) ) {
			return '';
		}

		$output         = '';
		$db             = Advanced_Ads_AdSense_Data::get_instance();
		$pub_id         = $db->get_adsense_id( $ad );

		if ( ! isset( $content->unitType ) || empty( $pub_id ) ) {
			return $output;
		}
		// deprecated since the adsbygoogle.js file is now always loaded.
		if ( ! isset( $gadsense['google_loaded'] ) || ! $gadsense['google_loaded'] ) {
			$gadsense['google_loaded'] = true;
		}

		// check if passive cb is used.
		if ( isset( $gadsense['adsense_count'] ) ) {
			$gadsense['adsense_count'] ++;
		} else {
			$gadsense['adsense_count'] = 1;
		}

		// "link" was a static format until AdSense stopped filling them in March 2021. Their responsive format serves as a fallback recommended by AdSense
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$is_static_normal_content = ! in_array( $content->unitType, [ 'responsive', 'link', 'link-responsive', 'matched-content', 'in-article', 'in-feed' ], true );

		$output = apply_filters( 'advanced-ads-gadsense-output', false, $ad, $pub_id, $content );
		if ( false !== $output ) {
			return $output;
		} elseif ( advads_is_amp() ) {
			// Prevent output on AMP pages.
			return '';
		}

		$output = '';

		// add notice when a link unit is used
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( in_array( $content->unitType, [ 'link', 'link-responsive' ], true ) ) {
			Advanced_Ads_Ad_Health_Notices::get_instance()->add( 'adsense_link_units_deprecated' );
		}

		// build static normal content ads first.
		if ( $is_static_normal_content ) {
			$output .= $this->get_script_tag( $pub_id );
			$output .= '<ins class="adsbygoogle" ';
			$output .= 'style="display:inline-block;width:' . $ad->width . 'px;height:' . $ad->height . 'px;" ' . "\n";
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
			$output     = apply_filters( 'advanced-ads-gadsense-responsive-output', $output, $ad, $pub_id );
			if ( $unmodified === $output ) {
				/**
				 * If the output has not been modified, perform a default responsive output.
				 * A simple did_action check isn't sufficient, some hooks may be attached and fired but didn't touch the output
				 */
				$this->append_defaut_responsive_content( $output, $pub_id, $content );

				// Remove float setting if this is a responsive ad unit without custom sizes.
				unset( $ad->wrapper['style']['float'] );
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
