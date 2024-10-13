<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

use AdvancedAds\Entities;

/**
 * Adsense data class.
 */
class Advanced_Ads_AdSense_Data {

	/**
	 * Singleton instance
	 *
	 * @var Advanced_Ads_AdSense_Data
	 */
	private static $instance;

	/**
	 * Hold options
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Hold resizing data
	 *
	 * @var array
	 */
	private $resizing;

	/**
	 * The constructor.
	 */
	private function __construct() {
		$options = get_option( GADSENSE_OPT_NAME, [] );

		// Set defaults.
		if ( ! isset( $options['adsense-id'] ) ) {
			$options['adsense-id'] = '';
			update_option( GADSENSE_OPT_NAME, $options );
		}

		$this->options = wp_parse_args(
			$options,
			[
				'background'         => false,
				'page-level-enabled' => false,
			]
		);

		// Resizing method for responsive ads.
		$this->resizing = [
			'auto' => __( 'Auto', 'advanced-ads' ),
		];
	}

	/**
	 * GETTERS
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get adsense id
	 *
	 * @param mixed $ad Ad instance.
	 *
	 * @return string
	 */
	public function get_adsense_id( $ad = null ) {
		if ( ! empty( $ad ) && isset( $ad->is_ad ) && true === $ad->is_ad && 'adsense' === $ad->type ) {
			$ad_content = json_decode( $ad->content );
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( $ad_content && isset( $ad_content->pubId ) && ! empty( $ad_content->pubId ) ) {
				return $ad_content->pubId;
			}
			// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return trim( $this->options['adsense-id'] );
	}

	/**
	 * Get limit per page
	 *
	 * @deprecated 1.47.0
	 * @deprecated The feature is deprecated by AdSense since 2019
	 *
	 * @return int
	 */
	public function get_limit_per_page() {
		_deprecated_function( __METHOD__, '1.47.0' );
		return 0;
	}

	/**
	 * Get responsive sizing
	 *
	 * @return array
	 */
	public function get_responsive_sizing() {
		$this->resizing = apply_filters( 'advanced-ads-gadsense-responsive-sizing', $this->resizing );

		return $this->resizing;
	}

	/**
	 * Get class instance
	 *
	 * @return Advanced_Ads_AdSense_Data
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new Advanced_Ads_AdSense_Data();
		}

		return self::$instance;
	}

	/**
	 * ISSERS/HASSERS
	 */
	public function is_page_level_enabled() {
		return $this->options['page-level-enabled'];
	}

	/**
	 * Is setup
	 *
	 * @return boolean
	 */
	public function is_setup() {
		if ( isset( $this->options ) && is_array( $this->options ) && isset( $this->options['adsense-id'] ) && $this->options['adsense-id'] ) {
			$adsense_id = $this->get_adsense_id();
			if ( $adsense_id ) {
				return Advanced_Ads_AdSense_MAPI::has_token( $adsense_id );
			}
		}

		return false;
	}

	/**
	 * Whether to hide the AdSense stats metabox.
	 *
	 * @return bool
	 */
	public function is_hide_stats() {
		global $post;

		if ( $post instanceof WP_Post && Entities::POST_TYPE_AD === $post->post_type ) {
			$the_ad = \Advanced_Ads\Ad_Repository::get( $post->ID );
			if ( 'adsense' !== $the_ad->type ) {
				return true;
			}
		}

		return isset( $this->options['hide-stats'] );
	}
}
