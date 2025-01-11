<?php
/**
 * Uninstall Insert Headers and Footers settings on uninstall.
 *
 * @since 2.1.0
 */


/**
 * Runs upon the uninstall hook.
 * @package WP Headers and Footers
 *
 * @since 2.1.0
 * @return string
 */
$settings = get_option( 'wpheaderandfooter_settings' );
global $wpdb;

// If not a multi-site.
if ( ! is_multisite() ) {

	if ( isset( $settings ) && isset( $settings['remove_all_settings'] ) && 'on' === $settings['remove_all_settings'] ) {
		delete_option( 'wpheaderandfooter_basics' );
		delete_option( 'wpheaderandfooter_settings' );
		delete_option( 'wpheaderandfooter_active_time' );
		delete_option( 'wpheaderandfooter_review_dismiss' );
	}

} else {

	// if multi-site then go through each blog and remove the page and its settings accordingly.
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

	foreach ( $blog_ids as $blog_id ) {

		// Switch to blogs if there are more than One(1).
		switch_to_blog( $blog_id );

		if ( isset( $settings ) && isset( $settings['remove_all_settings'] ) && 'on' === $settings['remove_all_settings'] ) {
			delete_option( 'login_customizer_options' );
			delete_option( 'login_customizer_settings' );
			delete_option( 'wpheaderandfooter_active_time' );
			delete_option( 'wpheaderandfooter_review_dismiss' );
		}

		restore_current_blog();

	}
}
