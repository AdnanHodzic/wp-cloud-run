<?php
/**
 * Installation Compatibility.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Installation;

use AdvancedAds\Constants;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Installation Compatibility.
 */
class Compatibility implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if ( is_admin() ) {
			add_action( 'plugin_loaded', [ $this, 'deactivate_plugins' ], 0 );
		}
		add_filter( 'upgrader_post_install', [ $this, 'upgrader_post_install' ], 10, 3 );
	}

	/**
	 * Check if the plugin was updated and run compatibility checks.
	 *
	 * @param bool  $response   Installation response.
	 * @param array $hook_extra Extra arguments passed to hooked filters.
	 * @param array $result     Installation result data.
	 *
	 * @return bool
	 */
	public function upgrader_post_install( $response, $hook_extra, $result ): bool {
		if ( $response && isset( $result['source_files'] ) && in_array( 'advanced-ads.php', $result['source_files'], true ) ) {
			$this->deactivate_plugins();
		}

		return $response;
	}

	/**
	 * Deactivate plugins that are not compatible with the current version of Advanced Ads.
	 *
	 * @return void
	 */
	public function deactivate_plugins(): void {
		// Early bail!!
		if ( get_option( 'advanced-ads-2-compatibility-flag' ) ) {
			return;
		}

		$plugins = WordPress::get_wp_plugins();
		foreach ( Constants::ADDONS_NON_COMPATIBLE_VERSIONS as $version => $slug ) {
			$addon = $plugins[ $slug ] ?? null;
			if ( ! $addon || ! is_plugin_active( $addon['file'] ) ) {
				continue;
			}

			if ( version_compare( $addon['version'], $version, '<=' ) ) {
				\deactivate_plugins( plugin_basename( $addon['file'] ), true );
			}
		}

		// Add flag as if we upload or update the plugin using FTP this hook will not be triggered.
		update_option( 'advanced-ads-2-compatibility-flag', true );
	}
}
