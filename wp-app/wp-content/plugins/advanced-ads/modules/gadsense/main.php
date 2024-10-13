<?php

if ( class_exists( 'Advanced_Ads', false ) ) {
	define( 'GADSENSE_BASE_PATH', plugin_dir_path( __FILE__ ) );
	define( 'GADSENSE_BASE_URL', plugins_url( basename( ADVADS_ABSPATH ) . '/modules/' . basename( GADSENSE_BASE_PATH ) . '/' ) );
	define( 'GADSENSE_OPT_NAME', ADVADS_SLUG . '-adsense' );

	function gadsense_init() {
		Advanced_Ads_AdSense_Data::get_instance();

        if ( is_admin() ) {
          Advanced_Ads_AdSense_MAPI::get_instance();
        }

        if ( ! wp_doing_ajax() && is_admin() ) {
            Advanced_Ads_AdSense_Admin::get_instance();
        } else {
            Advanced_Ads_AdSense_Public::get_instance();
        }

        $network = Advanced_Ads_Network_Adsense::get_instance();
        $network->register();
	}
	add_action( 'advanced-ads-plugin-loaded', 'gadsense_init' );
}
