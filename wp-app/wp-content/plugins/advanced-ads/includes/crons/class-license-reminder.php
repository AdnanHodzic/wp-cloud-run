<?php
/**
 * Admin License Reminder.
 *
 * @package AdvancedAds
 */

namespace AdvancedAds\Crons;

use Advanced_Ads_Admin_Licenses;
use AdvancedAds\Framework\Utilities\Arr;
use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Cron License Reminder.
 */
class License_Reminder implements Initializer_Interface {

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize() {
		add_action( 'advanced_ads_weekly_license_reminder', [ $this, 'send_license_reminder' ] );
		add_action( 'init', [ $this, 'schedule_weekly_reminder' ] );
	}

	/**
	 * Schedule a weekly event to send license reminders.
	 */
	public function schedule_weekly_reminder() {
		if ( ! Arr::get( Advanced_Ads_Admin_Licenses::get_instance()->get_licenses(), 'no-weekly-reminder', false ) && ! wp_next_scheduled( 'advanced_ads_weekly_license_reminder' ) ) {
			wp_schedule_event( time(), 'weekly', 'advanced_ads_weekly_license_reminder' );
		}
	}

	/**
	 * Send a license reminder email.
	 */
	public function send_license_reminder() {
		if ( ! $this->is_license_missing() || Arr::get( Advanced_Ads_Admin_Licenses::get_instance()->get_licenses(), 'no-weekly-reminder', false ) ) {
			return;
		}

		$subject = __( 'Reminder: Activate your Advanced Ads licenses for updates', 'advanced-ads' );
		$message = $this->get_email_content();
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		$status = wp_mail( get_option( 'admin_email' ), $subject, $message, $headers );
	}

	/**
	 * Check if any installed add-ons are missing a license key.
	 *
	 * @return bool
	 */
	private function is_license_missing() {
		$addons = [
			'AAP_SLUG'       => 'advanced-ads-pro',
			'AAT_SLUG'       => 'advanced-ads-tracking',
			'AAGAM_SETTINGS' => 'advanced-ads-gam',
			'AASA_SLUG'      => 'advanced-ads-selling',
			'AAS_SLUG'       => 'advanced-ads-slider',
			'AAPLDS_SLUG'    => 'advanced-ads-layer',
			'AASADS_SLUG'    => 'advanced-ads-sticky-ads',
			'AAR_SLUG'       => 'advanced-ads-responsive',
		];

		foreach ( $addons as $slug => $addon ) {
			if ( defined( $slug ) && ( Advanced_Ads_Admin_Licenses::get_instance()->get_license_status( $addon ) !== 'valid' ) ) {
				if ( ! Arr::get( Advanced_Ads_Admin_Licenses::get_instance()->get_licenses(), $addon, false ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get the email content from a separate file.
	 *
	 * @return string
	 */
	private function get_email_content() {
		ob_start();
		include ADVADS_ABSPATH . 'templates/emails/license-reminder.php';
		return ob_get_clean();
	}
}
