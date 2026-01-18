<?php

namespace GRIM_SG;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

use \WP_CLI;

class SitemapCLI {
	/**
	 * Generate a sitemap.
	 *
	 * ## OPTIONS
	 *
	 * [--template=<template>]
	 * : The sitemap template to use. Default: 'sitemap'
	 *
	 * ## EXAMPLES
	 *
	 *     # Generate a sitemap
	 *     $ wp sitemap generate
	 *
	 *     # Generate a specific Sitemap type
	 *     $ wp sitemap generate --template=sitemap
	 *     $ wp sitemap generate --template=image-sitemap
	 *     $ wp sitemap generate --template=video-sitemap
	 *     $ wp sitemap generate --template=google-news
	 *
	 * @param array $args Command arguments
	 * @param array $assoc_args Command associative arguments
	 */
	public function generate( $args, $assoc_args ) {
		$template          = $assoc_args['template'] ?? 'sitemap';
		$inner_sitemap     = null;
		$current_page      = null;
		$allowed_templates = array(
			'sitemap',
			'image-sitemap',
			'video-sitemap',
			'google-news',
		);

		if ( ! in_array( $template, $allowed_templates, true ) ) {
			WP_CLI::error( 'Invalid Sitemap Template. Allowed templates: ' . implode( ', ', $allowed_templates ) );
		}

		// Initialize the Sitemap class
		$sitemap = null;
		if ( 'sitemap' === $template ) {
			$sitemap = new Sitemap();
		} elseif ( 'image-sitemap' === $template ) {
			$sitemap = new ImageSitemap();
		} elseif ( 'video-sitemap' === $template ) {
			$sitemap = new VideoSitemap();
		} elseif ( 'google-news' === $template ) {
			$sitemap = new GoogleNews();
		}

		if ( ! $sitemap ) {
			WP_CLI::error( 'Sitemap class not found.' );
		}

		if ( $sitemap->settings->enable_cache ) {
			$cache = new Cache( $template, $inner_sitemap, $current_page );

			$sitemap->collect_urls( $template, $inner_sitemap, $current_page );
			$cache->set( $sitemap->urls );
		} else {
			$sitemap->collect_urls( $template, $inner_sitemap, $current_page );
		}

		WP_CLI::success(
			"Sitemap generated successfully for template: {$template}. URLs count: " . count( $sitemap->urls )
		);
	}
}

// Register the command
WP_CLI::add_command( 'sitemap', 'GRIM_SG\SitemapCLI' );
