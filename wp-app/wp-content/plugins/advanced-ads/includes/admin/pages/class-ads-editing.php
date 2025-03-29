<?php
/**
 * Ads edit screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Utilities\WordPress;
use DateTime;

defined( 'ABSPATH' ) || exit;

/**
 * Ads.
 */
class Ads_Editing extends Ads {

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'ads-editing';
	}

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$this->set_hook( Constants::POST_TYPE_AD );
		add_action( 'dbx_post_sidebar', [ $this, 'edit_form_end' ] );
		add_action( 'edit_form_top', [ $this, 'edit_form_above_title' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'add_submit_box_meta' ] );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		$wp_screen = get_current_screen();
		if ( 'post' === $wp_screen->base && 'add' !== $wp_screen->action ) {
			add_action( 'advanced-ads-admin-header-actions', [ $this, 'add_new_ad_button' ] );
		}

		if ( 'post' === $wp_screen->base && Constants::POST_TYPE_AD === $wp_screen->post_type ) {
			wp_advads()->registry->enqueue_script( 'screen-ads-editing' );
			wp_advads()->registry->enqueue_style( 'screen-ads-editing' );
		}
	}

	/**
	 * Define header args.
	 *
	 * @return array
	 */
	public function define_header_args(): array {
		return [
			'manual_url'         => 'https://wpadvancedads.com/manual/first-ad/',
			'show_filter_button' => false,
		];
	}

	/**
	 * Add information below the ad edit form
	 *
	 * @since 1.7.3
	 *
	 * @param WP_Post $post WordPress Post object.
	 *
	 * @return void
	 */
	public function edit_form_end( $post ): void {
		if ( Constants::POST_TYPE_AD !== $post->post_type ) {
			return;
		}

		include_once ADVADS_ABSPATH . 'views/admin/ads/info-bottom.php';
	}

	/**
	 * Add information above the ad title
	 *
	 * @since 1.5.6
	 *
	 * @param object $post WordPress post type object.
	 *
	 * @return void
	 */
	public function edit_form_above_title( $post ): void {
		if ( ! isset( $post->post_type ) || Constants::POST_TYPE_AD !== $post->post_type ) {
			return;
		}

		// Highlight Dummy ad if this is the first ad.
		if ( ! WordPress::get_count_ads() ) {
			?>
			<style>.advanced-ads-type-list-dummy {
					font-weight: bold;
				}</style>
			<?php
		}

		// Display general and wizard information.
		include_once ADVADS_ABSPATH . 'views/admin/ads/info-top.php';

		// Don’t show placement options if this is an ad translated with WPML since the placement might exist already.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$trid         = apply_filters( 'wpml_element_trid', null, $post->ID );
			$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'Advanced_Ads' );
			if ( count( $translations ) > 1 ) {
				return;
			}
		}

		$ad         = wp_advads_get_ad( $post->ID );
		$placements = wp_advads_get_placements();

		/**
		 * Display ad injection information after ad is created.
		 *
		 * Set `advanced-ads-ad-edit-show-placement-injection` to false if you want to prevent the box from appearing
		 */
		if ( 6 === Params::get( 'message', 0, FILTER_VALIDATE_INT ) && apply_filters( 'advanced-ads-ad-edit-show-placement-injection', true ) ) {
			$latest_post = $this->get_latest_post();
			include_once ADVADS_ABSPATH . 'admin/views/placement-injection-top.php';
		}
	}

	/**
	 * Add meta values below submit box
	 *
	 * @since 1.3.15
	 *
	 * @param WP_Post $post WordPress post type object.
	 *
	 * @return void
	 */
	public function add_submit_box_meta( $post ): void {
		global $wp_locale;

		if ( Constants::POST_TYPE_AD !== $post->post_type ) {
			return;
		}

		$ad = wp_advads_get_ad( $post->ID );

		// Get time set for ad or current timestamp (both GMT).
		$utc_ts   = $ad->get_expiry_date() ?: current_time( 'timestamp', true ); // phpcs:ignore
		$utc_time = new DateTime( '@' . $utc_ts );
		$utc_time->setTimezone( \Advanced_Ads_Utils::get_wp_timezone() );
		[ $curr_year, $curr_month, $curr_day, $curr_hour, $curr_minute ] = explode( '-', $utc_time->format( 'Y-m-d-H-i' ) );
		$enabled = 1 - empty( $ad->get_expiry_date() );

		include ADVADS_ABSPATH . 'views/admin/ads/submitbox-meta.php';
	}

	/**
	 * Whether to start the wizard by default or not
	 *
	 * @since 1.7.4
	 *
	 * @return bool true, if wizard should start automatically
	 */
	private function start_wizard_automatically(): bool {
		global $post;

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return true;
		}

		$hide_wizard = get_user_meta( $user_id, 'advanced-ads-hide-wizard', true );

		// true the ad already exists, if the wizard was never started or closed.
		return ( 'edit' !== $post->filter && ( ! $hide_wizard || 'false' === $hide_wizard ) ) ? true : false;
	}

	/**
	 * Whether to show the wizard welcome message or not
	 *
	 * @since 1.7.4
	 *
	 * @return bool true, if wizard welcome message should be displayed
	 */
	private function show_wizard_welcome(): bool {
		global $post;

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return true;
		}

		$hide_wizard = get_user_meta( $user_id, 'advanced-ads-hide-wizard', true );

		return ( ! $hide_wizard && 'edit' !== $post->filter ) ? true : false;
	}

	/**
	 * Load latest blog post
	 *
	 * @return WP_POST|null
	 */
	private function get_latest_post() {
		$posts = wp_get_recent_posts( [ 'numberposts' => 1 ] );

		return $posts ? $posts[0] : null;
	}
}
