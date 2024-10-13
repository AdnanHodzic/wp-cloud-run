<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\SitemapGenerator;

class MultilingualSitemap extends Sitemap {
	public static $template = 'multilingual-sitemap';

	public function show_sitemap( $template, $is_xml = true, $inner_sitemap = null, $current_page = null ) {
		remove_all_filters( 'pre_get_posts' );

		$sitemap = new SitemapGenerator( sgg_get_home_url() );

		$this->collect_urls( $template, $inner_sitemap, $current_page );

		try {
			$sitemap->createMultilingualSitemap( $this->urls );
		} catch ( \Exception $exc ) {
			echo $exc->getTraceAsString(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		try {
			$sitemap->outputSitemap( $template, $is_xml, $inner_sitemap );
		} catch ( \Exception $exc ) {
			echo $exc->getTraceAsString(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Collect Sitemap URLs
	 */
	public function collect_urls( $template = 'sitemap', $inner_sitemap = null, $current_page = null ) {
		$this->urls[] = sgg_get_sitemap_url( $this->settings->sitemap_url, 'sitemap_xml' );

		// Polylang, TranslatePress
		$languages = sgg_get_languages();
		if ( ! empty( $languages ) ) {
			foreach ( $languages as $language ) {
				$this->urls[] = sgg_get_sitemap_url( "{$language}/{$this->settings->sitemap_url}", 'sitemap_xml' );
			}
		}

		// WPML
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$wpml_languages = apply_filters( 'wpml_active_languages', array() );
			foreach ( $wpml_languages as $language ) {
				if ( apply_filters( 'wpml_default_language', null ) === $language['code'] ) {
					continue;
				}

				$this->urls[] = esc_url( "{$language['url']}{$this->settings->sitemap_url}" );
			}
		}

	}

}
