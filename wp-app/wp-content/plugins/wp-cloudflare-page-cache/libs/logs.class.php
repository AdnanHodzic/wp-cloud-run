<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Logs {


	private $is_logging_enabled = false;
	private $log_file_path      = false;
	private $log_file_url       = false;

	private $verbosity = 1; // 1: standard, 2: high

	function __construct( $log_file_path, $log_file_url, $logging_enabled, $max_file_size, $main_instance ) {

		$this->log_file_path      = $log_file_path;
		$this->log_file_url       = $log_file_url;
		$this->is_logging_enabled = $logging_enabled;

		// Reset log if it exceeded the max file size
		if ( $max_file_size > 0 && file_exists( $log_file_path ) && ( filesize( $log_file_path ) / 1024 / 1024 ) >= $max_file_size ) {
			$this->reset_log();
		}

		$this->actions();

	}


	function actions() {

		// Ajax clear logs
		add_action( 'wp_ajax_swcfpc_clear_logs', [ $this, 'ajax_clear_logs' ] );

		// Download logs
		add_action( 'init', [ $this, 'download_logs' ] );

	}


	function enable_logging() {
		$this->is_logging_enabled = true;
	}


	function disable_logging() {
		$this->is_logging_enabled = false;
	}


	function set_verbosity( $verbosity ) {

		$verbosity = (int) $verbosity;

		if ( $verbosity != SWCFPC_LOGS_STANDARD_VERBOSITY && $verbosity != SWCFPC_LOGS_HIGH_VERBOSITY ) {
			$verbosity = SWCFPC_LOGS_STANDARD_VERBOSITY;
		}

		$this->verbosity = $verbosity;

	}


	function get_verbosity() {
		return $this->verbosity;
	}


	function add_log( $identifier, $message ) {

		if ( $this->is_logging_enabled && $this->log_file_path ) {

			$log = sprintf( '[%s] [%s] %s', current_time( 'Y-m-d H:i:s' ), $identifier, $message ) . PHP_EOL;

			error_log( $log, 3, $this->log_file_path );

		}

	}


	function get_logs() {

		$log = '';

		if ( $this->log_file_path ) {
			$log = file_get_contents( $this->log_file_path );
		}

		return $log;

	}


	function reset_log() {

		if ( $this->log_file_path ) {
			file_put_contents( $this->log_file_path, '' );
		}

	}


	function download_logs() {

		if ( isset( $_GET['swcfpc_download_log'] ) && file_exists( $this->log_file_path ) && current_user_can( 'manage_options' ) ) {

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

	}


	function ajax_clear_logs() {

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$return_array = [ 'status' => 'ok' ];

		if ( ! current_user_can( 'manage_options' ) ) {
			$return_array['status'] = 'error';
			$return_array['error']  = __( 'Permission denied', 'wp-cloudflare-page-cache' );
			die( json_encode( $return_array ) );
		}

		$this->reset_log();

		$return_array['success_msg'] = __( 'Log cleaned successfully', 'wp-cloudflare-page-cache' );

		die( json_encode( $return_array ) );

	}

}
