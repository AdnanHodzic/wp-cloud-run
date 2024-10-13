<?php

namespace SPC\Views\Functions;

use SPC\Loader;
use SPC\Settings_Manager;

function render_description( $text, $highlighted = false, $hide = false ) {
	if ( $hide ) {
		return;
	}

	$classes = 'description';

	if ( $highlighted ) {
		$classes .= ' highlighted';
	}

	echo '<br/>';
	echo '<div class="' . esc_attr( $classes ) . '">' . wp_kses_post( $text ) . '</div>';
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

function render_header( $text, $first = false, $pill = '' ) {
	$classes = 'main_section_header';

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

function render_switch( $setting_id, $fallback_default = 0, $conditional = '', $disabled = false ) {
	global $sw_cloudflare_pagecache;

	$config_value = (int) $sw_cloudflare_pagecache->get_single_config( $setting_id, Settings_Manager::get_default_for_field( $setting_id, $fallback_default ) );

	$inputs = [
		[
			'label'  => __( 'Yes', 'wp-cloudflare-page-cache' ),
			'value'  => 1,
			'suffix' => 'left',
		],
		[
			'label'  => __( 'No', 'wp-cloudflare-page-cache' ),
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

function render_number_field( $setting_id, $fallback_default = 0, $input_attrs = [] ) {
	 global $sw_cloudflare_pagecache;

	$defaults = [
		'type'  => 'number',
		'step'  => 1,
		'min'   => 0,
		'max'   => 100,
		'name'  => "swcfpc_{$setting_id}",
		'value' => (int) $sw_cloudflare_pagecache->get_single_config( $setting_id, Settings_Manager::get_default_for_field( $setting_id, $fallback_default ) ),
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
