<?php
/**
 * The class is responsible for ajax functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick\Admin;

use WP_Error;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Modules\OneClick\Helpers;
use AdvancedAds\Modules\OneClick\Options;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Modules\OneClick\AdsTxt\Detector;
use AdvancedAds\Framework\Interfaces\Integration_Interface;
use AdvancedAds\Importers\Api_Ads;

defined( 'ABSPATH' ) || exit;

/**
 * Ajax.
 */
class Ajax implements Integration_Interface {

	/**
	 * API URL
	 *
	 * Production:  https://app.pubguru.com
	 * Development: https://new-stagingtools5.pubguru.com
	 *
	 * @var string
	 */
	const API_URL = 'https://app.pubguru.com/';

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'wp_ajax_search_posts', [ $this, 'search_posts' ] );
		add_action( 'wp_ajax_pubguru_connect', [ $this, 'pubguru_connect' ] );
		add_action( 'wp_ajax_pubguru_disconnect', [ $this, 'pubguru_disconnect' ] );
		add_action( 'wp_ajax_pubguru_module_change', [ $this, 'module_status_changed' ] );
		add_action( 'wp_ajax_pubguru_backup_ads_txt', [ $this, 'backup_ads_txt' ] );
		add_action( 'wp_ajax_update_oneclick_preview', [ $this, 'update_oneclick_preview' ] );
	}

	/**
	 * Init hook
	 *
	 * @return void
	 */
	public function init(): void {
		if ( Params::get( 'refresh_ads', false, FILTER_VALIDATE_BOOLEAN ) ) {
			$config = $this->pubguru_api_connect();

			if ( is_wp_error( $config ) ) {
				wp_die(
					$config->get_error_message(), // phpcs:ignore
					esc_html__( 'Refreshing PubGuru Ads', 'advanced-ads' ),
					$config->get_error_data() // phpcs:ignore
				);
			}
		}
	}

	/**
	 * PubGuru Connect
	 *
	 * @return void
	 */
	public function pubguru_connect(): void {
		check_ajax_referer( 'pubguru_oneclick_security', 'nonce' );

		$config = $this->pubguru_api_connect();

		if ( is_wp_error( $config ) ) {
			wp_send_json(
				[
					'success' => false,
					'code'    => $config->get_error_code(),
					'message' => $config->get_error_message(),
				],
				$config->get_error_data()
			);
		}

		// Default module enabled.
		Options::module( 'header_bidding', true );

		wp_send_json_success(
			[
				'message'       => esc_html__( 'We have successfully migrated your MonetizeMore PubGuru Ad Units to your WordPress site. The existing placements and ads have been paused.', 'advanced-ads' ),
				'hasTrafficCop' => Helpers::has_traffic_cop( $config ),
			]
		);
	}

	/**
	 * PubGuru Disconnect
	 *
	 * @return void
	 */
	public function pubguru_disconnect(): void {
		check_ajax_referer( 'pubguru_oneclick_security', 'nonce' );

		Options::pubguru_config( 'delete' );

		wp_send_json_success(
			[
				'message' => esc_html__( 'PubGuru successfully disconnected.', 'advanced-ads' ),
			]
		);
	}

	/**
	 * Handle module status changes
	 *
	 * @return void
	 */
	public function module_status_changed(): void {
		check_ajax_referer( 'pubguru_oneclick_security', 'security' );

		$module = Params::post( 'module', [] );
		$status = Params::post( 'status', false, FILTER_VALIDATE_BOOLEAN );

		Options::module( $module, $status );

		$data = apply_filters( 'pubguru_module_status_changed', [], $module, $status );

		wp_send_json_success( $data );
	}

	/**
	 * Handle module status changes
	 *
	 * @return void
	 */
	public function backup_ads_txt(): void {
		check_ajax_referer( 'pubguru_oneclick_security', 'security' );

		$result = ( new Detector() )->backup_file();
		if ( false === $result ) {
			$notice = sprintf(
				'<div class="flex items-center">'
				/* translators: 1 is the opening link to the Advanced Ads website, 2 the closing link */
				. __( 'The backup of your ads.txt file has failed. Please ensure that a manual backup is created You can find detailed instructions on how to manually back up your ads.txt file in the manual. %1$sManual%2$s', 'advanced-ads' ) // phpcs:ignore
				. '</div>',
				'<a href="https://wpadvancedads.com/manual/ads-txt/?utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-ads-txt-oci#Manual_backup_of_the_adstxt_file" target="_blank" class="button button-link ml-auto mr-2">',
				'</a>'
			);
			wp_send_json_error( $notice );
		}

		$notice = sprintf(
			'<div class="flex items-center">%s</div>',
			esc_html__( 'File successfully backed up.', 'advanced-ads' )
		);
		wp_send_json_success( $notice );
	}

	/**
	 * Search posts
	 *
	 * @return void
	 */
	public function search_posts(): void {
		global $wpdb;

		check_ajax_referer( 'pubguru_oneclick_security', 'security' );

		$search  = Params::get( 'q', '' );
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title FROM {$wpdb->posts}
				WHERE post_title LIKE %s
				AND post_status = 'publish'
				AND post_type IN ('post', 'page')",
				'%' . $wpdb->esc_like( $search ) . '%'
			)
		);

		$posts = [];
		foreach ( $results as $result ) {
			$posts[] = [
				'id'   => $result->ID,
				'text' => $result->post_title,
			];
		}

		wp_send_json( $posts );
	}

	/**
	 * Update OneClick preview
	 *
	 * @return void
	 */
	public function update_oneclick_preview(): void {
		check_ajax_referer( 'pubguru_oneclick_security', 'security' );

		$method = Params::post( 'method', 'page' );
		$page   = Params::post( 'page', 0, FILTER_VALIDATE_INT );
		$config = Options::pubguru_config();

		$config['method'] = $method;
		$config['page']   = $page;
		Options::pubguru_config( $config );

		// Importer.
		$importer = new Api_Ads();
		$importer->import();

		wp_send_json_success();
	}

	/**
	 * Fetch config from PubGuru api
	 *
	 * @return WP_Error|array
	 */
	private function pubguru_api_connect() {
		$domain   = Params::post( 'testDomain' ) ? Params::post( 'testDomain' ) : WordPress::get_site_domain();
		$domain   = str_replace( 'www.', '', $domain );
		$response = wp_remote_get(
			self::API_URL . 'domain_configs/?domain=' . $domain,
			[
				'timeout'   => 30,
				'sslverify' => false,
			]
		);

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new WP_Error(
				'connect_error',
				esc_html__( 'An error has occurred please try again.', 'advanced-ads' ),
				$response_code
			);
		}

		$config = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 'error' === $config['status'] ) {
			return new WP_Error(
				'domain_not_found',
				'Connection with PubGuru & MonetizeMore was unsuccessful. Please <a href="https://www.monetizemore.com/contact/">click here</a> to contact MonetizeMore Support or email us at <a href="mailto:support@monetizemore.com">support@monetizemore.com</a>',
				201
			);
		}

		$config['domain'] = $domain;
		Options::pubguru_config( $config );

		return $config;
	}
}
