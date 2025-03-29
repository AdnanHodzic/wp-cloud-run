<?php
/**
 * Frontend Scripts.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Frontend;

use Advanced_Ads;
use Advanced_Ads_Utils;
use Advanced_Ads_Privacy;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Scripts.
 */
class Scripts implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_head', [ $this, 'print_head_scripts' ], 7 );
		add_action( 'wp_footer', [ $this, 'print_footer_scripts' ], 100 );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		if ( advads_is_amp() ) {
			return;
		}

		wp_register_script(
			ADVADS_SLUG . '-advanced-js',
			sprintf( '%spublic/assets/js/advanced%s.js', ADVADS_BASE_URL, defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ),
			[ 'jquery' ],
			ADVADS_VERSION,
			false
		);

		$privacy                    = Advanced_Ads_Privacy::get_instance();
		$privacy_options            = $privacy->options();
		$privacy_options['enabled'] = ! empty( $privacy_options['enabled'] );
		$privacy_options['state']   = $privacy->get_state();

		wp_localize_script(
			ADVADS_SLUG . '-advanced-js',
			'advads_options',
			[
				'blog_id' => get_current_blog_id(),
				'privacy' => $privacy_options,
			]
		);

		$frontend_picker = Params::cookie( 'advads_frontend_picker' );
		$activated_js    = apply_filters( 'advanced-ads-activate-advanced-js', isset( Advanced_Ads::get_instance()->options()['advanced-js'] ) );

		if ( $activated_js || ! empty( $frontend_picker ) ) {
			wp_enqueue_script( ADVADS_SLUG . '-advanced-js' );
		}

		wp_register_script(
			ADVADS_SLUG . '-frontend-picker',
			sprintf( '%spublic/assets/js/frontend-picker%s.js', ADVADS_BASE_URL, defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ),
			[ 'jquery', ADVADS_SLUG . '-advanced-js' ],
			ADVADS_VERSION,
			false
		);

		if ( ! empty( $frontend_picker ) ) {
			wp_enqueue_script( ADVADS_SLUG . '-frontend-picker' );
		}
	}

	/**
	 * Print public-facing JavaScript in the HTML head.
	 *
	 * @return void
	 */
	public function print_head_scripts(): void {
		printf(
			'<!-- %1$s is managing ads with Advanced Ads %2$s – https://wpadvancedads.com/ -->',
			esc_html( WordPress::get_site_domain() ),
			esc_html( ADVADS_VERSION )
		);

		if ( advads_is_amp() ) {
			return;
		}

		$frontend_prefix = wp_advads()->get_frontend_prefix();

		ob_start();
		?>
		<script id="<?php echo esc_attr( $frontend_prefix ); ?>ready">
			<?php
			readfile( // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- we're getting the contents of a local file
				sprintf(
					'%spublic/assets/js/ready%s.js',
					ADVADS_ABSPATH,
					defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'
				)
			);
			?>
		</script>
		<?php

		/**
		 * Print inline script in the page header form add-ons.
		 *
		 * @param string $frontend_prefix the prefix used for Advanced Ads related HTML ID-s and classes.
		 */
		do_action( 'advanced_ads_inline_header_scripts', $frontend_prefix );

		echo Advanced_Ads_Utils::get_inline_asset( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Print inline scripts in wp_footer.
	 *
	 * @return void
	 */
	public function print_footer_scripts(): void {
		if ( advads_is_amp() ) {
			return;
		}

		$file_path = sprintf(
			'%spublic/assets/js/ready-queue%s.js',
			ADVADS_ABSPATH,
			defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min'
		);

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Advanced_Ads_Utils::get_inline_asset(
			sprintf(
				'<script>%s</script>',
				file_get_contents( $file_path ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			)
		);
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
