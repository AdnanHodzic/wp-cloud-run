<?php //phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols -- the sniff does not accept calling add_action and declaring the callback in the same file

// Only load if not already existing (maybe included from another plugin).
if ( defined( 'ADVADS_AB_BASE_PATH' ) ) {
	return;
}

// load basic path to the plugin
define( 'ADVADS_AB_BASE_PATH', plugin_dir_path( __FILE__ ) );
// general and global slug, e.g. to store options in WP, textdomain
define( 'ADVADS_AB_SLUG', 'advanced-ads-ab-module' );

add_action( 'advanced-ads-plugin-loaded', 'advanced_ads_load_adblocker' );

/**
 * Load ad blocker functionality.
 */
function advanced_ads_load_adblocker() {
	Advanced_Ads_Ad_Blocker::get_instance();

	if ( is_admin() && ! wp_doing_ajax() ) {
		Advanced_Ads_Ad_Blocker_Admin::get_instance();
	}
}
