<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

use AdvancedAds\Admin\Translation_Promo;

/**
 * Container class for callbacks for overview widgets
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 */
class Advanced_Ads_Overview_Widgets_Callbacks {
	/**
	 * In case one wants to inject several dashboards into a page, we will prevent executing redundant javascript
	 * with the help of this little bool
	 *
	 * @var mixed
	 */
	private static $processed_adsense_stats_js = false;

	/**
	 * When doing ajax request (refreshing the dashboard), we need to have a nonce.
	 * one is enough, that's why we need to remember it.
	 *
	 * @var mixed
	 */
	private static $gadsense_dashboard_nonce = false;


	/**
	 * Register the plugin overview widgets
	 */
	public static function setup_overview_widgets() {

		// initiate i18n notice.
		( new Translation_Promo(
			[
				'textdomain'     => 'advanced-ads',
				'plugin_name'    => 'Advanced Ads',
				'hook'           => 'advanced-ads-overview-below-support',
				'glotpress_logo' => false, // disables the plugin icon so we don’t need to keep up with potential changes.
			]
		) );

		// show errors.
		if ( Advanced_Ads_Ad_Health_Notices::notices_enabled() ) {
				self::add_meta_box( 'advads_overview_notices', __( 'Notifications', 'advanced-ads' ), 'right', 'render_notices' );
		}

		self::add_meta_box(
			'advads_overview_news',
			__( 'Next Steps', 'advanced-ads' ),
			'left',
			'render_next_steps'
		);

		self::add_meta_box(
			'advads_overview_support',
			__( 'Manual & Support', 'advanced-ads' ),
			'right',
			'render_support'
		);

		if (
			Advanced_Ads_AdSense_Data::get_instance()->is_setup() &&
			! Advanced_Ads_AdSense_Data::get_instance()->is_hide_stats()
		) {
			$disable_link_markup = '<span class="advads-hndlelinks hndle"><a href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ) . '" target="_blank">' . esc_attr__( 'Disable', 'advanced-ads' ) . '</a></span>';

			self::add_meta_box(
				'advads_overview_adsense_stats',
				__( 'AdSense Earnings', 'advanced-ads' ) . $disable_link_markup,
				'full',
				'render_adsense_stats'
			);
		}

		// add widgets for pro add ons.
		self::add_meta_box( 'advads_overview_addons', __( 'Add-Ons', 'advanced-ads' ), 'full', 'render_addons' );

		do_action( 'advanced-ads-overview-widgets-after' );
	}

	/**
	 * Loads a meta box into output
	 *
	 * @param string   $id meta box ID.
	 * @param string   $title title of the meta box.
	 * @param string   $position context in which to show the box.
	 * @param callable $callback function that fills the box with the desired content.
	 */
	public static function add_meta_box( $id, $title, $position, $callback ) {
		ob_start();
		call_user_func( [ 'Advanced_Ads_Overview_Widgets_Callbacks', $callback ] );
		do_action( 'advanced-ads-overview-widget-content-' . $id, $id );
		$content = ob_get_clean();

		include ADVADS_ABSPATH . 'admin/views/overview-widget.php';
	}

	/**
	 * Render Ad Health notices widget
	 */
	public static function render_notices() {
		Advanced_Ads_Ad_Health_Notices::get_instance()->render_widget();
		?>
		<script>jQuery( document ).ready( function(){ advads_ad_health_maybe_remove_list(); });</script>
		<?php
	}

	/**
	 * Render next steps widget
	 */
	public static function render_next_steps() {
		include ADVADS_ABSPATH . 'views/admin/widgets/aa-dashboard/next-steps/widget.php';
	}

	/**
	 * Support widget
	 */
	public static function render_support() {
		include ADVADS_ABSPATH . 'views/admin/widgets/aa-dashboard/support.php';
		do_action( 'advanced-ads-overview-below-support' );
	}

	/**
	 * Adsense stats widget
	 */
	public static function render_adsense_stats() {
		$report_type   = 'domain';
		$report_filter = get_option( 'advanced-ads-adsense-dashboard-filter', '' );

		if ( ! $report_filter ) {
			$report_filter = self::get_site_domain();
		}

		if ( '*' === $report_filter ) {
			$report_filter = '';
		}

		include ADVADS_ABSPATH . 'views/admin/metaboxes/ads/ad-gadsense-dashboard.php';
	}

	/**
	 * JavaScript loaded in AdSense stats widget.
	 *
	 * @param string $pub_id AdSense publisher ID.
	 *
	 * @return string
	 * @todo move to JS file.
	 */
	final public static function adsense_stats_js( $pub_id ) {
		if ( self::$processed_adsense_stats_js ) {
			return;
		}
		self::$processed_adsense_stats_js = true;
		$nonce                            = self::get_adsense_dashboard_nonce();
		?>
		<script>
		window.gadsenseData = window.gadsenseData || {};
		window.Advanced_Ads_Adsense_Report_Helper = window.Advanced_Ads_Adsense_Report_Helper || {};
		window.Advanced_Ads_Adsense_Report_Helper.nonce = '<?php echo esc_html( $nonce ); ?>';
		gadsenseData['pubId'] = '<?php echo esc_html( $pub_id ); ?>';
		</script>
		<?php
	}

	/**
	 * Return a nonce used in the AdSense stats widget.
	 *
	 * @return false|mixed|string
	 */
	final public static function get_adsense_dashboard_nonce() {
		if ( ! self::$gadsense_dashboard_nonce ) {
			self::$gadsense_dashboard_nonce = wp_create_nonce( 'advads-gadsense-dashboard' );
		}
		return self::$gadsense_dashboard_nonce;
	}

	/**
	 * Extracts the domain from the site url
	 *
	 * @return string the domain, that was extracted from get_site_url()
	 */
	public static function get_site_domain() {
		$site = get_site_url();
		preg_match( '|^([\d\w]+://)?([^/]+)|', $site, $matches );

		return count( $matches ) > 1 ? $matches[2] : null;
	}

	/**
	 * This method is called when the dashboard data is requested via ajax
	 * it prints the relevant data as json, then dies.
	 */
	public static function ajax_gadsense_dashboard() {
		$post_data = wp_unslash( $_POST );
		if ( wp_verify_nonce( $post_data['nonce'], 'advads-gadsense-dashboard' ) === false ) {
			wp_send_json_error( 'Unauthorized request', 401 );
		}
		$report_type = in_array( $post_data['type'], [ 'domain', 'unit' ], true ) ? $post_data['type'] : false;

		if ( ! $report_type ) {
			wp_send_json_error( 'Invalid arguments', 400 );
		}

		$report_filter = wp_strip_all_tags( $post_data['filter'] );
		$report        = new Advanced_Ads_AdSense_Report( $report_type, $report_filter );

		if ( $report->get_data()->is_valid() ) {
			wp_send_json_success( [ 'html' => $report->get_markup() ] );
		}

		if ( $report->refresh_report() ) {
			wp_send_json_success( [ 'html' => $report->get_markup() ] );
		}

		$error_message = $report->get_last_api_error();
		// Send markup with error info.
		wp_send_json_success( [ 'html' => '<div class="error"><p>' . wp_kses_post( $error_message ) . '</p></div>' ] );
	}

	/**
	 * Render stats box
	 *
	 * @param string $title title of the box.
	 * @param string $main main content.
	 * @param string $footer footer content.
	 *
	 * @deprecated ?
	 */
	final public static function render_stats_box( $title, $main, $footer ) {
		?>
		<div class="advanced-ads-stats-box flex1">
			<?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="advanced-ads-stats-box-main">
				<?php
				// phpcs:ignore
				echo $main;
				?>
			</div>
			<?php echo $footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
		<?php
	}

	/**
	 * Add-ons box
	 *
	 * @param bool $hide_activated if true, hide activated add-ons.
	 * @param bool $is_dashboard   whether it is used in the AA dashboard.
	 *
	 * @return void
	 */
	public static function render_addons( $hide_activated = false, $is_dashboard = true ) {
		$box = new \AdvancedAds\Admin\Addon_Box( $hide_activated );
		$box->display( $is_dashboard );
	}

	/**
	 * Sort by installed add-ons
	 *
	 * @param array $a argument a.
	 * @param array $b argument b.
	 *
	 * @return int
	 */
	protected static function sort_by_order( $a, $b ) {
		return $a['order'] - $b['order'];
	}
}
