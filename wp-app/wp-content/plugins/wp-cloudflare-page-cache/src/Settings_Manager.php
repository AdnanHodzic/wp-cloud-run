<?php

namespace SPC;

class Settings_Manager implements Module_Interface {
	private const BASE_FIELDS = [
		Constants::SETTING_NATIVE_LAZY_LOADING    => [
			'type'       => 'bool',
			'bust_cache' => true,
			'default'    => 1,
		],
		Constants::SETTING_LAZY_LOADING           => [
			'type'       => 'bool',
			'bust_cache' => true,
			'default'    => 0,
		],
		Constants::SETTING_LAZY_LOAD_VIDEO_IFRAME => [
			'type'       => 'bool',
			'bust_cache' => true,
			'default'    => 0,
		],
		Constants::SETTING_LAZY_LOAD_SKIP_IMAGES  => [
			'type'       => 'int',
			'bust_cache' => true,
			'default'    => 0,
		],
		Constants::SETTING_LAZY_EXCLUDED          => [
			'type'       => 'textarea',
			'bust_cache' => true,
			'default'    => [],
		],
		Constants::SETTING_LAZY_LOAD_BG           => [
			'type'       => 'bool',
			'bust_cache' => true,
			'default'    => 0,
		],
		Constants::SETTING_LAZY_LOAD_BG_SELECTORS => [
			'type'       => 'textarea',
			'bust_cache' => true,
			'default'    => [],
		],
	];

	private const ALLOWED_FIELD_TYPES = [ 'type', 'bust_cache', 'default' ];

	public function init() {
		add_action( 'swcfpc_after_settings_update', [ $this, 'update_additional_settings' ] );
		add_filter( 'swcfpc_main_config_defaults', [ $this, 'alter_default_config' ] );
	}

	/**
	 * Save additional settings.
	 *
	 * @param array $post_data $_POST data from backend settings page.
	 *
	 * @return void
	 */
	public function update_additional_settings( $post_data ) {
		if ( ! is_array( $post_data ) ) {
			return $post_data;
		}

		global $sw_cloudflare_pagecache;

		$fields_to_update = array_filter(
			$this->get_fields(),
			function ( $args, $key ) use ( $post_data ) {
				return isset( $post_data[ 'swcfpc_' . $key ] );
			},
			ARRAY_FILTER_USE_BOTH
		);

		if ( empty( $fields_to_update ) ) {
			return;
		}

		$previous_settings = $sw_cloudflare_pagecache->get_config();
		$will_bust_cache   = false;

		foreach ( $fields_to_update as $key => $args ) {
			$value = $post_data[ 'swcfpc_' . $key ];

			switch ( $args['type'] ) {
				case 'bool':
				case 'int':
					$sw_cloudflare_pagecache->set_single_config( $key, (int) $value );
					break;

				case 'textarea':
					$value = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", (string) $value ) ) ) );
					$sw_cloudflare_pagecache->set_single_config( $key, count( $value ) > 0 ? $value : $args['default'] );
					break;
				default:
					break;
			}

			if (
				$args['bust_cache'] === true &&
				isset( $previous_settings[ $key ] ) &&
				$previous_settings[ $key ] !== $value
			) {
				$will_bust_cache = true;
			}
		}

		if ( ! $will_bust_cache ) {
			return;
		}

		$sw_cloudflare_pagecache->get_modules()['cache_controller']->purge_all( false, false, true );
	}

	/**
	 * Alter settings default config
	 *
	 * @param array $config Default config.
	 *
	 * @return array
	 */
	public function alter_default_config( $config ) {
		return is_array( $config ) ? array_merge( $config, $this->get_fields( [], 'default' ) ) : $config;
	}

	/**
	 * Get fields by type.
	 *
	 * @param array $filters Filters to match.
	 * @param string $remapped_property Property to map to value.
	 *
	 * @return array
	 */
	public function get_fields( $filters = [], $remapped_property = null ) {
		$fields = apply_filters( 'spc_additional_settings_fields', self::BASE_FIELDS );

		// Use WordPress core filter to determine if lazy loading is enabled by default.
		$fields[ Constants::SETTING_NATIVE_LAZY_LOADING ]['default'] = apply_filters( 'wp_lazy_loading_enabled', true, 'img', 'spc_default_native_lazyloading' );

		if ( is_array( $filters ) && ! empty( $filters ) ) {
			$fields = wp_list_filter( $fields, $filters );
		}

		if ( is_string( $remapped_property ) && in_array( $remapped_property, self::ALLOWED_FIELD_TYPES, true ) ) {
			$fields = wp_list_pluck( $fields, $remapped_property );
		}

		return $fields;
	}

	/**
	 * Get the default for a field.
	 *
	 * @param string $field field ID.
	 * @param mixed $fallback fallback default, if the field is not defined.
	 *
	 * @return mixed
	 */
	public static function get_default_for_field( $field, $fallback ) {
		$fields = self::BASE_FIELDS;

		if ( ! isset( $fields[ $field ], $fields[ $field ]['default'] ) ) {
			return $fallback;
		}

		return $fields[ $field ]['default'];
	}
}
