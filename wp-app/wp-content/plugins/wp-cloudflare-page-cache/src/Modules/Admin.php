<?php

namespace SPC\Modules;

use SPC\Constants;
use SPC\Services\SDK_Integrations;
use SPC\Utils\Assets_Handler;
use SPC\Utils\I18n;

class Admin implements Module_Interface {

	public const CONFLICTS_QUERY_ARG = 'spc_conflicts';

	/**
	 * SDK service.
	 *
	 * @var SDK_Integrations $sdk_service
	 */
	private $sdk_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->sdk_service = new SDK_Integrations();
	}

	public function init() {
		add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( SPC_PATH ), [ $this, 'add_plugin_action_links' ] );
		add_action( 'admin_init', [ $this, 'redirect_to_settings' ] );
		add_action( 'admin_notices', [ $this, 'failed_rule_update_notice' ] );
		add_filter( 'all_plugins', [ $this, 'filter_conflicting_plugins' ] );

		add_filter( $this->sdk_service->get_product_key() . '_logger_data', [ $this->sdk_service, 'get_logger_data' ] );
		add_filter(
			$this->sdk_service->get_product_key() . '_about_us_metadata',
			[ $this->sdk_service, 'get_about_us_metadata' ]
		);
	}

	/**
	 * Redirect to the settings page after activation.
	 */
	public function redirect_to_settings() {
		if ( ! get_option( \SW_CLOUDFLARE_PAGECACHE::REDIRECT_KEY, false ) ) {
			return;
		}

		delete_option( \SW_CLOUDFLARE_PAGECACHE::REDIRECT_KEY );
		wp_safe_redirect( admin_url( 'admin.php?page=' . Dashboard::PAGE_SLUG ) );

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
				esc_url(
					add_query_arg(
						'page',
						Dashboard::PAGE_SLUG . '-settings',
						admin_url( 'admin.php' )
					)
				),
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

	/**
	 * Failed rule update notice.
	 *
	 * @return void
	 */
	public function failed_rule_update_notice() {
		$screen = get_current_screen();

		if ( ! $screen || strpos( $screen->id, 'super-page-cache' ) !== false ) {
			return;
		}

		if (
			! get_option( Constants::KEY_RULE_UPDATE_FAILED, false ) ||
			! current_user_can( 'manage_options' )
		) {
			return;
		}

		$admin_page_url = add_query_arg(
			[
				'page' => 'super-page-cache-settings',
			],
			admin_url( 'admin.php#cloudflare' )
		);

		$logo_url = Assets_Handler::get_image_url( 'logo.svg' );

		?>
		<style>
			.notice.spc-rule {
				display: flex;
				padding: 20px;
				border-left-color: #ca6308;
				gap: 20px;
			}

			.spc-rule p {
				margin-bottom: 10px;
			}

			.spc-rule h3 {
				margin: 0 0 10px;
			}

			.spc-rule .spc-logo {
				width: 40px;
				height: 40px;
			}
		</style>

		<div class="notice notice-warning spc-rule">
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="Super Page Cache" class="spc-logo">
			<div>
				<h3><?php echo esc_html( I18n::get( 'ruleFixTitle' ) ); ?></h3>
				<p>
					<?php
					echo wp_kses_post( I18n::get( 'ruleFixDescription' ) );
					?>
				</p>
				<a href="<?php echo esc_url( $admin_page_url ); ?>" class="button button-secondary">
					<?php _e( 'Settings page', 'wp-cloudflare-page-cache' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Get third party compatibilities.
	 *
	 * @return array
	 */
	public static function get_third_party_view_map() {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
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
	 * Filter plugins to show only conflicting ones when self::CONFLICTS_QUERY_ARG is present.
	 *
	 * @param array $plugins All plugins.
	 *
	 * @return array
	 */
	public function filter_conflicting_plugins( $plugins ) {
		if ( ! is_admin() || ! isset( $_GET[ self::CONFLICTS_QUERY_ARG ] ) || $_GET[ self::CONFLICTS_QUERY_ARG ] !== '1' ) {
			return $plugins;
		}

		$screen = get_current_screen();
		if ( ! $screen || $screen->id !== 'plugins' ) {
			return $plugins;
		}

		$conflicting_plugins = array_keys( self::get_conflicts() );

		// Filter plugins to only show conflicting ones
		return array_intersect_key( $plugins, array_flip( $conflicting_plugins ) );
	}

	/**
	 * Get active conflicting plugins.
	 *
	 * @param bool $names_only Whether to return only the plugin names.
	 *
	 * @return array<string, string> | string[]
	 */
	public static function get_conflicts( $names_only = false ) {
		$plugins = [
			'wp-rocket/wp-rocket.php'                    => 'WP Rocket',
			'wp-optimize/wp-optimize.php'                => 'WP Optimize',
			'autoptimize/autoptimize.php'                => 'Autoptimize',
			'wp-super-cache/wp-cache.php'                => 'WP Super Cache',
			'w3-total-cache/w3-total-cache.php'          => 'W3 Total Cache',
			'litespeed-cache/litespeed-cache.php'        => 'LiteSpeed Cache',
			'wp-fastest-cache/wpFastestCache.php'        => 'WP Fastest Cache',
			'sg-cachepress/sg-cachepress.php'            => 'SiteGround Optimizer',
			'swift-performance/performance.php'          => 'Swift Performance Pro',
			'swift-performance-lite/performance.php'     => 'Swift Performance',
			'hummingbird-performance/wp-hummingbird.php' => 'Hummingbird Performance',
		];

		$enabled = array_filter(
			$plugins,
			function ( $plugin_path ) {
				return is_plugin_active( $plugin_path );
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( $names_only ) {
			return array_values( $enabled );
		}

		return $enabled;
	}
}
