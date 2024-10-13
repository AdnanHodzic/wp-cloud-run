<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Language Files
if ( ! is_textdomain_loaded( 'xml-sitemap-generator-for-google' ) ) {
	load_plugin_textdomain( 'xml-sitemap-generator-for-google', false, 'xml-sitemap-generator-for-google/languages' );
}

// Autoload Files
require_once GRIM_SG_INCLUDES . 'hooks.php';
require_once GRIM_SG_INCLUDES . 'helpers.php';

// Autoload
require_once GRIM_SG_INCLUDES . 'vendor/Controller.php';
require_once GRIM_SG_INCLUDES . 'vendor/SitemapGenerator.php';
require_once GRIM_SG_INCLUDES . 'vendor/QueryBuilder.php';
require_once GRIM_SG_INCLUDES . 'vendor/PTSettings.php';
require_once GRIM_SG_INCLUDES . 'vendor/Settings.php';
require_once GRIM_SG_INCLUDES . 'vendor/Migration.php';
require_once GRIM_SG_INCLUDES . 'IndexNow.php';
require_once GRIM_SG_INCLUDES . 'Cache.php';
require_once GRIM_SG_INCLUDES . 'Sitemap.php';
require_once GRIM_SG_INCLUDES . 'Frontend.php';
require_once GRIM_SG_INCLUDES . 'GoogleNews.php';
require_once GRIM_SG_INCLUDES . 'MediaSitemap.php';
require_once GRIM_SG_INCLUDES . 'ImageSitemap.php';
require_once GRIM_SG_INCLUDES . 'VideoSitemap.php';
require_once GRIM_SG_INCLUDES . 'MultilingualSitemap.php';
require_once GRIM_SG_INCLUDES . 'Tools.php';
require_once GRIM_SG_INCLUDES . 'ImportExport.php';
require_once GRIM_SG_INCLUDES . 'PostSettings.php';

function sgg_init() {
	new GRIM_SG\Vendor\Migration();
	new GRIM_SG\Frontend();
	new GRIM_SG\Tools();
	new GRIM_SG\PostSettings();

	// WP Admin
	if ( is_admin() ) {
		require_once GRIM_SG_INCLUDES . 'Dashboard.php';
		require_once GRIM_SG_INCLUDES . 'Notices.php';

		new GRIM_SG\Dashboard();
		new GRIM_SG\Notices();
		new GRIM_SG\ImportExport();
	}
}
add_action( 'plugins_loaded', 'sgg_init', 1 );
