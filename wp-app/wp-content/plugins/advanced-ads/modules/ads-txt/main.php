<?php
/**
 * Prepare content of the 'ads.txt' file.
 *
 * @package Advanced_Ads_Ads_Txt
 */

/**
 * Initializes the ads.txt module for Advanced Ads plugin.
 *
 * @return void
 */
function advanced_ads_ads_txt_init(): void {
	if (
		! is_multisite()
		|| ( function_exists( 'is_site_meta_supported' ) && is_site_meta_supported() )
	) {

		$public = new Advanced_Ads_Ads_Txt_Public( new Advanced_Ads_Ads_Txt_Strategy() );

		if ( is_admin() ) {
			new Advanced_Ads_Ads_Txt_Admin( new Advanced_Ads_Ads_Txt_Strategy(), $public );
		} else {
			new Advanced_Ads_Ads_Txt_Public( new Advanced_Ads_Ads_Txt_Strategy() );
		}
	}
}

add_action( 'advanced-ads-plugin-loaded', 'advanced_ads_ads_txt_init' );
