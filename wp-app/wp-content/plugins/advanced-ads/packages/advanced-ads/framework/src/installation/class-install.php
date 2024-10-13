<?php
/**
 * Installation routine
 *
 * @package AdvancedAds\Installation
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Installation;

use WP_Site;
use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Install.
 */
abstract class Install implements Initializer_Interface {

	/**
	 * Plugin base file
	 *
	 * @var string
	 */
	protected $base_file = null;

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( null !== $this->base_file ) {
			register_activation_hook( $this->base_file, [ $this, 'activation' ] );
			register_deactivation_hook( $this->base_file, [ $this, 'deactivation' ] );

			add_action( 'wp_initialize_site', [ $this, 'initialize_site' ] );
		}
	}

	/**
	 * Activation routine.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public function activation( $network_wide = false ): void {
		register_uninstall_hook( $this->base_file, [ static::class, 'uninstall' ] );

		if ( ! is_multisite() || ! $network_wide ) {
			$this->activate();
			return;
		}

		$this->network_activate_deactivate( 'activate' );
	}

	/**
	 * Deactivation routine.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 *
	 * @return void
	 */
	public function deactivation( $network_wide = false ): void {
		if ( ! is_multisite() || ! $network_wide ) {
			$this->deactivate();
			return;
		}

		$this->network_activate_deactivate( 'deactivate' );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param WP_Site $site The new site's object.
	 *
	 * @return void
	 */
	public function initialize_site( $site ): void {
		switch_to_blog( $site->blog_id );
		$this->activate();
		restore_current_blog();
	}

	/**
	 * Run network-wide activation/deactivation of the plugin.
	 *
	 * @param string $action Action to perform.
	 *
	 * @return void
	 */
	private function network_activate_deactivate( $action ): void {
		global $wpdb;

		$site_ids = self::get_sites();

		if ( empty( $site_ids ) ) {
			return;
		}

		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );
			$this->$action();
			restore_current_blog();
		}
	}

	/**
	 * Get network sites
	 *
	 * @return array|int
	 */
	public static function get_sites() {
		global $wpdb;

		return get_sites(
			[
				'archived'   => 0,
				'spam'       => 0,
				'deleted'    => 0,
				'network_id' => $wpdb->siteid,
				'fields'     => 'ids',
			]
		);
	}

	/**
	 * Plugin activation callback.
	 *
	 * @return void
	 */
	abstract protected function activate(): void;

	/**
	 * Plugin deactivation callback.
	 *
	 * @return void
	 */
	abstract protected function deactivate(): void;

	/**
	 * Plugin uninstall callback.
	 *
	 * @return void
	 */
	abstract public static function uninstall(): void;
}
