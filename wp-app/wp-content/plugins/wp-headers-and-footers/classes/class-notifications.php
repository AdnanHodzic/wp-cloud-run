<?php
/**
 * Handling all the Notification calls in WP Headers and Footers.
 *
 * @package wp-headers-and-footers
 * @since 1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

if ( ! class_exists( 'WPHeaderAndFooter_Notification' ) ) :

	/**
	 * Handle Notification for Inline Headers and Footers.
	 */
	class WPHeaderAndFooter_Notification {

		/**
		 * Class constructor
		 */
		public function __construct() {

			$this->notification_hooks();
		}

		/**
		 * Hook into actions and filters
		 *
		 * @since  1.3.3
		 */
		private function notification_hooks() {
			add_action( 'admin_init', array( $this, 'wp_headers_and_footers_review_notice' ) );
		}

		/**
		 * Ask users to review our plugin on wordpress.org
		 *
		 * @since 1.3.3
		 * @version 2.1.0
		 */
		public function wp_headers_and_footers_review_notice() {

			$this->wpheaderandfooter_review_dismissal();
			$this->wpheaderandfooter_review_pending();

			$activation_time  = get_site_option( 'wpheaderandfooter_active_time' );
			$review_dismissal = get_site_option( 'wpheaderandfooter_review_dismiss' );

			// Update the $review_dismissal value in 2.1.0
			if ( 'yes_v2_1_0' === $review_dismissal ) :
				return;
			endif;

			if ( ! $activation_time ) :

				$activation_time = time();
				add_site_option( 'wpheaderandfooter_active_time', $activation_time );
			endif;

			// 1296000 = 15 Days in seconds.
			if ( ( time() - $activation_time > 1296000 ) && current_user_can( 'manage_options' ) ) :

				wp_enqueue_style( 'wpheaderandfooter_review_style', plugins_url( '../asset/css/style-review.css', __FILE__ ), array(), WPHEADERANDFOOTER_VERSION );
				add_action( 'admin_notices', array( $this, 'wp_headers_and_footers_review_notice_message' ) );
			endif;

		}

		/**
		 * Check and Dismiss review message.
		 *
		 * @since 1.3.3
		 * @version 2.1.0
		 */
		private function wpheaderandfooter_review_dismissal() {

			if ( ! is_admin() ||
				! current_user_can( 'manage_options' ) ||
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'wpheaderandfooter-review-nonce' ) ||
				! isset( $_GET['wpheaderandfooter_review_dismiss'] ) ) :

				return;
			endif;

			// Update the $review_dismissal value in 2.1.0
			update_site_option( 'wpheaderandfooter_review_dismiss', 'yes_v2_1_0' );
		}

		/**
		 * Set time to current so review notice will popup after 14 days
		 *
		 * @since 1.3.3
		 */
		private function wpheaderandfooter_review_pending() {

			if ( ! is_admin() ||
				! current_user_can( 'manage_options' ) ||
				! isset( $_GET['_wpnonce'] ) ||
				! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'wpheaderandfooter-review-nonce' ) ||
				! isset( $_GET['wpheaderandfooter_review_later'] ) ) :

				return;
			endif;

			// Reset Time to current time.
			update_site_option( 'wpheaderandfooter_active_time', time() );
		}

		/**
		 * Review notice message
		 *
		 * @since 1.3.3
		 * @version 2.1.0
		 */
		public function wp_headers_and_footers_review_notice_message() {

			$scheme      = ( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
			// Update the wpheaderandfooter_review_dismiss value in 2.1.0
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'wpheaderandfooter_review_dismiss=yes_v2_1_0';
			$dismiss_url = wp_nonce_url( $url, 'wpheaderandfooter-review-nonce' );

			$_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'wpheaderandfooter_review_later=yes';
			$later_url   = wp_nonce_url( $_later_link, 'wpheaderandfooter-review-nonce' ); ?>

			<div class="wpheaderandfooter-review-notice">
				<div class="wpheaderandfooter-review-thumbnail">
					<img src="<?php echo esc_url( plugins_url( '../asset/img/icon-128x128.png', __FILE__ ) ); ?>" alt="Inline Headers And Footers Logo">
				</div>
				<div class="wpheaderandfooter-review-text">
					<h3><?php esc_html_e( 'Leave A Review?', 'wp-headers-and-footers' ); ?></h3>
					<p><?php esc_html_e( 'We hope you\'ve enjoyed using Inline Headers And Footers! Would you consider leaving us a review on WordPress.org?', 'wp-headers-and-footers' ); ?></p>
					<ul class="wpheaderandfooter-review-ul">
						<li><a href="https://wordpress.org/support/view/plugin-reviews/wp-headers-and-footers?rate=5#rate-response" target="_blank"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'Sure! I\'d love to!', 'wp-headers-and-footers' ); ?></a></li>
						<li><a href="<?php echo esc_url( $dismiss_url ); ?>"><span class="dashicons dashicons-smiley"></span><?php esc_html_e( 'I\'ve already left a review', 'wp-headers-and-footers' ); ?></a></li>
						<li><a href="<?php echo esc_url( $later_url ); ?>"><span class="dashicons dashicons-calendar-alt"></span><?php esc_html_e( 'Maybe Later', 'wp-headers-and-footers' ); ?></a></li>
						<li><a href="<?php echo esc_url( $dismiss_url ); ?>"><span class="dashicons dashicons-dismiss"></span><?php esc_html_e( 'Never show again', 'wp-headers-and-footers' ); ?></a></li>
					</ul>
				</div>
			</div>
			<?php
		}
	}
endif;
new WPHeaderAndFooter_Notification();
