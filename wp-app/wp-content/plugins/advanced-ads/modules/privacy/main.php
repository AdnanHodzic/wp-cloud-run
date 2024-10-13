<?php
if ( class_exists( 'Advanced_Ads', false ) ) {

	// only load if not already existing (maybe included from another plugin)
	if ( defined( 'ADVADS_PRIVACY_SLUG' ) ) {
		return;
	}

	// general and global slug, e.g. to store options in WP
	define( 'ADVADS_PRIVACY_SLUG', 'advanced-ads-privacy' );
	define( 'ADVADS_PRIVACY_BASE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'ADVADS_PRIVACY_BASE_URL', plugins_url( basename( ADVADS_ABSPATH ) . '/modules/' . basename( ADVADS_PRIVACY_BASE_PATH ) . '/' ) );

	Advanced_Ads_Privacy::get_instance();

	if ( is_admin() ) {
		Advanced_Ads_Privacy_Admin::get_instance();
	}
}



