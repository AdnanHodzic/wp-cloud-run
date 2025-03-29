<?php
/**
 * Compatibility Inline JS.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Compatibility;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility Inline JS.
 */
class Inline_JS implements Integration_Interface {

	/**
	 * Array that holds strings that should not be optimized by other plugins.
	 *
	 * @var array
	 */
	private $inline_js;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		$this->critical_inline_js();

		add_filter( 'rocket_delay_js_exclusions', [ $this, 'rocket_exclude_inline_js' ] );
		add_filter( 'rocket_excluded_inline_js_content', [ $this, 'rocket_exclude_inline_js' ] );
		add_filter( $this->get_cmplz_hook(), [ $this, 'complianz_exclude_inline_js' ], 10, 2 );
	}

	/**
	 * Prevent the 'advanced_ads_ready' function declaration from being merged with other JS
	 * and outputted into the footer. This is needed because WP Rocket does not output all
	 * the code that depends on this function into the footer.
	 *
	 * @param array $exclusions Patterns to match in inline JS content.
	 *
	 * @return array
	 */
	public function rocket_exclude_inline_js( $exclusions ): array {
		return array_merge( $exclusions, $this->inline_js );
	}

	/**
	 * Prevent Complianz from suppressing our head inline script.
	 *
	 * @param string $classname   The class Complianz adds to the script, `cmplz-script` for prevented scripts, `cmplz-native` for allowed.
	 * @param string $total_match The script string.
	 *
	 * @return string
	 */
	public function complianz_exclude_inline_js( $classname, $total_match ) {
		// Early bail!!
		if ( 'cmplz-native' === $classname ) {
			return $classname;
		}

		foreach ( $this->inline_js as $critical_inline_js ) {
			if ( false !== strpos( $total_match, $critical_inline_js ) ) {
				return 'cmplz-native';
			}
		}

		return $classname;
	}

	/**
	 * Get an array of strings to exclude when plugins "optimize" JS.
	 *
	 * @return void
	 */
	private function critical_inline_js(): void {
		$frontend_prefix = wp_advads()->get_frontend_prefix();
		$default         = [
			sprintf( 'id="%sready"', $frontend_prefix ),
		];

		/**
		 * Filters an array of strings of (inline) JavaScript "identifiers" that should not be "optimized"/delayed etc.
		 *
		 * @param array $default Array of excluded patterns.
		 */
		$exclusions = apply_filters( 'advanced-ads-compatibility-critical-inline-js', $default, $frontend_prefix );

		if ( ! is_array( $exclusions ) ) {
			$exclusions = $default;
		}

		$this->inline_js = $exclusions;
	}

	/**
	 * Get cmplz hook by version
	 *
	 * @return string
	 */
	private function get_cmplz_hook(): string {
		$complianz_version = get_option( 'cmplz-current-version', false );
		if ( $complianz_version && version_compare( $complianz_version, '6.0.0', '>=' ) ) {
			return 'cmplz_service_category';
		}

		return 'cmplz_script_class';
	}
}
