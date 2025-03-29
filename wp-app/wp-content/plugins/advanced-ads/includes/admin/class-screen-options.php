<?php
/**
 * Admin Screen Options.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use WP_Screen;
use AdvancedAds\Options;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Screen Options.
 */
class Screen_Options implements Integration_Interface {

	const USER_META_KEY = 'advanced-ads-screen-options';

	/**
	 * Array key for screen options.
	 *
	 * @var string
	 */
	private $screen_key = '';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'screen_settings', [ $this, 'add_screen_options' ], 10, 2 );
		add_action( 'wp_loaded', [ $this, 'save_screen_options' ] );
		add_action( 'load-edit.php', [ $this, 'set_screen_options' ] );
	}

	/**
	 * Return true if the current screen is the ad or placement list.
	 *
	 * @return bool
	 */
	private function is_screen(): bool {
		return Conditional::is_screen( [ 'edit-advanced_ads', 'edit-advanced_ads_plcmnt' ] );
	}

	/**
	 * Register custom screen options on the ad overview page.
	 *
	 * @param string    $options Screen options HTML.
	 * @param WP_Screen $screen  Screen object.
	 *
	 * @return string
	 */
	public function add_screen_options( $options, WP_Screen $screen ) {
		if ( ! $this->is_screen() ) {
			return $options;
		}

		$selected_filters    = $screen->get_option( 'filters_to_show' ) ?? [];
		$is_filter_permanent = boolval( $screen->get_option( 'show-filters' ) );
		$optional_filters    = $this->get_optional_filters();

		// If the default WordPress screen options don't exist, we have to force the submit button to show.
		add_filter( 'screen_options_show_submit', '__return_true' );

		ob_start();
		require ADVADS_ABSPATH . 'views/admin/screen-options.php';

		return $options . ob_get_clean();
	}

	/**
	 * Add the screen options to the WP_Screen options
	 *
	 * @return void
	 */
	public function set_screen_options(): void {
		$screen_options = $this->get_screen_options();

		// Early bail!!
		if ( ! $this->is_screen() || empty( $screen_options ) ) {
			return;
		}

		$screen_key     = $this->get_screen_key( get_current_screen()->id );
		$screen_options = $screen_options[ $screen_key ] ?? [];

		foreach ( $screen_options as $option_name => $value ) {
			add_screen_option( $option_name, $value );
		}
	}

	/**
	 * Save the screen option setting.
	 *
	 * @return void
	 */
	public function save_screen_options() {
		$options = Params::post( self::USER_META_KEY, false, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$user    = wp_get_current_user();

		// Early bail!!
		if ( ! $options || ! $user ) {
			return;
		}

		check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );

		$screen_options = $this->get_screen_options();
		$screen_key     = $this->get_screen_key( $options['screen-id'] );

		// no need to save it.
		unset( $options['screen-id'] );

		$screen_options[ $screen_key ] = $options;

		update_user_meta( $user->ID, self::USER_META_KEY, $screen_options );
	}

	/**
	 * Get the current user screen options from DB.
	 *
	 * @return array
	 */
	private function get_screen_options() {
		$screen_options = get_user_meta( get_current_user_id(), self::USER_META_KEY, true );
		if ( ! is_array( $screen_options ) ) {
			return [];
		}

		return $screen_options;
	}

	/**
	 * Get the screen key for DB use.
	 *
	 * @param string $screen_id Screen ID.
	 *
	 * @return string
	 */
	private function get_screen_key( $screen_id = false ) {
		if ( ! $screen_id && ! empty( $this->screen_key ) ) {
			return $this->screen_key;
		}

		switch ( $screen_id ) {
			case 'edit-advanced_ads':
				$this->screen_key = 'ad';
				break;
			case 'edit-advanced_ads_plcmnt':
				$this->screen_key = 'placement';
				break;
			default:
				$this->screen_key = false;
		}

		return $this->screen_key;
	}

	/**
	 * Get optional filters.
	 *
	 * @return array The optional filters.
	 */
	private function get_optional_filters() {
		// $optional_filters array order determines display sequence.
		$optional_filters = [];

		if ( Conditional::is_screen( [ 'edit-advanced_ads' ] ) ) {
			$optional_filters ['all_debug_mode'] = __( 'Debug Mode', 'advanced-ads' );
			$optional_filters['all_authors']     = __( 'Author', 'advanced-ads' );

			// show only when privacy setting is enabled.
			if ( Options::instance()->get( 'privacy.enabled' ) ) {
				$optional_filters['all_privacyignore'] = __( 'Privacy Ignore', 'advanced-ads' );
			}

			$optional_filters = apply_filters( 'advanced_ads_optional_filters', $optional_filters );
		}

		return $optional_filters;
	}
}
