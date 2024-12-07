<?php

namespace SPC\Views\Functions;

use SPC\Loader;
use SPC\Modules\Admin;
use SPC\Modules\Settings_Manager;

function render_description( $text, $highlighted = false, $hide = false, $disable_top_spacing = false, $as_section = false ) {
	if ( $hide ) {
		return;
	}

	$classes = $as_section ? 'description_section' : 'description';

	if ( $highlighted ) {
		$classes .= ' highlighted';
	}

	if ( ! $disable_top_spacing ) {
		echo '<br/>';
	}

	echo '<div class="' . esc_attr( $classes ) . '">' . wp_kses_post( $text ) . '</div>';
}

function render_description_section( $text, $highlighted = true, $disable_top_spacing = true ) {
	render_description( $text, $highlighted, false, $disable_top_spacing, true );
}

function render_cache_disable_description() {
	render_description_section( __( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ) );
}

function get_header_pill( $type ) {
	$pills = [
		'update-wp' => '<span class="swcfpc_plugin_inactive">' . __( 'Update WordPress', 'wp-cloudflare-page-cache' ) . '</span>',
	];

	return array_key_exists( $type, $pills ) ? $pills[ $type ] : '';
}

function render_pro_tag( $utm_campaign = 'pro-tag' ) {
	echo sprintf(
		'<a href="%s" target="_blank" class="spc-pro-tag"><span class="dashicons dashicons-lock"></span><span>%s</span></a>',
		esc_url( tsdk_utmify( 'https://themeisle.com/plugins/super-page-cache-pro', $utm_campaign ) ),
		__( 'Pro', 'wp-cloudflare-page-cache' )
	);
}

function render_header( $text, $first = false, $pill = '', $additional_classes = '' ) {
	$classes = 'main_section_header ' . $additional_classes;

	if ( $first ) {
		$classes .= ' first_section';
	}

	echo '<div class="' . esc_attr( $classes ) . '">';
	echo '<h3>';
	echo esc_html( $text );

	if ( ! empty( $pill ) ) {
		echo wp_kses_post( get_header_pill( $pill ) );
	}

	echo '</h3>';
	echo '</div>';
}

function render_update_wordpress_notice() {
	/* translators: %s: link to WordPress update page - 'update now' */
	render_description(
		sprintf(
			__( 'This feature requires WordPress 6.2 or higher. %s to access all features.', 'wp-cloudflare-page-cache' ),
			'<a href="' . esc_url( admin_url( 'update-core.php' ) ) . '" target="_blank">' . __( 'Update now', 'wp-cloudflare-page-cache' ) . '</a>',
		),
		true,
		Loader::can_process_html()
	);
}

function render_switch( $setting_id, $fallback_default = 0, $conditional = '', $disabled = false, $use_enabled_disabled_lables = false, $override_value = null ) {
	global $sw_cloudflare_pagecache;

	$config_value = $override_value ? $override_value : (int) $sw_cloudflare_pagecache->get_single_config( $setting_id, Settings_Manager::get_default_for_field( $setting_id, $fallback_default ) );

	$inputs = [
		[
			'label'  => $use_enabled_disabled_lables ? __( 'Enabled', 'wp-cloudflare-page-cache' ) : __( 'Yes', 'wp-cloudflare-page-cache' ),
			'value'  => 1,
			'suffix' => 'left',
		],
		[
			'label'  => $use_enabled_disabled_lables ? __( 'Disabled', 'wp-cloudflare-page-cache' ) : __( 'No', 'wp-cloudflare-page-cache' ),
			'value'  => 0,
			'suffix' => 'right',
		],
	];

	echo '<div class="switch-field">';

	foreach ( $inputs as $input ) {
		$id    = "{$setting_id}_{$input['suffix']}";
		$attrs = [
			'type'  => 'radio',
			'name'  => "swcfpc_{$setting_id}",
			'id'    => $id,
			'value' => $input['value'],
			'class' => '',
		];

		if ( $conditional ) {
			$attrs['class']          .= ' conditional_item';
			$attrs['data-mainoption'] = $conditional;
		}

		if ( $disabled ) {
			$attrs['class'] .= ' disabled';
		}

		echo '<input ' . attributes_array_to_string( $attrs ) . ' ' . checked( $config_value, $input['value'], false ) . ' />';
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $input['label'] ) . '</label>';
	}
	echo '</div>';
}

function render_textarea( $setting_id, $placeholder = '', $fallback_default = [], $disabled = false ) {
	global $sw_cloudflare_pagecache;

	$values = $sw_cloudflare_pagecache->get_single_config( $setting_id, Settings_Manager::get_default_for_field( $setting_id, $fallback_default ) );

	$value = is_array( $values ) && count( $values ) > 0 ? implode( "\n", $values ) : '';

	$attrs = [
		'name'        => 'swcfpc_' . $setting_id,
		'placeholder' => $placeholder,
	];

	if ( $disabled ) {
		$attrs['disabled'] = true;
	}

	echo '<textarea ' . attributes_array_to_string( $attrs ) . '>';
	echo esc_attr( $value );
	echo '</textarea>';
}

function render_checkbox( $setting_id, $label = '', $recommended = false, $fallback_default = 0 ) {
	global $sw_cloudflare_pagecache;

	$saved_value = $sw_cloudflare_pagecache->get_single_config( $setting_id, Settings_Manager::get_default_for_field( $setting_id, $fallback_default ) );

	$attrs = [
		'name'  => 'swcfpc_' . $setting_id,
		'id'    => $setting_id,
		'type'  => 'checkbox',
		'value' => 1,
	];

	echo '<div>';

	// Hack because otherwise when unchecked, value will not exist.
	echo '<input type="hidden" name="swcfpc_' . $setting_id . '" value="0" />';
	echo '<input ' . attributes_array_to_string( $attrs ) . ' ' . checked( 1, $saved_value, false ) . ' />';

	if ( ! empty( $label ) ) {

		echo '<label for="' . esc_attr( $setting_id ) . '">';
		echo ' ' . wp_kses_post( $label );

		if ( $recommended ) {
			echo ' - <strong>' . __( '(recommended)', 'wp-cloudflare-page-cache' ) . '</strong>';
		}

		echo '</label>';
	}
	echo '</div>';
}

function render_number_field( $setting_id, $fallback_default = 0, $input_attrs = [] ) {
	global $sw_cloudflare_pagecache;

	$defaults = [
		'type'  => 'number',
		'step'  => 1,
		'min'   => 0,
		'name'  => "swcfpc_{$setting_id}",
		'value' => (int) $sw_cloudflare_pagecache->get_single_config( $setting_id, Settings_Manager::get_default_for_field( $setting_id, $fallback_default ) ),
	];

	$attrs = is_array( $input_attrs ) ? wp_parse_args( $input_attrs, $defaults ) : $defaults;

	echo '<input ' . attributes_array_to_string( $attrs ) . ' />';
}

function render_text_field( $setting_id, $fallback_default = '', $input_attrs = [] ) {
	global $sw_cloudflare_pagecache;

	$defaults = [
		'type'  => 'text',
		'name'  => "swcfpc_{$setting_id}",
		'value' => $sw_cloudflare_pagecache->get_single_config( $setting_id, Settings_Manager::get_default_for_field( $setting_id, $fallback_default ) ),
	];

	$attrs = is_array( $input_attrs ) ? wp_parse_args( $input_attrs, $defaults ) : $defaults;

	echo '<input ' . attributes_array_to_string( $attrs ) . ' />';
}

function render_dummy_switch( $id, $default = 0 ) {
	render_switch( 'dummy_switch_' . $id, $default, false, true );
}

function render_dummy_textarea( $id, $placeholder = '' ) {
	render_textarea( 'dummy_textarea_' . $id, $placeholder, '', true );
}

/**
 * Convert an array of attributes to a string to be used in HTML tags.
 *
 * @param array $attrs The attributes to convert to string.
 *
 * @return string
 */
function attributes_array_to_string( $attrs ) {
	$attributes = array_map(
		function ( $key, $value ) {
			return sprintf( '%s="%s"', esc_attr( $key ), esc_attr( $value ) );
		},
		array_keys( $attrs ),
		$attrs
	);

	return implode( ' ', $attributes );
}


/**
 * Load a view file.
 *
 * @param string $view The view file to load.
 * @param string $id The view ID to be used if the view is filtered later.
 */
function load_view( $view, $id = '' ) {
	if ( empty( $id ) ) {
		$id = $view;
	}

	$default_template = sprintf( '%s/src/views/%s.php', untrailingslashit( SWCFPC_PLUGIN_PATH ), $view );
	$template         = apply_filters( 'swcfpc_admin_tab_view_path', $default_template, $id );

	$template = is_file( $template ) ? $template : $default_template;

	if ( ! is_file( $template ) ) {
		return;
	}

	include_once $template;
}
