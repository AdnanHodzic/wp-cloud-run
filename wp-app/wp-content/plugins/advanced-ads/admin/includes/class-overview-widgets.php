<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

use AdvancedAds\Entities;

/**
 * Container class for callbacks for overview widgets
 *
 * @package WordPress
 * @subpackage Advanced Ads Plugin
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
		$promo = new Translation_Promo(
			[
				'textdomain'     => 'advanced-ads',
				'plugin_name'    => 'Advanced Ads',
				'hook'           => 'advanced-ads-overview-below-support',
				'glotpress_logo' => false, // disables the plugin icon so we don’t need to keep up with potential changes.
			]
		);

		// show errors.
		if ( Advanced_Ads_Ad_Health_Notices::notices_enabled()
				&& count( Advanced_Ads_Ad_Health_Notices::get_instance()->displayed_notices ) ) {
				self::add_meta_box( 'advads_overview_notices', false, 'full', 'render_notices' );
		}

		self::add_meta_box(
			'advads_overview_news',
			__( 'Next steps', 'advanced-ads' ),
			'left',
			'render_next_steps'
		);
		self::add_meta_box(
			'advads_overview_support',
			__( 'Manual and Support', 'advanced-ads' ),
			'right',
			'render_support'
		);
		if ( Advanced_Ads_AdSense_Data::get_instance()->is_setup()
			&& ! Advanced_Ads_AdSense_Data::get_instance()->is_hide_stats() ) {
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
		?><script>jQuery( document ).ready( function(){ advads_ad_health_maybe_remove_list(); });</script>
		<?php
	}

	/**
	 * Render next steps widget
	 */
	public static function render_next_steps() {
		$primary_taken = false;

		$model      = Advanced_Ads::get_instance()->get_model();
		$recent_ads = $model->get_ads();
		if ( count( $recent_ads ) === 0 ) :
			echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'post-new.php?post_type=' . Entities::POST_TYPE_AD ) ) .
			'">' . esc_html( __( 'Create your first ad', 'advanced-ads' ) ) . '</a></p>';

			// Connect to AdSense.
			echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#adsense' ) ) .
			'">' . esc_attr__( 'Connect to AdSense', 'advanced-ads' ) . '</a></p>';
			$primary_taken = true;
		endif;

		$is_subscribed = Advanced_Ads_Admin_Notices::get_instance()->is_subscribed();
		$can_subscribe = Advanced_Ads_Admin_Notices::get_instance()->user_can_subscribe();
		$options       = Advanced_Ads_Admin_Notices::get_instance()->options();

		$_notice = 'nl_free_addons';
		if ( $can_subscribe ) {
			?>
			<h3><?php esc_html_e( 'Join the newsletter for more benefits', 'advanced-ads' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Get 2 free add-ons', 'advanced-ads' ); ?></li>
				<li><?php esc_html_e( 'Get the first steps and more tutorials to your inbox', 'advanced-ads' ); ?></li>
				<li><?php esc_html_e( 'How to earn more with AdSense', 'advanced-ads' ); ?></li>
			</ul>
			<div class="advads-admin-notice">
				<p>
					<button type="button" class="button-<?php echo ( $primary_taken ) ? 'secondary' : 'primary'; ?> advads-notices-button-subscribe" data-notice="<?php echo esc_attr( $_notice ); ?>">
						<?php esc_html_e( 'Join now', 'advanced-ads' ); ?>
					</button>
				</p>
			</div>
			<?php
		} elseif ( count( $recent_ads ) > 3
			&& ! isset( $options['closed']['review'] ) ) {
			/**
			 * Ask for a review if the review message was not closed before
			 */
			?>
			<div class="advads-admin-notice" data-notice="review">
				<p><?php esc_html_e( 'Do you find Advanced Ads useful and would like to keep us motivated? Please help us with a review.', 'advanced-ads' ); ?>
				<p><span class="dashicons dashicons-external"></span>&nbsp;<strong><a href="https://wordpress.org/support/plugin/advanced-ads/reviews/?rate=5#new-post" target=_"blank">
				<?php esc_html_e( 'Sure, I’ll rate the plugin', 'advanced-ads' ); ?></a></strong>
				&nbsp;&nbsp;<span class="dashicons dashicons-smiley"></span>&nbsp;<a href="javascript:void(0)" target=_"blank" class="advads-notice-dismiss">
					<?php esc_html_e( 'I already did', 'advanced-ads' ); ?></a>
				</p>
			</div>
			<?php
		} elseif ( count( $recent_ads ) > 0 ) {
			// link to manage ads.
			echo '<p><a class="button button-secondary" href="' . esc_url( admin_url( 'edit.php?post_type=' . Entities::POST_TYPE_AD ) ) .
			'">' . esc_html__( 'Manage your ads', 'advanced-ads' ) . '</a></p>';
		}

		$all_access = Advanced_Ads_Admin_Licenses::get_instance()->get_probably_all_access();
		if ( $is_subscribed && ! $all_access ) {
			?>
			<a class="button button-primary" href="https://wpadvancedads.com/add-ons/all-access/?utm_source=advanced-ads&utm_medium=link&utm_campaign=pitch-bundle" target="_blank"><?php esc_html_e( 'Get the All Access pass', 'advanced-ads' ); ?></a>
			<?php
		}
	}

	/**
	 * Support widget
	 */
	public static function render_support() {
		?>
		<ul>
			<li><a href="https://wpadvancedads.com/manual/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-manual" target="_blank">
			<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
				</a>
			</li>
			<li><a href="https://wpadvancedads.com/support/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-support" target="_blank">
			<?php esc_html_e( 'FAQ and Support', 'advanced-ads' ); ?>
				</a>
			</li>
			<li>
			<?php
			printf(
				wp_kses(
					// translators: %s is a URL.
					__( 'Thank the developer with a &#9733;&#9733;&#9733;&#9733;&#9733; review on <a href="%s" target="_blank">wordpress.org</a>', 'advanced-ads' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				),
				'https://wordpress.org/support/plugin/advanced-ads/reviews/#new-post'
			);
			?>
				</li>
		</ul>
		<?php

		$ignored_count   = count( Advanced_Ads_Ad_Health_Notices::get_instance()->ignore );
		$displayed_count = count( Advanced_Ads_Ad_Health_Notices::get_instance()->displayed_notices );
		if ( ! $displayed_count && $ignored_count ) {
			?>
			<p><span class="dashicons dashicons-warning"></span>&nbsp;<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=advanced-ads&advads-show-hidden-notices=true' ), 'advanced-ads-show-hidden-notices', 'advads_nonce' ) ); ?>">
			<?php
			printf(
				// translators: %s is the number of hidden notices.
				esc_html__( 'Show %s hidden notices', 'advanced-ads' ),
				absint( $ignored_count )
			);
			?>
				</a></p>
				<?php
		}

		do_action( 'advanced-ads-overview-below-support' );
	}

	/**
	 * Adsense stats widget
	 */
	public static function render_adsense_stats() {
		$filter_value = get_option( 'advanced-ads-adsense-dashboard-filter', '' );
		if ( ! $filter_value ) {
			$filter_value = self::get_site_domain();
		}
		if ( '*' === $filter_value ) {
			$filter_value = '';
		}
		$report_type   = 'domain';
		$report_filter = $filter_value;
		$pub_id = Advanced_Ads_AdSense_Data::get_instance()->get_adsense_id();
		include ADVADS_ABSPATH . 'admin/views/gadsense-dashboard.php';
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
		$domain = count( $matches ) > 1 ? $matches[2] : null;
		return $domain;
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
	 * Pro addons widget
	 *
	 * @param   bool $hide_activated if true, hide activated add-ons.
	 */
	public static function render_addons( $hide_activated = false ) {
		if ( ! $hide_activated ) :
			?>
			<p><a href="https://wpadvancedads.com/manual/how-to-install-an-add-on/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-install-add-ons" target="_blank"><?php echo esc_attr__( 'How to download, install and activate an add-on.', 'advanced-ads' ); ?></a></p>
			<?php
		endif;

		$caching_used = Advanced_Ads_Checks::cache();

		ob_start();
		?>
		<p><?php esc_html_e( 'The solution for professional websites.', 'advanced-ads' ); ?></p><ul class='list'>
		<li>
		<?php
		if ( $caching_used ) :

			?>
			<strong>
			<?php
endif;
			esc_html_e( 'support for cached sites', 'advanced-ads' );
		if ( $caching_used ) :

			?>
			</strong>
			<?php
endif;
		?>
			</li>
		<?php
		if ( class_exists( 'bbPress', false ) ) :
			?>
			<li>
			<?php
			printf(
				// translators: %s is the name of another plugin.
				wp_kses( __( 'integrates with <strong>%s</strong>', 'advanced-ads' ), [ 'strong' => [] ] ),
				'bbPress'
			);
			?>
				</li><?php endif; /* bbPress */ ?>
		<?php
		if ( class_exists( 'BuddyPress', false ) ) : // BuddyPress or BuddyBoss.
			?>
			<li>
			<?php
			printf(
			// translators: %s is the name of another plugin.
				wp_kses( __( 'integrates with <strong>%s</strong>', 'advanced-ads' ), [ 'strong' => [] ] ),
				defined( 'BP_PLATFORM_VERSION' ) ? 'BuddyBoss' : 'BuddyPress'
			);
			?>
				</li><?php endif; /* BuddyPress */ ?>
		<?php
		if ( defined( 'PMPRO_VERSION' ) ) :
			?>
			<li>
			<?php
			printf(
			// translators: %s is the name of another plugin.
				wp_kses( __( 'integrates with <strong>%s</strong>', 'advanced-ads' ), [ 'strong' => [] ] ),
				'Paid Memberships Pro'
			);
			?>
				</li><?php endif; /* Paid Memberships Pro */ ?>
		<?php
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) :
			?>
			<li>
			<?php
			printf(
			// translators: %s is the name of another plugin.
				wp_kses( __( 'integrates with <strong>%s</strong>', 'advanced-ads' ), [ 'strong' => [] ] ),
				'WPML'
			);
			?>
				</li><?php endif; /* WPML */ ?>
		<li><?php esc_html_e( 'click fraud protection, lazy load, ad-block ads', 'advanced-ads' ); ?></li>
		<li><?php esc_html_e( '11 more display and visitor conditions', 'advanced-ads' ); ?></li>
		<li><?php esc_html_e( '6 more placements', 'advanced-ads' ); ?></li>
		<li><?php esc_html_e( 'placement tests for ad optimization', 'advanced-ads' ); ?></li>
		<li><?php esc_html_e( 'Geo Targeting', 'advanced-ads' ); ?></li>
		<li><?php esc_html_e( 'ad grids and many more advanced features', 'advanced-ads' ); ?></li>
		</ul>
		<?php
		$pro_content = ob_get_clean();

		$add_ons = [
			'pro'             => [
				'title' => 'Advanced Ads Pro',
				'desc'  => $pro_content,
				'link'  => 'https://wpadvancedads.com/add-ons/advanced-ads-pro/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 4,
				'class' => 'recommended',
			],
			'tracking'        => [
				'title' => 'Tracking',
				'desc'  => __( 'Analyze clicks and impressions of your ads locally or in Google Analytics, share reports, and limit ads to a specific number of impressions or clicks.', 'advanced-ads' ),
				'link'  => 'https://wpadvancedads.com/add-ons/tracking/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 4,
			],
			'responsive'      => [
				'title' => 'AMP Ads',
				'desc'  => __( 'Effortlessly integrate your ads on AMP (Accelerated Mobile Pages) and auto-convert your Google AdSense ad units.', 'advanced-ads' ),
				'link'  => 'https://wpadvancedads.com/add-ons/responsive-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 4,
			],
			'gam'             => [
				'title' => 'Google Ad Manager Integration',
				'desc'  => __( 'A quick and error-free way of implementing ad units from your Google Ad Manager account.', 'advanced-ads' ),
				'link'  => 'https://wpadvancedads.com/add-ons/google-ad-manager/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 5,
			],
			'sticky'          => [
				'title' => 'Sticky ads',
				'desc'  => __( 'Increase click rates on your ads by placing them in sticky positions above, next or below your site.', 'advanced-ads' ),
				'link'  => 'https://wpadvancedads.com/add-ons/sticky-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 5,
			],
			'layer'           => [
				'title' => 'PopUps and Layers',
				'desc'  => __( 'Users will never miss an ad or other information in a PopUp. Choose when it shows up and for how long a user can close it.', 'advanced-ads' ),
				'link'  => 'https://wpadvancedads.com/add-ons/popup-and-layer-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 5,
			],
			'selling'         => [
				'title' => 'Selling Ads',
				'desc'  => __( 'Earn more money and let advertisers pay for ad space directly on the frontend of your site.', 'advanced-ads' ),
				'link'  => 'https://wpadvancedads.com/add-ons/selling-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 6,
			],
			'slider'          => [
				'title' => 'Ad Slider',
				'desc'  => __( 'Create a beautiful and simple slider from your ads to show more information on less space.', 'advanced-ads' ),
				'link'  => 'https://wpadvancedads.com/add-ons/slider/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'order' => 6,
			],
			'adsense-in-feed' => [
				'title'      => 'AdSense In-feed',
				'desc'       => __( 'Place AdSense In-feed ads between posts on homepage, category, and archive pages.', 'advanced-ads' ),
				'class'      => 'free',
				'link'       => wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=advanced-ads-adsense-in-feed' ), 'install-plugin_advanced-ads-adsense-in-feed' ),
				'link_title' => __( 'Install now', 'advanced-ads' ),
				'order'      => 9,
			],
		];

		// get all installed plugins; installed is not activated.
		$installed_plugins     = get_plugins();
		$installed_pro_plugins = 0;

		// handle AdSense In-feed if already installed or not activated.
		if ( isset( $installed_plugins['advanced-ads-adsense-in-feed/advanced-ads-in-feed.php'] ) ) { // is installed, but not active.
			// remove plugin from the list.
			unset( $add_ons['adsense-in-feed'] );
		}

		// PRO.
		if ( isset( $installed_plugins['advanced-ads-pro/advanced-ads-pro.php'] ) && ! class_exists( 'Advanced_Ads_Pro' ) ) { // is installed, but not active.
			$add_ons['pro']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-pro/advanced-ads-pro.php&amp', 'activate-plugin_advanced-ads-pro/advanced-ads-pro.php' );
			$add_ons['pro']['link_title'] = __( 'Activate now', 'advanced-ads' );
			++$installed_pro_plugins;
		} elseif ( class_exists( 'Advanced_Ads_Pro' ) ) {
			$add_ons['pro']['link']      = 'https://wpadvancedads.com/manual/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			$add_ons['pro']['desc']      = '';
			$add_ons['pro']['installed'] = true;
			$add_ons['pro']['order']     = 20;
			++$installed_pro_plugins;

			// remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['pro'] );
			}
		}

		// TRACKING.
		if ( isset( $installed_plugins['advanced-ads-tracking/tracking.php'] ) && ! class_exists( 'Advanced_Ads_Tracking_Plugin' ) ) { // is installed, but not active.
			$add_ons['tracking']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-tracking/tracking.php&amp', 'activate-plugin_advanced-ads-tracking/tracking.php' );
			$add_ons['tracking']['link_title'] = __( 'Activate now', 'advanced-ads' );
			++$installed_pro_plugins;
		} elseif ( class_exists( 'Advanced_Ads_Tracking_Plugin', false ) &&
			method_exists( Advanced_Ads_Tracking_Plugin::get_instance(), 'get_tracking_method' ) ) {
			$add_ons['tracking']['link'] = 'https://wpadvancedads.com/manual/tracking-documentation/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			if ( 'ga' !== Advanced_Ads_Tracking_Plugin::get_instance()->get_tracking_method() ) {

				// don’t show Tracking link if Analytics method is enabled.
				$add_ons['tracking']['desc'] = '<a href="' . admin_url( '/admin.php?page=advanced-ads-stats' ) . '">' . __( 'Visit your ad statistics', 'advanced-ads' ) . '</a>';
			} else {
				$add_ons['tracking']['desc'] = '';
			}
			$add_ons['tracking']['installed'] = true;
			$add_ons['tracking']['order']     = 20;
			++$installed_pro_plugins;

			// remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['tracking'] );
			}
		}

		// RESPONSIVE.
		if ( isset( $installed_plugins['advanced-ads-responsive/responsive-ads.php'] ) && ! class_exists( 'Advanced_Ads_Responsive_Plugin' ) ) { // is installed, but not active.
			$add_ons['responsive']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-responsive/responsive-ads.php&amp', 'activate-plugin_advanced-ads-responsive/responsive-ads.php' );
			$add_ons['responsive']['link_title'] = __( 'Activate now', 'advanced-ads' );
			++$installed_pro_plugins;
		} elseif ( class_exists( 'Advanced_Ads_Responsive_Plugin' ) ) {
			$add_ons['responsive']['link']      = 'https://wpadvancedads.com/manual/ads-on-amp-pages/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			$add_ons['responsive']['desc']      = '';
			$add_ons['responsive']['installed'] = true;
			$add_ons['responsive']['order']     = 20;
			++$installed_pro_plugins;

			// remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['responsive'] );
			}
		}

		// GOOGLE AD MANAGER.
		if ( isset( $installed_plugins['advanced-ads-gam/advanced-ads-gam.php'] ) && ! class_exists( 'Advanced_Ads_Network_Gam' ) ) { // is installed, but not active.
			$add_ons['gam']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-gam/advanced-ads-gam.php&amp', 'activate-plugin_advanced-ads-gam/advanced-ads-gam.php' );
			$add_ons['gam']['link_title'] = __( 'Activate now', 'advanced-ads' );
			++$installed_pro_plugins;
		} elseif ( class_exists( 'Advanced_Ads_Network_Gam' ) ) {
			$add_ons['gam']['link']      = 'https://wpadvancedads.com/manual/google-ad-manager-integration-manual/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			$add_ons['gam']['desc']      = '';
			$add_ons['gam']['installed'] = true;
			$add_ons['gam']['order']     = 20;
			++$installed_pro_plugins;

			// remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['gam'] );
			}
		}

		// STICKY.
		if ( isset( $installed_plugins['advanced-ads-sticky-ads/sticky-ads.php'] ) && ! class_exists( 'Advanced_Ads_Sticky_Plugin' ) ) { // is installed, but not active.
			$add_ons['sticky']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-sticky-ads/sticky-ads.php&amp', 'activate-plugin_advanced-ads-sticky-ads/sticky-ads.php' );
			$add_ons['sticky']['link_title'] = __( 'Activate now', 'advanced-ads' );
			++$installed_pro_plugins;
		} elseif ( class_exists( 'Advanced_Ads_Sticky_Plugin' ) ) {
			$add_ons['sticky']['link']      = 'https://wpadvancedads.com/manual/sticky-ads-documentation/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			$add_ons['sticky']['desc']      = '';
			$add_ons['sticky']['installed'] = true;
			$add_ons['sticky']['order']     = 20;
			++$installed_pro_plugins;

			// remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['sticky'] );
			}
		}

		// LAYER.
		if ( isset( $installed_plugins['advanced-ads-layer/layer-ads.php'] ) && ! class_exists( 'Advanced_Ads_Layer_Plugin' ) ) { // is installed, but not active.
			$add_ons['layer']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-layer/layer-ads.php&amp', 'activate-plugin_advanced-ads-layer/layer-ads.php' );
			$add_ons['layer']['link_title'] = __( 'Activate now', 'advanced-ads' );
			++$installed_pro_plugins;
		} elseif ( class_exists( 'Advanced_Ads_Layer_Plugin' ) ) {
			$add_ons['layer']['link']      = 'https://wpadvancedads.com/manual/popup-and-layer-ads-documentation/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			$add_ons['layer']['desc']      = '';
			$add_ons['layer']['installed'] = true;
			$add_ons['layer']['order']     = 20;
			++$installed_pro_plugins;

			// remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['layer'] );
			}
		}

		// SELLING ADS.
		if ( isset( $installed_plugins['advanced-ads-selling/advanced-ads-selling.php'] ) && ! class_exists( 'Advanced_Ads_Selling_Plugin' ) ) { // is installed, but not active.
			$add_ons['selling']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-selling/advanced-ads-selling.php&amp', 'activate-plugin_advanced-ads-selling/advanced-ads-selling.php' );
			$add_ons['selling']['link_title'] = __( 'Activate now', 'advanced-ads' );
			++$installed_pro_plugins;
		} elseif ( class_exists( 'Advanced_Ads_Selling_Plugin' ) ) {
			$add_ons['selling']['link']      = 'https://wpadvancedads.com/manual/selling-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			$add_ons['selling']['desc']      = '';
			$add_ons['selling']['installed'] = true;
			$add_ons['selling']['order']     = 20;
			++$installed_pro_plugins;

			// Remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['selling'] );
			}
		}

		// SLIDER.
		if ( isset( $installed_plugins['advanced-ads-slider/slider.php'] ) && ! class_exists( 'Advanced_Ads_Slider_Plugin' ) ) { // is installed, but not active.
			$add_ons['slider']['link']       = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=advanced-ads-slider/slider.php&amp', 'activate-plugin_advanced-ads-slider/slider.php' );
			$add_ons['slider']['link_title'] = __( 'Activate now', 'advanced-ads' );
		} elseif ( class_exists( 'Advanced_Ads_Slider_Plugin' ) ) {
			$add_ons['slider']['link']      = 'https://wpadvancedads.com/manual/ad-slider/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons-manual';
			$add_ons['slider']['desc']      = '';
			$add_ons['slider']['installed'] = true;
			$add_ons['slider']['order']     = 20;

			// remove the add-on.
			if ( $hide_activated ) {
				unset( $add_ons['slider'] );
			}
		}

		// add Genesis Ads, if Genesis based theme was detected.
		if ( defined( 'PARENT_THEME_NAME' ) && 'Genesis' === PARENT_THEME_NAME ) {
			$add_ons['genesis'] = [
				'title'      => 'Genesis Ads',
				'desc'       => __( 'Use Genesis specific ad positions.', 'advanced-ads' ),
				'order'      => 2,
				'class'      => 'free',
				'link'       => wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=advanced-ads-genesis' ), 'install-plugin_advanced-ads-genesis' ),
				'link_title' => __( 'Install now', 'advanced-ads' ),
			];
			// handle install link as long as we can not be sure this is done by the Genesis plugin itself.
			if ( isset( $installed_plugins['advanced-ads-genesis/genesis-ads.php'] ) ) { // is installed (active or not).
				unset( $add_ons['genesis'] );
			}
		}

		// add Ads for WPBakery Page Builder (formerly Visual Composer), if VC was detected.
		if ( defined( 'WPB_VC_VERSION' ) ) {
			$add_ons['visual_composer'] = [
				'title'      => 'Ads for WPBakery Page Builder (formerly Visual Composer)',
				'desc'       => __( 'Manage ad positions with WPBakery Page Builder (formerly Visual Composer).', 'advanced-ads' ),
				'order'      => 2,
				'class'      => 'free',
				'link'       => wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=ads-for-visual-composer' ), 'install-plugin_ads-for-visual-composer' ),
				'link_title' => __( 'Install now', 'advanced-ads' ),
			];
			// handle install link as long as we can not be sure this is done by the Genesis plugin itself.
			if ( isset( $installed_plugins['ads-for-visual-composer/advanced-ads-vc.php'] ) ) { // is installed (active or not).
				unset( $add_ons['visual_composer'] );
			}
		}

		// show All Access Pitch if less than 2 add-ons exist.
		if ( $installed_pro_plugins < 2 ) {
			$add_ons['bundle'] = [
				'title'        => 'All Access',
				'desc'         => __( 'Our best deal with all add-ons included.', 'advanced-ads' ),
				'link'         => 'https://wpadvancedads.com/add-ons/all-access/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'link_title'   => __( 'Get full access', 'advanced-ads' ),
				'link_primary' => true,
				'order'        => 0,
			];
		}

		$all_access_expiry = Advanced_Ads_Admin_Licenses::get_instance()->get_probably_all_access_expiry();

		// show All Access long-term pitch if less than 2 add-ons exist or
		// All Access license is expiring within next 12 month or already expired.

		if (
			$installed_pro_plugins < 2
			|| ( $all_access_expiry && ( time() + YEAR_IN_SECONDS ) > strtotime( $all_access_expiry ) )
		) {
			$add_ons['long_term'] = [
				'title'        => 'All Access long-term',
				'desc'         => __( 'A one-time payment for four years of support and updates. The package saves you up to 70% compared to individually purchasing our add-ons.', 'advanced-ads' ),
				'link'         => 'https://wpadvancedads.com/add-ons/all-access-long-term/?utm_source=advanced-ads&utm_medium=link&utm_campaign=overview-add-ons',
				'link_title'   => __( 'Get full access', 'advanced-ads' ),
				'link_primary' => true,
				'order'        => 1,
			];
		}

		// allow add-ons to manipulate the output.
		$add_ons = apply_filters( 'advanced-ads-overview-add-ons', $add_ons );

		uasort( $add_ons, [ __CLASS__, 'sort_by_order' ] );
		?>
		<table class="widefat striped">
		<?php
		foreach ( $add_ons as $_addon ) :
			if ( isset( $_addon['installed'] ) ) {
				$link_title      = __( 'Visit the manual', 'advanced-ads' );
				$_addon['title'] = '<span class="dashicons dashicons-yes" style="color: green; font-size: 1.5em;"></span> ' . $_addon['title'];
			} else {
				$link_title = isset( $_addon['link_title'] ) ? $_addon['link_title'] : __( 'Get this add-on', 'advanced-ads' );
			}
			include ADVADS_ABSPATH . 'admin/views/overview-addons-line.php';
		endforeach;
		?>
		</table>
		<?php
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
