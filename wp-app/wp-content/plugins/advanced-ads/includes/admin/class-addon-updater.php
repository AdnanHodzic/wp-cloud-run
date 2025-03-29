<?php
/**
 * Admin Addon Updater.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads_Admin_Licenses;
use AdvancedAds\Utilities\Data;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Addon Updater.
 */
class Addon_Updater implements Integration_Interface {

	/**
	 * Get the license manager.
	 *
	 * @var \Advanced_Ads_Admin_Licenses
	 */
	private $manager = null;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		$this->manager = Advanced_Ads_Admin_Licenses::get_instance();

		if ( ! wp_doing_ajax() ) {
			add_action( 'load-plugins.php', [ $this, 'plugin_licenses_warning' ] );
		}

		if ( ! wp_doing_ajax() ) {
			add_action( 'admin_init', [ $this, 'add_on_updater' ], 1 );
		}
		add_action( 'advanced-ads-settings-init', [ $this, 'add_license_fields' ], 99 );
	}

	/**
	 * Register the Updater class for every add-on, which includes getting version information
	 */
	public function add_on_updater() {
		// Ignore, if not main blog or if updater was disabled.
		if ( ( is_multisite() && ! is_main_site() ) || ! apply_filters( 'advanced-ads-add-ons-updater', true ) ) {
			return;
		}

		$add_ons = Data::get_addons();
		foreach ( $add_ons as $_add_on ) {
			$_add_on_key  = $_add_on['id'];
			$options_slug = $_add_on['options_slug'];

			// Check if a license expired over time.
			$expiry_date = $this->manager->get_license_expires( $options_slug );
			$now         = time();
			if ( $expiry_date && 'lifetime' !== $expiry_date && strtotime( $expiry_date ) < $now ) {
				// Remove license status.
				delete_option( $options_slug . '-license-status' );
			}

			// Retrieve our license key.
			$licenses    = get_option( ADVADS_SLUG . '-licenses', [] );
			$license_key = $licenses[ $_add_on_key ] ?? '';

			( new EDD_Updater(
				Advanced_Ads_Admin_Licenses::API_ENDPOINT,
				$_add_on['path'],
				[
					'version' => $_add_on['version'],
					'license' => $license_key,
					'item_id' => Advanced_Ads_Admin_Licenses::ADDON_SLUGS_ID[ $options_slug ] ?? false,
					'author'  => 'Advanced Ads',
				]
			) );
		}
	}

	/**
	 * Initiate plugin checks
	 *
	 * @since 1.7.12
	 *
	 * @return void
	 */
	public function plugin_licenses_warning(): void {
		if ( is_multisite() ) {
			return;
		}

		$add_ons = Data::get_addons();
		foreach ( $add_ons as $_add_on ) {
			if ( $this->manager->get_license_status( $_add_on['options_slug'] ) !== 'valid' ) {
				$plugin_file = plugin_basename( $_add_on['path'] );
				add_action( 'after_plugin_row_' . $plugin_file, [ $this, 'add_plugin_list_license_notice' ], 10, 2 );
			}
		}
	}

	/**
	 * Add a row below add-ons with an invalid license on the plugin list
	 *
	 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data.
	 *
	 * @since 1.7.12
	 * @todo  make this work on multisite as well
	 *
	 * @return void
	 */
	public function add_plugin_list_license_notice( $plugin_file, $plugin_data ): void {
		static $cols;
		if ( null === $cols ) {
			$cols = count( _get_list_table( 'WP_Plugins_List_Table' )->get_columns() );
		}

		printf(
			'<tr class="advads-plugin-update-tr plugin-update-tr active"><td class="plugin-update colspanchange" colspan="%d"><div class="update-message notice inline notice-warning notice-alt"><p>%s</p></div></td></tr>',
			esc_attr( $cols ),
			wp_kses_post(
				sprintf(
					/* Translators: 1: add-on name 2: admin URL to license page */
					__( 'There might be a new version of %1$s. Please <strong>provide a valid license key</strong> in order to receive updates and support <a href="%2$s">on this page</a>.', 'advanced-ads' ),
					$plugin_data['Title'],
					admin_url( 'admin.php?page=advanced-ads-settings#top#licenses' )
				)
			)
		);
	}

	/**
	 * Add license fields to the settings page.
	 *
	 * @return void
	 */
	public function add_license_fields(): void {
		$add_ons = Data::get_addons();
		foreach ( $add_ons as $data ) {
			if ( 'responsive' === $data['id'] ) {
				continue;
			}

			add_settings_field(
				$data['id'] . '-license',
				$data['name'],
				[ $this, 'render_license_field' ],
				'advanced-ads-settings-license-page',
				'advanced_ads_settings_license_section',
				$data
			);
		}
	}

	/**
	 * Render license key field
	 *
	 * @param array $data add-on data.
	 *
	 * @return void
	 */
	public function render_license_field( $data ): void {
		$id             = $data['id'];
		$licenses       = $this->manager->get_licenses();
		$license_key    = $licenses[ $id ] ?? '';
		$options_slug   = $data['options_slug'];
		$license_status = $this->manager->get_license_status( $data['options_slug'] );
		$index          = $id;
		$plugin_name    = $data['name'];
		$plugin_url     = $data['uri'];

		include ADVADS_ABSPATH . 'admin/views/setting-license.php';
	}
}
