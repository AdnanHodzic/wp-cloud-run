<?php
/**
 * Advanced Ads Dummy Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <support@wpadvancedads.com>
 * @license   GPL-2.0+
 * @link      https://wpadvancedads.com
 * @copyright 2014-2017 Thomas Maier, Advanced Ads GmbH
 * @since     1.8
 *
 * Class containing information about the dummy ad type
 *
 */
class Advanced_Ads_Ad_Type_Dummy extends Advanced_Ads_Ad_Type_Abstract{

	/**
	 * ID - internal type of the ad type
	 */
	public $ID = 'dummy';

	/**
	 * Set basic attributes
	 */
	public function __construct() {
		$this->title       = __( 'Dummy', 'advanced-ads' );
		$this->description = __( 'Uses a simple placeholder ad for quick testing.', 'advanced-ads' );
	}

	/**
	 * Output for the ad parameters metabox
	 *
	 * this will be loaded using ajax when changing the ad type radio buttons
	 * echo the output right away here
	 * name parameters must be in the "advanced_ads" array
	 *
	 * @param obj $ad ad object
	 */
	public function render_parameters( $ad ){

		// don’t show url field if tracking plugin enabled
		if( ! defined( 'AAT_VERSION' )) :
		    $url = ( ! empty( $ad->url ) ) ? esc_attr( $ad->url ) : home_url();
		    ?><span class="label"><?php _e( 'URL', 'advanced-ads' ); ?></span>
		    <div><input type="text" name="advanced_ad[url]" id="advads-url" class="advads-ad-url" value="<?php echo $url; ?>"/></div><hr/>
		<?php endif;

		?><img src="<?php echo ADVADS_BASE_URL ?>public/assets/img/dummy.jpg" width="300" height="250" /><?php

		?><input type="hidden" name="advanced_ad[width]" value="300"/>
		<input type="hidden" name="advanced_ad[height]" value="250"/><?php
	}

	/**
	 * Prepare the ads frontend output.
	 *
	 * @param Advanced_Ads_Ad $ad The ad object.
	 *
	 * @return string static image content.
	 */
	public function prepare_output( $ad ) {
		$style = '';
		if ( isset( $ad->output['position'] ) && strpos( $ad->output['position'], 'center' ) === 0 ) {
			$style .= 'display: inline-block;';
		}
		$style = '' !== $style ? 'style="' . $style . '"' : '';
		$img = sprintf( '<img src="%s" width="300" height="250" %s />', esc_url( ADVADS_BASE_URL . 'public/assets/img/dummy.jpg' ), $style );

		$url = ( isset( $ad->url ) ) ? esc_url( $ad->url ) : '';
		if ( ! defined( 'AAT_VERSION' ) && $url ) {
			// get general target setting.
			$options      = Advanced_Ads::get_instance()->options();
			$target_blank = ! empty( $options['target-blank'] ) ? ' target="_blank"' : '';
			$img          = sprintf( '<a href="%s"%s aria-label="dummy">%s</a>', esc_url( $url ), $target_blank, $img );
		}

		// Add 'loading' attribute if applicable, available from WP 5.5.
		if ( function_exists( 'wp_lazy_loading_enabled' ) && wp_lazy_loading_enabled( 'img', 'the_content' ) ) {
			// Optimize image HTML tag with loading attributes based on WordPress filter context.
			$img = $this->img_tag_add_loading_attr( $img, 'the_content' );
		}

		return $img;
	}

}
