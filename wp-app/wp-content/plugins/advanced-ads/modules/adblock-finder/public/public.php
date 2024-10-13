<?php

/**
 * Class Advanced_Ads_Adblock_Finder
 */
class Advanced_Ads_Adblock_Finder {

	/**
	 * Advanced_Ads_Adblock_Finder constructor.
	 */
	public function __construct() {
		add_action( 'wp_footer', [ $this, 'print_adblock_check_js' ], 9 );
	}

	/**
	 * Print the appropriate script into wp_footer.
	 *
	 * Don't print anything on AMP pages.
	 * Print minimal script if Advanced Ads Pro module "Ads for ad blockers" is active.
	 */
	public function print_adblock_check_js() {
		if ( advads_is_amp() ) {
			return;
		}

		$options  = Advanced_Ads::get_instance()->options();
		$minified = ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG;

		// if ad blocker counter is active.
		if ( ! empty( $options['ga-UID'] ) ) {
			printf(
				'<script>(function(){var advanced_ads_ga_UID="%s",advanced_ads_ga_anonymIP=!!%d;%s})();</script>',
				esc_attr( $options['ga-UID'] ),
				esc_attr( ! defined( 'ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP' ) || ! ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP ),
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- escaping could break the script and we're getting the contents of a local file
				$minified
					? file_get_contents( __DIR__ . '/ga-adblock-counter.min.js' )
					: file_get_contents( __DIR__ . '/adblocker-enabled.js' ) . file_get_contents( __DIR__ . '/ga-adblock-counter.js' )
				// phpcs:enable
			);
		} elseif ( defined( 'AAP_SLUG' ) && ! empty( get_option( 'advanced-ads-pro' )['ads-for-adblockers']['enabled'] ) ) {
			// if Advanced Ads Pro module "Ads for ad blockers" is active but no tracking.
			printf(
				'<script>%s</script>',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- escaping could break the script and we're getting the contents of a local file
				file_get_contents( __DIR__ . '/adblocker-enabled' . ( $minified ? '.min' : '' ) . '.js' )
			);
		}
	}
}
