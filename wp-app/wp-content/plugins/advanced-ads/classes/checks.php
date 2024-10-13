<?php

/**
 * Checks for various things
 *
 * @since 1.6.9
 */
class Advanced_Ads_Checks {

	/**
	 * Minimum required PHP version of Advanced Ads
	 */
	const MINIMUM_PHP_VERSION = '5.6.20';


	/**
	 * Show the list of potential issues
	 */
	public static function show_issues() {
		include_once ADVADS_ABSPATH . '/admin/views/checks.php';
	}

	/**
	 * PHP version minimum
	 *
	 * @return bool true if uses the minimum PHP version or higher
	 */
	public static function php_version_minimum() {

		if ( version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Caching used
	 *
	 * @return bool true if active
	 */
	public static function cache() {
		if ( ( defined( 'WP_CACHE' ) && WP_CACHE ) // general cache constant.
			|| defined( 'W3TC' ) // W3 Total Cache.
			|| function_exists( 'wp_super_cache_text_domain' ) // WP SUper Cache.
			|| defined( 'WP_ROCKET_SLUG' ) // WP Rocket.
			|| defined( 'WPFC_WP_CONTENT_DIR' ) // WP Fastest Cache.
			|| class_exists( 'HyperCache', false ) // Hyper Cache.
			|| defined( 'CE_CACHE_DIR' ) // Cache Enabler.
		) {
			return true;
		}

		return false;
	}

	/**
	 * WordPress update available
	 *
	 * @return bool true if WordPress update available
	 */
	public static function wp_update_available() {

		$update_data = wp_get_update_data();
		$count       = absint( $update_data['counts']['wordpress'] );

		if ( $count ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if license keys are missing or invalid or expired
	 *
	 * @since 1.6.6
	 * @update 1.6.9 moved from Advanced_Ads_Plugin
	 * @update 1.8.21 also check for expired licenses
	 * @return true if there are missing licenses
	 */
	public static function licenses_invalid() {

		$add_ons = apply_filters( 'advanced-ads-add-ons', [] );

		if ( [] === $add_ons ) {
			Advanced_Ads_Ad_Health_Notices::get_instance()->remove( 'license_invalid' );
			return false;
		}

		foreach ( $add_ons as $_add_on_key => $_add_on ) {
			$status = Advanced_Ads_Admin_Licenses::get_instance()->get_license_status( $_add_on['options_slug'] );

			// check expiry date.
			$expiry_date = Advanced_Ads_Admin_Licenses::get_instance()->get_license_expires( $_add_on['options_slug'] );

			if ( $expiry_date && 'lifetime' !== $expiry_date && strtotime( $expiry_date ) < time() ) {
				return true;
			}

			// don’t check if license is valid.
			if ( 'valid' === $status ) {
				continue;
			}

			// retrieve our license key from the DB.
			$licenses = Advanced_Ads_Admin_Licenses::get_instance()->get_licenses();

			$license_key = isset( $licenses[ $_add_on_key ] ) ? $licenses[ $_add_on_key ] : false;

			if ( ! $license_key || 'valid' !== $status ) {
				return true;
			}
		}

		// remove notice, if one is given.
		Advanced_Ads_Ad_Health_Notices::get_instance()->remove( 'license_invalid' );
		return false;
	}

	/**
	 * Autoptimize plugin installed
	 *   can change ad tags, especially inline css and scripts
	 *
	 * @link https://wordpress.org/plugins/autoptimize/
	 * @return bool true if Autoptimize is installed
	 */
	public static function active_autoptimize() {

		if ( defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * WP rocket plugin installed
	 *
	 * @return bool true if WP rocket is installed
	 */
	public static function active_wp_rocket() {
		if ( defined( 'WP_ROCKET_SLUG' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks the settings of wp rocket to find out if combining of javascript files is enabled
	 *
	 * @return boolean true, when "Combine JavaScript files" is enabled
	 */
	public static function is_wp_rocket_combine_js_enabled() {
		if ( self::active_wp_rocket() ) {
			$settings = get_option( 'wp_rocket_settings' );
			if ( $settings ) {
				if ( isset( $settings['minify_concatenate_js'] ) && $settings['minify_concatenate_js'] ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Any AMP plugin enabled
	 *
	 * @return bool true if AMP plugin is installed
	 */
	public static function active_amp_plugin() {
		// Accelerated Mobile Pages.
		if ( function_exists( 'ampforwp_is_amp_endpoint' ) ) {
			return true;
		}

		// AMP plugin.
		if ( function_exists( 'is_amp_endpoint' ) ) {
			return true;
		}

		// other plugins.
		if ( function_exists( 'is_wp_amp' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the preconditions are met to wrap an ad with <!--noptimize--> comments
	 *
	 * @return boolean
	 */
	public static function requires_noptimize_wrapping() {
		return self::active_autoptimize() || self::is_wp_rocket_combine_js_enabled();
	}

	/**
	 * Check for additional conflicting plugins
	 *
	 * @return array $plugins names of conflicting plugins
	 */
	public static function conflicting_plugins() {
		$conflicting_plugins = [];

		if ( defined( 'Publicize_Base' ) ) { // JetPack Publicize module.
			$conflicting_plugins[] = 'Jetpack – Publicize';
		}
		if ( defined( 'PF__PLUGIN_DIR' ) ) { // Facebook Instant Articles & Google AMP Pages by PageFrog.
			$conflicting_plugins[] = 'Facebook Instant Articles & Google AMP Pages by PageFrog';
		}
		if ( defined( 'GT_VERSION' ) ) { // GT ShortCodes.
			$conflicting_plugins[] = 'GT ShortCodes';
		}
		if ( class_exists( 'SimilarPosts', false ) ) { // Similar Posts, https://de.wordpress.org/plugins/similar-posts/.
			$conflicting_plugins[] = 'Similar Posts';
		}

		return $conflicting_plugins;
	}

	/**
	 * Check if any of the global hide ads options is set
	 * ignore RSS feed setting, because it is standard
	 *
	 * @since 1.7.10
	 * @return bool
	 */
	public static function ads_disabled() {
		$options = Advanced_Ads::get_instance()->options();
		if ( isset( $options['disabled-ads'] ) && is_array( $options['disabled-ads'] ) ) {
			foreach ( $options['disabled-ads'] as $_key => $_value ) {
				// don’t warn if "RSS Feed", "404", or "REST API" option are enabled, because they are normally not critical.
				if ( ! empty( $_value ) && ! in_array( (string) $_key, [ 'feed', '404', 'rest-api' ], true ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Check for required php extensions
	 *
	 * @return array
	 */
	public static function php_extensions() {

		$missing_extensions = [];

		if ( ! extension_loaded( 'dom' ) ) {
			$missing_extensions[] = 'dom';
		}

		if ( ! extension_loaded( 'mbstring' ) ) {
			$missing_extensions[] = 'mbstring';
		}

		return $missing_extensions;
	}

	/**
	 * Get the list of Advanced Ads constant defined by the user.
	 *
	 * @return array
	 */
	public static function get_defined_constants() {
		$constants = apply_filters(
			'advanced-ads-constants',
			[
				'ADVADS_ADS_DISABLED',
				'ADVADS_ALLOW_ADSENSE_ON_404',
				'ADVADS_DISABLE_RESPONSIVE_IMAGES',
				'ADVANCED_ADS_AD_DEBUG_FOR_ADMIN_ONLY',
				'ADVANCED_ADS_DISABLE_ANALYTICS_ANONYMIZE_IP',
				'ADVANCED_ADS_DISABLE_CHANGE',
				'ADVANCED_ADS_DISABLE_CODE_HIGHLIGHTING',
				'ADVANCED_ADS_DISABLE_SHORTCODE_BUTTON',
				'ADVANCED_ADS_DISALLOW_PHP',
				'ADVANCED_ADS_ENABLE_REVISIONS',
				'ADVANCED_ADS_GEO_TEST_IP',
				'ADVANCED_ADS_PRO_CUSTOM_POSITION_MOVE_INTO_HIDDEN',
				'ADVANCED_ADS_PRO_PAGE_IMPR_EXDAYS',
				'ADVANCED_ADS_PRO_REFERRER_EXDAYS',
				'ADVANCED_ADS_RESPONSIVE_DISABLE_BROWSER_WIDTH',
				'ADVANCED_ADS_SHOW_LICENSE_RESPONSE',
				'ADVANCED_ADS_SUPPRESS_PLUGIN_ERROR_NOTICES',
				'ADVANCED_ADS_TRACKING_DEBUG',
				'ADVANCED_ADS_TRACKING_NO_HOURLY_LIMIT',
			]
		);

		$result = [];
		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$result[] = $constant;
			}
		}
		return $result;
	}


	/**
	 * WP Engine hosting detected
	 *
	 * @return bool true if site is hosted by WP Engine
	 */
	public static function wp_engine_hosting() {
		if ( defined( 'WPE_APIKEY' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Notice for Adblocker module if assets have expired
	 */
	public static function assets_expired() {
		$plugin_options    = Advanced_Ads_Plugin::get_instance()->options();
		$adblocker_options = Advanced_Ads_Ad_Blocker::get_instance()->options();

		return ( ! empty( $plugin_options['use-adblocker'] ) && empty( $adblocker_options['module_can_work'] ) );
	}

	/**
	 * Check for other ads.txt plugins
	 *
	 * @return array
	 */
	public static function ads_txt_plugins() {

		$ads_txt_plugins = [];

		// Ads.txt Manager.
		if ( function_exists( 'tenup_display_ads_txt' ) ) {
			$ads_txt_plugins[] = 'Ads.txt Manager';
		}

		// todo:
		// ads-txt-admin/unveil-media-ads-txt.php
		// simple-ads-txt/bs_ads_txt.php
		// ads-txt-manager/adstxtmanager.php
		// monetizemore-ads-txt/wp-ads-txt.php
		// authorized-sellers-manager/ads-txt-publisher.php.

		return $ads_txt_plugins;
	}

	/**
	 * Check for activated plugins that manage header or footer code
	 *
	 * @return array
	 */
	public static function header_footer_plugins() {

		$plugins = [];

		// Header Footer Code Manager.
		if ( function_exists( 'hfcm_options_install' ) ) {
			$plugins[] = 'Header Footer Code Manager';
		}
		// Head, Footer and Post Injections.
		if ( function_exists( 'hefo_template_redirect' ) ) {
			$plugins[] = 'Head, Footer and Post Injections';
		}
		// Insert Headers and Footers /insert-headers-and-footers/.
		if ( class_exists( 'InsertHeadersAndFooters', false ) ) {
			$plugins[] = 'Insert Headers and Footers';
		}
		// Header and Footer Scripts /header-and-footer-scripts/.
		if ( class_exists( 'HeaderAndFooterScripts', false ) ) {
			$plugins[] = 'Header and Footer Scripts';
		}

		return $plugins;
	}
}
