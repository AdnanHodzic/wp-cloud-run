<?php
/**
 * Admin WordPress Dashboard.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Options;
use Advanced_Ads_AdSense_Data;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Utilities\Conditional;
use Advanced_Ads_Overview_Widgets_Callbacks;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * WordPress Dashboard.
 */
class WordPress_Dashboard implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'wp_dashboard_setup', [ $this, 'add_adsense_widget' ] );
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ], 10, 0 );
		add_action( 'advanced-ads-dashbaord-widget', [ $this, 'display_performing_ads' ] );
		add_action( 'advanced-ads-dashbaord-widget', [ $this, 'display_rss_widget' ] );
	}

	/**
	 * Enqueue styles and scripts for current screen
	 *
	 * @return void
	 */
	public function enqueue(): void {
		// Early bail!!
		$wp_screen = get_current_screen();
		if ( 'dashboard' !== $wp_screen->id ) {
			return;
		}

		wp_advads()->registry->enqueue_style( 'wp-dashboard' );
		wp_advads()->registry->enqueue_script( 'wp-dashboard' );
	}

	/**
	 * Add dashboard widget with ad stats and additional information
	 *
	 * @return void
	 */
	public function add_dashboard_widget(): void {
		if ( ! Conditional::user_can( 'advanced_ads_see_interface' ) ) {
				return;
		}

		$icon = WordPress::get_svg( 'logo.svg' );
		$icon = '<span class="advads-logo--icon">' . $icon . '</span>';

		wp_add_dashboard_widget(
			'advads-dashboard-widget',
			$icon . '<span class="advads-logo--text">' . __( 'Advanced Ads', 'advanced-ads' ) . '</span>',
			[ $this, 'display_dashboard_widget' ],
			null,
			null,
			'side',
			'high'
		);
	}

	/**
	 * Adds an AdSense widget to the WordPress dashboard.
	 *
	 * @return void
	 */
	public function add_adsense_widget(): void {
		if (
			Advanced_Ads_AdSense_Data::get_instance()->is_setup() &&
			! Advanced_Ads_AdSense_Data::get_instance()->is_hide_stats() &&
			Options::instance()->get( 'adsense.adsense-wp-widget' )
		) {
			wp_add_dashboard_widget(
				'advads-adsense-widget',
				__( 'AdSense Earnings', 'advanced-ads' ),
				[ $this, 'display_adsense_widget' ],
				null,
				null,
				'side'
			);
		}
	}

	/**
	 * Display widget functions
	 *
	 * @return void
	 */
	public function display_dashboard_widget(): void {
		if ( Conditional::user_can( 'advanced_ads_edit_ads' ) ) {
			include ADVADS_ABSPATH . 'views/admin/widgets/wordpress-dashboard/header.php';
		}

		if ( Conditional::user_can_subscribe( 'nl_first_steps' ) || Conditional::user_can_subscribe( 'nl_adsense' ) ) {
			include ADVADS_ABSPATH . 'views/admin/widgets/wordpress-dashboard/newsletter.php';
		}

		/**
		 * Let developer add KPIs and info into dashabord
		 *
		 * @param WordPress_Dashboard $this Dashabord widget instance.
		 */
		do_action( 'advanced-ads-dashbaord-widget', $this );

		include ADVADS_ABSPATH . 'views/admin/widgets/wordpress-dashboard/footer.php';
	}

	/**
	 * Display the AdSense widget on the WordPress dashboard.
	 *
	 * @return void
	 */
	public function display_adsense_widget(): void {
		Advanced_Ads_Overview_Widgets_Callbacks::render_adsense_stats();
	}

	/**
	 * Display performing ads widget
	 *
	 * @return void
	 */
	public function display_performing_ads(): void {
		include_once ADVADS_ABSPATH . 'views/admin/widgets/wordpress-dashboard/performing-ads.php';
	}

	/**
	 * Display rss widget
	 *
	 * @return void
	 */
	public function display_rss_widget(): void {
		include_once ADVADS_ABSPATH . 'views/admin/widgets/wordpress-dashboard/rss.php';
	}
}
