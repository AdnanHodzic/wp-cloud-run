<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '_WP_Editors' ) ) {
	require ABSPATH . WPINC . '/class-wp-editor.php';
}

/**
 * Handle localization of the shortcode in the classic editor.
 *
 * @return string
 */
function advads_shortcode_creator_l10n() {
	$strings    = [
		'title'  => _x( 'Add an ad', 'shortcode creator', 'advanced-ads' ),
		'ok'     => _x( 'Add shortcode', 'shortcode creator', 'advanced-ads' ),
		'cancel' => _x( 'Cancel', 'shortcode creator', 'advanced-ads' ),
		'image'  => ADVADS_BASE_URL . 'admin/assets/img/tinymce-icon.png',
	];
	$locale     = _WP_Editors::get_mce_locale();
	$translated = 'tinyMCE.addI18n("' . $locale . '.advads_shortcode", ' . wp_json_encode( $strings ) . ");\n";

	return $translated;
}

$strings = advads_shortcode_creator_l10n();
