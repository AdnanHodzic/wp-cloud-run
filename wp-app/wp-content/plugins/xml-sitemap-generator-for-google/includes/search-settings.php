<?php
function xml_sitemap_generate_settings() {
	$settings_array = array(
		'general' => array(
			array(
				'id'    => 'sitemap_to_robots',
				'label' => 'Add Sitemap Output URLs to site "robots.txt" file',
				'tags'  => 'robots.txt',
			),
			array(
				'id'    => 'enable_indexnow',
				'label' => 'Enable IndexNow Protocol (Microsoft Bing, Seznam.cz, Naver, Yandex)',
				'tags'  => 'Posts',
			),
			array(
				'id'    => 'enable_sitemap',
				'label' => 'XML Sitemap',
			),
			array(
				'id'    => 'enable_html_sitemap',
				'label' => 'HTML Sitemap',
			),
			array(
				'id'    => 'webserver_configuration',
				'label' => 'Webserver Configuration' ,
			),
			array(
				'id'    => 'sitemap_structure',
				'label' => 'Sitemap Structure' ,
			),
			array(
				'id'    => 'links_per_page',
				'label' => 'Links per page:' ,
			),
			array(
				'id'    => 'custom_sitemaps',
				'label' => 'Custom Sitemaps' ,
			),
			array(
				'id'    => 'additional_urls',
				'label' => 'Additional URLs',
			),
			array(
				'id'    => 'general_exclude_posts',
				'label' => 'Exclude',
			),
			array(
				'id'    => 'general_include_only_terms',
				'label' => 'Include only selected Terms',
			),
			array(
				'id'    => 'posts_priority',
				'label' => 'Posts Priority',
			),
			array(
				'id'    => 'sitemap_options',
				'label' => 'Sitemap Options',
			),
			array(
				'id'    => 'general_custom_post_types',
				'label' => 'General Custom Post Types',
			),
		),
		'google-news' => array(
			array(
				'id'    => 'enable_google_news',
				'label' => 'Google News',
				'tags'  => '',
			),
			array(
				'id'    => 'google_news_name',
				'label' => 'Publication Name',
			),
			array(
				'id'    => 'google_news_old_posts',
				'label' => 'Include Older Posts',
			),
			array(
				'id'    => 'google__url',
				'label' => 'Google News URL',
			),
			array(
				'id'    => 'google_keywords',
				'label' => 'Keywords',
			),
			array(
				'id'    => 'google_news_stocks',
				'label' => 'Stock Tickers',
			),
			array(
				'id'    => 'google_exclude',
				'label' => 'Exclude',
			),
			array(
				'id'    => 'google_include',
				'label' => 'Include only selected Terms',
			),
			array(
				'id'    => 'google_content_post',
				'label' => 'Content Options',
			),
			array(
				'id'    => 'google_custom_post_types',
				'label' => 'Google Custom Post Types',
			),
		),
		'image-sitemap' => array(
			array(
				'id'    => 'enable_image_sitemap',
				'label' => 'Image Sitemap',
			),
			array(
				'id'    => 'image_sitemap_url',
				'label' => 'Image Sitemap URL:',
			),
			array(
				'id'    => 'hide_image_previews',
				'label' => 'Hide Image Previews',
			),
			array(
				'id'    => 'hide_image_sitemap_xsl',
				'label' => 'Hide XSL Template',
			),
			array(
				'id'    => 'image_mime_types',
				'label' => 'Image MIME Types',
			),
			array(
				'id'    => 'exclude_broken_images',
				'label' => 'Exclude Broken Images',
			),
			array(
				'id'    => 'include_featured_images',
				'label' => 'Include Featured Images',
			),
			array(
				'id'    => 'include_woo_gallery',
				'label' => 'Include WooCommerce Gallery Images',
			),
			array(
				'id'    => 'image_content_option',
				'label' => 'Content Options',
			),
			array(
				'id'    => 'image_custom_post_types',
				'label' => 'Image Custom Post Types',
			),
		),
		'video-sitemap' => array(
			array(
				'id'    => 'enable_video_sitemap',
				'label' => 'Enable Video Sitemap',
			),
			array(
				'id'    => 'video_sitemap_url',
				'label' => 'Video Sitemap URL:',
			),
			array(
				'id'    => 'hide_video_sitemap_xsl',
				'label' => 'Hide XSL Template',
			),
			array(
				'id'    => 'sgg_youtube',
				'label' => 'YouTube',
			),
			array(
				'id'    => 'youtube_api_key',
				'label' => 'YouTube Data API v3 Key:',
			),
			array(
				'id'    => 'sgg_vimeo',
				'label' => 'Vimeo',
			),
			array(
				'id'    => 'vimeo_check_api_key',
				'label' => 'Vimeo Access Token:',
			),
			array(
				'id'    => 'enable_video_api_cache',
				'label' => 'API Data Cache',
			),
			array(
				'id'    => 'video_content_option',
				'label' => 'Content Options',
			),
			array(
				'id'    => 'video_custom_post_types',
				'label' => 'Custom Post Types',
			),
		),
		'advanced' => array(
			array(
				'id'    => 'enable_cache',
				'label' => 'Cache',
			),
			array(
				'id'    => 'cache_timeout_period',
				'label' => 'Cache Expiration Time:',
			),
			array(
				'id'    => 'last_cached_time',
				'label' => 'Last Cached Time:',
			),
			array(
				'id'    => 'clear_cache',
				'label' => 'Clear Cache',
			),
			array(
				'id'    => 'clear_cache_on_save_post',
				'label' => 'Smart Caching',
			),
			array(
				'id'    => 'disable_media_sitemap_cache',
				'label' => 'Disable Media Sitemap Cache Collection',
			),
			array(
				'id'    => 'minimize_sitemap',
				'label' => 'Minimize Sitemap source code',
			),
			array(
				'id'    => 'advanced_sitemap_styles',
				'label' => 'Sitemap Styles',
			),
			array(
				'id'    => 'hide_branding',
				'label' => 'Hide Branding Marks',
			),
			array(
				'id'    => 'enable_cronjob',
				'label' => 'Cron Job',
			),
			array(
				'id'    => 'cronjob_runtime',
				'label' => 'Cron Job Run Time:',
			),
			array(
				'id'    => 'advanced_wp_cli',
				'label' => 'WP CLI',
			),
			array(
				'id'    => 'advanced_import_settings',
				'label' => 'Import Settings',
			),
			array(
				'id'    => 'advanced_export_settings',
				'label' => 'Export Settings',
			),
		),
	);

	$flat_settings_array = array();
	foreach ( $settings_array as $tab => $settings ) {
		foreach ( $settings as $setting ) {
			$setting['tab']        = $tab;
			$flat_settings_array[] = $setting;
		}
	}

	return $flat_settings_array;
}
