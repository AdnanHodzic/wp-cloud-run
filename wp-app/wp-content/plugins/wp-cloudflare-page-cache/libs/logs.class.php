<?php

use SPC\Constants;
use SPC\Services\Settings_Store;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Logs {

	/**
	 * Whether logging is enabled.
	 *
	 * @var bool
	 */
	private $is_logging_enabled = false;

	/**
	 * Log file path.
	 *
	 * @var string
	 */
	private $log_file_path;

	/**
	 * Log verbosity.
	 *
	 * 1: Standard
	 * 2: High
	 *
	 * @var int
	 */
	private $verbosity = 1;

	public function __construct( SW_CLOUDFLARE_PAGECACHE $main_instance ) {
		$settings_store = Settings_Store::get_instance();

		$this->log_file_path      = $main_instance->get_plugin_wp_content_directory() . '/debug.log';
		$this->is_logging_enabled = $settings_store->get( Constants::SETTING_LOG_ENABLED );
		$max_file_size            = $settings_store->get( Constants::SETTING_LOG_MAX_FILESIZE );

		// Reset log if it exceeded the max file size
		if ( $max_file_size > 0 && file_exists( $this->log_file_path ) && ( filesize( $this->log_file_path ) / 1024 / 1024 ) >= $max_file_size ) {
			$this->reset_log();
		}

		$this->set_verbosity( $settings_store->get( Constants::SETTING_LOG_VERBOSITY ) );
		$this->actions();
	}

	private function actions() {
		add_action( 'init', [ $this, 'download_logs' ] );
	}

	public function enable_logging() {
		$this->is_logging_enabled = true;
	}

	public function disable_logging() {
		$this->is_logging_enabled = false;
	}

	/**
	 * Set log verbosity.
	 *
	 * @param int $verbosity Verbosity.
	 *
	 * @return void
	 */
	public function set_verbosity( $verbosity ) {
		$verbosity = (int) $verbosity;

		if ( ! in_array( $verbosity, [ SWCFPC_LOGS_STANDARD_VERBOSITY, SWCFPC_LOGS_HIGH_VERBOSITY ], true ) ) {
			$verbosity = SWCFPC_LOGS_STANDARD_VERBOSITY;
		}

		$this->verbosity = $verbosity;
	}

	/**
	 * Add log line.
	 *
	 * @param string $identifier Identifier.
	 * @param string $message Message.
	 * @param bool $only_verbose Only log in high verbosity mode.
	 *
	 * @return void
	 */
	public function add_log( $identifier, $message, $only_verbose = false ) {
		if (
			! $this->is_logging_enabled ||
			! $this->log_file_path ||
			$only_verbose && $this->verbosity !== SWCFPC_LOGS_HIGH_VERBOSITY
		) {
			return;
		}

		$log = sprintf( '[%s] [%s] %s', gmdate( 'Y-m-d H:i:s' ), $identifier, $message ) . PHP_EOL;

		error_log( $log, 3, $this->log_file_path );
	}


	/**
	 * Get log.
	 *
	 * @param int $last_lines Last lines to return.
	 *
	 * @return string
	 */
	public function get_logs( $last_lines = 0 ) {
		if ( ! $this->log_file_path ) {
			return '';
		}

		$log = file_get_contents( $this->log_file_path );

		if ( ! is_string( $log ) ) {
			return '';
		}

		if ( $last_lines > 0 ) {
			$log = explode( PHP_EOL, $log );
			$log = array_slice( $log, -$last_lines );
			$log = implode( PHP_EOL, $log );
		}

		return $log;
	}

	/**
	 * Reset log file.
	 *
	 * @return void
	 */
	public function reset_log() {
		if ( ! $this->log_file_path ) {
			return;
		}
		file_put_contents( $this->log_file_path, '' );
	}

	/**
	 * Download or view logs.
	 *
	 * @return void
	 */
	public function download_logs() {
		if ( ! isset( $_GET['swcfpc_download_log'] ) || ! file_exists( $this->log_file_path ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $_GET['swcfpc_download_log'] === 'view' ) {
			echo '<pre>' . file_get_contents( $this->log_file_path ) . '</pre>';

			exit;
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=debug.log' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Connection: Keep-Alive' );
		header( 'Expires: 0' );
		header( 'Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0, s-maxage=0' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $this->log_file_path ) );
		readfile( $this->log_file_path );

		exit;
	}

	/**
	 * Get log file path.
	 *
	 * @return string
	 */
	public function get_log_file_path() {
		return $this->log_file_path;
	}
}
