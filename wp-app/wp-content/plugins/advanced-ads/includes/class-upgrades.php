<?php
/**
 * Upgrades.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds;

use AdvancedAds\Framework\Updates;
use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Upgrades.
 */
class Upgrades extends Updates implements Initializer_Interface {

	const DB_VERSION = '1.53.0';

	/**
	 * Get updates that need to run.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_updates(): array {
		return [
			'1.48.4' => 'upgrade-1.48.4.php',
			'1.48.5' => 'upgrade-1.48.5.php',
			'1.52.1' => 'upgrade-1.52.1.php',
			'2.0.0'  => 'upgrade-2.0.0.php',
		];
	}

	/**
	 * Get folder path
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_folder(): string {
		return ADVADS_ABSPATH . 'upgrades/';
	}

	/**
	 * Get plugin version number
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_version(): string {
		return self::DB_VERSION;
	}

	/**
	 * Get plugin option name.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_option_name(): string {
		return 'advanced_ads_db_version';
	}

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		// Force run the upgrades.
		$is_first_time = empty( $this->get_installed_version() );
		$this->hooks();

		if ( $is_first_time ) {
			update_option( $this->get_option_name(), '1.0.0' );
			add_action( 'admin_init', [ $this, 'perform_updates' ] );
		}
	}
}
