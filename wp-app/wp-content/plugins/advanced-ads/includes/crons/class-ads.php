<?php
/**
 * Crons Ads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Crons;

use DateTimeImmutable;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Crons Ads.
 */
class Ads implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'advanced-ads-ad-pre-save', [ $this, 'save_expiration_date' ] );
		add_action( Constants::CRON_JOB_AD_EXPIRATION, [ $this, 'update_ad_status' ] );
	}

	/**
	 * Create CRON job and save into independent meta
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return void
	 */
	public function save_expiration_date( Ad $ad ): void {
		$args = [ 'post_id' => $ad->get_id() ];
		$next = wp_next_scheduled( Constants::CRON_JOB_AD_EXPIRATION, $args );

		if ( 0 === $ad->get_expiry_date() ) {
			delete_post_meta( $ad->get_id(), Constants::AD_META_EXPIRATION_TIME );
			wp_unschedule_event( $next, Constants::CRON_JOB_AD_EXPIRATION, $args );
			return;
		}

		if ( array_key_exists( 'expiry_date', $ad->get_changes() ) ) {
			wp_unschedule_event( $next, Constants::CRON_JOB_AD_EXPIRATION, $args );
		}

		$datetime = ( new DateTimeImmutable() )->setTimestamp( $ad->get_expiry_date() );
		update_post_meta( $ad->get_id(), Constants::AD_META_EXPIRATION_TIME, $datetime->format( 'Y-m-d H:i:s' ) );

		wp_schedule_single_event( $ad->get_expiry_date(), Constants::CRON_JOB_AD_EXPIRATION, $args );
	}

	/**
	 * Update post status to expired
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function update_ad_status( $post_id ): void {
		kses_remove_filters();
		wp_update_post(
			[
				'ID'          => $post_id,
				'post_status' => Constants::AD_STATUS_EXPIRED,
			]
		);
		kses_init_filters();
	}
}
