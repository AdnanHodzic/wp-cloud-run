<?php
/**
 * Admin System Info.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Admin System Info.
 */
class System_Info {

	/**
	 * Get system information.
	 *
	 * @return string
	 */
	public function get_info() {
		$data = '### Begin System Info ###' . "\n\n";

		$data .= $this->advanced_ads_info();
		$data .= $this->site_info();
		$data .= $this->wp_info();
		$data .= $this->uploads_info();
		$data .= $this->plugins_info();
		$data .= $this->server_info();

		$data .= "\n" . '### End System Info ###';

		return $data;
	}

	/**
	 * Get Advanced Ads info.
	 *
	 * @return string
	 */
	private function advanced_ads_info() {
		$data  = '-- Advanced Ads Info' . "\n\n";
		$data .= $this->get_it_spaced( 'Pro', defined( 'AAP_VERSION' ) ? 'Activated' : 'Not Activated', 22 );

		return $data;
	}

	/**
	 * Get Site info.
	 *
	 * @return string
	 */
	private function site_info() {
		$data  = "\n" . '-- Site Info' . "\n\n";
		$data .= $this->get_it_spaced( 'Site URL', site_url(), 17 );
		$data .= $this->get_it_spaced( 'Home URL', home_url(), 17 );
		$data .= $this->get_it_spaced( 'Multisite', is_multisite() ? 'Yes' : 'No', 16 );

		return $data;
	}

	/**
	 * Get WordPress Configuration info.
	 *
	 * @return string
	 */
	private function wp_info() {
		global $wpdb;

		$theme_data = wp_get_theme();
		$theme      = $theme_data->name . ' ' . $theme_data->version;

		$data  = "\n" . '-- WordPress Configuration' . "\n\n";
		$data .= $this->get_it_spaced( 'Version', get_bloginfo( 'version' ), 18 );
		$data .= $this->get_it_spaced( 'Language', get_locale(), 17 );
		$data .= $this->get_it_spaced( 'User Language', get_user_locale(), 12 );
		$data .= $this->get_it_spaced( 'Permalink Structure', get_option( 'permalink_structure' ) ?? 'Default', 6 );
		$data .= $this->get_it_spaced( 'Active Theme', $theme, 13 );
		$data .= $this->get_it_spaced( 'Show On Front', get_option( 'show_on_front' ), 12 );
		$data .= $this->get_it_spaced( 'ABSPATH', ABSPATH, 18 );
		$data .= $this->get_it_spaced( 'Table Prefix', 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ), 13 ); //phpcs:ignore
		$data .= $this->get_it_spaced( 'WP_DEBUG', defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set', 17 );
		$data .= $this->get_it_spaced( 'Memory Limit', WP_MEMORY_LIMIT, 13 );
		$data .= $this->get_it_spaced( 'Registered Post Stati', implode( ', ', get_post_stati() ), 4 );
		$data .= $this->get_it_spaced( 'Revisions', WP_POST_REVISIONS ? WP_POST_REVISIONS > 1 ? 'Limited to ' . WP_POST_REVISIONS : 'Enabled' : 'Disabled', 16 );

		return $data;
	}

	/**
	 * Get Uploads/Constants info.
	 *
	 * @return string
	 */
	private function uploads_info() {
		$uploads_dir = wp_upload_dir();

		$data  = "\n" . '-- WordPress Uploads/Constants' . "\n\n";
		$data .= $this->get_it_spaced( 'WP_CONTENT_DIR', defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR ? WP_CONTENT_DIR : 'Disabled' : 'Not set', 11 );
		$data .= $this->get_it_spaced( 'WP_CONTENT_URL', defined( 'WP_CONTENT_URL' ) ? WP_CONTENT_URL ? WP_CONTENT_URL : 'Disabled' : 'Not set', 11 );
		$data .= $this->get_it_spaced( 'UPLOADS', defined( 'UPLOADS' ) ? UPLOADS ? UPLOADS : 'Disabled' : 'Not set', 18 );
		$data .= $this->get_it_spaced( 'wp_uploads_dir() path', $uploads_dir['path'], 4 );
		$data .= $this->get_it_spaced( 'wp_uploads_dir() url', $uploads_dir['url'], 5 );
		$data .= $this->get_it_spaced( 'wp_uploads_dir() basedir', $uploads_dir['basedir'], 1 );
		$data .= $this->get_it_spaced( 'wp_uploads_dir() baseurl', $uploads_dir['baseurl'], 1 );

		return $data;
	}

	/**
	 * Get Plugins info.
	 *
	 * @return string
	 */
	private function plugins_info() {
		$data  = $this->mu_plugins();
		$data .= $this->installed_plugins();
		$data .= $this->multisite_plugins();

		return $data;
	}

	/**
	 * Get MU Plugins info.
	 *
	 * @return string
	 */
	private function mu_plugins() {
		$data = '';

		// Must-use plugins.
		// NOTE: MU plugins can't show updates!
		$muplugins = get_mu_plugins();

		if ( ! empty( $muplugins ) && count( $muplugins ) > 0 ) {
			$data = "\n" . '-- Must-Use Plugins' . "\n\n";

			foreach ( $muplugins as $plugin => $plugin_data ) {
				$data .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "\n";
			}
		}

		return $data;
	}

	/**
	 * Get Installed Plugins info.
	 *
	 * @return string
	 */
	private function installed_plugins() {
		$updates = get_plugin_updates();

		// WordPress active plugins.
		$data = "\n" . '-- WordPress Active Plugins' . "\n\n";

		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', [] );

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$data  .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		// WordPress inactive plugins.
		$data .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$data  .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		return $data;
	}

	/**
	 * Get Multisite Plugins info.
	 *
	 * @return string
	 */
	private function multisite_plugins() {
		$data = '';

		if ( ! is_multisite() ) {
			return $data;
		}

		$updates = get_plugin_updates();

		// WordPress Multisite active plugins.
		$data = "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins        = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', [] );

		foreach ( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
				continue;
			}

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$plugin = get_plugin_data( $plugin_path );
			$data  .= $plugin['Name'] . ': ' . $plugin['Version'] . $update . "\n";
		}

		return $data;
	}

	/**
	 * Get Server info.
	 *
	 * @return string
	 */
	private function server_info() {
		global $wpdb;

		// Server configuration (really just versions).
		$software = Params::server( 'SERVER_SOFTWARE', '' );
		$software = sanitize_text_field( wp_unslash( $software ) );

		$data  = "\n" . '-- Webserver Configuration' . "\n\n";
		$data .= $this->get_it_spaced( 'PHP Version:', PHP_VERSION, 14 );
		$data .= $this->get_it_spaced( 'MySQL Version', $wpdb->db_version(), 13 );
		$data .= $this->get_it_spaced( 'Webserver Info', $software, 12 );

		// PHP configs... now we're getting to the important stuff.
		$data .= "\n" . '-- PHP Configuration' . "\n\n";
		$data .= $this->get_it_spaced( 'Memory Limit', ini_get( 'memory_limit' ), 13 );
		$data .= $this->get_it_spaced( 'Upload Max Size', ini_get( 'upload_max_filesize' ), 10 );
		$data .= $this->get_it_spaced( 'Post Max Size', ini_get( 'post_max_size' ), 12 );
		$data .= $this->get_it_spaced( 'Upload Max Filesize', ini_get( 'upload_max_filesize' ), 6 );
		$data .= $this->get_it_spaced( 'Time Limit', ini_get( 'max_execution_time' ), 15 );
		$data .= $this->get_it_spaced( 'Max Input Vars', ini_get( 'max_input_vars' ), 11 );
		$data .= $this->get_it_spaced( 'Display Errors', ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ), 11 );

		// PHP extensions and such.
		$data .= "\n" . '-- PHP Extensions' . "\n\n";
		$data .= $this->get_it_spaced( 'cURL', function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported', 21 );
		$data .= $this->get_it_spaced( 'fsockopen', function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported', 16 );
		$data .= $this->get_it_spaced( 'SOAP Client', class_exists( 'SoapClient', false ) ? 'Installed' : 'Not Installed', 14 );
		$data .= $this->get_it_spaced( 'Suhosin', extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed', 18 );

		// Session stuff.
		$data .= "\n" . '-- Session Configuration' . "\n\n";
		$data .= $this->get_it_spaced( 'Session', isset( $_SESSION ) ? 'Enabled' : 'Disabled', 18 );

		// The rest of this is only relevant if session is enabled.
		if ( isset( $_SESSION ) ) {
			$data .= $this->get_it_spaced( 'Session Name', esc_html( ini_get( 'session.name' ) ), 13 );
			$data .= $this->get_it_spaced( 'Cookie Path', esc_html( ini_get( 'session.cookie_path' ) ), 14 );
			$data .= $this->get_it_spaced( 'Save Path', esc_html( ini_get( 'session.save_path' ) ), 16 );
			$data .= $this->get_it_spaced( 'Use Cookies', ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ), 14 );
			$data .= $this->get_it_spaced( 'Use Only Cookies', ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ), 9 );
		}

		return $data;
	}

	/**
	 * Consistent spacing in labels
	 *
	 * @param string $label Label of data.
	 * @param string $value Value of data.
	 * @param int    $space Space count.
	 *
	 * @return string
	 */
	private function get_it_spaced( $label, $value, $space = 9 ): string {
		return sprintf(
			'%1$s:%2$s%3$s' . "\n",
			$label,
			str_repeat( ' ', $space ),
			$value
		);
	}
}
