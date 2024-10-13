<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Tools extends Controller {
	public function __construct() {
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 100, 3 );
		add_action( 'admin_init', array( $this, 'run_tools_actions' ) );
	}

	public function transition_post_status( $new_status, $old_status, $post ) {
		$settings = $this->get_settings();

		// Ping IndexNow
		if ( $settings->enable_indexnow && 'publish' === $new_status ) {
			( new IndexNow() )->ping_url( get_permalink( $post ) );
		}
	}

	public function run_tools_actions() {
		if ( ! isset( $_POST['sgg_tools_nonce'] ) || ! wp_verify_nonce( $_POST['sgg_tools_nonce'], GRIM_SG_BASENAME . '-tools' ) ) {
			return;
		}

		if ( isset( $_POST['sgg-indexnow'] ) ) {
			$response = ( new IndexNow() )->ping_site_url();

			add_settings_error(
				'indexnow_notice',
				'indexnow_notice',
				$response['message'],
				$response['status']
			);
		} elseif ( isset( $_POST['sgg-flush-rewrite-rules'] ) ) {
			Frontend::activate_plugin();

			$this->add_admin_notice( __( 'WordPress Rewrite Rules flushed.', 'xml-sitemap-generator-for-google' ) );
		} elseif ( isset( $_POST['sgg-clear-cache'] ) ) {
			Cache::clear();

			$this->add_admin_notice( __( 'Sitemaps Cache cleared.', 'xml-sitemap-generator-for-google' ) );
		}
	}

	public function add_admin_notice( $message ) {
		add_settings_error( 'tools_admin_notice', 'tools_admin_notice', $message, 'success' );
	}
}
