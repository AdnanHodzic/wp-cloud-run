<?php
/**
 * Admin Welcome.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Welcome.
 */
class Welcome {
	/**
	 * Dismiss user meta
	 *
	 * @var string
	 */
	const USER_META = 'advanced-ads-welcome';

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Welcome
	 */
	public static function get() {
		static $instance;

		if ( null === $instance ) {
			$instance = new Welcome();
		}

		return $instance;
	}

	/**
	 * Print the welcome box
	 *
	 * @return void
	 */
	public function display() {
		if ( ! $this->can_display() ) {
			return;
		}
		require_once plugin_dir_path( ADVADS_FILE ) . '/views/admin/welcome-box.php';
	}

	/**
	 * Stop displaying the welcome box fot the current user
	 *
	 * @return void
	 */
	public function dismiss() {
		update_user_meta( get_current_user_id(), self::USER_META, '1' );
	}

	/**
	 * Checks if the welcome box can be displayed
	 *
	 * @return bool
	 */
	public function can_display(): bool {
		$meta        = get_user_meta( get_current_user_id(), self::USER_META, true );
		$wizard_done = get_option( Constants::OPTION_WIZARD_COMPLETED, false );

		return empty( $meta ) && ! $wizard_done;
	}
}
