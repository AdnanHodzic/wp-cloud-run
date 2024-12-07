<?php
/**
 * Plugin bootstrap file.
 */

use SPC\Constants;

if ( ! class_exists( 'SW_CLOUDFLARE_PAGECACHE' ) ) {
	define( 'SPC_PATH', defined( 'SPC_PRO_PATH' ) ? SPC_PRO_PATH : SPC_FREE_PATH );

	define( 'SWCFPC_PLUGIN_PATH', plugin_dir_path( SPC_PATH ) );
	define( 'SWCFPC_PLUGIN_URL', plugin_dir_url( SPC_PATH ) );
	define( 'SWCFPC_BASEFILE', SPC_PATH );
	define( 'SWCFPC_PLUGIN_REVIEWS_URL', 'https://wordpress.org/support/plugin/wp-cloudflare-page-cache/reviews/' );
	define( 'SWCFPC_PLUGIN_FORUM_URL', 'https://wordpress.org/support/plugin/wp-cloudflare-page-cache/' );
	define( 'SWCFPC_AUTH_MODE_API_KEY', 0 );
	define( 'SWCFPC_AUTH_MODE_API_TOKEN', 1 );
	define( 'SWCFPC_LOGS_STANDARD_VERBOSITY', 1 );
	define( 'SWCFPC_LOGS_HIGH_VERBOSITY', 2 );

	if ( ! defined( 'SWCFPC_PRELOADER_MAX_POST_NUMBER' ) ) {
		define( 'SWCFPC_PRELOADER_MAX_POST_NUMBER', 50 );
	}

	if ( ! defined( 'SWCFPC_CACHE_BUSTER' ) ) {
		define( 'SWCFPC_CACHE_BUSTER', 'swcfpc' );
	}

	if ( ! defined( 'SWCFPC_CURL_TIMEOUT' ) ) {
		define( 'SWCFPC_CURL_TIMEOUT', 10 );
	}

	if ( ! defined( 'SWCFPC_PURGE_CACHE_LOCK_SECONDS' ) ) {
		define( 'SWCFPC_PURGE_CACHE_LOCK_SECONDS', 10 );
	}

	if ( ! defined( 'SWCFPC_HOME_PAGE_SHOWS_POSTS' ) ) {
		define( 'SWCFPC_HOME_PAGE_SHOWS_POSTS', true );
	}

	class SW_CLOUDFLARE_PAGECACHE {

		private $config  = false;
		private $modules = [];
		private $version = '5.0.9';

		public const REDIRECT_KEY = 'swcfpc_dashboard_redirect';

		function __construct() {
			register_deactivation_hook( SPC_PATH, [ $this, 'deactivate_plugin' ] );

			if ( ! $this->init_config() ) {
				$this->config = $this->get_default_config();
				$this->update_config();
			}

			if ( ! file_exists( $this->get_plugin_wp_content_directory() ) ) {
				$this->create_plugin_wp_content_directory();
			}

			$this->update_plugin();
			$this->include_libs();
			$this->actions();
		}

		function load_textdomain() {
			load_plugin_textdomain( 'wp-cloudflare-page-cache', false, basename( dirname( SPC_PATH ) ) . '/languages/' );
		}


		function include_libs() {
			if ( count( $this->modules ) > 0 ) {
				return;
			}

			$this->modules = [];

			include_once ABSPATH . 'wp-includes/pluggable.php';

			// Composer autoload.
			if ( file_exists( SWCFPC_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
				require SWCFPC_PLUGIN_PATH . 'vendor/autoload.php';
			}

			require_once SWCFPC_PLUGIN_PATH . 'libs/preloader.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/cloudflare.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/logs.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/cache_controller.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/backend.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/fallback_cache.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/varnish.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/html_cache.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/test_cache.class.php';

			$log_file_path = $this->get_plugin_wp_content_directory() . '/debug.log';
			$log_file_url  = $this->get_plugin_wp_content_directory_url() . '/debug.log';

			$this->modules = apply_filters( 'swcfpc_include_libs_early', $this->modules );

			if ( $this->get_single_config( 'log_enabled', 0 ) > 0 ) {
				$this->modules['logs'] = new SWCFPC_Logs( $log_file_path, $log_file_url, true, $this->get_single_config( 'log_max_file_size', 2 ), $this );
			} else {
				$this->modules['logs'] = new SWCFPC_Logs( $log_file_path, $log_file_url, false, $this->get_single_config( 'log_max_file_size', 2 ), $this );
			}

			$this->modules['logs']->set_verbosity( $this->get_single_config( 'log_verbosity', SWCFPC_LOGS_HIGH_VERBOSITY ) );

			$this->modules['cloudflare'] = new SWCFPC_Cloudflare( $this );

			$this->modules['fallback_cache']   = new SWCFPC_Fallback_Cache( $this );
			$this->modules['html_cache']       = new SWCFPC_Html_Cache( $this );
			$this->modules['cache_controller'] = new SWCFPC_Cache_Controller( SWCFPC_CACHE_BUSTER, $this );
			$this->modules['varnish']          = new SWCFPC_Varnish( $this );
			$this->modules['backend']          = new SWCFPC_Backend( $this );

			$this->maybe_load_pro_modules();

			new SPC\Loader();

			if ( ( ! defined( 'WP_CLI' ) || ( defined( 'WP_CLI' ) && WP_CLI === false ) ) && isset( $_SERVER['REQUEST_METHOD'] ) && strcasecmp( $_SERVER['REQUEST_METHOD'], 'GET' ) == 0 && ! is_admin() && ! $this->is_login_page() && $this->get_single_config( 'cf_fallback_cache', 0 ) > 0 && $this->modules['cache_controller']->is_cache_enabled() ) {
				$this->modules['fallback_cache']->fallback_cache_retrive_current_page();
			}

			$this->modules = apply_filters( 'swcfpc_include_libs_lately', $this->modules );

			// Initialize the preloader class here as this method is called on the plugin_loaded event. After that, I can instantiate the object even in Ajax calls
			new SWCFPC_Preloader_Process( $this );

			$this->enable_wp_cli_support();
		}

		function actions() {
			add_action( 'admin_init', [ $this, 'maybe_deactivate_free' ] );
			add_filter( 'themeisle_sdk_products', [ $this, 'load_sdk' ] );
			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		}

		function load_sdk( $products ) {
			$products[] = SWCFPC_BASEFILE;

			return $products;
		}

		function get_default_config() {

			$config = [];

			// Cloudflare config
			$config['cf_zoneid']                       = '';
			$config['cf_zoneid_list']                  = [];
			$config['cf_email']                        = '';
			$config['cf_apitoken']                     = '';
			$config['cf_apikey']                       = '';
			$config['cf_token']                        = '';
			$config['cf_apitoken_domain']              = $this->get_second_level_domain();
			$config['cf_old_bc_ttl']                   = '';
			$config['cf_page_rule_id']                 = '';
			$config['cf_bypass_backend_page_rule_id']  = '';
			$config['cf_bypass_backend_page_rule']     = 0;
			$config['cf_cache_enabled']                = 0;
			$config['cf_maxage']                       = 31536000; // 1 year
			$config['cf_browser_maxage']               = 60; // 1 minute
			$config['cf_post_per_page']                = get_option( 'posts_per_page', 0 );
			$config['cf_purge_url_secret_key']         = $this->generate_password( 20, false, false );
			$config['cf_strip_cookies']                = 0;
			$config['cf_fallback_cache']               = 0;
			$config['cf_fallback_cache_ttl']           = 0;
			$config['cf_fallback_cache_auto_purge']    = 1;
			$config['cf_fallback_cache_curl']          = 0;
			$config['cf_fallback_cache_excluded_urls'] = [];
			$config['cf_fallback_cache_save_headers']  = 0;
			$config['cf_fallback_cache_prevent_cache_urls_without_trailing_slash'] = 1;
			$config['cf_preloader']                               = 1;
			$config['cf_preloader_start_on_purge']                = 1;
			$config['cf_preloader_nav_menus']                     = [];
			$config['cf_preload_last_urls']                       = 1;
			$config['cf_preload_excluded_post_types']             = [
				'attachment',
				'jet-menu',
				'elementor_library',
				'jet-theme-core',
			];
			$config['cf_woker_enabled']                           = 0;
			$config['cf_woker_id']                                = 'swcfpc_worker_' . time();
			$config['cf_woker_route_id']                          = '';
			$config['cf_worker_bypass_cookies']                   = [];
			$config['cf_purge_only_html']                         = 0;
			$config['cf_disable_cache_purging_queue']             = 0;
			$config['cf_auto_purge_on_upgrader_process_complete'] = 0;

			// Pages
			$config['cf_bypass_front_page']      = 0;
			$config['cf_bypass_pages']           = 0;
			$config['cf_bypass_home']            = 0;
			$config['cf_bypass_archives']        = 0;
			$config['cf_bypass_tags']            = 0;
			$config['cf_bypass_category']        = 0;
			$config['cf_bypass_author_pages']    = 0;
			$config['cf_bypass_single_post']     = 0;
			$config['cf_bypass_feeds']           = 1;
			$config['cf_bypass_search_pages']    = 1;
			$config['cf_bypass_404']             = 1;
			$config['cf_bypass_logged_in']       = 1;
			$config['cf_bypass_amp']             = 0;
			$config['cf_bypass_file_robots']     = 1;
			$config['cf_bypass_sitemap']         = 1;
			$config['cf_bypass_ajax']            = 1;
			$config['cf_cache_control_htaccess'] = 0;
			$config['cf_auth_mode']              = SWCFPC_AUTH_MODE_API_KEY;
			// $config['cf_bypass_post']                   = 0;
			$config['cf_bypass_query_var']    = 0;
			$config['cf_bypass_wp_json_rest'] = 0;

			// Ruleset
			$config['cf_cache_settings_ruleset_id']      = '';
			$config['cf_cache_settings_ruleset_rule_id'] = '';

			// Varnish
			$config['cf_varnish_support']          = 0;
			$config['cf_varnish_auto_purge']       = 1;
			$config['cf_varnish_hostname']         = 'localhost';
			$config['cf_varnish_port']             = 6081;
			$config['cf_varnish_cw']               = 0;
			$config['cf_varnish_purge_method']     = 'PURGE';
			$config['cf_varnish_purge_all_method'] = 'PURGE';

			// WooCommerce
			$config['cf_bypass_woo_shop_page']           = 0;
			$config['cf_bypass_woo_pages']               = 0;
			$config['cf_bypass_woo_product_tax_page']    = 0;
			$config['cf_bypass_woo_product_tag_page']    = 0;
			$config['cf_bypass_woo_product_cat_page']    = 0;
			$config['cf_bypass_woo_product_page']        = 0;
			$config['cf_bypass_woo_cart_page']           = 1;
			$config['cf_bypass_woo_checkout_page']       = 1;
			$config['cf_bypass_woo_checkout_pay_page']   = 1;
			$config['cf_auto_purge_woo_product_page']    = 1;
			$config['cf_auto_purge_woo_scheduled_sales'] = 1;
			$config['cf_bypass_woo_account_page']        = 1;

			// Swift Performance (Lite/Pro)
			$config['cf_spl_purge_on_flush_all']         = 1;
			$config['cf_spl_purge_on_flush_single_post'] = 1;

			// W3TC
			$config['cf_w3tc_purge_on_flush_minfy']         = 0;
			$config['cf_w3tc_purge_on_flush_posts']         = 0;
			$config['cf_w3tc_purge_on_flush_objectcache']   = 0;
			$config['cf_w3tc_purge_on_flush_fragmentcache'] = 0;
			$config['cf_w3tc_purge_on_flush_dbcache']       = 0;
			$config['cf_w3tc_purge_on_flush_all']           = 1;

			// WP Rocket
			$config['cf_wp_rocket_purge_on_post_flush']               = 1;
			$config['cf_wp_rocket_purge_on_domain_flush']             = 1;
			$config['cf_wp_rocket_purge_on_cache_dir_flush']          = 1;
			$config['cf_wp_rocket_purge_on_clean_files']              = 1;
			$config['cf_wp_rocket_purge_on_clean_cache_busting']      = 1;
			$config['cf_wp_rocket_purge_on_clean_minify']             = 1;
			$config['cf_wp_rocket_purge_on_ccss_generation_complete'] = 1;
			$config['cf_wp_rocket_purge_on_rucss_job_complete']       = 1;

			// Litespeed Cache
			$config['cf_litespeed_purge_on_cache_flush']        = 1;
			$config['cf_litespeed_purge_on_ccss_flush']         = 1;
			$config['cf_litespeed_purge_on_cssjs_flush']        = 1;
			$config['cf_litespeed_purge_on_object_cache_flush'] = 1;
			$config['cf_litespeed_purge_on_single_post_flush']  = 1;

			// Flying Press
			$config['cf_flypress_purge_on_cache_flush'] = 1;

			// Hummingbird
			$config['cf_hummingbird_purge_on_cache_flush'] = 1;

			// WP-Optimize
			$config['cf_wp_optimize_purge_on_cache_flush'] = 1;

			// Yasr
			$config['cf_yasr_purge_on_rating'] = 0;

			// WP Asset Clean Up
			$config['cf_wpacu_purge_on_cache_flush'] = 1;

			// Autoptimize
			$config['cf_autoptimize_purge_on_cache_flush'] = 1;

			// WP Asset Clean Up
			$config['cf_nginx_helper_purge_on_cache_flush'] = 1;

			// WP Performance
			$config['cf_wp_performance_purge_on_cache_flush'] = 1;

			// EDD
			$config['cf_bypass_edd_checkout_page']         = 1;
			$config['cf_bypass_edd_success_page']          = 0;
			$config['cf_bypass_edd_failure_page']          = 0;
			$config['cf_bypass_edd_purchase_history_page'] = 1;
			$config['cf_bypass_edd_login_redirect_page']   = 1;
			$config['cf_auto_purge_edd_payment_add']       = 1;

			// WP Engine
			$config['cf_wpengine_purge_on_flush'] = 1;

			// SpinupWP
			$config['cf_spinupwp_purge_on_flush'] = 1;

			// Kinsta
			$config['cf_kinsta_purge_on_flush'] = 1;

			// Siteground
			$config['cf_siteground_purge_on_flush'] = 1;

			// Logs
			$config['log_enabled']       = 1;
			$config['log_max_file_size'] = 2; // Megabytes
			$config['log_verbosity']     = SWCFPC_LOGS_STANDARD_VERBOSITY;

			// Other
			$config['cf_remove_purge_option_toolbar']      = 0;
			$config['cf_disable_single_metabox']           = 1;
			$config['cf_seo_redirect']                     = 0;
			$config['cf_opcache_purge_on_flush']           = 0;
			$config['cf_object_cache_purge_on_flush']      = 0;
			$config['cf_purge_roles']                      = [];
			$config['cf_prefetch_urls_viewport']           = 0;
			$config['cf_prefetch_urls_viewport_timestamp'] = time();
			$config['keep_settings_on_deactivation']       = 1;

			return apply_filters( 'swcfpc_main_config_defaults', $config );
		}

		function get_single_config( $name, $default = false ) {
			if ( ! is_array( $this->config ) || ! isset( $this->config[ $name ] ) ) {
				return $default;
			}

			if ( is_array( $this->config[ $name ] ) ) {
				return $this->config[ $name ];
			}

			return trim( $this->config[ $name ] );
		}

		function set_single_config( $name, $value ) {
			if ( ! is_array( $this->config ) ) {
				$this->config = [];
			}

			if ( is_array( $value ) ) {
				$this->config[ trim( $name ) ] = $value;
			} else {
				$this->config[ trim( $name ) ] = trim( $value );
			}
		}

		function update_config() {
			update_option( 'swcfpc_config', $this->config );
		}

		function init_config() {
			$this->config = get_option( 'swcfpc_config', false );

			if ( ! $this->config ) {
				return false;
			}

			// If the option exists, return true
			return true;
		}

		function set_config( $config ) {
			$this->config = $config;
		}

		function get_config() {
			return $this->config;
		}

		function update_plugin() {

			$current_version = get_option( 'swcfpc_version', false );

			$is_different_version_from_previous = version_compare( $current_version, $this->version, '!=' );

			if ( $current_version === false || $is_different_version_from_previous ) {

				require_once SWCFPC_PLUGIN_PATH . 'libs/installer.class.php';

				if ( $current_version === false ) {
					$installer = new SWCFPC_Installer();
					$installer->start();
				} else {

					if ( version_compare( $current_version, '4.5', '<' ) ) {

						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.5' );

						$this->set_single_config( 'cf_auto_purge_on_upgrader_process_complete', 0 );
						$this->set_single_config( 'cf_bypass_wp_json_rest', 0 );
						$this->set_single_config( 'cf_bypass_woo_account_page', 1 );
						$this->set_single_config( 'keep_settings_on_deactivation', 1 );

						$excluded_urls = $this->get_single_config( Constants::SETTING_EXCLUDED_URLS, [] );

						if ( is_array( $excluded_urls ) ) {

							if ( ! in_array( '/my-account*', $excluded_urls ) ) {
								$excluded_urls[] = '/my-account*';
							}

							if ( ! in_array( '/wc-api/*', $excluded_urls ) ) {
								$excluded_urls[] = '/wc-api/*';
							}

							if ( ! in_array( '/edd-api/*', $excluded_urls ) ) {
								$excluded_urls[] = '/edd-api/*';
							}

							if ( ! in_array( '/wp-json*', $excluded_urls ) ) {
								$excluded_urls[] = '/wp-json*';
							}

							$this->set_single_config( Constants::SETTING_EXCLUDED_URLS, $excluded_urls );

						}

						$this->update_config();

						// Called to force the creation of nginx.conf inside the plugin's directory inside the wp-content one
						$this->create_plugin_wp_content_directory();

						add_action(
							'shutdown',
							function () {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								if ( $sw_cloudflare_pagecache->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {

									$error_msg_cf = '';

									$objects['cloudflare']->disable_page_cache( $error_msg_cf );
									$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								}

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.5 complete' );

							},
							PHP_INT_MAX
						);

					}

					if ( version_compare( $current_version, '4.5.6', '<' ) ) {

						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.5.6' );

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Initiating the removal of double serialization for swcfpc_config' );

						// Get the serialized version of the swcfpc_config
						$serialized_swcfpc_config = get_option( 'swcfpc_config', false );

						if ( ! $serialized_swcfpc_config ) {

							$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Serialized swcfpc_config not present' );

						} else {

							// Unserialize the data to be further stored
							if ( is_string( $serialized_swcfpc_config ) ) {
								$unserialized_swcfpc_config = unserialize( $serialized_swcfpc_config );

								// Now store the same data again to swcfpc_config,
								// But this time we won't serialize the data, instead WP will automatically do it.
								update_option( 'swcfpc_config', $unserialized_swcfpc_config );
							} else {
								$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Unfortunately swcfpc_config did not returned a string. So, we can\'t unserialize it.' );
							}
						}


						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Initiating the removal of double serialization for swcfpc_fc_ttl_registry' );

						// Get the serialized version of the swcfpc_fc_ttl_registry
						$serialized_swcfpc_fc_ttl_registry = get_option( 'swcfpc_fc_ttl_registry', false );

						if ( ! $serialized_swcfpc_fc_ttl_registry ) {

							$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Serialized swcfpc_fc_ttl_registry not present' );

						} else {

							if ( is_string( $serialized_swcfpc_fc_ttl_registry ) ) {
								// Unserialize the data to be further stored
								$unserialized_swcfpc_fc_ttl_registry = unserialize( $serialized_swcfpc_fc_ttl_registry );

								// Now store the same data again to swcfpc_fc_ttl_registry,
								// But this time we won't serialize the data, instead WP will automatically do it.
								update_option( 'swcfpc_fc_ttl_registry', serialize( $unserialized_swcfpc_fc_ttl_registry ) );
							} else {
								$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Unfortunately swcfpc_fc_ttl_registry did not returned a string. So, we can\'t unserialize it.' );
							}
						}

						add_action(
							'shutdown',
							function () {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								if ( $sw_cloudflare_pagecache->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {

									$error_msg_cf = '';

									$objects['cloudflare']->disable_page_cache( $error_msg_cf );
									$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								}

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.5.6 complete' );

							},
							PHP_INT_MAX
						);
					}

					if ( version_compare( $current_version, '4.6.1', '<' ) ) {
						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.6.1' );

						add_action(
							'shutdown',
							function () {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								$error_msg_cf = '';

								// Enable Disable the Page Cache to take effect of the changes
								$objects['cloudflare']->disable_page_cache( $error_msg_cf );
								$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.6.1 complete' );

							},
							PHP_INT_MAX
						);
					}

					if ( version_compare( $current_version, '4.7.3', '<' ) ) {
						if ( count( $this->modules ) == 0 ) {
							$this->include_libs();
						}

						$this->modules['logs']->add_log( 'swcfpc::update_plugin', 'Updating to v4.7.3' );

						add_action(
							'shutdown',
							function () {

								global $sw_cloudflare_pagecache;

								$objects = $sw_cloudflare_pagecache->get_modules();

								$error_msg_cf = '';

								// Enable Disable the Page Cache to take effect of the changes
								$objects['cloudflare']->disable_page_cache( $error_msg_cf );
								$objects['cloudflare']->enable_page_cache( $error_msg_cf );

								$objects['logs']->add_log( 'swcfpc::update_plugin', 'Update to v4.7.3 complete' );

							},
							PHP_INT_MAX
						);
					}
				}
			}

			if ( $is_different_version_from_previous || get_option( 'cf_will_run_free_to_pro_migrations' ) === 'yes' ) {
				add_action(
					'shutdown',
					function () {
						delete_option( 'cf_will_run_free_to_pro_migrations' );
						update_option( 'swcfpc_version', $this->version );
						$migrations = new \SPC\Migrator( $this );
						$migrations->run_update_migrations();
					}
				);
			}

			update_option( 'swcfpc_version', $this->version );
		}

		function deactivate_plugin() {
			// Keep settings when upgrading.
			if ( defined( 'SPC_PRO_PATH' ) && defined( 'SPC_FREE_PATH' ) ) {
				return;
			}

			if ( $this->get_single_config( 'keep_settings_on_deactivation', 1 ) > 0 ) {
				$this->modules['cache_controller']->reset_all( true );
			} else {
				$this->modules['cache_controller']->reset_all();
			}

			$this->delete_plugin_wp_content_directory();
		}

		/**
		 * If both free & pro are active, we attempt to deactivate the free version.
		 *
		 * @return void
		 */
		function maybe_deactivate_free() {
			if ( defined( 'SPC_PRO_PATH' ) && defined( 'SPC_FREE_PATH' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';

				deactivate_plugins( SPC_FREE_PATH );

				add_action(
					'admin_notices',
					function () {
						printf(
							'<div class="notice notice-warning"><p><strong>%s</strong><br>%s</p></div>',
							sprintf(
							/* translators: %s: Name of deactivated plugin */
								__( '%s plugin deactivated.', 'wp-cloudflare-page-cache' ),
								'Super Page Cache for Cloudflare(Free)'
							),
							'Using the Premium version of Super Page Cache for Cloudflare is not requiring using the Free version.'
						);
					}
				);

				update_option( 'cf_will_run_free_to_pro_migrations', 'yes' );

				do_action( 'swcfpc_after_free_deactivation' );
			}
		}

		/**
		 * Get the modules.
		 *
		 * @return array
		 */
		function get_modules() {
			return $this->modules;
		}

		/**
		 * Get the modules.
		 *
		 * Legacy function to preserve backward compatibility for old `advanced-cache.php` files.
		 *
		 * @return array
		 *
		 * @deprecated Use get_modules() instead.
		 */
		function get_objects() {
			return $this->get_modules();
		}

		/**
		 * Get the Zone ID.
		 *
		 * @return string
		 */
		public function get_cloudflare_api_zone_id() {

			if ( defined( 'SWCFPC_CF_API_ZONE_ID' ) ) {
				return SWCFPC_CF_API_ZONE_ID;
			}

			return $this->get_single_config( 'cf_zoneid', '' );
		}

		/**
		 * Checks if the Zone ID is set.
		 *
		 * @return bool
		 */
		public function has_cloudflare_api_zone_id() {
			return ! empty( $this->get_cloudflare_api_zone_id() );
		}

		/**
		 * Get the Zone Name.
		 *
		 * @param string $zone_id The Zone ID.
		 *
		 * @return string
		 */
		function get_cloudflare_api_zone_domain_name( $zone_id ) {

			if ( defined( 'SWCFPC_CF_API_ZONE_NAME' ) ) {
				return SWCFPC_CF_API_ZONE_NAME;
			}

			$zone_id_list = $this->get_single_config( 'cf_zoneid_list', [] );
			foreach ( $zone_id_list as $zone_name => $zone_id_item ) {
				if ( $zone_id === $zone_id_item ) {
					return $zone_name;
				}
			}

			return '';
		}

		/**
		 * Get the API Key.
		 *
		 * @return string
		 */
		function get_cloudflare_api_key() {

			if ( defined( 'SWCFPC_CF_API_KEY' ) ) {
				return SWCFPC_CF_API_KEY;
			}

			return $this->get_single_config( 'cf_apikey', '' );

		}

		/**
		 * Get the API Email.
		 *
		 * @return string
		 */
		function get_cloudflare_api_email() {

			if ( defined( 'SWCFPC_CF_API_EMAIL' ) ) {
				return SWCFPC_CF_API_EMAIL;
			}

			return $this->get_single_config( 'cf_email', '' );

		}

		/**
		 * Get the API Token.
		 *
		 * @return string
		 */
		function get_cloudflare_api_token() {

			if ( defined( 'SWCFPC_CF_API_TOKEN' ) ) {
				return SWCFPC_CF_API_TOKEN;
			}

			return $this->get_single_config( 'cf_apitoken', '' );

		}

		/**
		 * Get Worker status.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_mode() {

			if ( defined( 'SWCFPC_CF_WOKER_ENABLED' ) ) {
				return SWCFPC_CF_WOKER_ENABLED;
			}

			return $this->get_single_config( 'cf_woker_enabled', 0 );

		}

		/**
		 * Get the Worker ID.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_id() {

			if ( defined( 'SWCFPC_CF_WOKER_ID' ) ) {
				return SWCFPC_CF_WOKER_ID;
			}

			return $this->get_single_config( 'cf_woker_id', 'swcfpc_worker_' . time() );
		}

		/**
		 * Get the Worker Route ID.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_route_id() {

			if ( defined( 'SWCFPC_CF_WOKER_ROUTE_ID' ) ) {
				return SWCFPC_CF_WOKER_ROUTE_ID;
			}

			return $this->get_single_config( 'cf_woker_route_id', '' );

		}

		/**
		 * Get the Worker Content.
		 *
		 * @return string
		 */
		function get_cloudflare_worker_content() {

			$worker_content = '';

			if ( defined( 'SWCFPC_CF_WOKER_FULL_PATH' ) && file_exists( SWCFPC_CF_WOKER_FULL_PATH ) ) {
				$worker_content = file_get_contents( SWCFPC_CF_WOKER_FULL_PATH );
			} elseif ( file_exists( SWCFPC_PLUGIN_PATH . 'assets/js/worker_template.js' ) ) {
				$worker_content = file_get_contents( SWCFPC_PLUGIN_PATH . 'assets/js/worker_template.js' );
			}

			return $worker_content;
		}


		function get_plugin_wp_content_directory() {

			$parts = parse_url( home_url() );

			return WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$parts['host']}";

		}


		function get_plugin_wp_content_directory_url() {

			$parts = parse_url( home_url() );

			return content_url( "wp-cloudflare-super-page-cache/{$parts['host']}" );

		}


		function get_plugin_wp_content_directory_uri() {

			$parts = parse_url( home_url() );

			return str_replace(
				[
					"https://{$parts['host']}",
					"http://{$parts['host']}",
				],
				'',
				content_url( "wp-cloudflare-super-page-cache/{$parts['host']}" )
			);

		}


		function create_plugin_wp_content_directory() {

			$parts = parse_url( home_url() );
			$path  = WP_CONTENT_DIR . '/wp-cloudflare-super-page-cache/';

			if ( ! file_exists( $path ) && wp_mkdir_p( $path, 0755 ) ) {
				file_put_contents( "{$path}index.php", '<?php // Silence is golden' );
			}

			$path .= $parts['host'];

			if ( ! file_exists( $path ) && wp_mkdir_p( $path, 0755 ) ) {
				file_put_contents( "{$path}/index.php", '<?php // Silence is golden' );
			}

			$nginx_conf = "{$path}/nginx.conf";

			if ( ! file_exists( $nginx_conf ) ) {
				file_put_contents( $nginx_conf, '' );
			}

		}


		function delete_plugin_wp_content_directory() {

			$parts = parse_url( home_url() );
			$path  = WP_CONTENT_DIR . '/wp-cloudflare-super-page-cache/';
			$path .= $parts['host'];

			if ( file_exists( $path ) ) {
				$this->delete_directory_recursive( $path );
			}

		}


		function delete_directory_recursive( $dir ) {

			if ( ! class_exists( 'RecursiveDirectoryIterator' ) || ! class_exists( 'RecursiveIteratorIterator' ) ) {
				return false;
			}

			$it    = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
			$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );

			foreach ( $files as $file ) {

				if ( $file->isDir() ) {
					rmdir( $file->getRealPath() );
				} else {
					unlink( $file->getRealPath() );
				}
			}

			rmdir( $dir );

			return true;

		}


		function generate_password( $length = 12, $special_chars = true, $extra_special_chars = false ) {

			$chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$password = '';

			if ( $special_chars ) {
				$chars .= '!@#$%^&*()';
			}
			if ( $extra_special_chars ) {
				$chars .= '-_ []{}<>~`+=,.;:/?|';
			}

			for ( $i = 0; $i < $length; $i ++ ) {
				$password .= substr( $chars, rand( 0, strlen( $chars ) - 1 ), 1 );
			}

			return $password;

		}


		function is_login_page() {

			return in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ] );

		}


		function get_second_level_domain() {

			$site_hostname = parse_url( home_url(), PHP_URL_HOST );

			if ( is_null( $site_hostname ) ) {
				return '';
			}

			// get the domain name from the hostname
			$site_domain = preg_replace( '/^www\./', '', $site_hostname );

			return $site_domain;

		}


		function enable_wp_cli_support() {

			if ( defined( 'WP_CLI' ) && WP_CLI && ! class_exists( 'SWCFPC_WP_CLI' ) && class_exists( 'WP_CLI_Command' ) ) {

				require_once SWCFPC_PLUGIN_PATH . 'libs/wpcli.class.php';

				$wpcli = new SWCFPC_WP_CLI( $this );

				WP_CLI::add_command( 'cfcache', $wpcli );


			}

		}


		function can_current_user_purge_cache() {

			if ( ! is_user_logged_in() ) {
				return false;
			}

			if ( current_user_can( 'manage_options' ) ) {
				return true;
			}

			$allowed_roles = $this->get_single_config( 'cf_purge_roles', [] );

			if ( count( $allowed_roles ) > 0 ) {

				$user = wp_get_current_user();

				foreach ( $allowed_roles as $role_name ) {

					if ( in_array( $role_name, (array) $user->roles ) ) {
						return true;
					}
				}
			}

			return false;

		}


		function get_wordpress_roles() {

			global $wp_roles;
			$wordpress_roles = [];

			foreach ( $wp_roles->roles as $role => $role_data ) {
				$wordpress_roles[] = $role;
			}

			return $wordpress_roles;

		}


		function does_current_url_have_trailing_slash() {

			if ( ! preg_match( '/\/$/', $_SERVER['REQUEST_URI'] ) ) {
				return false;
			}

			return true;

		}


		function is_api_request() {

			// WordPress standard API
			if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || strcasecmp( substr( $_SERVER['REQUEST_URI'], 0, 8 ), '/wp-json' ) == 0 ) {
				return true;
			}

			// WooCommerce standard API
			if ( strcasecmp( substr( $_SERVER['REQUEST_URI'], 0, 8 ), '/wc-api/' ) == 0 ) {
				return true;
			}

			// WooCommerce standard API
			if ( strcasecmp( substr( $_SERVER['REQUEST_URI'], 0, 9 ), '/edd-api/' ) == 0 ) {
				return true;
			}

			return false;

		}


		function wildcard_match( $pattern, $subject ) {

			$pattern = '#^' . preg_quote( $pattern ) . '$#i'; // Case insensitive
			$pattern = str_replace( '\*', '.*', $pattern );
			// $pattern = str_replace('\.', '.', $pattern);

			if ( ! preg_match( $pattern, $subject, $regs ) ) {
				return false;
			}

			return true;

		}

		// Pass parse_url() array and get the URL back as string
		function get_unparsed_url( $parsed_url ) {
			// PHP_URL_SCHEME
			$scheme   = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
			$host     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
			$port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
			$user     = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
			$pass     = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass'] : '';
			$pass     = ( $user || $pass ) ? "$pass@" : '';
			$path     = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
			$query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
			$fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';

			return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
		}

		// Return the ignored query params array
		function get_ignored_query_params() {
			return apply_filters( 'swcfpc_ignored_query_params', \SPC\Constants::IGNORED_QUERY_PARAMS );
		}

		function get_current_lang_code() {

			$current_language_code = false;

			if ( has_filter( 'wpml_current_language' ) ) {
				$current_language_code = apply_filters( 'wpml_current_language', null );
			}

			return $current_language_code;

		}


		function get_permalink( $post_id ) {

			$url = get_the_permalink( $post_id );

			if ( has_filter( 'wpml_permalink' ) ) {
				$url = apply_filters( 'wpml_permalink', $url, $this->get_current_lang_code() );
			}

			return $url;

		}

		function get_home_url( $blog_id = null, $path = '', $scheme = null ) {

			global $pagenow;

			if ( empty( $blog_id ) || ! is_multisite() ) {
				$url = get_option( 'home' );
			} else {
				switch_to_blog( $blog_id );
				$url = get_option( 'home' );
				restore_current_blog();
			}

			if ( ! in_array( $scheme, [ 'http', 'https', 'relative' ], true ) ) {

				if ( is_ssl() && ! is_admin() && 'wp-login.php' !== $pagenow ) {
					$scheme = 'https';
				} else {
					$scheme = parse_url( $url, PHP_URL_SCHEME );
				}
			}

			$url = set_url_scheme( $url, $scheme );

			if ( $path && is_string( $path ) ) {
				$url .= '/' . ltrim( $path, '/' );
			}

			return $url;

		}

		function home_url( $path = '', $scheme = null ) {
			return $this->get_home_url( null, $path, $scheme );
		}

		function maybe_load_pro_modules() {
			if (
				! is_file( SWCFPC_PLUGIN_PATH . 'pro/Loader.php' ) ||
				! defined( 'SPC_PRO_PATH' ) ||
				! class_exists( 'SPC_Pro\\Loader' )
			) {
				return;
			}

			$loader = new SPC_Pro\Loader( $this );
			$loader->init();
		}

		function get_plugin_version() {
			return $this->version;
		}

		/**
		 * @return SWCFPC_Cloudflare
		 */
		public function get_cloudflare_handler() {
			if ( ! isset( $this->modules['cloudflare'] ) ) {
				$this->include_libs();
			}

			return $this->modules['cloudflare'];
		}

		/**
		 * @return SWCFPC_Cache_Controller
		 */
		public function get_cache_controller() {
			if ( ! isset( $this->modules['cache_controller'] ) ) {
				$this->include_libs();
			}

			return $this->modules['cache_controller'];
		}

		/**
		 * @return SWCFPC_Logs
		 */
		public function get_logger() {
			if ( ! isset( $this->modules['logs'] ) ) {
				$this->include_libs();
			}

			return $this->modules['logs'];
		}

		/**
		 * @return SWCFPC_Fallback_Cache
		 */
		public function get_fallback_cache_handler() {
			if ( ! isset( $this->modules['fallback_cache'] ) ) {
				$this->include_libs();
			}

			return $this->modules['fallback_cache'];
		}

		/**
		 * @return SWCFPC_Html_Cache
		 */
		public function get_html_cache_handler() {
			if ( ! isset( $this->modules['html_cache'] ) ) {
				$this->include_libs();
			}

			return $this->modules['html_cache'];
		}

		/**
		 * @return SWCFPC_Varnish
		 */
		public function get_varnish_handler() {
			if ( ! isset( $this->modules['varnish'] ) ) {
				$this->include_libs();
			}

			return $this->modules['varnish'];
		}
	}

	// Activate this plugin as last plugin
	add_action(
		'plugins_loaded',
		function () {

			if ( ! isset( $GLOBALS['sw_cloudflare_pagecache'] ) || empty( $GLOBALS['sw_cloudflare_pagecache'] ) ) {
				$GLOBALS['sw_cloudflare_pagecache'] = new SW_CLOUDFLARE_PAGECACHE();
			}

		},
		PHP_INT_MAX
	);

	register_activation_hook(
		SPC_PATH,
		function () {
			update_option( SW_CLOUDFLARE_PAGECACHE::REDIRECT_KEY, true );
		}
	);
}
