<?php

/**
 * WordPress Header and Footer Setup
 *
 * @package wp-headers-and-footers
 */

if ( ! class_exists( 'WPHeaderAndFooter_Setting' ) ) :
	/**
	 * The WPHeaderAndFooter Settings class
	 */
	class WPHeaderAndFooter_Setting {

		/**
		 * Settings sections array
		 *
		 * @var array $settings_api The settings API array.
		 */
		private $settings_api;

		/**
		 * Settings sections array
		 *
		 * @var object $diagnostics The diagnostics object of another class.
		 */
		private $diagnostics;
		/**
		 * The constructor of WPHeaderAndFooter Settings class
		 *
		 * @since 1.0.0
		 * @version 2.1.2
		 */
		public function __construct() {

			if ( $this->wphnf_setting_optimization() ) {
				include_once WPHEADERANDFOOTER_DIR_PATH . 'classes/class-settings-api.php';
				include_once WPHEADERANDFOOTER_DIR_PATH . 'classes/class-diagnostics-log.php';

				$this->settings_api = new WPHeaderAndFooter_Settings_API();
				$this->diagnostics  = new WPHeadersAndFooters_Diagnostics_Log();

				add_action( 'admin_init', array( $this, 'admin_init' ) );
			}
			add_action( 'admin_menu', array( $this, 'register_options_page' ) );
		}

		/**
		 * WP Headers and Footers Settings Optimization if is_admin page.
		 *
		 * @since 2.1.2
		 */
		public function wphnf_setting_optimization() {

			if ( ( is_admin() && isset( $_GET['page'] ) && 'wp-headers-and-footers' === $_GET['page'] ) || ( isset( $_POST['_wp_http_referer'] ) && strpos( $_POST['_wp_http_referer'], 'wp-headers-and-footers' ) ) ) {   // @codingStandardsIgnoreLine.
				return true;
			}

			return false;
		}

		/**
		 * Admin initialize function.
		 */
		public function admin_init() {
			// Set the settings.
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );

			// Initialize settings.
			$this->settings_api->admin_init();

			// reset settings.
			$this->load_default_settings();
		}

		/**
		 * Register the plugin settings panel
		 *
		 * @since 1.1.0
		 */
		public function register_options_page() {

			add_submenu_page( 'options-general.php', __( 'WP Headers and Footers', 'wp-headers-and-footers' ), __( 'WP Headers and Footers', 'wp-headers-and-footers' ), 'manage_options', 'wp-headers-and-footers', array( $this, 'wp_header_and_footer_callback' ) );
		}

		/**
		 * Load the default settings
		 *
		 * @since 3.1.0
		 * @return void
		 */
		function load_default_settings() {

			$settings      = get_option( 'wpheaderandfooter_settings' );
			$factory_reset = isset( $settings['factory_reset_settings'] ) ? $settings['factory_reset_settings'] : 'off';

			if ( 'on' === $factory_reset ) {
				if ( get_option( 'wpheaderandfooter_settings' ) ) {
					$default = array(
						'wp_header_priority'     => '',
						'wp_body_priority'       => '',
						'wp_footer_priority'     => '',
						'remove_all_settings'    => 'off',
						'factory_reset_settings' => 'off',
					);
					update_option( 'wpheaderandfooter_settings', $default );
				}

				if ( get_option( 'wpheaderandfooter_basics' ) ) {
					$default = array(
						'wp_header_textarea' => '',
						'wp_body_textarea'   => '',
						'wp_footer_textarea' => '',
					);
					update_option( 'wpheaderandfooter_basics', $default );
				}
			}
		}

		/**
		 * The settings section.
		 *
		 * @since 1.1.0
		 * @version 2.1.0
		 */
		public function get_settings_sections() {

			$diagnostic_log = $this->diagnostics->wp_headers_and_footers_get_sysinfo();

			$sections = array(
				array(
					'id'    => 'wpheaderandfooter_basics',
					'title' => __( 'Scripts', 'wp-headers-and-footers' ),
				),
				array(
					'id'    => 'wpheaderandfooter_settings',
					'title' => __( 'Settings', 'wp-headers-and-footers' ),
					'desc'  => __( 'Set your priorities for each script tag.', 'wp-headers-and-footers' ),
				),
				array(
					'id'    => 'wpheaderandfooter_diagnostic_log',
					'title' => __( 'Help & Troubleshooting', 'wp-headers-and-footers' ),
					'desc'  => $diagnostic_log,
				),
			);
			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @since 1.0.0
		 * @version 3.1.0
		 *
		 * @return array settings fields
		 */
		public function get_settings_fields() {
			$settings_fields = array(
				'wpheaderandfooter_basics'   => array(
					array(
						'name'  => 'wp_header_textarea',
						'label' => __( 'Scripts in Header', 'wp-headers-and-footers' ),
						/* Translators: The header textarea description */
						'desc'  => sprintf( __( 'These scripts will be printed in the %1$s section.', 'wp-headers-and-footers' ), '&#60head&#62' ),
						'type'  => 'textarea',
					),
					array(
						'name'  => 'wp_body_textarea',
						'label' => __( 'Scripts in Body', 'wp-headers-and-footers' ),
						/* Translators: The body textarea description */
						'desc'  => sprintf( __( 'These scripts will be printed below the %1$s tag.', 'wp-headers-and-footers' ), '&#60body&#62' ),
						'type'  => 'textarea',
					),
					array(
						'name'  => 'wp_footer_textarea',
						'label' => __( 'Scripts in Footer', 'wp-headers-and-footers' ),
						/* Translators: The footer textarea description */
						'desc'  => sprintf( __( 'These scripts will be printed below the %1$s tag.', 'wp-headers-and-footers' ), '&#60footer&#62' ),
						'type'  => 'textarea',
					),
				),
				'wpheaderandfooter_settings' => array(
					array(
						'name'        => 'wp_header_priority',
						'label'       => __( "Header's Priority:", 'wp-headers-and-footers' ),
						/* Translators: The header textarea description */
						'desc'        => sprintf( __( 'The priority for %1$s section. %2$sDefault is 10%3$s', 'wp-headers-and-footers' ), '&#60head&#62', '<i>', '</i>' ),
						'type'        => 'number',
						'min'         => 1,
						'max'         => 999999,
						'placeholder' => '1',
					),
					array(
						'name'        => 'wp_body_priority',
						'label'       => __( "Body's Priority:", 'wp-headers-and-footers' ),
						/* Translators: The body textarea description */
						'desc'        => sprintf( __( 'The priority for %1$s tag. %2$sDefault is 10%3$s', 'wp-headers-and-footers' ), '&#60body&#62', '<i>', '</i>' ),
						'type'        => 'number',
						'min'         => 1,
						'max'         => 999999,
						'placeholder' => '10',
					),
					array(
						'name'        => 'wp_footer_priority',
						'label'       => __( "Footer's Priority:", 'wp-headers-and-footers' ),
						/* Translators: The footer textarea description */
						'desc'        => sprintf( __( 'The priority for %1$s tag. %2$sDefault is 10%3$s', 'wp-headers-and-footers' ), '&#60footer&#62', '<i>', '</i>' ),
						'type'        => 'number',
						'min'         => 1,
						'max'         => 999999,
						'placeholder' => '99',
					),
					array(
						'name'  => 'factory_reset_settings',
						'label' => __( 'Factory Reset:', 'wp-headers-and-footers' ),
						/* Translators: The footer textarea description */
						'desc'  => sprintf( __( 'Enable to remove all scripts and reset all settings made by Insert Headers and Footers upon saving.', 'wp-headers-and-footers' ) ),
						'type'  => 'checkbox',
					),
					array(
						'name'  => 'remove_all_settings',
						'label' => __( 'Remove Settings on Uninstall:', 'wp-headers-and-footers' ),
						/* Translators: The footer textarea description */
						'desc'  => sprintf( __( 'Enable to remove all custom settings and scripts added by Insert Headers and Footers upon uninstall.', 'wp-headers-and-footers' ) ),
						'type'  => 'checkbox',
					),
				),
			);

			return $settings_fields;
		}

		/**
		 * The header and footer settings section and forms callback
		 *
		 * @since 1.1.0
		 * @version 2.0.0
		 */
		public function wp_header_and_footer_callback() {
			echo $this::wp_hnf_admin_page_header();

			echo '<div class="wrap wp-headers-and-footers">';
			echo '<h1 style="display:none;">' . __( 'Insert Headers And Footers', 'wp-headers-and-footers' ) . '</h1>';
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();

			echo '</div>';
		}

		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
		public function get_pages() {
			$pages         = get_pages();
			$pages_options = array();
			if ( $pages ) {
				foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
				}
			}

			return $pages_options;
		}

		/**
		 * Header HTML.
		 * Call on Header and Footer page at dashboard.
		 *
		 * @since 2.1.0
		 */
		public static function wp_hnf_admin_page_header() {
			?>
			<div class="wp_hnf-header-wrapper">
				<div class="wp_hnf-header-container">
					<div class="wp_hnf-header-logo">
						<a href="<?php echo esc_url( 'https://wpbrigade.com' ); ?>" target="_blank"><img src="<?php echo esc_url( WPHEADERANDFOOTER_DIR_URL . 'asset/img/logo.svg' ); ?>"></a>
					</div>
					<div class="wp_hnf-header-cta">
					<a href="#" id="wpheaderandfooter_diagnostic_log-header">
						<?php printf( esc_html__( 'Diagnostic %1$sLog%2$s', 'wp-headers-and-footers' ), '<span>', '</span>' ); ?>
					</a>

					<a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/wp-headers-and-footers/' ); ?>" class="wp_hnf-pro-cta" target="_blank">
						<?php echo esc_html__( 'Support', 'wp-headers-and-footers' ); ?>
					</a>
					</div>
				</div>
			</div>
			<?php
		}
	}
endif;
