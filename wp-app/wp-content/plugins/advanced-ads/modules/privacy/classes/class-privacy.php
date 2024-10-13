<?php

/**
 * Handles Advanced Ads privacy settings.
 */
class Advanced_Ads_Privacy {
	/**
	 * Singleton instance of the plugin
	 *
	 * @var self
	 */
	protected static $instance;

	/**
	 * Module options
	 *
	 * @var null|array
	 */
	protected $options;

	/**
	 * Option key
	 *
	 * @const string
	 */
	const OPTION_KEY = 'advanced-ads-privacy';

	/**
	 * Initialize the module
	 */
	private function __construct() {
		add_filter( 'advanced-ads-can-display', [ $this, 'can_display_by_consent' ], 10, 3 );

		$this->options();

		if ( ! empty( $this->options['enabled'] ) ) {
			add_filter( 'advanced-ads-activate-advanced-js', '__return_true' );

			if ( $this->options['consent-method'] === 'iab_tcf_20' ) {
				add_filter( 'advanced-ads-output-final', [ $this, 'final_ad_output' ], 10, 2 );
			}
		}
	}

	/**
	 * If this ad is not image or dummy base64_encode the text.
	 *
	 * @param string          $output The output string.
	 * @param Advanced_Ads_Ad $ad     The ad object.
	 *
	 * @return string
	 */
	public function final_ad_output( $output, Advanced_Ads_Ad $ad ) {
		if (
			advads_is_amp()
			// Never encode image, dummy or group ads.
			|| ! $this->ad_type_needs_consent( $ad->type )
			// Consent is overridden, and this is not an AdSense ad, don't encode it.
			|| ( $ad->type !== 'adsense' && isset( $ad->options()['privacy']['ignore-consent'] ) )
		) {
			return $output;
		}

		return $this->encode_ad( $output, $ad );
	}

	/**
	 * Encode the ad output.
	 *
	 * @param string          $output The output string.
	 * @param Advanced_Ads_Ad $ad     The ad object.
	 *
	 * @return string
	 */
	public function encode_ad( $output, Advanced_Ads_Ad $ad ) {
		$data_attributes = [
			'id'  => $ad->id,
			'bid' => get_current_blog_id(),
		];
		if ( ! empty( $ad->output['placement_id'] ) ) {
			$data_attributes['placement'] = $ad->output['placement_id'];
		}

		/**
		 * Filter the data attributes and allow removing/adding attributes.
		 * All attributes will be prefix with `data-` on output.
		 *
		 * @param array           $data_attributes The default data attributes.
		 * @param Advanced_Ads_Ad $ad              The current ad.
		 */
		$data_attributes = (array) apply_filters( 'advanced-ads-privacy-output-attributes', $data_attributes, $ad );

		// convert the data-attributes array into a string.
		$attributes_string = '';
		array_walk( $data_attributes, static function( $value, $key ) use ( &$attributes_string ) {
			$attributes_string .= sprintf( ' data-%s="%s"', sanitize_key( $key ), esc_attr( $value ) );
		} );

		return sprintf(
			'<script type="text/plain" data-tcf="waiting-for-consent"%s>%s</script>',
			$attributes_string,
			// phpcs:ignore  WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- we need to obfuscate the html (and maybe decode it in JS).
			base64_encode( $output )
		);
	}

	/**
	 * Check if the current ad output is encoded.
	 *
	 * @param string $output The ad output.
	 *
	 * @return bool
	 */
	public function is_ad_output_encoded( $output ) {
		return (bool) strpos( $output, 'data-tcf="waiting-for-consent"' );
	}

	/**
	 * Return an instance of Advanced_Ads_Privacy
	 *
	 * @return self
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return module options
	 *
	 * @return array
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( self::OPTION_KEY, [] );
			if ( isset( $this->options['enabled'] ) && empty( $this->options['consent-method'] ) ) {
				$this->options['enabled'] = false;
			}
		}

		return $this->options;
	}

	/**
	 * Check if ad can be displayed based on user's consent.
	 *
	 * @param bool            $can_display   Whether to display this ad.
	 * @param Advanced_Ads_Ad $ad            The ad object.
	 * @param array           $check_options Additional options passed to can_display.
	 *
	 * @return bool
	 */
	public function can_display_by_consent( $can_display, Advanced_Ads_Ad $ad, $check_options ) {
		// already false, honor this.
		if ( ! $can_display ) {
			return $can_display;
		}

		// passive cache busting enabled.
		if ( $check_options['passive_cache_busting'] ) {
			return true;
		}

		// privacy module not active, bail early.
		if ( empty( $this->options['enabled'] ) ) {
			return true;
		}

		// If consent is overriden for the ad.
		if ( ! empty( $ad->options()['privacy']['ignore-consent'] ) ) {
			return true;
		}

		$consent_method = isset( $this->options['consent-method'] ) ? $this->options['consent-method'] : '';

		// If the consent method is set to cookie and the ad type does not need consent.
		if ( $consent_method === 'custom' && ! $this->ad_type_needs_consent( $ad->type ) ) {
			return true;
		}

		// If method is iab_tcf_20, always set to true, JS needs to decide whether to display ad or not.
		if ( $consent_method === 'iab_tcf_20' ) {
			return true;
		}

		// Either personalized or non-personalized ad will be shown.
		if ( $ad->type === 'adsense' && ! empty( $this->options()['show-non-personalized-adsense'] ) ) {
			return true;
		}

		return $this->get_state() !== 'unknown';
	}

	/**
	 * Check whether this ad_type needs consent.
	 *
	 * @param string $type The ad type.
	 *
	 * @return bool
	 */
	public function ad_type_needs_consent( $type ) {
		return ! in_array( $type, [ 'image', 'dummy', 'group' ], true );
	}

	/**
	 * Check if consent is not needed or was given by the user.
	 *
	 * @return string
	 *     'not_needed' - consent is not needed.
	 *     'accepted' - consent was given.
	 *     'unknown' - consent was not given yet.
	 */
	public function get_state() {
		static $state;
		if ( is_null( $state ) ) {
			$state = $this->parse_state();
		}

		return $state;
	}

	/**
	 * Used by get_state() to parse the state of privacy/consent.
	 *
	 * @return string
	 *     'not_needed' - consent is not needed.
	 *     'accepted' - consent was given.
	 *     'unknown' - consent was not given yet.
	 */
	private function parse_state() {
		if ( empty( $this->options['enabled'] ) || advads_is_amp() ) {
			return 'not_needed';
		}

		$consent_method = isset( $this->options['consent-method'] ) ? $this->options['consent-method'] : '';
		switch ( $consent_method ) {
			case 'custom':
				$name = $this->options['custom-cookie-name'];
				if ( empty( $name ) ) {
					return 'not_needed';
				}

				if ( ! isset( $_COOKIE[ $name ] ) ) {
					return 'unknown';
				}

				$value = isset( $this->options['custom-cookie-value'] ) ? $this->options['custom-cookie-value'] : '';
				if (
					( $value === '' && $_COOKIE[ $name ] === '' )
					|| ( $value !== '' && strpos( $_COOKIE[ $name ], $value ) !== false )
				) {
					return 'accepted';
				}

				return 'unknown';
			case 'iab_tcf_20':
			default:
				return 'unknown';
		}
	}
}
