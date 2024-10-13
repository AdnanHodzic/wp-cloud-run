<?php
/**
 * Auto Ad Creation using PGHB config.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick;

use Advanced_Ads;
use AdvancedAds\Entities;

defined( 'ABSPATH' ) || exit;

/**
 * Create ads from PGHB config.
 */
class Auto_Ads {

	/**
	 * CRON job gook
	 *
	 * @var string
	 */
	const CRON_HOOK = 'advanced-ads-pghb-auto-ad-creation';

	/**
	 * Author id
	 *
	 * @var int
	 */
	private $author_id = null;

	/**
	 * Hold slot ids from database.
	 *
	 * @var array
	 */
	private $slots = [];

	/**
	 * Execute the job
	 *
	 * @param array $ads Hold all the ads.
	 *
	 * @return void
	 */
	public function run( $ads ): void {
		kses_remove_filters();
		$this->fetch_created_slots();
		array_map( [ $this, 'create_ad' ], $ads );
	}

	/**
	 * Fetch created slots from database.
	 *
	 * @return void
	 */
	private function fetch_created_slots(): void {
		global $wpdb;

		$this->slots = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'pghb_slot_id'
			)
		);
	}

	/**
	 * Create an ad
	 *
	 * @param array $ad Hold ad info.
	 *
	 * @return void
	 */
	protected function create_ad( $ad ): void {
		// Early bail!!
		if ( empty( $ad['slot'] ) || in_array( $ad['slot'], $this->slots, true ) ) {
			return;
		}

		$options = [
			'type' => 'plain',
		];

		if ( 'all' !== $ad['device'] ) {
			$options['visitors'] = [
				[
					'type'  => 'mobile',
					'value' => [ $ad['device'] ],
				],
			];
		}

		if ( ! empty( $ad['sizes'] ) && ! empty( $ad['sizes'][0] ) ) {
			$options['width']  = $ad['sizes'][0][0];
			$options['height'] = $ad['sizes'][0][1];
		}

		wp_insert_post(
			[
				'post_title'   => $ad['slot'],
				'post_content' => sprintf( '<pubguru id="%s"></pubguru>', $ad['slot'] ),
				'post_status'  => 'publish',
				'post_type'    => Entities::POST_TYPE_AD,
				'post_author'  => $this->get_author_id(),
				'meta_input'   => [
					'pghb_slot_id'            => $ad['slot'],
					'advanced_ads_ad_options' => $options,
				],
			]
		);
	}

	/**
	 * Get author id
	 *
	 * @return int
	 */
	private function get_author_id(): int {
		if ( null !== $this->author_id ) {
			return $this->author_id;
		}

		$users = get_users(
			[
				'role'   => 'Administrator',
				'number' => 1,
			]
		);

		$this->author_id = isset( $users[0] ) ? $users[0]->ID : 0;

		return $this->author_id;
	}
}
