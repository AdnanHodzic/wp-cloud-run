<?php
/**
 * Plugin bootstrap file.
 */

use SPC\Constants;
use SPC\Models\Asset_Rules;
use SPC\Modules\Third_Party;
use SPC\Services\Settings_Store;

if ( ! class_exists( 'SW_CLOUDFLARE_PAGECACHE' ) ) {
	define( 'SPC_PATH', defined( 'SPC_PRO_PATH' ) ? SPC_PRO_PATH : SPC_FREE_PATH );

	define( 'SWCFPC_PLUGIN_PATH', plugin_dir_path( SPC_PATH ) );
	define( 'SWCFPC_PLUGIN_URL', plugin_dir_url( SPC_PATH ) );
	define( 'SWCFPC_BASEFILE', SPC_PATH );
	define( 'SWCFPC_PRODUCT_SLUG', basename( dirname( SPC_PATH ) ) );
	define( 'SWCFPC_PLUGIN_REVIEWS_URL', 'https://wordpress.org/support/plugin/wp-cloudflare-page-cache/reviews/' );
	define( 'SWCFPC_PLUGIN_FORUM_URL', 'https://wordpress.org/support/plugin/wp-cloudflare-page-cache/' );
	define( 'SWCFPC_AUTH_MODE_API_KEY', 0 );
	define( 'SWCFPC_AUTH_MODE_API_TOKEN', 1 );
	define( 'SWCFPC_LOGS_STANDARD_VERBOSITY', 1 );
	define( 'SWCFPC_LOGS_HIGH_VERBOSITY', 2 );
	define( 'SWCFPC_VERSION', '5.2.2' );
	if ( ! defined( 'SPC_METRICS_DIR' ) ) {
		$home_url_parts = parse_url( home_url() );
		define( 'SPC_METRICS_DIR', WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$home_url_parts['host']}/metrics" );
	}

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

	add_filter(
		'themesle_sdk_namespace_' . md5( SPC_PATH ),
		function ( $encoded_basefile ) {
			return 'spc';
		}
	);

	class SW_CLOUDFLARE_PAGECACHE {

		/**
		 * The plugin version.
		 */
		private $version = SWCFPC_VERSION;
		/**
		 * The modules.
		 */
		private $modules = [];

		/**
		 * The core loader.
		 * @var \SPC\Loader $core_loader
		 */
		private $core_loader;

		/**
		 * The pro modules.
		 * @var \SPC_Pro\Loader $pro_loader
		 */
		private $pro_loader;

		/**
		 * The redirect option key.
		 */
		public const REDIRECT_KEY = 'swcfpc_dashboard_redirect';

		/**
		 * SW_CLOUDFLARE_PAGECACHE constructor.
		 */
		public function __construct() {
			register_deactivation_hook( SPC_PATH, [ $this, 'deactivate_plugin' ] );

			if ( ! file_exists( $this->get_plugin_wp_content_directory() ) ) {
				$this->create_plugin_wp_content_directory();
			}

			$this->load_composer();

			$this->update_plugin();
			$this->include_libs();
			$this->actions();
		}

		/**
		 * Load the composer autoloader.
		 *
		 * @return void
		 */
		public function load_composer() {
			if ( file_exists( SWCFPC_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
				require SWCFPC_PLUGIN_PATH . 'vendor/autoload.php';
			}
		}

		/**
		 * Load the textdomain.
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'wp-cloudflare-page-cache', false, basename( dirname( SPC_PATH ) ) . '/languages/' );
		}

		/**
		 * Include the libraries.
		 */
		private function include_libs() {
			if ( count( $this->modules ) > 0 ) {
				return;
			}

			$this->modules = [];

			include_once ABSPATH . 'wp-includes/pluggable.php';

			require_once SWCFPC_PLUGIN_PATH . 'libs/preloader.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/cloudflare.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/logs.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/cache_controller.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/backend.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/fallback_cache.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/varnish.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/html_cache.class.php';
			require_once SWCFPC_PLUGIN_PATH . 'libs/test_cache.class.php';

			$settings_store = Settings_Store::get_instance();

			$this->modules = apply_filters( 'swcfpc_include_libs_early', $this->modules );

			$this->core_loader = new SPC\Loader();

			$this->modules['logs']             = new SWCFPC_Logs( $this );
			$this->modules['cloudflare']       = new SWCFPC_Cloudflare( $this );
			$this->modules['fallback_cache']   = new SWCFPC_Fallback_Cache( $this );
			$this->modules['html_cache']       = new SWCFPC_Html_Cache( $this );
			$this->modules['cache_controller'] = new SWCFPC_Cache_Controller( SWCFPC_CACHE_BUSTER, $this );
			$this->modules['varnish']          = new SWCFPC_Varnish( $this );
			$this->modules['backend']          = new SWCFPC_Backend( $this );

			$this->maybe_load_pro_modules();
			$this->core_loader->load_modules();

			if ( ( ! defined( 'WP_CLI' ) || ( defined( 'WP_CLI' ) && WP_CLI === false ) ) && isset( $_SERVER['REQUEST_METHOD'] ) && strcasecmp( $_SERVER['REQUEST_METHOD'], 'GET' ) == 0 && ! is_admin() && ! $this->is_login_page() && $settings_store->get( Constants::SETTING_ENABLE_FALLBACK_CACHE ) && $this->modules['cache_controller']->is_cache_enabled() && ( ! defined( 'DOING_CRON' ) || ( defined( 'DOING_CRON' ) && DOING_CRON === false ) ) ) {
				$this->modules['fallback_cache']->fallback_cache_retrive_current_page();
			}

			$this->modules = apply_filters( 'swcfpc_include_libs_lately', $this->modules );

			// Initialize the preloader class here as this method is called on the plugin_loaded event. After that, I can instantiate the object even in Ajax calls
			new SWCFPC_Preloader_Process( $this );

			$this->enable_wp_cli_support();
		}

		/**
		 * Run actions.
		 */
		private function actions() {
			add_action( 'admin_init', [ $this, 'maybe_deactivate_free' ] );
			add_filter( 'themeisle_sdk_products', [ $this, 'load_sdk' ] );
			add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		}

		/**
		 * Load the SDK.
		 *
		 * @param array $products
		 *
		 * @return array
		 */
		public function load_sdk( $products ) {
			$products[] = SWCFPC_BASEFILE;

			return $products;
		}

		/**
		 * Get a single config value.
		 *
		 * @param string $name
		 * @param mixed $default
		 *
		 * @deprecated Use Settings_Store::get() instead. It also pulls the default value from Settings_Manager::BASE_FIELDS.
		 *
		 * @return mixed
		 */
		public function get_single_config( $name, $default_value = false ) {
			return Settings_Store::get_instance()->get( $name );
		}

		/**
		 * Set a single config value.
		 *
		 * @param string $name
		 * @param mixed $value
		 *
		 * @deprecated Use Settings_Store::set() instead.
		 *
		 * @return void
		 */
		public function set_single_config( $name, $value ) {
			Settings_Store::get_instance()->set( $name, $value );
		}

		/**
		 * Update the config.
		 *
		 * @deprecated Use Settings_Store::save() instead.
		 *
		 * @return void
		 */
		public function update_config() {
			Settings_Store::get_instance()->save();
		}

		/**
		 * Set the config.
		 *
		 * @param array $config
		 *
		 * @return void
		 */
		public function set_config( array $config ) {
			Settings_Store::get_instance()->set_multiple( $config );
		}

		/**
		 * Get all settings.
		 *
		 * @deprecated Use Settings_Store::get_all() instead.
		 *
		 * @return array
		 */
		public function get_config() {
			return Settings_Store::get_instance()->get_all();
		}

		/**
		 * Update the plugin.
		 *
		 * Runs the update process if the version is different from the previous one.
		 *
		 * @return void
		 */
		private function update_plugin() {

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

						$settings_store = Settings_Store::get_instance();

						$settings_store->set( Constants::SETTING_FALLBACK_CACHE_PURGE_ON_UPGRADER_COMPLETE, false );
						$settings_store->set( Constants::SETTING_BYPASS_WP_JSON_REST, 0 );
						$settings_store->set( Third_Party::SETTING_WOO_BYPASS_ACCOUNT_PAGE, 1 );
						$settings_store->set( Constants::SETTING_KEEP_ON_DEACTIVATION, 1 );

						$excluded_urls = $settings_store->get( Constants::SETTING_EXCLUDED_URLS );

						if ( is_array( $excluded_urls ) ) {

							if ( ! in_array( '/my-account*', $excluded_urls, true ) ) {
								$excluded_urls[] = '/my-account*';
							}

							if ( ! in_array( '/wc-api/*', $excluded_urls, true ) ) {
								$excluded_urls[] = '/wc-api/*';
							}

							if ( ! in_array( '/edd-api/*', $excluded_urls, true ) ) {
								$excluded_urls[] = '/edd-api/*';
							}

							if ( ! in_array( '/wp-json*', $excluded_urls, true ) ) {
								$excluded_urls[] = '/wp-json*';
							}

							$settings_store->set( Constants::SETTING_EXCLUDED_URLS, $excluded_urls );

						}

						$settings_store->save();

						// Called to force the creation of nginx.conf inside the plugin's directory inside the wp-content one
						$this->create_plugin_wp_content_directory();
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
						$migrations = new \SPC\Migrator( $this );
						$migrations->run_update_migrations();
					}
				);
			}

			update_option( 'swcfpc_version', $this->version );
		}

		/**
		 * Deactivate the plugin.
		 *
		 * @hooked register_deactivation_hook()
		 *
		 * @return void
		 */
		public function deactivate_plugin() {
			// Keep settings when upgrading.
			if ( defined( 'SPC_PRO_PATH' ) && defined( 'SPC_FREE_PATH' ) ) {
				return;
			}

			$keep_settings = Settings_Store::get_instance()->get( Constants::SETTING_KEEP_ON_DEACTIVATION );

			if ( ! $keep_settings ) {
				Settings_Store::get_instance()->reset();
				Asset_Rules::remove_database_table();
			}

			$this->delete_plugin_wp_content_directory();
		}

		/**
		 * If both free & pro are active, we attempt to deactivate the free version.
		 *
		 * @return void
		 */
		public function maybe_deactivate_free() {
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
								'Super Page Cache for Cloudflare (Free)'
							),
							__( 'Using the Premium version of Super Page Cache for Cloudflare is not requiring using the Free version.', 'wp-cloudflare-page-cache' )
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
		public function get_modules() {
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
		public function get_objects() {
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

			return Settings_Store::get_instance()->get( Constants::SETTING_CF_ZONE_ID );
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
		public function get_cloudflare_api_zone_domain_name( $zone_id ) {

			if ( defined( 'SWCFPC_CF_API_ZONE_NAME' ) ) {
				return SWCFPC_CF_API_ZONE_NAME;
			}

			$zone_id_list = Settings_Store::get_instance()->get( Constants::ZONE_ID_LIST );
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
		public function get_cloudflare_api_key() {

			if ( defined( 'SWCFPC_CF_API_KEY' ) ) {
				return SWCFPC_CF_API_KEY;
			}

			return Settings_Store::get_instance()->get( Constants::SETTING_CF_API_KEY );
		}

		/**
		 * Get the API Email.
		 *
		 * @return string
		 */
		public function get_cloudflare_api_email() {

			if ( defined( 'SWCFPC_CF_API_EMAIL' ) ) {
				return SWCFPC_CF_API_EMAIL;
			}

			return Settings_Store::get_instance()->get( Constants::SETTING_CF_EMAIL );
		}

		/**
		 * Get the API Token.
		 *
		 * @return string
		 */
		public function get_cloudflare_api_token() {

			if ( defined( 'SWCFPC_CF_API_TOKEN' ) ) {
				return SWCFPC_CF_API_TOKEN;
			}

			return Settings_Store::get_instance()->get( Constants::SETTING_CF_API_TOKEN );
		}

		/**
		 * Get the plugin wp content directory.
		 *
		 * @return string
		 *
		 * @deprecated Use \SPC\Utils\Helpers::get_plugin_content_dir() instead.
		 */
		public function get_plugin_wp_content_directory() {
			$parts = parse_url( home_url() );

			return WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$parts['host']}";
		}

		/**
		 * Get the plugin wp content directory url.
		 *
		 * @return string
		 *
		 * @deprecated Use \SPC\Utils\Helpers::get_plugin_content_dir_url() instead.
		 */
		public function get_plugin_wp_content_directory_url() {
			$parts = parse_url( home_url() );

			return content_url( "wp-cloudflare-super-page-cache/{$parts['host']}" );
		}

		/**
		 * Get the plugin wp content directory uri.
		 *
		 * @return string
		 */
		public function get_plugin_wp_content_directory_uri() {
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

		/**
		 * Create the plugin wp content directory.
		 *
		 * @return void
		 */
		public function create_plugin_wp_content_directory() {
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


		/**
		 * Delete the plugin wp content directory.
		 *
		 * @return void
		 */
		public function delete_plugin_wp_content_directory() {
			$parts = parse_url( home_url() );
			$path  = WP_CONTENT_DIR . '/wp-cloudflare-super-page-cache/';
			$path .= $parts['host'];

			if ( file_exists( $path ) ) {
				$this->delete_directory_recursive( $path );
			}
		}


		/**
		 * Delete the directory recursively.
		 *
		 * @return void
		 */
		public function delete_directory_recursive( $dir ) {
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

		/**
		 * Check if the current page is the login page.
		 *
		 * @return bool
		 */
		public function is_login_page() {
			return in_array( $GLOBALS['pagenow'], [ 'wp-login.php', 'wp-register.php' ], true );
		}

		/**
		 * Enable WP CLI support.
		 *
		 * @return void
		 */
		private function enable_wp_cli_support() {
			if ( defined( 'WP_CLI' ) && WP_CLI && ! class_exists( 'SWCFPC_WP_CLI' ) && class_exists( 'WP_CLI_Command' ) ) {
				require_once SWCFPC_PLUGIN_PATH . 'libs/wpcli.class.php';
				$wpcli = new SWCFPC_WP_CLI( $this );
				WP_CLI::add_command( 'cfcache', $wpcli );
			}
		}

		/**
		 * Check if the current user can purge cache.
		 *
		 * @return bool
		 */
		public function can_current_user_purge_cache() {
			if ( ! is_user_logged_in() ) {
				return false;
			}

			if ( current_user_can( 'manage_options' ) ) {
				return true;
			}

			$allowed_roles = Settings_Store::get_instance()->get( Constants::SETTING_PURGE_ROLES );
			if ( count( $allowed_roles ) < 1 ) {
				return false;
			}

			$user = wp_get_current_user();
			foreach ( $allowed_roles as $role_name ) {
				if ( in_array( $role_name, (array) $user->roles, true ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if the current URL has a trailing slash.
		 *
		 * @return bool
		 */
		public function does_current_url_have_trailing_slash() {
			if ( ! preg_match( '/\/$/', $_SERVER['REQUEST_URI'] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Check if the current request is an API request.
		 *
		 * @return bool
		 */
		public function is_api_request() {
			$rest_base    = trim( parse_url( rest_url(), PHP_URL_PATH ), '/' );
			$request_path = trim( $_SERVER['REQUEST_URI'], '/' );

			// WordPress standard API
			if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || strpos( $request_path, $rest_base ) === 0 ) {
				return true;
			}

			// WooCommerce standard API
			if ( strpos( $request_path, 'wc-api' ) === 0 ) {
				return true;
			}

			// EDD standard API
			if ( strpos( $request_path, 'edd-api' ) === 0 ) {
				return true;
			}

			return false;
		}


		/**
		 * Check if the subject matches the pattern.
		 *
		 * @param string $pattern The pattern to match.
		 * @param string $subject The subject to match.
		 *
		 * @return bool
		 */
		public function wildcard_match( $pattern, $subject ) {
			$pattern = '#^' . preg_quote( $pattern ) . '$#i'; // Case insensitive
			$pattern = str_replace( '\*', '.*', $pattern );
			// $pattern = str_replace('\.', '.', $pattern);

			if ( ! preg_match( $pattern, $subject, $regs ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Pass parse_url() array and get the URL back as string
		 *
		 * @param array $parsed_url The parsed URL.
		 *
		 * @return string
		 */
		public function get_unparsed_url( $parsed_url ) {
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

		/**
		 * Get the ignored query params.
		 *
		 * @return array
		 */
		public function get_ignored_query_params() {
			return apply_filters( 'swcfpc_ignored_query_params', \SPC\Constants::IGNORED_QUERY_PARAMS );
		}

		/**
		 * Get the current language code.
		 *
		 * @return string
		 */
		private function get_current_lang_code() {
			$current_language_code = false;

			if ( has_filter( 'wpml_current_language' ) ) {
				$current_language_code = apply_filters( 'wpml_current_language', null );
			}

			return $current_language_code;
		}

		/**
		 * Get the home URL.
		 *
		 * @param int $blog_id The blog ID.
		 * @param string $path The path.
		 * @param string $scheme The scheme.
		 *
		 * @return string
		 */
		public function get_home_url( $blog_id = null, $path = '', $scheme = null ) {
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

		/**
		 * Get the home URL.
		 *
		 * @param string $path The path.
		 * @param string $scheme The scheme.
		 *
		 * @return string
		 */
		public function home_url( $path = '', $scheme = null ) {
			return $this->get_home_url( null, $path, $scheme );
		}

		/**
		 * Maybe load the pro modules.
		 *
		 * @return void
		 */
		private function maybe_load_pro_modules() {
			if (
				! is_file( SWCFPC_PLUGIN_PATH . 'pro/Loader.php' ) ||
				! defined( 'SPC_PRO_PATH' ) ||
				! class_exists( 'SPC_Pro\\Loader' )
			) {
				return;
			}

			$this->pro_loader = new SPC_Pro\Loader( $this );
			$this->pro_loader->init();
		}

		/**
		 * Get the pro loader.
		 *
		 * @return SPC_Pro\Loader
		 */
		public function get_pro_loader() {
			return $this->pro_loader;
		}
		/**
		 * Get the core loader.
		 *
		 * @return SPC\Loader
		 */
		public function get_core_loader() {
			return $this->core_loader;
		}
		/**
		 * Get the plugin version.
		 *
		 * @return string
		 */
		public function get_plugin_version() {
			return $this->version;
		}

		/**
		 * Get the cloudflare handler.
		 *
		 * @return SWCFPC_Cloudflare
		 */
		public function get_cloudflare_handler() {
			if ( ! isset( $this->modules['cloudflare'] ) ) {
				$this->include_libs();
			}

			return $this->modules['cloudflare'];
		}

		/**
		 * Get the cache controller.
		 *
		 * @return SWCFPC_Cache_Controller
		 */
		public function get_cache_controller() {
			if ( ! isset( $this->modules['cache_controller'] ) ) {
				$this->include_libs();
			}

			return $this->modules['cache_controller'];
		}

		/**
		 * Get the logger.
		 *
		 * @return SWCFPC_Logs
		 */
		public function get_logger() {
			if ( ! isset( $this->modules['logs'] ) ) {
				$this->include_libs();
			}

			return $this->modules['logs'];
		}

		/**
		 * Get the fallback cache handler.
		 *
		 * @return SWCFPC_Fallback_Cache
		 */
		public function get_fallback_cache_handler() {
			if ( ! isset( $this->modules['fallback_cache'] ) ) {
				$this->include_libs();
			}

			return $this->modules['fallback_cache'];
		}

		/**
		 * Get the HTML cache handler.
		 *
		 * @return SWCFPC_Html_Cache
		 */
		public function get_html_cache_handler() {
			if ( ! isset( $this->modules['html_cache'] ) ) {
				$this->include_libs();
			}

			return $this->modules['html_cache'];
		}

		/**
		 * Get the varnish handler.
		 *
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

	/**
	 * Register the activation hook.
	 *
	 * action scheduler registers on the `plugins_loaded` hook with priority 0, so we need to require the file before that hook.
	 * This is to ensure that the Action Scheduler is loaded before any other plugin that might use it.
	 *
	 * @return void
	 */
	add_action(
		'plugin_loaded',
		function () {
			// If WooCommerce already loaded it.
			if ( class_exists( 'ActionScheduler' ) ) {
				return;
			}

			$scheduler_path = SWCFPC_PLUGIN_PATH . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

			if ( file_exists( $scheduler_path ) ) {
				require_once $scheduler_path;
			}
		},
		0
	);

	register_activation_hook(
		SPC_PATH,
		function () {
			update_option( SW_CLOUDFLARE_PAGECACHE::REDIRECT_KEY, true );
		}
	);
}
