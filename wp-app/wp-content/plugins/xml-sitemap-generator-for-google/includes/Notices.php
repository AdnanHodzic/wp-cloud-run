<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Notices extends Controller {
	private const RATE    = 'sgg_rate';
	private const BUY_PRO = 'sgg_buy_pro';

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
			update_option( "sgg_disable_notice_{$notice}", true, false );
		}

		wp_send_json_success();
	}

	public function init_notices() {
		$installation_time = get_option( 'sgg_installation_time' );

		if ( ! $installation_time ) {
			update_option( 'sgg_installation_time', time() );
		} else {
			$days         = round( ( time() - $installation_time ) / DAY_IN_SECONDS );
			$disable_rate = get_option( 'sgg_disable_notice_' . self::RATE, false );
			$disable_pro  = get_option( 'sgg_disable_notice_' . self::BUY_PRO, false );

			add_action( 'current_screen', function ( $screen ) use ( $days, $disable_rate, $disable_pro ) {
				if ( strpos( $screen->id, 'xml-sitemap-generator-for-google' ) === false ) {
					return;
				}

				remove_all_actions( 'admin_notices' );
				remove_all_actions( 'all_admin_notices' );

				if ( $days >= 3 && ! $disable_rate ) {
					add_action( 'admin_notices', array( $this, 'rate_notice' ) );
				}

				if ( $days >= 5 && ! sgg_pro_enabled() && ! $disable_pro ) {
					add_action( 'admin_notices', array( $this, 'pro_notice' ) );
				}
			});
		}
	}

	public function rate_notice() {
		$this->enqueue_scripts();

		Dashboard::render(
			'partials/rate-banner.php',
			array(
				'label'        => esc_html__( 'Hi, thank you for using Google XML Sitemaps Generator!', 'xml-sitemap-generator-for-google' ),
				'description'  => sprintf(
					esc_html__( 'If you like the plugin, please leave us a %s rating. A huge thank you from the team in advance!', 'xml-sitemap-generator-for-google' ),
					'<span><a href="' . esc_url( sgg_get_review_url() ) . '" target="_blank">★★★★★</a></span>'
				),
				'button_text'  => esc_html__( 'Yes, Rate Now', 'xml-sitemap-generator-for-google' ),
				'button_url'   => esc_url( sgg_get_review_url() ),
				'data_notice'  => self::RATE,
				'notice_class' => 'grim-dynamic-notice',
			)
		);
	}

	public function pro_notice() {
		$this->enqueue_scripts();

		Dashboard::render(
			'partials/rate-banner.php',
			array(
				'label'        => esc_html__( 'Hi, thank you for using Google XML Sitemaps Generator!', 'xml-sitemap-generator-for-google' ),
				'description'  => sprintf(
					esc_html__( 'If you want to unlock more features, please check out our %s.', 'xml-sitemap-generator-for-google' ),
					'<a href="' . esc_url( sgg_get_pro_url( 'notice' ) ) . '" target="_blank">' . esc_html__( 'Pro version', 'xml-sitemap-generator-for-google' ) . '</a>'
				),
				'button_text'  => esc_html__( 'Yes, Read More', 'xml-sitemap-generator-for-google' ),
				'button_url'   => esc_url( sgg_get_pro_url( 'notice' ) ),
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
