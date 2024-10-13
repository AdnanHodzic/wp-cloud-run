<?php // phpcs:ignoreFile

use AdvancedAds\Installation\Capabilities;

/**
 * Upgrade logic from older data to new one
 *
 * the version number itself is changed in /admin/includes/class-notices.php::register_version_notices()
 *
 * @since   1.7
 * @todo    we need internal markers to check if the update ran on a normal request and not AJAX, where it happened to break sometimes
 */
class Advanced_Ads_Upgrades {

	public function __construct(){

		$internal_options = Advanced_Ads_Plugin::get_instance()->internal_options();

		// 'advanced_ads_edit_ads' capability was added to Entities::POST_TYPE_AD post type in this version
		if ( ! isset( $internal_options['version'] ) || version_compare( $internal_options['version'], '1.7.2', '<' ) ) {
			Capabilities::get_instance()->create_capabilities();
		}

		// suppress version update?
		$suppress_version_number_update = false;

		// don’t upgrade if no previous version existed
		if( ! empty( $internal_options['version'] ) ) {

			/**
			 * Example of how to use an update
			 * this is no longer valid
			 */
			 /*
			if ( version_compare( $internal_options['version'], '1.7' ) == -1 ) {
				// run with wp_loaded action, because WP_Query is needed and some plugins inject data that is not yet initialized
				add_action( 'wp_loaded', array( $this, 'upgrade_1_7') );
			}*/
		}

		// update version notices – if this doesn’t happen here, the upgrade might run multiple times and destroy updated data
		if( ! $suppress_version_number_update ){
		    Advanced_Ads_Admin_Notices::get_instance()->update_version_number();
		}
	}
}
