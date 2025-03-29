<?php
// phpcs:ignoreFile

/**
 * Alternative version installer.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use stdClass;
use WP_Error;
use Plugin_Upgrader;
use Automatic_Upgrader_Skin;

defined( 'ABSPATH' ) || exit;

/**
 * Alternate plugin version installer
 */
class Plugin_Installer {
	/**
	 * The version to install
	 *
	 * @var string
	 */
	private $version;

	/**
	 * URL to the .zip archive for the desired version
	 *
	 * @var string
	 */
	private $package_url;

	/**
	 * The plugin name
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * The plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Constructor
	 *
	 * @param string $version     The version to install.
	 * @param string $package_url The url to the .zip archive on https://wordpress.org.
	 */
	public function __construct( $version, $package_url ) {
		$this->version     = $version;
		$this->package_url = $package_url;
		$this->plugin_name = ADVADS_PLUGIN_BASENAME;
		$this->plugin_slug = basename( ADVADS_FILE ) . '.php';
	}

	/**
	 * Apply package.
	 *
	 * Change the plugin data when WordPress checks for updates. This method
	 * modifies package data to update the plugin from a specific URL containing
	 * the version package.
	 */
	protected function apply_package() {
		$update_plugins = get_site_transient( 'update_plugins' );
		if ( ! is_object( $update_plugins ) ) {
			$update_plugins = new stdClass();
		}

		$plugin_info              = new stdClass();
		$plugin_info->new_version = $this->version;
		$plugin_info->slug        = $this->plugin_slug;
		$plugin_info->package     = $this->package_url;

		$update_plugins->response[ $this->plugin_name ] = $plugin_info;

		set_site_transient( 'update_plugins', $update_plugins );
	}

	/**
	 * Do the plugin update process
	 *
	 * @return array|bool|WP_Error
	 */
	public function install() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$this->apply_package();

		$upgrader_args = [
			'url'    => 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $this->plugin_name ),
			'plugin' => $this->plugin_name,
			'nonce'  => 'upgrade-plugin_' . $this->plugin_name,
			'title'  => esc_html__( 'Rollback to Previous Version', 'advanced-ads' ),
		];

		$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin( $upgrader_args ) );

		return $upgrader->upgrade( $this->plugin_name );
	}
}
