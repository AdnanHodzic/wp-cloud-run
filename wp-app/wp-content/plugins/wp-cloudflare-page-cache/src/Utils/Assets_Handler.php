<?php

namespace SPC\Utils;

class Assets_Handler {
	/**
	 * Enqueue style.
	 *
	 * @param string   $handle Name of the style.
	 * @param string   $file Path to the style file.
	 * @param string[] $deps Array of style handles this style depends on.
	 * @param string   $inline_style Optional inline CSS to add.
	 * @return void
	 */
	public static function enqueue_style( string $handle, string $file, array $deps = array(), string $inline_style = '' ) {
		$uri = SWCFPC_PLUGIN_URL . 'assets/build/' . $file . '.css';

		wp_register_style( $handle, esc_url( $uri ), $deps, SWCFPC_VERSION );
		wp_style_add_data( $handle, 'rtl', 'replace' );

		$inline_style = apply_filters( $handle . '_inline_style', $inline_style, $handle );

		if ( ! empty( $inline_style ) ) {
			wp_add_inline_style( $handle, $inline_style );
		}

		wp_enqueue_style( $handle );
	}

	/**
	 * Get style URL.
	 *
	 * @param string $handle Name of the style.
	 * @return string
	 */
	public static function get_style_url( string $handle ): string {
		return SWCFPC_PLUGIN_URL . 'assets/build/' . $handle . '.css';
	}

	/**
	 * Enqueue script.
	 *
	 * @param string   $handle Name of the script.
	 * @param string   $file Path to the script file.
	 * @param string[] $dependencies Array of other script handles this script depends on.
	 * @param array    $i18n_data Array of data to pass to the script.
	 * @param string   $i18n_object Name of the object to create in the global scope.
	 * @return void
	 */
	public static function enqueue_script( string $handle, string $file, array $dependencies = [], array $i18n_data = [], string $i18n_object = 'SPCDash' ) {
		$uri = SWCFPC_PLUGIN_URL . 'assets/build/' . $file . '.js';
		$php = SWCFPC_PLUGIN_PATH . '/assets/build/' . $file . '.asset.php';

		$deps = is_file( $php ) ? include $php : [
			'version'      => SWCFPC_VERSION,
			'dependencies' => [],
		];

		if ( ! empty( $dependencies ) ) {
			$deps['dependencies'] = array_merge( $deps['dependencies'], $dependencies );
		}

		wp_register_script( $handle, esc_url( $uri ), $deps['dependencies'], $deps['version'], true );

		if ( ! empty( $i18n_data ) ) {
			wp_localize_script( $handle, $i18n_object, $i18n_data );
		}

		wp_enqueue_script( $handle );
		wp_set_script_translations( $handle, 'wp-cloudflare-page-cache' );
	}

	/**
	 * Get image URL from assets folder.
	 *
	 * @param string $file Filename with extension.
	 * @return string
	 */
	public static function get_image_url( string $file ): string {
		return SWCFPC_PLUGIN_URL . 'assets/img/' . $file;
	}
}
