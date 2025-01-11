<?php

/**
 * Plugin Name: WP Headers And Footers
 * Plugin URI: https://www.WPBrigade.com/wordpress/plugins/wp-headers-and-footers/?utm_source=?utm_source=wp-headers-and-footers&utm_medium=author-uri-link
 * Description: Allows you to insert code or text in the header or footer of your WordPress site.
 * Version: 3.1.1
 * Author: WPBrigade
 * Author URI: https://wpbrigade.com/?utm_source=wp-headers-and-footers&utm_medium=author-uri-link
 * License: GPLv3
 * Text Domain: wp-headers-and-footers
 * Domain Path: /languages
 *
 * @package wp-headers-and-footers
 * @category Core
 * @author WPBrigade
 */

if ( ! function_exists( 'whaf_wpb56690683' ) ) {
	// Create a helper function for easy SDK access.
	function whaf_wpb56690683() {
		global $whaf_wpb56690683;

		if ( ! isset( $whaf_wpb56690683 ) ) {
			// Include Telemetry SDK.
			require_once __DIR__ . '/lib/wpb-sdk/start.php';

			$whaf_wpb56690683 = wpb_dynamic_init(
				array(
					'id'             => '2',
					'slug'           => 'wp-headers-and-footers',
					'type'           => 'plugin',
					'public_key'     => '1|4aOA8EuyIN4pi2miMvC23LLpnHbBZFNki9R9pVmwd673d3c8',
					'secret_key'     => 'sk_b36c525848fee035',
					'is_premium'     => false,
					'has_addons'     => false,
					'has_paid_plans' => false,
					'menu'           => array(
						'slug'    => 'wp-headers-and-footers',
						'account' => false,
						'support' => false,
					),
					'settings'       => array(
						'wpheaderandfooter_settings' => '',
						'_wpheaderandfooter_optin'   => '',
					),
				)
			);
		}

		return $whaf_wpb56690683;
	}

	// Init Telemetry.
	whaf_wpb56690683();
	// Signal that SDK was initiated.
	do_action( 'whaf_wpb56690683_loaded' );
}

if ( ! class_exists( 'WPHeaderAndFooter' ) ) :

	/**
	 * The class WPHeaderAndFooter
	 */
	final class WPHeaderAndFooter {

		/**
		 * The single instance of the class.
		 *
		 * @var string $version
		 */
		public $version = '3.1.1';

		/**
		 * The single instance of the class.
		 *
		 * @var object $instance
		 */
		protected static $instance = null;

		/**
		 * WPHeaderAndFooter Class constructor
		 */
		public function __construct() {

			$this->define_constants();
			$this->includes();
			$this->hooks();
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @version 1.3.1
		 */
		public function includes() {

			include_once WPHEADERANDFOOTER_DIR_PATH . 'classes/class-setup.php';
			include_once WPHEADERANDFOOTER_DIR_PATH . 'classes/plugin-meta.php';
			include_once WPHEADERANDFOOTER_DIR_PATH . 'classes/class-notifications.php';

			// set the logger settings option if was not set before.
			if ( ! get_option( 'wpheaderandfooter_basics_logger' ) ) {

				$setting = get_option( 'wpheaderandfooter_basics' );

				$logger_value = array();

				$logger_value['is_using_wp_header_textarea'] = isset( $setting['wp_header_textarea'] ) && ! empty( trim( $setting['wp_header_textarea'] ) ) ? true : false;
				$logger_value['is_using_wp_body_textarea']   = isset( $setting['wp_body_textarea'] ) && ! empty( trim( $setting['wp_body_textarea'] ) ) ? true : false;
				$logger_value['is_using_wp_footer_textarea'] = isset( $setting['wp_footer_textarea'] ) && ! empty( trim( $setting['wp_footer_textarea'] ) ) ? true : false;

				update_option( 'wpheaderandfooter_basics_logger', $logger_value );
			}
		}


		/**
		 * Hook into actions and filters
		 *
		 * @since  1.0.0
		 * @version 2.1.0
		 */
		private function hooks() {

			$head_priority   = $this->hnf_option( 'wpheaderandfooter_settings', 'wp_header_priority' );
			$body_priority   = $this->hnf_option( 'wpheaderandfooter_settings', 'wp_body_priority' );
			$footer_priority = $this->hnf_option( 'wpheaderandfooter_settings', 'wp_footer_priority' );

			add_action( 'init', array( $this, 'textdomain' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_print_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'admin_init', array( $this, 'redirect_optin' ) );

			if ( ! empty( $head_priority ) ) {
				add_action( 'wp_head', array( $this, 'frontend_header' ), $head_priority );
			} else {
				add_action( 'wp_head', array( $this, 'frontend_header' ) );
			}

			if ( function_exists( 'wp_body_open' ) && version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ) {
				if ( ! empty( $body_priority ) ) {
					add_action( 'wp_body_open', array( $this, 'frontend_body' ), $body_priority );
				} else {
					add_action( 'wp_body_open', array( $this, 'frontend_body' ) );
				}
			}

			if ( ! empty( $footer_priority ) ) {
				add_action( 'wp_footer', array( $this, 'frontend_footer' ), $footer_priority );
			} else {
				add_action( 'wp_footer', array( $this, 'frontend_footer' ) );
			}

			add_action( 'wp_ajax_wpheadersandfooters_log_download', array( $this, 'wp_headers_and_footers_log_download' ) );
			add_action( 'wp_ajax_nopriv_wpheadersandfooters_log_download', array( $this, 'wp_headers_and_footers_log_download' ) );
			add_action( 'wp_wpb_sdk_after_uninstall', array( $this, 'plugin_uninstallation' ) );
			add_action( 'admin_footer', array( $this, 'add_deactivate_modal' ) );
			add_action( 'admin_menu', array( $this, 'register_wpheaders_optin_page' ) );
			add_action( 'wp_ajax_wpheadersandfooters_optout_yes', array( $this, 'optout_yes' ) );

		}

		/**
		 * Summary of register_wpheaders_optin_page
		 *
		 * @since 2.2.3
		 */
		function register_wpheaders_optin_page() {
			add_submenu_page( 'HeaderandFooter', __( 'Activate', 'wp-headers-and-footers' ), __( 'Activate', 'wp-headers-and-footers' ), 'manage_options', 'wpheadersandfooters-optin', array( $this, 'wpheadersandfooters_render_optin_page' ) );
		}

		/**
		 * Summary of register_wpheaders_optin_page
		 *
		 * @since 2.2.3
		 */
		function wpheadersandfooters_render_optin_page() {
			include plugin_dir_path( __FILE__ ) . 'inc/hnf-optin-form.php';
		}

		/**
		 * Summary of register_wpheaders_optin_page
		 *
		 * @since 2.2.3
		 */
		function add_deactivate_modal() {
			global $pagenow;

			if ( 'plugins.php' !== $pagenow ) {
				return;
			}

			include plugin_dir_path( __FILE__ ) . 'inc/hnf-optout-form.php';
		}

		/**
		 * Opt-out
		 *
		 * @since 2.2.3
		 */
		function optout_yes() {

			if( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'headerandfooter-optout-nonce', 'optout_nonce' ) ){
				wp_die( '<p>' . __( 'Sorry, you are not allowed to edit this item.' ) . '</p>', 403 );
			}

			// Get the current option and decode it as an associative array
			$sdk_data = json_decode( get_option( 'wpb_sdk_wp-headers-and-footers' ), true );

			// If there is no current option, initialize an empty array
			if ( ! $sdk_data ) {
				$sdk_data = array();
			}

			$setting_name  = $_POST['setting_name'];  // e.g., communication, diagnostic_info, extensions
			$setting_value = $_POST['setting_value'];  // The new value to be updated

			// Update the specific setting in the array
			$sdk_data[ $setting_name ] = $setting_value;

			// Encode the array back into a JSON string and update the option
			update_option( 'wpb_sdk_wp-headers-and-footers', json_encode( $sdk_data ) );

			wp_die();
		}

		/**
		 * Summary of register_wpheaders_optin_page
		 *
		 * @since 2.2.3
		 */
		function redirect_optin() {

			/**
			 * Fix the Broken Access Control (BAC) security fix.
			 *
			 * @since 1.6.3
			 */
			if ( current_user_can( 'manage_options' ) ) {
				if ( isset( $_POST['headerandfooter-submit-optout'] ) ) {
					if ( ! wp_verify_nonce( sanitize_text_field( $_POST['headerandfooter_submit_optin_nonce'] ), 'headerandfooter_submit_optin_nonce' ) ) {
						return;
					}
					update_option( '_wpheaderandfooter_optin', 'no' );
					// Retrieve WPB SDK existing option and set user_skip
					$sdk_data              = json_decode( get_option( 'wpb_sdk_wp-headers-and-footers' ), true );
					$sdk_data['user_skip'] = '1';
					$sdk_data_json         = json_encode( $sdk_data );
					update_option( 'wpb_sdk_wp-headers-and-footers', $sdk_data_json );
				} elseif ( isset( $_POST['headerandfooter-submit-optin'] ) ) {
					if ( ! wp_verify_nonce( sanitize_text_field( $_POST['headerandfooter_submit_optin_nonce'] ), 'headerandfooter_submit_optin_nonce' ) ) {
						return;
					}
					update_option( '_wpheaderandfooter_optin', 'yes' );
					// WPB SDK OPT IN OPTIONS
					$sdk_data      = array(
						'communication'   => '1',
						'diagnostic_info' => '1',
						'extensions'      => '1',
						'user_skip'       => '0',
					);
					$sdk_data_json = json_encode( $sdk_data );
					update_option( 'wpb_sdk_wp-headers-and-footers', $sdk_data_json );
				} elseif ( ! get_option( '_wpheaderandfooter_optin' ) && isset( $_GET['page'] ) && ( $_GET['page'] === 'wp-headers-and-footers' || $_GET['page'] === 'wp-headers-and-footers' || $_GET['page'] === 'abw' ) ) {

					/**
					 * XSS Attack vector found and fixed.
					 *
					 * @since 1.5.11
					 */
					$page_redirect = $_GET['page'] === 'wp-headers-and-footers' ? 'wp-headers-and-footers' : 'wp-headers-and-footers';
					wp_redirect( admin_url( 'admin.php?page=wpheadersandfooters-optin&redirect-page=' . $page_redirect ) );
					exit;

				} elseif ( get_option( '_wpheaderandfooter_optin' ) && ( get_option( '_wpheaderandfooter_optin' ) == 'yes' ) && isset( $_GET['page'] ) && $_GET['page'] === 'wpheadersandfooters-optin' ) {
					wp_redirect( admin_url( 'options-general.php?page=wp-headers-and-footers' ) );
					exit;
				}
			}
		}

		/**
		 * Plugin Un-installation
		 *
		 * @since 2.2.2
		 * @version 2.2.3
		 */
		public function plugin_uninstallation() {
			include_once WPHEADERANDFOOTER_DIR_PATH . 'inc/uninstall.php';
		}

		/**
		 * Define WP Header and Footer Constants
		 */
		private function define_constants() {

			$this->define( 'WPHEADERANDFOOTER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WPHEADERANDFOOTER_DIR_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WPHEADERANDFOOTER_DIR_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'WPHEADERANDFOOTER_ROOT_PATH', __DIR__ . '/' );
			$this->define( 'WPHEADERANDFOOTER_VERSION', $this->version );
			$this->define( 'WPHEADERANDFOOTER_FEEDBACK_SERVER', 'https://wpbrigade.com/' );
		}

		/**
		 * Get plugin options and apply proper check on their sub items.
		 *
		 * @param string     $option_name Name of the option.
		 * @param string     $index Name of the sub option.
		 * @param string|int $default default value of the option.
		 *
		 * @since 2.0.0
		 */
		public function hnf_option( $option_name, $index, $default = '' ) {

			$option = get_option( $option_name );
			if ( isset( $option[ $index ] ) && ! empty( $option[ $index ] ) ) {
				return $option[ $index ];
			}
			return $default;
		}

		/**
		 * Admin Scripts
		 *
		 * @param string $page The page slug.
		 * @version 2.0.0
		 */
		public function admin_scripts( $page ) {

			if ( 'settings_page_wp-headers-and-footers' === $page ) {

				wp_enqueue_style( 'wpheaderandfooter_style', plugins_url( 'asset/css/style.css', __FILE__ ), array(), WPHEADERANDFOOTER_VERSION );

				wp_enqueue_style( 'wpheaderandfooter_admin_style', plugins_url( 'asset/css/admin-style.css', __FILE__ ), array(), WPHEADERANDFOOTER_VERSION );

				$editor_args = array( 'type' => 'text/html' );

				if ( ! current_user_can( 'manage_options' ) ) {
					$editor_args['codemirror']['readOnly'] = true;
				}

				// Enqueue code editor and settings for manipulating HTML.
				$settings = wp_enqueue_code_editor( $editor_args );

				// Bail if user disabled CodeMirror.
				if ( false === $settings ) {
					return;
				}

				wp_enqueue_script( 'wpheaderandfooter_script', plugins_url( 'asset/js/script.js', __FILE__ ), array( 'jquery' ), WPHEADERANDFOOTER_VERSION, false );

				// Create an array for localize.
				$wp_headers_and_footers_localize = array(
					'plugin_url' => plugins_url(),
					'help_nonce' => wp_create_nonce( 'wp-headers-and-footers-log-nonce' ),
				);
				wp_localize_script( 'wpheaderandfooter_script', 'wpheadersandfooters_log', $wp_headers_and_footers_localize );
			}
		}

		/**
		 * Define constant if not already set
		 *
		 * @param string      $name The name of the variable.
		 * @param string|bool $value The value of the variable.
		 */
		private function define( $name, $value ) {

			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		function render_optin() {
			include headerandfooter_DIR_PATH . 'inc/hnf-optin-form.php';
		}
		/**
		 * Main Instance
		 *
		 * @since 1.0.0
		 * @static
		 * @see wpheaderandfooter_loader()
		 * @return Main instance
		 */
		public static function instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}


		/**
		 * Load Languages
		 *
		 * @since 1.0.0
		 */
		public function textdomain() {

			$plugin_dir = dirname( plugin_basename( __FILE__ ) );
			load_plugin_textdomain( 'wp-headers-and-footers', false, $plugin_dir . '/languages/' );
		}

		/**
		 * Outputs script / style to the header
		 *
		 * @since 1.0.0
		 * @version 1.3.2
		 */
		public function frontend_header() {

			/**
			 * Filter to add or exclude scripts to and from the frontend header.
			 *
			 * @since 1.3.2
			 */
			if ( apply_filters( 'wp_hnf_header_script', true ) ) {
				$this->wp_hnf_output( 'wp_header_textarea' );
			}
		}

		/**
		 * Outputs script / style to the frontend below opening body
		 *
		 * @since 1.0.0
		 * @version 1.3.2
		 */
		public function frontend_body() {

			/**
			 * Filter to add or exclude scripts to and from the frontend body.
			 *
			 * @since 1.3.2
			 */
			if ( apply_filters( 'wp_hnf_body_script', true ) ) {
				$this->wp_hnf_output( 'wp_body_textarea' );
			}
		}

		/**
		 * Outputs script / style to the footer
		 *
		 * @since 1.0.0
		 * @version 1.3.2
		 */
		public function frontend_footer() {

			/**
			 * Filter to add or exclude scripts to and from the frontend footer.
			 *
			 * @since 1.3.2
			 */
			if ( apply_filters( 'wp_hnf_footer_script', true ) ) {
				$this->wp_hnf_output( 'wp_footer_textarea' );
			}
		}

		/**
		 * Outputs the given setting, if conditions are met
		 *
		 * @param string $script Setting Name.
		 *
		 * @version 2.0.0
		 * @return output
		 */
		public function wp_hnf_output( $script ) {

			// Ignore admin, feed, robots or track backs.
			if ( is_admin() || is_feed() || is_robots() || is_trackback() ) :
				return;
			endif;

			// Get meta.
			$meta = $this->hnf_option( 'wpheaderandfooter_basics', $script, false );

			if ( '' === trim( $meta ) || ! $meta ) :
				return;
			endif;

			// Output.
			echo wp_unslash($meta) . PHP_EOL;  // @codingStandardsIgnoreLine.
		}

		/**
		 * The Ajax function callback to download the diagnostics.
		 *
		 * @since 2.1.0
		 * @return void
		 */
		function wp_headers_and_footers_log_download() {

			check_ajax_referer( 'wp-headers-and-footers-log-nonce', 'security' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'No cheating, huh!' );
			}
			$diagnostics = new WPHeadersAndFooters_Diagnostics_Log();
			echo $diagnostics->wp_headers_and_footers_get_sysinfo( false );

			wp_die();
		}

	}

endif;

/**
 * Returns the main instance of WP to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return WPHeaderAndFooter
 */
function wpheaderandfooter_loader() {
	return WPHeaderAndFooter::instance();
}

// Call the function.
wpheaderandfooter_loader();
new WPHeaderAndFooter_Setting();
