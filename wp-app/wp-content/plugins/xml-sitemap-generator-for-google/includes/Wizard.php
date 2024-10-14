<?php

namespace GRIM_SG;

class Wizard extends Dashboard {
	public static $activation_redirect = 'sgg_activation_redirect';

	private static $wizard_completed = 'sgg_wizard_completed';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'maybe_redirect_to_wizard' ) );
		add_action( 'wp_ajax_save_wizard_settings', array( $this, 'save_wizard_settings' ) );
	}

	public function add_admin_menu() {
		add_submenu_page(
			self::$slug,
			esc_html__( 'Google XML Sitemaps Generator Wizard', 'xml-sitemap-generator-for-google' ),
			esc_html__( 'Sitemaps Wizard', 'xml-sitemap-generator-for-google' ),
			'manage_options',
			$this->get_wizard_page_slug(),
			array( $this, 'render_wizard_page' )
		);
	}

	public function render_wizard_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style( 'sgg-wizard-styles', GRIM_SG_URL . 'assets/css/wizard.min.css', array(), GRIM_SG_VERSION );
		wp_enqueue_script( 'sgg-wizard-scripts', GRIM_SG_URL . 'assets/js/wizard.js', array( 'jquery' ), GRIM_SG_VERSION, true );
		wp_localize_script(
			'sgg-wizard-scripts',
			'sggWizard',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'sgg_wizard_nonce' ),
				'continue' => esc_html__( 'Continue', 'xml-sitemap-generator-for-google' ),
				'finish'   => esc_html__( 'Finish', 'xml-sitemap-generator-for-google' ),
			)
		);

		self::render(
			'wizard/main.php',
			array(
				'settings' => $this->get_settings(),
			)
		);
	}

	public function maybe_redirect_to_wizard() {
		if ( ! get_transient( self::$activation_redirect ) || ! current_user_can( 'manage_options' )
			|| wp_doing_ajax() || is_network_admin() ) {
			return;
		}

		delete_transient( self::$activation_redirect );

		if ( ! get_option( self::$wizard_completed ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . $this->get_wizard_page_slug() ) );
			exit;
		}
	}

	public function save_wizard_settings() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['nonce'] ?? '', 'sgg_wizard_nonce' ) ) {
			return;
		}

		$settings = $this->get_settings();

		$settings->enable_sitemap       = sanitize_text_field( $_POST['enable_sitemap'] ?? 0 );
		$settings->enable_html_sitemap  = sanitize_text_field( $_POST['enable_html_sitemap'] ?? 0 );
		$settings->enable_google_news   = sanitize_text_field( $_POST['enable_google_news'] ?? 0 );
		$settings->enable_image_sitemap = sanitize_text_field( $_POST['enable_image_sitemap'] ?? 0 );
		$settings->enable_video_sitemap = sanitize_text_field( $_POST['enable_video_sitemap'] ?? 0 );
		$settings->sitemap_view         = sanitize_text_field( $_POST['sitemap_view'] ?? '' );
		$settings->enable_cache         = sanitize_text_field( $_POST['enable_cache'] ?? 0 );
		$settings->cache_timeout        = sanitize_text_field( $_POST['cache_timeout'] ?? $settings->cache_timeout );
		$settings->cache_timeout_period = sanitize_text_field( $_POST['cache_timeout_period'] ?? $settings->cache_timeout_period );

		update_option( self::$slug, $settings );

		flush_rewrite_rules();

		wp_send_json(
			array(
				'success' => true,
				'redirect' => admin_url( 'admin.php?page=' . self::$slug ),
			)
		);
	}

	public function get_wizard_page_slug() {
		return self::$slug . '-wizard';
	}
}
