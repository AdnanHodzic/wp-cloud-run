<?php

function sgg_pro_enabled() {
	return defined( 'SGG_PRO_VERSION' );
}

function sgg_get_pro_url( $utm = 'buy-now' ) {
	return "https://wpgrim.com/google-xml-sitemaps-generator-pro/?utm_source=sgg-plugin&utm_medium={$utm}&utm_campaign=xml_sitemap";
}

function sgg_get_support_url() {
	return 'https://wordpress.org/support/plugin/xml-sitemap-generator-for-google/';
}

function sgg_get_review_url() {
	return sgg_get_support_url() . 'reviews/?filter=5#new-post';
}

function sgg_show_pro_badge() {
	if ( ! sgg_pro_enabled() ) {
		load_template( GRIM_SG_PATH . '/templates/partials/pro-badge.php', false );
	}
}

function sgg_show_pro_overlay( $args = array() ) {
	if ( ! sgg_pro_enabled() ) {
		load_template( GRIM_SG_PATH . '/templates/partials/pro-overlay.php', false, $args );
	}
}

function sgg_pro_class() {
	return sgg_pro_enabled() ? 'active' : 'inactive';
}

function sgg_parse_language( $lang ) {
	$lang = str_replace( '_', '-', convert_chars( strtolower( strip_tags( $lang ) ) ) );

	if ( 0 === strpos( $lang, 'zh' ) ) {
		$lang = strpos( $lang, 'hk' ) || strpos( $lang, 'hant' ) || strpos( $lang, 'tw' ) ? 'zh-tw' : 'zh-cn';
	} else {
		$explode = explode( '-', $lang );
		$lang    = $explode[0];
	}

	return ! empty( $lang ) ? $lang : 'en';
}

function sgg_is_sitemap_index( $template, $settings = null ) {
	if ( ! $settings ) {
		$settings = ( new \GRIM_SG\Vendor\Controller() )->get_settings();
	}

	return in_array( $template, array( 'sitemap', 'inner-sitemap' ), true ) && ! empty( $settings->sitemap_view );
}

function sgg_get_home_url( $path = '' ) {
	$home_url = function_exists( 'pll_home_url' ) ? pll_home_url() : get_home_url();
	$home_url = trim( apply_filters( 'wpml_home_urls', $home_url ), '/' );

	if ( function_exists( 'trp_get_locale' ) ) {
		$trp_settings   = get_option( 'trp_settings' );
		$current_locale = trp_get_locale();

		if ( $current_locale !== $trp_settings['default-language'] ?? null ) {
			$lang     = substr( $current_locale, 0, 2 );
			$home_url = get_site_url( null, $lang );
		}
	}

	if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! empty( $path ) && ! empty( $_GET['lang'] ) && false !== strpos( $home_url, '?lang=' ) ) {
		$home_url = trim( strtok( $home_url, '?' ), '/' );
		$home_url = add_query_arg( 'lang', sanitize_text_field( $_GET['lang'] ), "{$home_url}/{$path}" );
		$path     = ''; // Reset path to avoid duplication
	}

	$home_url = apply_filters( 'xml_sitemaps_site_url', $home_url );

	return ! empty( $path ) ? "{$home_url}/{$path}" : $home_url;
}

function sgg_is_nginx() {
	return isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ), 'nginx' ) !== false;
}

function sgg_is_using_mod_rewrite() {
	global $wp_rewrite;

	return $wp_rewrite->using_mod_rewrite_permalinks();
}

function sgg_get_sitemap_url( $sitemap_url, $sitemap_type, $suffix = true ) {
	if ( sgg_is_using_mod_rewrite() ) {
		return sgg_get_home_url( $sitemap_url );
	} else {
		return sgg_get_home_url( "index.php?{$sitemap_type}" . ( $suffix ? '=true' : '' ) );
	}
}

function sgg_get_languages() {
	$languages = array();

	if ( function_exists( 'pll_languages_list' ) ) {
		$languages    = pll_languages_list( array( 'fields' => 'slug' ) );
		$default_lang = array_search( pll_default_language(), $languages, true );

		if ( false !== $default_lang ) {
			unset( $languages[ $default_lang ] );
		}
	}

	if ( function_exists( 'trp_get_languages' ) ) {
		$trp_settings = get_option( 'trp_settings' );
		$trp_slugs    = $trp_settings['url-slugs'] ?? array();

		unset( $trp_slugs[ $trp_settings['default-language'] ?? '' ] );

		$languages = array_values( $trp_slugs );
	}

	return $languages;
}

function sgg_is_multilingual() {
	return function_exists( 'pll_languages_list' )
		|| function_exists( 'trp_get_languages' )
		|| defined( 'ICL_SITEPRESS_VERSION' );
}
