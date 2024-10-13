<?php
/**
 * Advanced Ads Abstract Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2014 Thomas Maier, Advanced Ads GmbH
 *
 * Class containing information that are defaults for all the other ad types
 *
 * see ad_type_content.php for an example on ad type
 *
 */
class Advanced_Ads_Ad_Type_Abstract {

	/**
	 * ID - internal type of the ad type
	 *
	 * must be static so set your own ad type ID here
	 * use slug like format, only lower case, underscores and hyphens
	 *
	 * @since 1.0.0
	 */
	public $ID = '';

	/**
	 * Public title
	 *
	 * will be set within __construct so one can localize it
	 *
	 * @since 1.0.0
	 */
	public $title = '';

	/**
	 * Description of the ad type
	 *
	 * will be set within __construct so one can localize it
	 *
	 * @since 1.0.0
	 */
	public $description = '';

	/**
	 * Parameters of the ad
	 *
	 * defaults are set in construct
	 */
	public $parameters = [];

	/**
	 * Output for the ad parameters metabox
	 *
	 * @param Advanced_Ads_Ad $ad ad object.
	 */
	public function render_parameters( Advanced_Ads_Ad $ad ) {
		/**
		* This will be loaded by default or using ajax when changing the ad type radio buttons
		* echo the output right away here
		* name parameters must be in the "advanced_ads" array
		 */
	}

	/**
	 * Render icon on the ad overview list.
	 *
	 * @param Advanced_Ads_Ad $ad ad object.
	 *
	 * @return void
	 */
	public function render_icon( Advanced_Ads_Ad $ad ) {
		$icon_path = sprintf( 'admin/assets/img/ad-types/%s.svg', esc_attr( $ad->type ) );
		if ( ! file_exists( ADVADS_ABSPATH . $icon_path ) ) {
			$icon_path = 'admin/assets/img/ad-types/empty.svg';
		}
		printf( '<img src="%s" width="50" height="50" />', esc_url( ADVADS_BASE_URL . $icon_path ) );
	}

	/**
	 * Render preview on the ad overview list
	 *
	 * @param Advanced_Ads_Ad $ad ad object.
	 */
	public function render_preview( Advanced_Ads_Ad $ad ) {}

	/**
	 * Render additional information in the ad type tooltip on the ad overview page
	 *
	 * @param Advanced_Ads_Ad $ad ad object.
	 */
	public function render_ad_type_tooltip( Advanced_Ads_Ad $ad ) {}

	/**
	 * Sanitize ad options on save
	 *
	 * @param array $options all ad options.
	 * @return array sanitized ad options.
	 * @since 1.0.0
	 */
	public function sanitize_options( $options = [] ) {
		return $options;
	}

	/**
	 * Sanitize content field on save
	 *
	 * @param str $content ad content
	 * @return str $content sanitized ad content
	 * @since 1.0.0
	 */
	public function sanitize_content($content = ''){
		return $content = wp_unslash( $content );
	}

	/**
	 * Load content field for the ad
	 *
	 * @param WP_Post $post WP post object
	 * @return string $content ad content
	 * @since 1.0.0
	 */
	public function load_content($post){
		return $post->post_content;
	}

	/**
	 * Prepare the ads frontend output
	 *
	 * @param Advanced_Ads_Ad $ad The current ad object.
	 *
	 * @return string $content ad content prepared for frontend output
	 * @since 1.0.0
	 */
	public function prepare_output( $ad ) {
		return $ad->content;
	}

	/**
	 * Process shortcodes.
	 *
	 * @param string          $output Ad content.
	 * @param Advanced_Ads_Ad $ad     The current ad object.
	 *
	 * @return string
	 */
	protected function do_shortcode( $output, Advanced_Ads_Ad $ad ) {
		$ad_options = $ad->options();

		if ( ! isset( $ad_options['output']['has_shortcode'] ) || $ad_options['output']['has_shortcode'] ) {
			// Store arguments so that shortcode hooks can access it.
			$ad_args = $ad->args;
			$ad_args['shortcode_ad_id'] = $ad->id;
			$output = preg_replace( '/\[(the_ad_group|the_ad_placement|the_ad)/', '[$1 ad_args="' . urlencode( json_encode( $ad_args ) )  . '"', $output );
		}

		return do_shortcode( $output );
	}


	/**
	 * Applies image loading optimization attributes to an image HTML tag based on WordPress version.
	 *
	 * @param string $img     HTML image tag.
	 * @param string $context content
	 *
	 * @return string Updated HTML image tag with loading optimization attributes.
	 */
	public function img_tag_add_loading_attr( $img, $context ) {
		if ( is_array( $context ) ) {
			$context = end( $context );
		}

		// Check if the current WordPress version is compatible.
		if ( is_wp_version_compatible( '6.3' ) ) {
			 // If compatible, apply the function to add loading optimization attributes
			return wp_img_tag_add_loading_optimization_attrs( $img, $context );
		}

		// If not compatible, use the deprecated function to add loading attribute
		return wp_img_tag_add_loading_attr( $img, $context );
	}

}
