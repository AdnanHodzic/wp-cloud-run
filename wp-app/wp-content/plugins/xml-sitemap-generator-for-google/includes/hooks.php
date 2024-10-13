<?php
/**
 * Plugin Activation Hook
 */
function sgg_activation() {
	\GRIM_SG\Frontend::activate_plugin();

	\GRIM_SG\Vendor\Migration::update_version();

	( new \GRIM_SG\IndexNow() )->set_api_key();

	update_option( 'sgg_installation_time', time(), false );

	// Set the activation redirect transient.
	set_transient( 'sgg_activation_redirect', true, MINUTE_IN_SECONDS );
}

/**
 * Plugin Deactivation Hook
 */
function sgg_deactivation() {
	\GRIM_SG\IndexNow::delete_api_key();

	delete_option( 'sgg_installation_time' );
}

/**
 * Get Polylang language for a post.
 */
function sgg_polylang_post_language( $language, $post_id ) {
	if ( function_exists( 'pll_get_post_language' ) ) {
		$language = pll_get_post_language( $post_id, 'slug' );
	}

	return $language;
}
add_filter( 'xml_sitemap_news_language', 'sgg_polylang_post_language', 10, 2 );

/**
 * Get the WPML language for a post.
 */
function sgg_wpml_post_language( $language, $post_id, $post_type = 'post' ) {
	global $sitepress;

	if ( $sitepress ) {
		$language = apply_filters(
			'wpml_element_language_code',
			$language,
			array(
				'element_id'   => $post_id,
				'element_type' => $post_type,
			)
		);
	}

	return $language;
}
add_filter( 'xml_sitemap_news_language', 'sgg_wpml_post_language', 10, 3 );

/**
 * Exclude posts with noindex from the sitemap.
 */
function sgg_exclude_noindex_posts( $value, $post_id ) {
	// Yoast SEO noindex
	if ( defined( 'WPSEO_VERSION' ) && '1' === get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true ) ) {
		return false;
	}

	// Rank Math noindex
	if ( class_exists( 'RankMath' ) ) {
		$rank_math_robots = get_post_meta( $post_id, 'rank_math_robots', true );
		if ( ! empty( $rank_math_robots ) && is_array( $rank_math_robots ) && in_array( 'noindex', $rank_math_robots, true ) ) {
			return false;
		}
	}

	return $value;
}
add_filter( 'xml_sitemap_include_post', 'sgg_exclude_noindex_posts', 99, 2 );

/**
 * Exclude terms with noindex from the sitemap.
 */
function sgg_exclude_noindex_terms( $value, $term_id, $taxonomy ) {
	// Yoast SEO noindex
	if ( is_callable( '\WPSEO_Taxonomy_Meta::get_term_meta' ) ) {
		$noindex = \WPSEO_Taxonomy_Meta::get_term_meta( $term_id, $taxonomy, 'noindex' );
		if ( 'noindex' === $noindex ) {
			return true;
		}
	}

	// Rank Math noindex
	if ( class_exists( 'RankMath' ) ) {
		$rank_math_robots = get_term_meta( $term_id, 'rank_math_robots', true );
		if ( ! empty( $rank_math_robots ) && is_array( $rank_math_robots ) && in_array( 'noindex', $rank_math_robots, true ) ) {
			return true;
		}
	}

	return $value;
}
add_filter( 'sgg_sitemap_exclude_single_term', 'sgg_exclude_noindex_terms', 99, 3 );

/**
 * Add FooGallery image URLs to the sitemap.
 */
function sgg_add_foogallery_image_urls( $urls, $post_id ) {
	if ( defined( 'FOOGALLERY_CPT_GALLERY' ) && class_exists( 'FooGallery' ) ) {
		$content = get_post_field( 'post_content', $post_id );

		if ( false !== strpos( $content, FOOGALLERY_CPT_GALLERY ) ) {
			$gallery_ids = array();

			preg_match_all( '/<!--\s*wp:fooplugins\/foogallery\s*{"id":\s*(\d+)\s*}\s*\/-->/', $content, $matches );

			if ( ! empty( $matches[1] ) ) {
				$gallery_ids = array_map( 'intval', $matches[1] );
			}

			if ( ! empty( $gallery_ids ) ) {
				$gallery_ids = array_unique( $gallery_ids );

				foreach ( $gallery_ids as $gallery_id ) {
					$gallery = FooGallery::get_by_id( $gallery_id );
					if ( $gallery ) {
						foreach ( $gallery->attachments() as $attachment ) {
							$urls[] = wp_get_attachment_url( $attachment->ID );
						}
					}
				}
			}
		}
	}

	return $urls;
}
add_filter( 'sgg_sitemap_post_media_urls', 'sgg_add_foogallery_image_urls', 10, 2 );

/**
 * Serve IndexNow API key.
 */
function sgg_serve_indexnow_api_key() {
	global $wp;

	$indexnow    = new \GRIM_SG\IndexNow();
	$current_url = home_url( $wp->request );

	if ( ! empty( $current_url ) && $indexnow->get_api_key_location() === $current_url ) {
		header( 'Content-Type: text/plain' );
		header( 'X-Robots-Tag: noindex' );
		status_header( 200 );

		echo esc_html( $indexnow->get_api_key() );

		exit();
	}
}
add_action( 'wp', 'sgg_serve_indexnow_api_key' );

/**
 * Disable default WordPress Sitemaps.
 */
add_filter( 'wp_sitemaps_enabled', '__return_false' );
