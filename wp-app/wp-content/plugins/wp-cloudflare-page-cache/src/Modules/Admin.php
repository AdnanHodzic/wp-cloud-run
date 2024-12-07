<?php

namespace SPC\Modules;

use SPC\Constants;

class Admin implements Module_Interface {

	private const RESET_RULE_ACTION_KEY = 'spc_reset_rule';

	public function init() {
		add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( SPC_PATH ), [ $this, 'add_plugin_action_links' ] );
		add_action( 'admin_init', [ $this, 'redirect_to_settings' ] );
		add_action( 'wp_ajax_swcfpc_test_page_cache', [ $this, 'ajax_test_page_cache' ] );
		add_action( 'admin_notices', [ $this, 'failed_rule_update_notice' ] );
		add_action( 'admin_init', [ $this, 'reset_cf_rule' ] );
	}

	/**
	 * Redirect to the settings page after activation.
	 */
	public function redirect_to_settings() {
		if ( ! get_option( \SW_CLOUDFLARE_PAGECACHE::REDIRECT_KEY, false ) ) {
			return;
		}

		delete_option( \SW_CLOUDFLARE_PAGECACHE::REDIRECT_KEY );
		wp_safe_redirect( admin_url( 'options-general.php?page=wp-cloudflare-super-page-cache-index' ) );

		exit;
	}

	/**
	 * Adds settings link to the plugins page.
	 *
	 * @param string[] $links The plugin action links.
	 *
	 * @return array|string[]
	 */
	public function add_plugin_action_links( $links ) {
		if ( is_array( $links ) ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( admin_url( 'options-general.php?page=wp-cloudflare-super-page-cache-index' ) ),
				__( 'Settings', 'wp-cloudflare-page-cache' )
			);
		}

		return $links;
	}

	/**
	 * Adds plugin meta links.
	 *
	 * @param array $meta_fields The plugin meta fields.
	 * @param string $file The plugin file.
	 *
	 * @return array
	 */
	public function add_plugin_meta_links( $meta_fields, $file ) {
		if ( plugin_basename( SPC_PATH ) === $file && is_array( $meta_fields ) ) {
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="#ffb900" stroke="#ffb900" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';

			$meta_fields[] = sprintf(
				'<a href="%s" target="_blank" title="%s"><i>%s</i></a>',
				esc_url( SWCFPC_PLUGIN_REVIEWS_URL . '?rate=5#new-post' ),
				esc_html__( 'Rate', 'wp-cloudflare-page-cache' ),
				str_repeat( $svg, 5 )
			);
		}

		return $meta_fields;
	}

	public function ajax_test_page_cache() {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE
		 */
		global $sw_cloudflare_pagecache;

		check_ajax_referer( 'ajax-nonce-string', 'security' );

		$return_array = [ 'status' => 'ok' ];

		$test_file_url = SWCFPC_PLUGIN_URL . 'assets/testcache.html';
		$tester        = new \SWCFPC_Test_Cache( $test_file_url );

		$disk_cache_error = false;
		$cloudflare_error = false;
		$status_messages  = [];
		$cache_issues     = [];

		$is_disk_cache_enabled = $sw_cloudflare_pagecache->get_single_config( 'cf_fallback_cache' );
		$is_cloudflare_enabled = (
			! empty( $sw_cloudflare_pagecache->get_single_config( 'cf_page_rule_id' ) ) ||
			! empty( $sw_cloudflare_pagecache->get_single_config( 'cf_cache_settings_ruleset_rule_id' ) ) ||
			! empty( $sw_cloudflare_pagecache->get_single_config( 'cf_woker_route_id' ) )
		);

		if ( ! $is_cloudflare_enabled ) {
			$status_messages[] = [
				'status'  => 'info',
				'message' => __( 'Cloudflare (Cache Rule or Worker) is not enabled.', 'wp-cloudflare-page-cache' ),
			];
		}

		// Check Cloudflare if it is possible.
		if ( $is_cloudflare_enabled ) {
			if ( ! $tester->check_cloudflare_cache() ) {
				$cloudflare_error  = true;
				$cache_issues      = $tester->get_errors();
				$status_messages[] = [
					'status'  => 'error',
					'message' => __( 'Cloudflare integration has an issue.', 'wp-cloudflare-page-cache' ),
				];
			} else {
				$status_messages[] = [
					'status'  => 'success',
					'message' => __( 'Cloudflare Page Caching is working properly.', 'wp-cloudflare-page-cache' ),
				];
			}
		}

		// Check Fallback cache.
		if ( ! $is_disk_cache_enabled ) {
			$status_messages[] = [
				'status'  => 'info',
				'message' => __( 'Disk Page Cache is not enabled.', 'wp-cloudflare-page-cache' ),
			];
		}

		if ( $is_disk_cache_enabled ) {

			/**
			 * @var \SWCFPC_Fallback_Cache $fallback_cache
			 */
			$fallback_cache = $sw_cloudflare_pagecache->get_modules()['fallback_cache'];

			$fallback_cache->fallback_cache_add_current_url_to_cache( $test_file_url, true );
			$disk_cache_error = ! $fallback_cache->fallback_cache_check_cached_page( $test_file_url );

			if ( $disk_cache_error ) {
				$cache_issues[]    = __( 'Could not cache the page on the disk. [Page Disk Cache]', 'wp-cloudflare-page-cache' );
				$status_messages[] = [
					'status'  => 'error',
					'message' => __( 'Disk Page Caching has an issue.', 'wp-cloudflare-page-cache' ),
				];
			} else {
				$status_messages[] = [
					'status'  => 'success',
					'message' => __( 'Disk Page Caching is functional.', 'wp-cloudflare-page-cache' ),
				];
			}
		}

		$html_response = '<div class="swcfpc-test-response">';

		if ( ! empty( $status_messages ) ) {
			$html_response .= '<div class="test-container">';
			$html_response .= '<h3>' . __( 'Status', 'wp-cloudflare-page-cache' ) . '</h3>';
			$html_response .= '<ul>';

			foreach ( $status_messages as $status ) {
				$html_response .= '<li class="is-' . $status['status'] . '">' . $status['message'] . '</li>';
			}

			$html_response .= '</ul>';
			$html_response .= '</div>';
		}

		if ( ! empty( $cache_issues ) ) {
			$html_response .= '<div class="test-container">';
			$html_response .= '<h3>' . __( 'Issues', 'wp-cloudflare-page-cache' ) . '</h3>';
			$html_response .= '<ul>';
			foreach ( $cache_issues as $issue ) {
				$html_response .= '<li class="is-error">' . $issue . '</li>';
			}
			$html_response .= '</ul>';

			if ( $cloudflare_error ) {
				$html_response .= '<p>' . __( 'Please check if the page caching is working by yourself by surfing the website in incognito mode \'cause sometimes Cloudflare bypass the cache for cURL requests. Reload a page two or three times. If you see the response header <strong>cf-cache-status: HIT</strong>, the page caching is working well.', 'wp-cloudflare-page-cache' ) . '</p>';
			}

			if ( $is_cloudflare_enabled ) {
				$html_response .= '<p><a href="' . esc_url( $test_file_url ) . '" target="_blank">' . __( 'Cloudflare Test Page', 'wp-cloudflare-page-cache' ) . '</a></p>';
			}
			$html_response .= '</div>';
		}

		$html_response .= '</div>';

		$return_array['html'] = $html_response;

		if (
			! empty( $cache_issues ) ||
			( ! $is_cloudflare_enabled && ! $is_disk_cache_enabled )
		) {
			$return_array['status'] = 'error';
		}

		die( json_encode( $return_array ) );
	}

	/**
	 * Failed rule update notice.
	 *
	 * @return void
	 */
	public function failed_rule_update_notice() {
		if (
			! get_option( Constants::KEY_RULE_UPDATE_FAILED, false ) ||
			! current_user_can( 'manage_options' )
		) {
			return;
		}

		$args = [
			'page'       => 'wp-cloudflare-super-page-cache-index',
			'active_tab' => 'general',
		];

		$nonce          = wp_create_nonce( self::RESET_RULE_ACTION_KEY );
		$admin_page_url = add_query_arg( $args, admin_url( 'options-general.php' ) );


		?>
		<style>
			.spc-rule {
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				padding: 20px;
			}

			.spc-rule .actions {
				display: flex;
				gap: 20px;
				margin-top: 10px;
				align-items: center;
			}
		</style>
		<script>
		  document.addEventListener('DOMContentLoaded', function () {
			const ruleFixForm = document.querySelector('.spc-rule .actions form')

			if (ruleFixForm && window.swcfpc_lock_screen) {
			  ruleFixForm.addEventListener('submit', function () {
				window.swcfpc_lock_screen();
			  });
			}
		  });
		</script>
		<div class="notice notice-warning spc-rule">
			<div>
				<h3><?php esc_html_e( 'It seems that Super Page Cache failed to update the Cloudflare cache rule.', 'wp-cloudflare-page-cache' ); ?></h3>

				<p>
					<?php
					// translators: %s: Enable Cloudflare CDN & Caching
					echo sprintf( __( 'We can attempt to reset the rule automatically for you, or you could toggle the %s setting on and off to fix this.', 'wp-cloudflare-page-cache' ), sprintf( '<code>%s</code>', __( 'Enable Cloudflare CDN & Caching', 'wp-cloudflare-page-cache' ) ) );
					?>
				</p>
			</div>

			<div class="actions">
				<?php if ( ! isset( $_GET['page'] ) || 'wp-cloudflare-super-page-cache-index' !== sanitize_text_field( $_GET['page'] ) ) { ?>
					<a href="<?php echo esc_url( $admin_page_url ); ?>"
					   class="button button-secondary"><?php _e( 'Settings page', 'wp-cloudflare-page-cache' ); ?></a>
				<?php } else { ?>
					<form action="<?php echo esc_url( $admin_page_url ); ?>" method="post">
						<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
						<input type="hidden" name="action" value="<?php echo esc_attr( 'swcfpc_reset_cf_rule' ); ?>">
						<button type="submit" class="button button-primary">
							<?php esc_attr_e( 'Fix Rule', 'wp-cloudflare-page-cache' ); ?>
						</button>
					</form>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Reset the Cloudflare cache rule.
	 *
	 * @return void
	 */
	public function reset_cf_rule() {
		if (
			! current_user_can( 'manage_options' ) ||
			! get_option( Constants::KEY_RULE_UPDATE_FAILED, false ) ||
			! current_user_can( 'manage_options' ) ||
			! isset( $_POST['action'] ) ||
			'swcfpc_reset_cf_rule' !== sanitize_text_field( $_POST['action'] ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), self::RESET_RULE_ACTION_KEY )
		) {
			return;
		}

		/**
		 * @type \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$error = '';

		$status = $sw_cloudflare_pagecache->get_cloudflare_handler()->reset_cf_rule( $error );

		if ( ! empty( $error ) ) {
			add_action(
				'admin_notices',
				function () use ( $error ) {
					?>
					<div class="notice notice-error">
						<p><?php echo esc_html( $error ); ?></p>
					</div>
					<?php
				}
			);
		}


		if ( $status ) {
			delete_option( Constants::KEY_RULE_UPDATE_FAILED );

			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-success">
						<p><?php esc_html_e( 'The Cloudflare cache rule has been reset successfully.', 'wp-cloudflare-page-cache' ); ?></p>
					</div>
					<?php
				}
			);
		}
	}


	/**
	 * Get third party compatibilities.
	 *
	 * @return array
	 */
	public static function get_third_party_view_map() {
		/**
		 * @var $sw_cloudflare_pagecache \SW_CLOUDFLARE_PAGECACHE
		 */
		global $sw_cloudflare_pagecache;

		return apply_filters(
			'swcfpc_admin_third_party_compatibilities_view_map',
			[
				'woocommerce'       => is_plugin_active( 'woocommerce/woocommerce.php' ),
				'edd'               => is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ),
				'autoptimize'       => is_plugin_active( 'autoptimize/autoptimize.php' ),
				'w3tc'              => is_plugin_active( 'w3-total-cache/w3-total-cache.php' ),
				'litespeed_cache'   => is_plugin_active( 'litespeed-cache/litespeed-cache.php' ),
				'hummingbird'       => is_plugin_active( 'hummingbird-performance/wp-hummingbird.php' ),
				'wp_optimize'       => is_plugin_active( 'wp-optimize/wp-optimize.php' ),
				'flying_press'      => is_plugin_active( 'flying-press/flying-press.php' ),
				'wp_rocket'         => is_plugin_active( 'wp-rocket/wp-rocket.php' ),
				'wp_asset_cleanup'  => is_plugin_active( 'wp-asset-clean-up/wpacu.php' ),
				'nginx_helper'      => is_plugin_active( 'nginx-helper/nginx-helper.php' ),
				'wp_performance'    => is_plugin_active( 'wp-performance/wp-performance.php' ),
				'yasr'              => is_plugin_active( 'yet-another-stars-rating/yet-another-stars-rating.php' ) || is_plugin_active( 'yet-another-stars-rating-premium/yet-another-stars-rating.php' ),
				'swift_performance' => is_plugin_active( 'swift-performance-lite/performance.php' ) || is_plugin_active( 'swift-performance/performance.php' ),
				'siteground'        => $sw_cloudflare_pagecache->get_cache_controller()->is_siteground_supercacher_enabled(),
				'wp_engine'         => $sw_cloudflare_pagecache->get_cache_controller()->can_wpengine_cache_be_purged(),
				'spinup_wp'         => $sw_cloudflare_pagecache->get_cache_controller()->can_spinupwp_cache_be_purged(),
				'kinsta'            => $sw_cloudflare_pagecache->get_cache_controller()->can_kinsta_cache_be_purged(),
			]
		);
	}

	/**
	 * Checks if the third party tab should be loaded.
	 *
	 * @return bool
	 */
	public static function should_load_third_party_tab() {
		foreach ( self::get_third_party_view_map() as $view_id => $enabled ) {
			if ( $enabled ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the zone id list for display.
	 *
	 * @return array
	 */
	public static function get_zone_id_list_for_display() {
		/**
		 * @var $sw_cloudflare_pagecache \SW_CLOUDFLARE_PAGECACHE
		 */
		global $sw_cloudflare_pagecache;

		$zone_id_list = $sw_cloudflare_pagecache->get_single_config( 'cf_zoneid_list', [] );

		if ( ! is_array( $zone_id_list ) || empty( $zone_id_list ) ) {
			return [];
		}

		return $zone_id_list;
	}

	/**
	 * Get the cronjob URL.
	 *
	 * @param string $type The type of link purge|preloader
	 *
	 * @return string
	 */
	public static function get_cronjob_url( $type = 'purge' ) {
		/**
		 * @var $sw_cloudflare_pagecache \SW_CLOUDFLARE_PAGECACHE
		 */
		global $sw_cloudflare_pagecache;

		$args = $type === 'purge' ? [
			'swcfpc-purge-all' => '1',
			'swcfpc-sec-key'   => $sw_cloudflare_pagecache->get_single_config( 'cf_purge_url_secret_key', wp_generate_password( 20, false, false ) ),
		] : [
			'swcfpc-preloader' => '1',
			'swcfpc-sec-key'   => $sw_cloudflare_pagecache->get_single_config( 'cf_preloader_url_secret_key', wp_generate_password( 20, false, false ) ),
		];

		if ( (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_REMOVE_CACHE_BUSTER, 1 ) !== 1 ) {
			$args[ $sw_cloudflare_pagecache->get_cache_controller()->get_cache_buster() ] = '1';
		}

		return add_query_arg( $args, site_url() );
	}

	/**
	 * Get the third party tabs.
	 *
	 * @return array
	 */
	public static function get_admin_tabs() {
		/**
		 * @var $sw_cloudflare_pagecache \SW_CLOUDFLARE_PAGECACHE
		 */
		global $sw_cloudflare_pagecache;
		$tabs = [
			[
				'id'       => 'cache',
				'template' => 'admin_cache_tab',
				'label'    => __( 'Cache', 'wp-cloudflare-page-cache' ),
			],
			[
				'id'          => 'general',
				'template'    => 'admin_cloudflare_tab',
				'label'       => __( 'Cloudflare (CDN & Edge Caching)', 'wp-cloudflare-page-cache' ),
				'tab_classes' => $sw_cloudflare_pagecache->has_cloudflare_api_zone_id() ? 'swcfpc_hide' : '',
			],
			[
				'id'          => 'advanced',
				'template'    => 'admin_advanced_tab',
				'label'       => __( 'Advanced', 'wp-cloudflare-page-cache' ),
				'tab_classes' => 'show_advanced',
			],
			[
				'id'       => 'javascript',
				'template' => 'admin_js_tab',
				'label'    => __( 'Javascript', 'wp-cloudflare-page-cache' ),
				'locked'   => ! defined( 'SPC_PRO_PATH' ),
			],
			[
				'id'       => 'media',
				'template' => 'admin_media_tab',
				'label'    => __( 'Media', 'wp-cloudflare-page-cache' ),
			],
			[
				'id'       => 'thirdparty',
				'template' => 'admin_third_party_tab',
				'label'    => __( 'Third Party', 'wp-cloudflare-page-cache' ),
				'enabled'  => self::should_load_third_party_tab(),
			],
			[
				'id'       => 'faq',
				'template' => 'admin_faq_tab',
				'label'    => __( 'FAQ', 'wp-cloudflare-page-cache' ),
			],
			[
				'id'       => 'image_optimization',
				'template' => 'optimole',
				'label'    => __( 'Image Optimization', 'wp-cloudflare-page-cache' ),
				'enabled'  => ! defined( 'OPTML_VERSION' ),
			],
		];

		$tabs = apply_filters( 'swcfpc_admin_tabs', $tabs );

		return array_filter(
			$tabs,
			function( $tab ) {
				return ! isset( $tab['enabled'] ) || $tab['enabled'] === true;
			} 
		);
	}
}
