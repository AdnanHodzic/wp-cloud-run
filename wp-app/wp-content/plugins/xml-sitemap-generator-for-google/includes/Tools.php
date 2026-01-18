<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Tools extends Controller {
	public function __construct() {
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 100, 3 );
	}

	public function transition_post_status( $new_status, $old_status, $post ) {
		$settings = $this->get_settings();

		// Ping IndexNow
		if ( $settings->enable_indexnow && 'publish' === $new_status ) {
			( new IndexNow() )->ping_url( get_permalink( $post ) );
		}
	}

	public static function run_tools_actions( $data = array() ) {
		if ( ! empty( $data['sgg-indexnow'] ) ) {
			$response = ( new IndexNow() )->ping_site_url();

			add_settings_error(
				Controller::$slug,
				'indexnow_notice',
				$response['message'],
				$response['status']
			);

			return true;
		} elseif ( ! empty( $data['sgg-flush-rewrite-rules'] ) ) {
			Frontend::activate_plugin();

			self::add_admin_notice( __( 'WordPress Rewrite Rules flushed.', 'xml-sitemap-generator-for-google' ) );

			return true;
		} elseif ( ! empty( $data['sgg-clear-cache'] ) ) {
			Cache::clear();

			self::add_admin_notice( __( 'Sitemaps Cache cleared.', 'xml-sitemap-generator-for-google' ) );

			return true;
		}

		return false;
	}

	public static function add_admin_notice( $message ) {
		add_settings_error( Controller::$slug, 'sitemap_tools', $message, 'success' );
	}
}
