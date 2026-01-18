<?php

namespace SPC\Modules;

use SPC\Constants;
use SPC\Services\Settings_Store;
use SPC\Entities\Google_Font;
use SPC\Utils\Helpers;

class Font_Optimizer implements Module_Interface {

	/**
	 * Whether to load Google Fonts locally.
	 *
	 * @var boolean
	 */
	private $load_locally;

	/**
	 * Whether to optimize Google Fonts.
	 *
	 * @var boolean
	 */
	private $optimize;

	/**
	 * Google Fonts URLs to be optimized
	 *
	 * @var Google_Font[]
	 */
	private $fonts = [];

	/**
	 * Settings store instance
	 *
	 * @var Settings_Store
	 */
	private $settings_store;

	/**
	 * Font_Optimizer constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->settings_store = Settings_Store::get_instance();

		$this->load_locally = $this->settings_store->get( Constants::SETTING_LOCAL_GOOGLE_FONTS );
		$this->optimize     = $this->settings_store->get( Constants::SETTING_OPTIMIZE_GOOGLE_FONTS );
	}

	/**
	 * Initialize the module
	 *
	 * @return void
	 */
	public function init() {
		if ( $this->load_locally ) {
			$this->init_local_font_loading();
		}

		if ( $this->optimize ) {
			$this->init_optimization();
		}
	}

	/**
	 * Initialize optimization
	 *
	 * @return void
	 */
	private function init_optimization() {
		add_filter( 'style_loader_src', [ $this, 'capture_google_fonts' ], 10, 2 );
		add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_google_fonts' ], 100 );
		add_action( 'wp_head', [ $this, 'add_preconnect_hints' ], PHP_INT_MAX );
		add_action( 'wp_head', [ $this, 'output_optimized_fonts' ], 15 );
	}

	/**
	 * Initialize local font loading
	 *
	 * @return void
	 */
	private function init_local_font_loading() {
		$this->setup_wptt_filters();
		$this->load_webfont_loader();

		// Only add these hooks if optimization is disabled.
		// Otherwise, it's handled by the optimization process.
		if ( ! $this->optimize ) {
			add_filter( 'style_loader_src', [ $this, 'load_google_fonts_locally' ], 10, 2 );
			add_action( 'wp_enqueue_scripts', [ $this, 'process_local_fonts' ], PHP_INT_MAX );
		}
	}

	/**
	 * Capture Google Fonts URLs before they're outputted
	 *
	 * @param string $src Style source URL
	 * @param string $handle Style handle
	 * @return string|false
	 */
	public function capture_google_fonts( $src, $handle ) {
		if ( ! Google_Font::is_google_font_url( $src ) ) {
			return $src;
		}

		$this->fonts[] = new Google_Font( $src );

		return false;
	}

	/**
	 * Dequeue Google Fonts to prevent duplicate loading
	 *
	 * @return void
	 */
	public function dequeue_google_fonts() {
		global $wp_styles;

		if ( ! is_object( $wp_styles ) ) {
			return;
		}

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! Google_Font::is_google_font_url( $style->src ) ) {
				return;
			}

			wp_dequeue_style( $handle );
			$this->fonts[] = new Google_Font( $style->src );
		}
	}

	/**
	 * Add preconnect hints for Google Fonts
	 *
	 * @return void
	 */
	public function add_preconnect_hints() {
		if ( $this->load_locally ) {
			return;
		}

		if ( empty( $this->fonts ) ) {
			return;
		}

		echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	}

	/**
	 * Output optimized Google Fonts
	 *
	 * @return void
	 */
	public function output_optimized_fonts() {
		if ( ! $this->optimize ) {
			return;
		}

		if ( empty( $this->fonts ) ) {
			return;
		}

		$combined_fonts = $this->get_combined_fonts();

		if ( empty( $combined_fonts ) ) {
			return;
		}

		foreach ( $combined_fonts as $url ) {
			// If loading locally, convert URL to local version
			if ( $this->load_locally && $this->can_load_locally() ) {
				$url = wptt_get_webfont_url( $url );
			}

			echo '<link rel="preload" href="' . esc_url( $url ) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" crossorigin>' . "\n";
			echo '<noscript><link rel="stylesheet" href="' . ( $url ) . '" crossorigin></noscript>' . "\n";
		}
	}

	/**
	 * Get combined Google Fonts URLs.
	 *
	 * @return array Combined font URLs.
	 */
	private function get_combined_fonts() {
		$api_v1_fonts = [];
		$api_v2_fonts = [];

		foreach ( $this->fonts as $font ) {
			if ( $font->get_api_version() === Google_Font::API_V2 ) {
				$api_v2_fonts = array_merge( $api_v2_fonts, $font->get_families() );
			} else {
				$api_v1_fonts = array_merge( $api_v1_fonts, $font->get_families() );
			}
		}

		$combined_urls = [];

		if ( ! empty( $api_v1_fonts ) ) {
			$combined_urls[] = add_query_arg(
				[
					'family'  => implode( '|', $api_v1_fonts ),
					'display' => 'swap',
				],
				'https://fonts.googleapis.com/css'
			);
		}

		if ( ! empty( $api_v2_fonts ) ) {
			$combined_urls[] = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $api_v2_fonts ) . '&display=swap';
		}

		return $combined_urls;
	}

	/**
	 * Convert Google Fonts URLs to local URLs
	 *
	 * @param string $src Style source URL
	 * @param string $handle Style handle
	 * @return string
	 */
	public function load_google_fonts_locally( $src, $handle ) {
		if ( ! Google_Font::is_google_font_url( $src ) ) {

			return $src;
		}

		if ( ! $this->can_load_locally() ) {
			return $src;
		}

		return wptt_get_webfont_url( $src );
	}

	/**
	 * Process fonts for local loading when not using optimization
	 *
	 * This method only runs when optimization is disabled.
	 * When optimization is enabled, output_optimized_fonts() handles everything.
	 *
	 * @return void
	 */
	public function process_local_fonts() {
		if ( $this->optimize ) {
			return;
		}

		if ( ! $this->can_load_locally() ) {
			return;
		}

		global $wp_styles;

		if ( ! is_object( $wp_styles ) ) {
			return;
		}

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( ! Google_Font::is_google_font_url( $style->src ) ) {
				continue;
			}

			// Handle protocol-relative URLs.
			if ( strpos( $style->src, '//' ) === 0 ) {
				$style->src = 'https:' . $style->src;
			}

			$wp_styles->registered[ $handle ]->src = wptt_get_webfont_url( $style->src );
		}
	}


	/**
	 * Check if WPTT WebFont Loader is available
	 *
	 * @return bool
	 */
	private function can_load_locally() {
		return function_exists( 'wptt_get_webfont_url' );
	}

	/**
	 * Setup filters for WebFontLoader.
	 *
	 * @return void
	 */
	private function setup_wptt_filters() {
		// Set custom font storage path
		add_filter(
			'wptt_get_local_fonts_base_path',
			function ( $path ) {
				return Helpers::get_plugin_content_dir();
			}
		);

		// Set custom font storage URL
		add_filter(
			'wptt_get_local_fonts_base_url',
			function ( $url ) {
				return Helpers::get_plugin_content_dir_url();
			}
		);

		// Set custom subfolder name
		add_filter(
			'wptt_get_local_fonts_subfolder_name',
			function ( $subfolder_name ) {
				return 'fonts';
			}
		);
	}

	/**
	 * Load WPTT_WebFont_Loader.
	 *
	 * @return void
	 */
	private function load_webfont_loader() {
		if ( class_exists( 'WPTT_WebFont_Loader' ) ) {
			return;
		}

		require_once SWCFPC_PLUGIN_PATH . 'vendor/wptt/webfont-loader/wptt-webfont-loader.php';
	}
}
