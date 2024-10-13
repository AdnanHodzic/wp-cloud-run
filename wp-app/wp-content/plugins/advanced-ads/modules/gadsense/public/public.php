<?php

/**
 * Class Advanced_Ads_AdSense_Public.
 */
class Advanced_Ads_AdSense_Public {

	/**
	 * AdSense account related data
	 *
	 * @var Advanced_Ads_AdSense_Data
	 */
	private $data;

	/**
	 * Instance of Advanced_Ads_AdSense_Public
	 *
	 * @var Advanced_Ads_AdSense_Public
	 */
	private static $instance;

	/**
	 * Advanced_Ads_AdSense_Public constructor.
	 */
	private function __construct() {
		$this->data = Advanced_Ads_AdSense_Data::get_instance();
		add_action( 'wp_head', [ $this, 'inject_header' ], 20 );
		// Fires before cache-busting frontend is initialized and tracking method is set
		add_action( 'wp', [ $this, 'inject_amp_code' ], 20 );
	}

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Print data in the head tag on the front end.
	 */
	public function inject_header() {
		$options = $this->data->get_options();

		// Inject CSS to make AdSense background transparent.
		if ( ! empty( $options['background'] ) ) {
			// Some themes not only get the background wrong, but also add some padding to ins element.
			echo '<style>ins.adsbygoogle { background-color: transparent; padding: 0; }</style>';
		}

		if ( defined( 'ADVADS_ADS_DISABLED' ) || advads_is_amp() ) {
			return;
		}

		$privacy         = Advanced_Ads_Privacy::get_instance();
		$privacy_options = $privacy->options();
		$privacy_enabled = $privacy->get_state() !== 'not_needed';
		$npa_enabled     = ( ! empty( $privacy_options['enabled'] ) && $privacy_options['consent-method'] === 'custom' ) && ! empty( $privacy_options['show-non-personalized-adsense'] );

		// Show non-personalized Adsense ads if non-personalized ads are enabled and consent was not given.
		if ( $privacy_enabled && $npa_enabled ) {
			echo '<script>';
			// If the page is not from a cache.
			if ( $privacy->get_state() === 'unknown' ) {
				echo '(adsbygoogle=window.adsbygoogle||[]).requestNonPersonalizedAds=1;';
			}
			// If the page is from a cache, wait until 'advads.privacy' is available. Execute before cache-busting.
			echo '( window.advanced_ads_ready || jQuery( document ).ready ).call( null, function() {
					var state = ( advads.privacy ) ? advads.privacy.get_state() : "";
					var use_npa = ( state === "unknown" ) ? 1 : 0;
					(adsbygoogle=window.adsbygoogle||[]).requestNonPersonalizedAds=use_npa;
				} )';
			echo '</script>';
		}

		if ( ! apply_filters( 'advanced-ads-can-display-ads-in-header', true ) ) {
			return;
		}

		$pub_id = trim( $this->data->get_adsense_id() );

		if ( $pub_id && isset( $options['page-level-enabled'] ) && $options['page-level-enabled'] ) {
			$pub_id          = $this->data->get_adsense_id();
			$client_id       = 'ca-' . $pub_id;
			$top_anchor      = isset( $options['top-anchor-ad'] ) && $options['top-anchor-ad'];
			$top_anchor_code = sprintf(
				'(adsbygoogle = window.adsbygoogle || []).push({
					google_ad_client: "%s",
					enable_page_level_ads: true,
					overlays: {bottom: true}
				});',
				esc_attr( $client_id )
			);
			/**
			 * Filter the output of the publisher ID appended to the AdSense JavaScript Code.
			 *
			 * @param boolean
			 */
			$add_publisher_id = apply_filters( 'advanced-ads-adsense-publisher-id', true );
			$script_src       = add_query_arg( [
				'client' => $add_publisher_id ? esc_attr( $client_id ) : false,
			], 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js' );

			/**
			 * Allows to override the page-level code.
			 *
			 * The Pro add-on uses this filter to inject a drop-in replacement for the page-level header code.
			 *
			 * @param string $code Existing page level code.
			 * @param array $parameters {
			 *    Parameters of the AdSense code.
			 *
			 *    @type string $client_id       The Google AdSense client ID.
			 *    @type bool   $top_anchor      AdSense anchor ad on top of pages.
			 *    @type string $top_anchor_code The code for top anchor ads.
			 *    @type string $script_src      AdSense script url.
			 * }
			 */
			$custom_code = apply_filters(
				'advanced-ads-gadsense-page-level-code',
				'',
				compact( [ 'client_id', 'top_anchor', 'top_anchor_code', 'script_src' ] )
			);

			if ( $custom_code ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the snippet has already been escaped.
				echo $custom_code;
				return;
			}

			// inject page-level header code.
			include GADSENSE_BASE_PATH . 'public/templates/page-level.php';
		}
	}

	/**
	 * Handle AdSense AMP code
	 */
	public function inject_amp_code() {
		// for non-AMP pages we do this on the `template_redirect` hook, this has not fired yet.
		Advanced_Ads::get_instance()->set_disabled_constant();

		if (
			// check if ads are disabled.
			( defined( 'ADVADS_ADS_DISABLED' ) && ADVADS_ADS_DISABLED )
			// check if this an AMP page, we're inside `wp` action so it's safe to use.
			|| ( ! function_exists( 'advads_is_amp' ) || ! advads_is_amp() )
		) {
			return;
		}
		// The is_amp_endpoint function is used for multiple plugins.
		if ( function_exists( 'is_amp_endpoint' ) ) {
			$adsense_data    = Advanced_Ads_AdSense_Data::get_instance();
			$adsense_options = $adsense_data->get_options();

			// AMP Auto ads was removed from Responsive add-on version 1.10.0
			if ( defined( 'AAR_VERSION' ) && 1 === version_compare( '1.10.0', AAR_VERSION )
				|| empty( $adsense_options['amp']['auto_ads_enabled'] ) ) {
				return;
			}

			// Adds the AdSense Auto ads AMP code to the page (head) in "Reader" mode.
			add_action( 'amp_post_template_data', [ $this, 'add_auto_ads_amp_head_script' ] );

			// SmartMag theme (http://theme-sphere.com/smart-mag/documentation/).
			add_action( 'bunyad_amp_pre_main', [ $this, 'add_auto_ads_amp_body_script' ] );

			/**
			 * Add AMP Auto ads body code to footer for `AMP` plugin ( https://wordpress.org/plugins/amp/ )
			 *
			 * Adds the Auto ads `body` tag to `wp_footer` because there is no WordPress right hook after `body`
			 * The AdSense Auto ads code is added automatically to the `head` section using the amp_post_template_data hook above.
			 *
			 * use `wp_footer` in Transition and Standard mode
			 * use `amp_post_template_footer` in Reader mode
			 */
			add_action( 'wp_footer', [ $this, 'add_auto_ads_amp_body_script' ] );
			add_action( 'amp_post_template_footer', [ $this, 'add_auto_ads_amp_body_script' ] );

			// Other AMP plugins.
		}
	}

	/**
	 * Add AdSense AMP Auto ads code to the header.
	 *
	 * @param array $data AMP components.
	 */
	public function add_auto_ads_amp_head_script( $data ) {
		$data['amp_component_scripts']['amp-auto-ads'] = 'https://cdn.ampproject.org/v0/amp-auto-ads-0.1.js';

		return $data;
	}

	/**
	 * Add Adsense Auto Ads body script.
	 */
	public function add_auto_ads_amp_body_script() {
		$pub_id = $this->data->get_adsense_id();
		if ( $pub_id ) {
			printf( '<amp-auto-ads type="adsense" data-ad-client="ca-%s"></amp-auto-ads>', esc_attr( $pub_id ) );
		}
	}
}
