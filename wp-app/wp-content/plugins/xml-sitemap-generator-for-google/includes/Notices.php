<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Notices extends Controller {
	private const RATE                 = 'sgg_rate';
	private const BUY_PRO              = 'sgg_buy_pro';
	private const NOTICE_COOLDOWN_DAYS = 3;
	private const NOTICE_DELAY_DAYS    = array(
		self::RATE    => array( 3, 15 ),
		self::BUY_PRO => array( 7, 30, 60 ),
	);

	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_notices' ) );
		add_action( 'wp_ajax_sgg_disable_notice', array( $this, 'disable_notice' ) );
	}

	public function disable_notice() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'sgg-notice' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce.', 'xml-sitemap-generator-for-google' ) ) );
		}

		$notice = sanitize_text_field( $_POST['notice'] ?? '' );

		if ( in_array( $notice, array( self::RATE, self::BUY_PRO ), true ) ) {
			$current_dismiss_count = (int) get_option( "xml_sitemap_disable_notice_{$notice}", 0 );
			$delays_count          = count( self::NOTICE_DELAY_DAYS[ $notice ] ?? array() );
			$max_dismiss_count     = max( 0, $delays_count );
			$next_dismiss_count    = min( $current_dismiss_count + 1, $max_dismiss_count );

			update_option( "xml_sitemap_disable_notice_{$notice}", $next_dismiss_count, false );
			set_transient( 'xml_sitemap_notice_cooldown', 1, self::NOTICE_COOLDOWN_DAYS * DAY_IN_SECONDS );
		}

		wp_send_json_success();
	}

	public function init_notices() {
		$installation_time = absint( get_option( 'xml_sitemap_installation_time' ) );

		if ( ! $installation_time ) {
			update_option( 'xml_sitemap_installation_time', time() );

			return;
		}

		if ( $this->is_notice_cooldown_active() ) {
			return;
		}

		$days               = round( ( time() - $installation_time ) / DAY_IN_SECONDS );
		$rate_dismiss_count = $this->get_notice_dismiss_count( self::RATE );
		$pro_dismiss_count  = $this->get_notice_dismiss_count( self::BUY_PRO );

		add_action( 'current_screen', array( $this, 'clear_admin_notices_on_plugin_screen' ) );

		if ( $this->should_show_notice( self::RATE, $days, $rate_dismiss_count ) ) {
			add_action( 'admin_notices', array( $this, 'rate_notice' ) );
			return;
		}

		if ( ! sgg_pro_enabled() && $this->should_show_notice( self::BUY_PRO, $days, $pro_dismiss_count ) ) {
			add_action( 'admin_notices', array( $this, 'pro_notice' ) );
			return;
		}
	}

	private function should_show_notice( $notice, $days, $dismiss_count ) {
		$delays = self::NOTICE_DELAY_DAYS[ $notice ] ?? array();

		if ( empty( $delays ) || $dismiss_count >= count( $delays ) ) {
			return false;
		}

		$required_days = (int) $delays[ $dismiss_count ];

		return $days >= $required_days;
	}

	private function get_notice_dismiss_count( $notice ) {
		$dismiss_state = get_option( 'xml_sitemap_disable_notice_' . $notice, 0 );

		if ( true === $dismiss_state ) {
			return 1;
		}

		return max( 0, (int) $dismiss_state );
	}

	private function is_notice_cooldown_active() {
		return (bool) get_transient( 'xml_sitemap_notice_cooldown' );
	}

	public function clear_admin_notices_on_plugin_screen( $screen ) {
		if ( strpos( $screen->id, 'xml-sitemap-generator-for-google' ) !== false ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	public function rate_notice() {
		$this->enqueue_scripts();

		Dashboard::render(
			'partials/rate-banner.php',
			array(
				'label'        => esc_html__( 'Hi, Thank you for using Google XML Sitemaps Generator!', 'xml-sitemap-generator-for-google' ),
				'description'  => sprintf(
					/* translators: %s: Rating */
					esc_html__( 'Enjoying the plugin? Please leave us a %s rating. It helps us improve and support more users.', 'xml-sitemap-generator-for-google' ),
					'<span><a href="' . esc_url( sgg_get_review_url() ) . '" target="_blank" rel="noopener noreferrer">★★★★★</a></span>'
				),
				'button_text'  => esc_html__( 'Leave a 5-star rating', 'xml-sitemap-generator-for-google' ),
				'button_url'   => esc_url( sgg_get_review_url() ),
				'data_notice'  => self::RATE,
				'notice_class' => 'grim-dynamic-notice',
			)
		);
	}

	public function pro_notice() {
		$this->enqueue_scripts();
		$pro_url = sgg_get_segment_pro_url( 'agencies', 'notice-pro' );

		Dashboard::render(
			'partials/rate-banner.php',
			array(
				'label'        => esc_html__( 'Want more SEO growth from your Sitemaps?', 'xml-sitemap-generator-for-google' ),
				'description'  => sprintf(
					/* translators: %s: Pro version */
					esc_html__( 'Unlock advanced controls, automation, and better scaling with the %s.', 'xml-sitemap-generator-for-google' ),
					'<a href="' . esc_url( $pro_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Pro version', 'xml-sitemap-generator-for-google' ) . '</a>'
				),
				'button_text'  => esc_html__( 'Explore Pro features', 'xml-sitemap-generator-for-google' ),
				'button_url'   => esc_url( $pro_url ),
				'data_notice'  => self::BUY_PRO,
				'notice_class' => 'grim-pro-notice grim-dynamic-notice',
			)
		);
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'sgg-notices', GRIM_SG_URL . 'assets/js/notices.js', array( 'jquery' ), GRIM_SG_VERSION, true );
		wp_localize_script(
			'sgg-notices',
			'sggNotice',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'sgg-notice' ),
			)
		);
	}
}
