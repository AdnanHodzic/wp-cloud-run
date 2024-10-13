<?php

/**
 * Handles Advanced Ads Inline CSS settings.
 */
class Advanced_Ads_Inline_Css {
	/**
	 *  Holds the state if inline css should be output or not.
	 *
	 * @var bool
	 */
	protected $add_inline_css;

	/**
	 * Initialize the module.
	 */
	public function __construct() {

		/**
		 * Filters the state if inline css should be output or not.
		 * Ajax CB container could have added inline css already.
		 *
		 * Set to false if an addon output inline css before the main plugin.
		 *
		 * @param bool Contains the state.
		 */
		$this->add_inline_css = apply_filters( 'advanced-ads-output-inline-css', true );
		if ( ! $this->add_inline_css ) {
			return;
		}

		// Add inline css to the tcf container.
		$this->check_tcf_option();
	}

	/**
	 * Adds inline css.
	 *
	 * @param array     $wrapper       Add wrapper array.
	 * @param string    $css           Custom inline css.
	 * @param bool|null $global_output Whether this ad is using cache-busting.
	 *
	 * @return array
	 */
	public function add_css( $wrapper, $css, $global_output ) {
		$this->add_inline_css = $this->add_inline_css && $global_output !== false;
		if ( ! $this->add_inline_css ) {
			return $wrapper;
		}

		$styles               = $this->get_styles_by_string( $css );
		$wrapper['style']     = empty( $wrapper['style'] ) ? $styles : array_merge( $wrapper['style'], $styles );
		$this->add_inline_css = false;

		return $wrapper;
	}

	/**
	 * Extend TCF output with a container containing inline css.
	 *
	 * @param string          $output The output string.
	 * @param Advanced_Ads_Ad $ad     The ad object.
	 *
	 * @return string
	 */
	public function add_tcf_container( $output, Advanced_Ads_Ad $ad ) {
		$inline_css = $ad->options( 'inline-css' );

		if (
			! $ad->global_output
			|| empty( $inline_css )
			|| strpos( $output, '<div class="tcf-container"' ) === 0
		) {
			return $output;
		}

		return sprintf(
			'<div class="tcf-container" style="' . $inline_css . '">%s</div>',
			$output
		);
	}

	/**
	 * Reformat css styles string to array.
	 *
	 * @param string $string CSS-Style.
	 *
	 * @return array
	 */
	private function get_styles_by_string( string $string ): array {
		$chunks = array_chunk( preg_split( '/[:;]/', $string ), 2 );
		array_walk_recursive( $chunks, function( &$value ) {
			$value = trim( $value );
		} );

		$keys   = array_filter( array_column( $chunks, 0 ) );
		$values = array_filter( array_column( $chunks, 1 ) );

		$length = min( count( $keys ), count( $values ) );

		return array_combine( array_slice( $keys, 0, $length ), array_slice( $values, 0, $length ) );
	}

	/**
	 * If TCF is active, i.e. there is a TCF container, add the options to this container.
	 */
	private function check_tcf_option() {
		static $privacy_options;
		if ( $privacy_options === null ) {
			$privacy_options = Advanced_Ads_Privacy::get_instance()->options();
		}

		if ( ! empty( $privacy_options['enabled'] ) && $privacy_options['enabled'] === 'on' && $privacy_options['consent-method'] === 'iab_tcf_20' ) {
			add_filter( 'advanced-ads-output-final', [ $this, 'add_tcf_container' ], 20, 2 );
			$this->add_inline_css = false;
		}
	}
}
