<?php
/**
 * Update manger class
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework;

use InvalidArgumentException;

defined( 'ABSPATH' ) || exit;

/**
 * Updates class
 */
class Updates {

	/**
	 * Updates that need to be run.
	 *
	 * @var array
	 */
	private $updates = [];

	/**
	 * Folder path.
	 *
	 * @var string
	 */
	private $folder = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version = null;

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = null;

	/**
	 * Retrieve main instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @return Updates
	 */
	public static function get() {
		static $instance;

		if ( null === $instance && ! ( $instance instanceof Updates ) ) {
			$instance = new Updates();
		}

		return $instance;
	}

	/**
	 * Bind all hooks.
	 *
	 * @since 1.0.0
	 *
	 * @throws InvalidArgumentException When folder not defined.
	 * @throws InvalidArgumentException When version not defined.
	 * @throws InvalidArgumentException When option name not defined.
	 *
	 * @return Updates
	 */
	public function hooks() {
		if ( empty( $this->folder ) ) {
			throw new InvalidArgumentException( 'Please set the folder path for update files.' );
		}

		if ( empty( $this->version ) ) {
			throw new InvalidArgumentException( 'Please set the plugin version number.' );
		}

		if ( empty( $this->option_name ) ) {
			throw new InvalidArgumentException( 'Please set option name to save version in database.' );
		}

		add_action( 'admin_init', [ $this, 'do_updates' ] );

		return $this;
	}

	/**
	 * Set folder path
	 *
	 * @since  1.0.0
	 *
	 * @param  string $folder Folder path to look for updates.
	 * @return Updates
	 */
	public function set_folder( $folder ) {
		$this->folder = trailingslashit( $folder );

		return $this;
	}

	/**
	 * Set plugin version number
	 *
	 * @since  1.0.0
	 *
	 * @param  string $version Plugin version.
	 * @return Updates
	 */
	public function set_version( $version ) {
		$this->version = $version;

		return $this;
	}

	/**
	 * Set plugin option name.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $name Plugin option name.
	 * @return Updates
	 */
	public function set_option_name( $name ) {
		$this->option_name = $name;

		return $this;
	}

	/**
	 * Add updates database.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $updates Array of updates to be run.
	 * @return Updates
	 */
	public function add_updates( array $updates ) {
		$this->updates = $updates;

		return $this;
	}

	/**
	 * Check if need any update
	 *
	 * @since 1.0.0
	 */
	public function do_updates() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$installed_version = get_option( $this->option_name );

		// Maybe it's the first install.
		if ( ! $installed_version ) {
			$this->save_version();
			return false;
		}

		if ( version_compare( $installed_version, $this->version, '<' ) ) {
			$this->perform_updates();
		}
	}

	/**
	 * Perform all updates
	 *
	 * @since 1.0.0
	 */
	public function perform_updates() {
		$installed_version = get_option( $this->option_name );

		foreach ( $this->updates as $version => $path ) {
			if ( version_compare( $installed_version, $version, '<' ) ) {
				include $this->folder . $path;
				$this->save_version( $version );
			}
		}

		$this->save_version();
	}

	/**
	 * Save version info.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $version Version number to save.
	 */
	private function save_version( $version = false ) {
		if ( empty( $version ) ) {
			$version = $this->version;
		}

		update_option( $this->option_name, $this->version );
	}
}
