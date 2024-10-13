<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Settings extends Controller {
	// Global Settings
	public $sitemap_url         = 'sitemap.xml';
	public $html_sitemap_url    = 'sitemap.html';
	public $enable_sitemap      = true;
	public $links_per_page      = 1000;
	public $enable_html_sitemap = false;
	public $sitemap_to_robots   = true;
	public $enable_indexnow     = true;
	public $sitemap_view        = 'sitemap-index';

	// Sitemap Data Settings
	public $home;
	public $page;
	public $post;
	public $archive;
	public $archive_older;
	public $authors;
	public $exclude_posts;
	public $exclude_terms;
	public $posts_priority;
	public $custom_sitemaps  = array();
	public $additional_pages = array();
	public $cpt              = array();
	public $taxonomies       = array();

	// Google News Data Settings
	public $enable_google_news    = false;
	public $google_news_old_posts = false;
	public $google_news_name      = '';
	public $google_news_url       = 'google-news.xml';
	public $google_news_keywords  = '';
	public $google_news_stocks    = false;
	public $google_news_exclude;
	public $google_news_exclude_terms;

	// Media Sitemap Data Settings
	public $enable_image_sitemap    = false;
	public $enable_video_sitemap    = false;
	public $image_sitemap_url       = 'image-sitemap.xml';
	public $video_sitemap_url       = 'video-sitemap.xml';
	public $image_mime_types        = array(
		'image/jpeg' => true,
		'image/png'  => true,
		'image/gif'  => true,
		'image/bmp'  => true,
		'image/webp' => true,
	);
	public $youtube_api_key         = '';
	public $vimeo_api_key           = '';
	public $exclude_broken_images   = false;
	public $include_featured_images = false;
	public $include_woo_gallery     = false;

	// Cache Settings
	public $enable_cache             = false;
	public $cache_timeout            = 24;
	public $cache_timeout_period     = 3600;
	public $clear_cache_on_save_post = false;
	public $enable_video_api_cache   = true;
	public $minimize_sitemap         = false;

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		$this->home          = new PTSettings( 10, PTSettings::$DAILY );
		$this->page          = new PTSettings( 6, PTSettings::$WEEKLY, false, true );
		$this->post          = new PTSettings( 6, PTSettings::$MONTHLY, true, true );
		$this->archive       = new PTSettings( 6, PTSettings::$DAILY );
		$this->archive_older = new PTSettings( 3, PTSettings::$YEARLY );
		$this->authors       = new PTSettings( 3, PTSettings::$WEEKLY );

		foreach ( $this->get_cpt() as $cpt ) {
			$this->cpt[ $cpt ] = new PTSettings( 6, PTSettings::$MONTHLY );
		}

		foreach ( $this->get_taxonomy_types() as $taxonomy ) {
			$this->taxonomies[ $taxonomy ] = new PTSettings( 3, PTSettings::$WEEKLY );
		}
	}

	/**
	 * Get Default Settings
	 * @param $option
	 * @return PTSettings
	 */
	public function get_row_value( $option ) {
		$settings = new PTSettings();

		$settings->include       = isset( $_POST[ $option . '_include' ] ) ? sanitize_text_field( $_POST[ $option . '_include' ] ) : false;
		$settings->priority      = isset( $_POST[ $option . '_priority' ] ) ? sanitize_text_field( $_POST[ $option . '_priority' ] ) : 0;
		$settings->frequency     = isset( $_POST[ $option . '_frequency' ] ) ? sanitize_text_field( $_POST[ $option . '_frequency' ] ) : $settings->frequency;
		$settings->google_news   = isset( $_POST[ $option . '_google_news' ] ) ? sanitize_text_field( $_POST[ $option . '_google_news' ] ) : 0;
		$settings->media_sitemap = isset( $_POST[ $option . '_media_sitemap' ] ) ? sanitize_text_field( $_POST[ $option . '_media_sitemap' ] ) : 0;

		return $settings;
	}
}
