<?php

namespace SPC\Modules;

use SPC\Modules\Database_Optimization;
use SPC\Constants;
use SPC\Models\Asset_Rules;
use SPC\Modules\Module_Interface;
use SPC\Modules\Settings_Manager;
use SPC\Services\Cloudflare_Client;
use SPC\Services\Log_Parser;
use SPC\Services\SDK_Integrations;
use SPC\Services\Settings_Store;
use SPC\Utils\Cache_Tester;
use SPC\Services\Notices_Handler;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

class Rest_Server implements Module_Interface {


	public const REST_NAMESPACE = 'spc/v1';

	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function register_rest_routes() {
		global $sw_cloudflare_pagecache;

		register_rest_route(
			self::REST_NAMESPACE,
			'/cache/purge',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'purge_cache' ],
				'permission_callback' => [ $sw_cloudflare_pagecache, 'can_current_user_purge_cache' ],
				// TODO Args for single page.
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cache/test',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'test_cache' ],
				'permission_callback' => [ $sw_cloudflare_pagecache, 'can_current_user_purge_cache' ],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cache/purge-varnish',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'purge_varnish_cache' ],
				'permission_callback' => [ $sw_cloudflare_pagecache, 'can_current_user_purge_cache' ],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/logs/clear',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'clear_logs' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/logs/get',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_logs' ],
				'permission_callback' => 'is_user_logged_in',
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/preloader/start',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'start_preloader' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
		register_rest_route(
			self::REST_NAMESPACE,
			'/config/import',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'import_config' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'settings' => [
						'required'          => true,
						'validate_callback' => function ( $settings ) {
							return is_array( $settings );
						},
						'sanitize_callback' => [ $this, 'sanitize_settings' ],
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/toggle-license',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'toggle_license' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'key'    => [
						'type'              => 'string',
						'sanitize_callback' => function ( $key ) {
							return (string) esc_attr( $key );
						},
						'validate_callback' => function ( $key ) {
							return is_string( $key );
						},
					],
					'action' => [
						'type'              => 'string',
						'sanitize_callback' => function ( $key ) {
							return (string) esc_attr( $key );
						},
						'validate_callback' => function ( $key ) {
							return in_array( $key, [ 'activate', 'deactivate' ], true );
						},
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/settings/reset',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'reset_settings' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/settings/wizard',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'enable_page_cache' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/settings/update',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'update_settings' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'settings' => [
						'required'          => true,
						'validate_callback' => function ( $settings ) {
							return is_array( $settings );
						},
						'sanitize_callback' => [ $this, 'sanitize_settings' ],
					],
				],
			],
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cloudflare/connect',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'cloudflare_connect' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'auth_mode'   => [
						'required'          => true,
						'validate_callback' => function ( $auth_mode ) {
							return in_array( $auth_mode, [ 'api_key', 'api_token' ], true );
						},
					],
					'email'       => [
						'required'          => false,
						'validate_callback' => function ( $email ) {
							return is_string( $email ) && filter_var( $email, FILTER_VALIDATE_EMAIL );
						},
						'sanitize_callback' => 'sanitize_email',
					],
					'api_key'     => [
						'required'          => false,
						'validate_callback' => function ( $value ) {
							return is_string( $value );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
					'api_token'   => [
						'required'          => false,
						'validate_callback' => function ( $value ) {
							return is_string( $value );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
					'domain_name' => [
						'required'          => false,
						'validate_callback' => function ( $value ) {
							return is_string( $value );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cloudflare/disconnect',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'cloudflare_disconnect' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cloudflare/confirm-zone-id',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'cloudflare_confirm_zone_id' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'zone_id' => [
						'required'          => true,
						'validate_callback' => function ( $zone_id ) {
							return is_string( $zone_id ) && ! empty( $zone_id );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/database/optimize',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'optimize_database' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'action' => array(
						'required'          => false,
						'validate_callback' => function ( $action ) {
							return is_string( $action );
						},
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cloudflare/analytics',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'cloudflare_analytics' ],
				'permission_callback' => 'is_user_logged_in',
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cloudflare/repair-rule',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'cloudflare_repair_rule' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/notice/dismiss',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'dismiss_notice' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'key' => [
						'required'          => true,
						'validate_callback' => function ( $key ) {
							return is_string( $key );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/assets/save-rules',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_assets_rules' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => [
					'assets_data' => [
						'required'          => true,
						'validate_callback' => function ( $assets_data ) {
							return is_array( $assets_data );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/optimizations',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'optimizations' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'd' => [
						'type'     => 'integer',
						'required' => true,
					],
					'a' => [
						'type'     => 'array',
						'required' => true,
					],
					'u' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/cached-pages',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_cached_pages' ],
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			]
		);
	}

	/**
	 * Get cached pages.
	 *
	 * @param WP_REST_Request $request Rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_cached_pages( WP_REST_Request $request ) {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$cached_pages = $sw_cloudflare_pagecache->get_html_cache_handler()->get_cached_urls();

		return $this->data_response( $cached_pages );
	}

	/**
	 * Store optimization data.
	 *
	 * Expected request parameters:
	 * - d: Device type (1=mobile, 2=desktop)
	 * - a: Array of above-fold image IDs
	 * - b: Background selectors object
	 * - l: LCP data (imageId, bgSelector, bgUrls, type)
	 * - c: Critical CSS data { css: structured_css_object }
	 * - u: Profile URL/ID
	 * - p: Base64 encoded page URL
	 * - t: Timestamp
	 * - h: HMAC signature
	 *
	 * @param WP_REST_Request $request Rest request.
	 *
	 * @return WP_REST_Response
	 */
	public function optimizations( WP_REST_Request $request ) {
		if ( ! Settings_Store::get_instance()->is_client_optimizations_enabled() ) {
			return $this->message_response( 'Optimization is not enabled', 400 );
		}
		$time     = $request->get_param( 't' );
		$hmac     = $request->get_param( 'h' );
		$page_url = $request->get_param( 'p' );
		if ( empty( $time ) || empty( $hmac ) ) {
			return $this->message_response( 'Missing required parameters', 400 );
		}

		$device_type       = $request->get_param( 'd' );
		$above_fold_images = $request->get_param( 'a' );
		$critical_css      = Settings_Store::get_instance()->get( Constants::SETTING_UNUSED_CSS ) ? $request->get_param( 'c' ) : [];
		$url               = $request->get_param( 'u' );
		if ( $time < time() - DAY_IN_SECONDS ) {
			return $this->message_response( 'Invalid Signature.', 400 );
		}
		if ( wp_hash( $url . $time . $page_url, 'nonce' ) !== $hmac ) {
			return $this->message_response( 'Invalid Signature.', 400 );
		}
		$bg_selectors = $request->get_param( 'b' );
		$lcp_data     = $request->get_param( 'l' );
		$origin       = $request->get_header( 'origin' );
		if ( empty( $origin ) || ! is_allowed_http_origin( $origin ) ) {
			return $this->message_response( 'Invalid origin', 400 );
		}
		if ( empty( $device_type ) || empty( $url ) || ! is_array( $above_fold_images ) ) {
			return $this->message_response( 'Missing required parameters', 400 );
		}
		if ( count( $above_fold_images ) > 20 ) {
			return $this->message_response( 'Above fold images limit exceeded', 400 );
		}
		if ( count( $bg_selectors ) > 100 ) {
			return $this->message_response( 'Background selectors limit exceeded', 400 );
		}
		if ( $url === \SPC_Pro\Modules\PageProfiler\Profile::PLACEHOLDER ) {
			return $this->message_response( 'Missing profile parameters', 400 );
		}
		global $sw_cloudflare_pagecache;
		$lazyload_background_selectors = $sw_cloudflare_pagecache->get_core_loader()->get_modules()['frontend']->is_background_lazyload_enabled()
		? $sw_cloudflare_pagecache->get_core_loader()->get_modules()['frontend']->get_lazyload_background_selectors() : [];
		$current_selectors             = array_values( $lazyload_background_selectors );
		$sanitized_selectors           = [];
		foreach ( $bg_selectors as $selector => $above_fold_bg_selectors ) {
			if ( ! in_array( $selector, $current_selectors, true ) ) {
				return $this->message_response( 'Invalid background selector', 400 );
			}
			if ( count( $above_fold_bg_selectors ) > 100 ) {
				return $this->message_response( 'Above fold background selectors limit exceeded', 400 );
			}
			$selector                         = strip_tags( $selector );
			$sanitized_selectors[ $selector ] = [];
			foreach ( $above_fold_bg_selectors as $above_fold_bg_selector => $bg_urls ) {
				if ( count( $bg_urls ) > 3 ) {
					return $this->message_response( 'Background URLs limit exceeded', 400 );
				}
				$sanitized_selectors[ $selector ][ strip_tags( $above_fold_bg_selector ) ] = array_filter(
					array_map( 'sanitize_url', array_values( $bg_urls ) )
				);
			}
		}
		$sanitized_lcp_data = [];
		if ( ! empty( $lcp_data ) ) {
			$sanitized_lcp_data['imageId']    = sanitize_text_field( $lcp_data['i'] ?? '' );
			$sanitized_lcp_data['bgSelector'] = sanitize_text_field( $lcp_data['s'] ?? '' );
			if ( count( $lcp_data['u'] ?? [] ) > 3 ) {
				return $this->message_response( 'LCP Background URLs limit exceeded', 400 );
			}
			$sanitized_lcp_data['bgUrls'] = array_filter(
				array_map( 'sanitize_url', array_values( $lcp_data['u'] ?? [] ) )
			);
			$sanitized_lcp_data['type']   = empty( $sanitized_lcp_data['imageId'] ) ? 'bg' : 'img';
		}
		$profile = new \SPC_Pro\Modules\PageProfiler\Profile();
		$profile->store( $url, $device_type, $above_fold_images, $sanitized_selectors, $sanitized_lcp_data, $critical_css );
		if ( $profile->exists_all( $url ) ) {
			$page_url = base64_decode( $page_url );
			add_filter(
				'spc_page_profiler_dont_delete_url',
				function ( $dont_delete, $key ) use ( $url ) {
					return $url === \SPC_Pro\Modules\PageProfiler\Profile::generate_id( $key );
				},
				10,
				2
			);
			/**
			 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
			 */
			$sw_cloudflare_pagecache->get_cache_controller()->purge_urls( [ ( $page_url ) ] );
		}
		return $this->message_response( 'Above fold data stored successfully' );
	}


	/**
	 * Purge the entire Cloudflare cache via REST API.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function purge_cache( WP_REST_Request $request ) {
		global $sw_cloudflare_pagecache;

		$sw_cloudflare_pagecache->get_logger()->add_log( 'cache_controller::ajax_purge_whole_cache', 'Purge whole cache' );

		$status = $sw_cloudflare_pagecache->get_cache_controller()->purge_all( false, false );

		if ( ! $status ) {
			return $this->message_response(
				__( 'Failed to purge cache. Please try again later.', 'wp-cloudflare-page-cache' ),
				500
			);
		}

		return $this->message_response( __( 'Cache purge initiated successfully. Please wait for the process to complete.', 'wp-cloudflare-page-cache' ) );
	}

	/**
	 * Purge the Varnish cache via REST API.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function purge_varnish_cache( WP_REST_Request $request ) {
		global $sw_cloudflare_pagecache;

		$status = $sw_cloudflare_pagecache->get_varnish_handler()->purge_varnish_cache();

		if ( ! is_array( $status ) || ! isset( $status['status'] ) ) {
			// If the response is not an array or does not contain the expected status.
			return $this->message_response(
				__( 'Failed to purge Varnish cache. Please try again later.', 'wp-cloudflare-page-cache' ),
				500
			);
		}

		if ( $status['status'] === 'error' ) {
			// If the status indicates an error.
			return $this->message_response(
				$status['error'] ?? __( 'An unknown error occurred while purging Varnish cache.', 'wp-cloudflare-page-cache' ),
				500
			);
		}

		return $this->message_response( __( 'Cache purge initiated successfully. Please wait for the process to complete.', 'wp-cloudflare-page-cache' ) );
	}

	/**
	 * Test the cache functionality.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function test_cache( WP_REST_Request $request ) {
		try {
			$results = ( new Cache_Tester() )->test();

			return $this->data_response( $results );
		} catch ( \Exception $e ) {
			return $this->message_response(
				__( 'An error occurred while testing the cache: ', 'wp-cloudflare-page-cache' ) . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Clear the log file.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function clear_logs( WP_REST_Request $request ) {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		try {
			$sw_cloudflare_pagecache->get_logger()->reset_log();
			return $this->message_response( __( 'Logs cleared successfully.', 'wp-cloudflare-page-cache' ) );
		} catch ( \Exception $e ) {
			return $this->message_response(
				__( 'An error occurred while clearing logs: ', 'wp-cloudflare-page-cache' ) . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get logs.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function get_logs( WP_REST_Request $request ) {
		return $this->data_response( ( new Log_Parser() )->get_log_data() );
	}

	public function start_preloader( WP_REST_Request $request ) {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		if ( ! Settings_Store::get_instance()->get( Constants::SETTING_ENABLE_PRELOADER ) ) {
			return $this->message_response(
				__( 'Preloader is not enabled', 'wp-cloudflare-page-cache' ),
				400
			);
		}

		$cache_controller = $sw_cloudflare_pagecache->get_cache_controller();

		if ( ! $cache_controller->can_i_start_preloader() ) {
			return $this->message_response(
				__( 'Unable to start the preloader. Another preloading process is currently running.', 'wp-cloudflare-page-cache' ),
				400
			);
		}

		if ( ! class_exists( 'SWCFPC_Preloader_Process' ) ) {
			return $this->message_response(
				__( 'Unable to start background processes: SWCFPC_Preloader_Process does not exists.', 'wp-cloudflare-page-cache' ),
				500
			);
		}

		if ( ! $cache_controller->is_cache_enabled() ) {
			return $this->message_response(
				__( 'You cannot start the preloader while the page cache is disabled.', 'wp-cloudflare-page-cache' ),
				400
			);
		}

		$cache_controller->start_preloader_for_all_urls();

		return $this->message_response( __( 'Preloader started successfully', 'wp-cloudflare-page-cache' ) );
	}

	/**
	 * Reset all settings and configurations.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 *
	 */
	public function reset_settings( WP_REST_Request $request ) {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$error = '';

		$settings = Settings_Store::get_instance();

		$cloudflare_handler     = $sw_cloudflare_pagecache->get_cloudflare_handler();
		$cache_controller       = $sw_cloudflare_pagecache->get_cache_controller();
		$html_cache_handler     = $sw_cloudflare_pagecache->get_html_cache_handler();
		$fallback_cache_handler = $sw_cloudflare_pagecache->get_fallback_cache_handler();

		// Purge all caches and prevent preloader to start
		$cache_controller->purge_all( true, false, true );

		// Reset old browser cache TTL
		$cloudflare_handler->change_browser_cache_ttl( $settings->get( Constants::SETTING_OLD_BC_TTL ), $error );

		// Delete the page rule
		$page_rule_id = $settings->get( Constants::RULE_ID_PAGE );
		if ( ! empty( $page_rule_id ) ) {
			$cloudflare_handler->delete_page_rule( $page_rule_id, $error );
		}

		// Delete additional page rule if exists
		$bypass_backend_page_rule_id = $settings->get( Constants::RULE_ID_BYPASS_BACKEND );
		if ( ! empty( $bypass_backend_page_rule_id ) ) {
			$cloudflare_handler->delete_page_rule( $bypass_backend_page_rule_id, $error );
		}

		// Delete Cache Rule
		$cloudflare_handler->delete_cache_rule();
		$settings
			->set( Constants::RULESET_ID_CACHE, '' )
			->set( Constants::RULE_ID_CACHE, '' );

		// Disable fallback cache
		if ( defined( 'SWCFPC_ADVANCED_CACHE' ) ) {
			$fallback_cache_handler->fallback_cache_advanced_cache_disable();
		}

		// Delete all cached HTML pages temp files
		$html_cache_handler->delete_all_cached_urls();

		if ( $settings->get( Constants::SETTING_ENABLE_DATABASE_OPTIMIZATION ) ) {
			$database_optimization = new Database_Optimization();
			$database_optimization->delete_events();
		}

		// Restore default plugin config
		$settings
			->reset()
			->save();

		// Delete all htaccess rules
		$cache_controller->reset_htaccess();

		// Unschedule purge cache cron
		$timestamp = wp_next_scheduled( 'swcfpc_cache_purge_cron' );

		if ( $timestamp !== false ) {
			wp_unschedule_event( $timestamp, 'swcfpc_cache_purge_cron' );
			wp_clear_scheduled_hook( 'swcfpc_cache_purge_cron' );
		}

		// Reset log
		$sw_cloudflare_pagecache->get_logger()->reset_log();
		$sw_cloudflare_pagecache->get_logger()->add_log( 'cache_controller::reset_all', 'Reset complete' );

		if ( ! empty( $error ) ) {
			return $this->message_response(
				__( 'Settings reset completed with some errors: ', 'wp-cloudflare-page-cache' ) . $error,
				500
			);
		}

		return $this->message_response(
			__( 'Settings reset completed successfully.', 'wp-cloudflare-page-cache' )
		);
	}

	/**
	 * Enable the page cache via REST API.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function enable_page_cache( WP_REST_Request $request ) {
		global $sw_cloudflare_pagecache;

		Settings_Store::get_instance()
			->set( Constants::SETTING_CF_CACHE_ENABLED, 1 )
			->set( Constants::SETTING_ENABLE_FALLBACK_CACHE, 1 )
			->save();

		if (
			Settings_Manager::is_on( Constants::SETTING_ENABLE_FALLBACK_CACHE ) &&
			! Settings_Manager::is_on( Constants::SETTING_FALLBACK_CACHE_CURL ) &&
			! defined( 'SWCFPC_ADVANCED_CACHE' )
		) {
			$sw_cloudflare_pagecache->get_fallback_cache_handler()->fallback_cache_advanced_cache_enable();
		}

		$return_array['success_msg'] = __( 'Page cache enabled successfully', 'wp-cloudflare-page-cache' );

		// Enable the fallback cache
		$sw_cloudflare_pagecache->get_fallback_cache_handler()->fallback_cache_enable();

		// Set the config to enable the page cache
		Settings_Store::get_instance()
			->set( Constants::SETTING_ENABLE_FALLBACK_CACHE, 1 )
			->save();

		return $this->data_response(
			[
				'message'  => __( 'Page cache enabled successfully.', 'wp-cloudflare-page-cache' ),
				'settings' => Settings_Store::get_instance()->get_all(),
			]
		);
	}

	/**
	 * Toggle the license key.
	 *
	 * @param WP_REST_Request $request activation request.
	 *
	 * @return WP_REST_Response
	 */
	public function toggle_license( WP_REST_Request $request ) {
		$fields = $request->get_json_params();

		if ( ! isset( $fields['key'] ) || ! isset( $fields['action'] ) ) {
			return $this->data_response(
				[
					'message' => __( 'Please refresh the page and try again.', 'wp-cloudflare-page-cache' ),
					'success' => false,
				],
				400
			);
		}

		$response = apply_filters( 'themeisle_sdk_license_process_spc', $fields['key'], $fields['action'] );

		if ( is_wp_error( $response ) ) {
			return $this->message_response(
				$response->get_error_message(),
				500
			);
		}

		$license = get_option( ( new SDK_Integrations() )->get_license_option_key(), (object) [] );

		return $this->data_response(
			[
				'message' => $fields['action'] === 'activate' ? __( 'Activated.', 'wp-cloudflare-page-cache' ) : __( 'Deactivated', 'wp-cloudflare-page-cache' ),
				'success' => true,
				'license' => $license,
			]
		);
	}

	/**
	 * Update settings via REST API.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function update_settings( WP_REST_Request $request ) {
		$data = $request->get_body();

		if ( empty( $data ) || ! is_string( $data ) ) {
			return $this->message_response(
				__( 'Invalid settings format provided.', 'wp-cloudflare-page-cache' ),
				400
			);
		}

		$data = json_decode( $data, true );

		if ( ! is_array( $data ) || ! isset( $data['settings'] ) ) {
			return $this->message_response(
				__( 'Invalid settings format provided.', 'wp-cloudflare-page-cache' ),
				400
			);
		}

		try {
			$settings_manager = new Settings_Manager();
			$settings_manager->update_settings( $data['settings'] );

			return $this->data_response(
				[
					'message'  => __( 'Settings updated successfully.', 'wp-cloudflare-page-cache' ),
					'settings' => Settings_Store::get_instance()->get_all(),
				]
			);
		} catch ( \Exception $e ) {
			return $this->message_response(
				__( 'An error occurred while updating settings: ', 'wp-cloudflare-page-cache' ) . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Import settings from a JSON file.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function import_config( WP_REST_Request $request ) {
		$data = $request->get_body();

		$data = json_decode( $data, true );

		if ( ! is_array( $data ) || ! isset( $data['settings'] ) ) {
			return $this->message_response( __( 'Invalid settings format provided.', 'wp-cloudflare-page-cache' ), 400 );
		}

		$status = Settings_Store::get_instance()->import_settings( $data['settings'] );

		if ( ! $status ) {
			return $this->message_response( __( 'Failed to import settings.', 'wp-cloudflare-page-cache' ), 500 );
		}

		return $this->message_response( __( 'Settings imported successfully.', 'wp-cloudflare-page-cache' ) );
	}

	/**
	 * Connect to Cloudflare via API Key or API Token.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function cloudflare_connect( WP_REST_Request $request ) {
		global $sw_cloudflare_pagecache;

		$data = $request->get_body();
		$data = json_decode( $data, true );

		if ( ! is_array( $data ) || ! isset( $data['auth_mode'] ) ) {
			return $this->message_response( __( 'Invalid data format provided.', 'wp-cloudflare-page-cache' ), 400 );
		}

		$auth_mode = $data['auth_mode'];
		$error_msg = '';

		try {
			$settings = Settings_Store::get_instance();

			// Set authentication mode using constants
			$auth_mode_value = $auth_mode === 'api_key' ? SWCFPC_AUTH_MODE_API_KEY : SWCFPC_AUTH_MODE_API_TOKEN;
			$settings->set( Constants::SETTING_AUTH_MODE, $auth_mode_value );

			if ( $auth_mode === 'api_key' ) {
				if ( ! isset( $data['email'] ) || ! isset( $data['api_key'] ) ) {
					return $this->message_response( __( 'Email and API Key are required for API Key authentication.', 'wp-cloudflare-page-cache' ), 400 );
				}

				// Set API Key credentials, clear token credentials
				$settings
					->set( Constants::SETTING_CF_EMAIL, sanitize_email( $data['email'] ) )
					->set( Constants::SETTING_CF_API_KEY, sanitize_text_field( $data['api_key'] ) )
					->set( Constants::SETTING_CF_API_TOKEN, '' )
					->set( Constants::SETTING_CF_DOMAIN_NAME, '' );

				// Configure Cloudflare handler
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_auth_mode( SWCFPC_AUTH_MODE_API_KEY );
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_api_email( sanitize_email( $data['email'] ) );
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_api_key( sanitize_text_field( $data['api_key'] ) );
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_api_token( '' );
			} elseif ( $auth_mode === 'api_token' ) {
				if ( ! isset( $data['api_token'] ) ) {
					return $this->message_response( __( 'API Token is required for API Token authentication.', 'wp-cloudflare-page-cache' ), 400 );
				}

				// Set API Token credentials, clear API Key credentials
				$settings
					->set( Constants::SETTING_CF_API_TOKEN, sanitize_text_field( $data['api_token'] ) )
					->set( Constants::SETTING_CF_EMAIL, '' )
					->set( Constants::SETTING_CF_API_KEY, '' )
					->set( Constants::ZONE_ID_LIST, null )
					->set( Constants::SETTING_CF_ZONE_ID, '' );

				// Configure Cloudflare handler
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_auth_mode( SWCFPC_AUTH_MODE_API_TOKEN );
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_api_token( sanitize_text_field( $data['api_token'] ) );
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_api_email( '' );
				$sw_cloudflare_pagecache->get_cloudflare_handler()->set_api_key( '' );
			} else {
				return $this->message_response( __( 'Invalid authentication mode provided.', 'wp-cloudflare-page-cache' ), 400 );
			}

			// Save settings immediately to enable connection
			$settings->save();

			$client = new Cloudflare_Client( $sw_cloudflare_pagecache );

			// Attempt to get zone ID list to validate the connection
			$zone_id_list = $client->get_zone_id_list( $error_msg );

			if ( $zone_id_list === false || ! empty( $error_msg ) ) {
				return $this->message_response(
					__( 'Failed to connect to Cloudflare: ', 'wp-cloudflare-page-cache' ) . $error_msg,
					400
				);
			}

			// Store zone ID list using Settings_Store
			$settings->set( Constants::ZONE_ID_LIST, $zone_id_list );

			// Final save
			$settings->save();

			$response_data = [
				'auth_mode' => $auth_mode,
				'settings'  => Settings_Store::get_instance()->get_all(),
				'message'   => $auth_mode === 'api_key'
					? __( 'Cloudflare connected successfully via API Key.', 'wp-cloudflare-page-cache' )
					: __( 'Cloudflare connected successfully via API Token.', 'wp-cloudflare-page-cache' ),
			];

			return $this->data_response( $response_data );
		} catch ( \Exception $e ) {
			return $this->message_response(
				__( 'An error occurred while connecting to Cloudflare: ', 'wp-cloudflare-page-cache' ) . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Disconnect from Cloudflare.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function cloudflare_disconnect( WP_REST_Request $request ) {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$error = '';
		$sw_cloudflare_pagecache->get_cloudflare_handler()->disconnect( $error );

		Settings_Store::get_instance()
			->set( Constants::SETTING_CF_EMAIL, '' )
			->set( Constants::SETTING_CF_API_KEY, '' )
			->set( Constants::SETTING_CF_API_TOKEN, '' )
			->set( Constants::SETTING_CF_DOMAIN_NAME, '' )
			->set( Constants::ZONE_ID_LIST, null )
			->set( Constants::SETTING_CF_ZONE_ID, '' )
			->set( Constants::RULESET_ID_CACHE, '' )
			->set( Constants::RULE_ID_CACHE, '' )
			->set( Constants::ENABLE_CACHE_RULE, 0 )
			->save();

		return $this->data_response(
			[
				'message'  => __( 'Disconnected from Cloudflare.', 'wp-cloudflare-page-cache' ),
				'settings' => Settings_Store::get_instance()->get_all(),
			]
		);
	}

	/**
	 * Confirm the zone ID.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function cloudflare_confirm_zone_id( WP_REST_Request $request ) {
		$data = $request->get_body();
		$data = json_decode( $data, true );

		if ( ! is_array( $data ) || ! isset( $data['zone_id'] ) ) {
			return $this->message_response( __( 'Invalid data format provided.', 'wp-cloudflare-page-cache' ), 400 );
		}

		$zone_id = $data['zone_id'];

		$settings = Settings_Store::get_instance();

		$is_token_auth = $settings->get( Constants::SETTING_AUTH_MODE ) === SWCFPC_AUTH_MODE_API_TOKEN;

		if ( $is_token_auth ) {
			/**
			 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
			 */
			global $sw_cloudflare_pagecache;
			$client = new Cloudflare_Client( $sw_cloudflare_pagecache );

			$missing_permissions = $client->verify_token_permissions( $zone_id );

			if ( is_wp_error( $missing_permissions ) ) {
				return $this->message_response( $missing_permissions->get_error_message(), 400 );
			}

			if ( ! is_array( $missing_permissions ) ) {
				return $this->message_response( __( 'Invalid response from Cloudflare.', 'wp-cloudflare-page-cache' ), 400 );
			}

			if ( ! empty( $missing_permissions ) ) {
				return $this->data_response(
					[
						'success'     => false,
						'permissions' => $missing_permissions,
					]
				);
			}
		}

		$settings->set( Constants::SETTING_CF_ZONE_ID, $zone_id )->save();

		return $this->data_response(
			[
				'message'  => __( 'Successfully connected to Cloudflare.', 'wp-cloudflare-page-cache' ),
				'settings' => $settings->get_all(),
			]
		);
	}

	/**
	 * Optimize the database.
	 * This method handles various database optimization tasks based on the action specified in the request.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function optimize_database( WP_REST_Request $request ) {
		$data   = $request->get_body();
		$data   = json_decode( $data, true );
		$action = isset( $data['action'] ) ? $data['action'] : 'all';

		try {
			$message               = '';
			$database_optimization = new Database_Optimization();

			switch ( $action ) {
				case Constants::SETTING_POST_REVISION_INTERVAL:
					$message = $database_optimization->remove_revision_posts();
					break;
				case Constants::SETTING_AUTO_DRAFT_POST_INTERVAL:
					$message = $database_optimization->remove_draft_posts();
					break;
				case Constants::SETTING_TRASHED_POST_INTERVAL:
					$message = $database_optimization->remove_trashed_posts();
					break;
				case Constants::SETTING_SPAM_COMMENT_INTERVAL:
					$message = $database_optimization->remove_spam_comments();
					break;
				case Constants::SETTING_TRASHED_COMMENT_INTERVAL:
					$message = $database_optimization->remove_trashed_comments();
					break;
				case Constants::SETTING_ALL_TRANSIENT_INTERVAL:
					$message = $database_optimization->remove_transients();
					break;
				case Constants::SETTING_OPTIMIZE_TABLE_INTERVAL:
					$message = $database_optimization->optimize_database_table();
					break;
				default:
					$message = $database_optimization->remove_all();
			}

			return $this->message_response( $message );
		} catch ( \Exception $e ) {
			return $this->message_response(
				__( 'An error occurred while optimizing the database:', 'wp-cloudflare-page-cache' ) . ' ' . $e->getMessage(),
				500
			);
		}
	}

	/**
	 * Get the Cloudflare analytics for the last 24 hours.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function cloudflare_analytics( WP_REST_Request $request ) {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$client = new Cloudflare_Client( $sw_cloudflare_pagecache );

		$analytics = $client->get_analytics();

		// We don't want to show an error message to the user.
		if ( is_wp_error( $analytics ) ) {
			return $this->data_response( [] );
		}

		return $this->data_response(
			$analytics
		);
	}

	/**
	 * Repair the Cloudflare rule.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function cloudflare_repair_rule( WP_REST_Request $request ) {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$error = '';

		$status = $sw_cloudflare_pagecache->get_cloudflare_handler()->reset_cf_rule( $error );

		if ( ! empty( $error ) ) {
			return $this->message_response( $error, 400 );
		}

		if ( ! $status ) {
			return $this->message_response( __( 'Failed to repair the Cloudflare rule.', 'wp-cloudflare-page-cache' ), 400 );
		}

		delete_option( Constants::KEY_RULE_UPDATE_FAILED );

		return $this->message_response( __( 'Cloudflare rule repaired successfully.', 'wp-cloudflare-page-cache' ) );
	}

	/**
	 * Dismiss a notice.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function dismiss_notice( WP_REST_Request $request ) {
		$data = $request->get_body();
		$data = json_decode( $data, true );

		if ( ! is_array( $data ) || ! isset( $data['key'] ) ) {
			return $this->message_response( __( 'Invalid data format provided.', 'wp-cloudflare-page-cache' ), 400 );
		}

		$dismiss = Notices_Handler::dismiss( $data['key'] );

		if ( ! $dismiss ) {
			return $this->message_response( __( 'Failed to dismiss notice.', 'wp-cloudflare-page-cache' ), 400 );
		}

		return $this->message_response( __( 'Notice dismissed successfully.', 'wp-cloudflare-page-cache' ) );
	}

	/**
	 * Save assets rules.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 *
	 * @return WP_REST_Response
	 */
	public function save_assets_rules( WP_REST_Request $request ) {
		$data = $request->get_body();

		$data = json_decode( $data, true );

		if ( ! is_array( $data ) || ! isset( $data['assets_data'] ) ) {
			return $this->message_response( __( 'Invalid data format provided.', 'wp-cloudflare-page-cache' ), 400 );
		}

		$assets_data = $data['assets_data'];

		if ( ! is_array( $assets_data ) || empty( $assets_data ) ) {
			return $this->message_response( __( 'No changes to save.', 'wp-cloudflare-page-cache' ), 400 );
		}

		$saved_count = 0;

		foreach ( $assets_data as $asset_hash => $asset_data ) {
			$result = Asset_Rules::upsert_asset_rules( $asset_hash, $asset_data );

			if ( $result !== false ) {
				$saved_count++;
			}
		}

		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$sw_cloudflare_pagecache->get_cache_controller()->purge_all( false, false );

		$message = __( 'No changes to save.', 'wp-cloudflare-page-cache' );

		if ( $saved_count > 0 ) {
			$message = $saved_count > 1 ? sprintf(
				// translators: %d is the number of assets with saved rules.
				__( 'Saved rules for %d assets.', 'wp-cloudflare-page-cache' ),
				$saved_count
			) : sprintf(
				__( 'Saved rules for one asset.', 'wp-cloudflare-page-cache' ),
				$saved_count
			);
		}

		return $this->message_response( $message );
	}

	/**
	 * Sanitize incoming settings via REST API.
	 *
	 * @param array $settings The incoming settings.
	 *
	 * @return array
	 */
	public function sanitize_settings( array $settings ) {
		$settings_manager = new Settings_Manager();
		$valid_fields     = array_keys( $settings_manager->get_fields() );

		return array_intersect_key( $settings, array_flip( $valid_fields ) );
	}

	/**
	 * Send a response with a message and status code.
	 *
	 * @param string $message The message to send.
	 * @param int $status_code The HTTP status code (default is 200).
	 *
	 * @return WP_REST_Response
	 */
	private function message_response( string $message, int $status_code = 200 ): WP_REST_Response {
		return new WP_REST_Response(
			[
				'success' => $status_code === 200,
				'message' => $message,
			]
		);
	}

	/**
	 * Send a response with data and status code.
	 *
	 * @param array $data The data to send.
	 * @param int $status_code The HTTP status code (default is 200).
	 *
	 * @return WP_REST_Response
	 */
	private function data_response( array $data = [], int $status_code = 200 ): WP_REST_Response {
		return new WP_REST_Response(
			[
				'success' => $status_code === 200,
				'message' => $data['message'] ?? '',
				'data'    => $data,
			],
			$status_code
		);
	}
}
