<?php
/**
 * Auto Ads Creation from api.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use WP_Error;
use Advanced_Ads;
use AdvancedAds\Constants;
use Advanced_Ads_Placements;
use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Modules\OneClick\Helpers;
use AdvancedAds\Interfaces\Importer as Interface_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * Auto Ads Creation.
 */
class Api_Ads extends Importer implements Interface_Importer {

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
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'api_ads';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'Ads from API', 'advanced-ads' );
	}

	/**
	 * Get a description of the importer.
	 *
	 * @return string The description of the importer.
	 */
	public function get_description(): string {
		return __( 'For MonetizeMore clients using PubGuru, you will be able to create all of your new ads from api.', 'advanced-ads' );
	}

	/**
	 * Get the icon to this importer.
	 *
	 * @return string The icon for the importer.
	 */
	public function get_icon(): string {
		return '<span class="dashicons dashicons-media-spreadsheet"></span>';
	}

	/**
	 * Detect the importer in database.
	 *
	 * @return bool True if detected; otherwise, false.
	 */
	public function detect(): bool {
		return false;
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void {}

	/**
	 * Import data.
	 *
	 * @return WP_Error|string
	 */
	public function import() {

		kses_remove_filters();
		$this->fetch_created_slots();

		// Final import create ads.
		$ads = Helpers::get_ads_from_config();
		$ads = $this->normalize_ads( $ads );
		if ( $ads ) {
			return $this->create_ads( $ads );
		}
	}

	/**
	 * Rollback import
	 *
	 * @param string $key Session key.
	 *
	 * @return void
	 */
	public function rollback( $key ): void {
		parent::rollback( $key );
		$this->migrate_old_entities( $key, 'publish' );
	}

	/**
	 * Get ads from sheet by device
	 *
	 * @param array $ads Ads selected by user.
	 *
	 * @return string
	 */
	private function create_ads( $ads ): string {
		$count       = 0;
		$history_key = $this->get_id() . '_' . wp_rand() . '_' . count( $ads );
		$this->migrate_old_entities( $history_key, 'draft' );

		foreach ( $ads as $data ) {
			$ad_options = [
				'type' => 'plain',
			];

			if ( 'all' !== $data['device'] ) {
				$ad_options['visitors'] = [
					[
						'type'  => 'mobile',
						'value' => [ $data['device'] ],
					],
				];
			}

			$ad_id = wp_insert_post(
				[
					'post_title'   => '[Migrated from API] Ad # ' . $data['ad_unit'],
					'post_content' => sprintf( '<pubguru data-pg-ad="%s"></pubguru>', $data['ad_unit'] ),
					'post_status'  => 'publish',
					'post_type'    => Constants::POST_TYPE_AD,
					'post_author'  => $this->get_author_id(),
					'meta_input'   => [
						'pghb_slot_id'            => $data['ad_unit'],
						'advanced_ads_ad_options' => $ad_options,
					],
				]
			);

			if ( $ad_id > 0 ) {
				++$count;

				$placement = [
					'type'    => $data['placement'],
					'name'    => '[Migrated from API] Placement # ' . $ad_id,
					'item'    => 'ad_' . $ad_id,
					'options' => [],
				];

				if ( ! empty( $data['placement_conditions'] ) ) {
					$placement['options']['display'] = [ $data['placement_conditions'] ];
				}

				if ( 'post_content' === $data['placement'] ) {
					$placement['options']['position'] = $data['in_content_position'];
					$placement['options']['index']    = $data['in_content_count'];
					$placement['options']['tag']      = $data['in_content_element'];
					$placement['options']['repeat']   = boolval( $data['in_content_repeat'] );
				}

				Advanced_Ads_Placements::save_new_placement( $placement );
				update_post_meta( $ad_id, '_importer_session_key', $history_key );
			}
		}

		update_option( 'advanced-ads-importer-history', $history_key );

		/* translators: 1: counts 2: Importer title */
		return sprintf( __( '%1$d ads migrated from %2$s', 'advanced-ads' ), $count, $this->get_title() );
	}

	/**
	 * Maps the placement type to a corresponding value.
	 *
	 * This function takes a placement type as input and returns the corresponding value based on a predefined mapping.
	 *
	 * @param string $type The placement type to be mapped.
	 *
	 * @return string The mapped placement type value.
	 */
	private function map_placement_type( $type ): string {
		$type = strtolower( str_replace( ' ', '_', $type ) );
		$hash = [
			'leaderboard'  => 'post_top',
			'in_content_1' => 'post_content',
			'in_content_2' => 'post_content',
			'sidebar'      => 'sidebar_widget',
		];

		foreach ( $hash as $key => $value ) {
			if ( Str::contains( $type, $key ) ) {
				return $value;
			}
		}

		return 'post_content';
	}

	/**
	 * Parse display conditions
	 *
	 * @param string $term Dictionary term.
	 *
	 * @return array|null
	 */
	private function parse_display_conditions( $term ) {
		$term = str_replace( ' ', '_', strtolower( $term ) );
		if ( 'all' === $term ) {
			return null;
		}

		if ( 'homepage' === $term ) {
			return [
				'type'  => 'general',
				'value' => [ 'is_front_page' ],
			];
		}

		if ( 'post_pages' === $term ) {
			return [
				'type'  => 'general',
				'value' => [ 'is_singular' ],
			];
		}

		if ( 'category_pages' === $term ) {
			return [
				'type'  => 'general',
				'value' => [ 'is_archive' ],
			];
		}
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

	/**
	 * Fetch created slots from database.
	 *
	 * @return void
	 */
	private function fetch_created_slots(): void {
		global $wpdb;

		$this->slots = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'pghb_slot_id'
			)
		);
	}

	/**
	 * Migrate old entities
	 *
	 * @param string $key    Session key.
	 * @param string $status Status of ads.
	 *
	 * @return void
	 */
	private function migrate_old_entities( $key, $status ): void {
		$args = [
			'post_type'      => [ Constants::POST_TYPE_AD, Constants::POST_TYPE_PLACEMENT ],
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];

		if ( 'publish' === $status ) {
			$args['post_status'] = 'draft';
			$args['meta_query']  = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				[
					'key'   => '_importer_session_key',
					'value' => $key . '_draft',
				],
			];
		}

		$entities = get_posts( $args );

		foreach ( $entities as $entity ) {
			if ( 'draft' === $status ) {
				$entity->meta_input  = [
					'_importer_session_key' => $key . '_draft',
					'_importer_old_status'  => $entity->post_status,
				];
				$entity->post_status = 'draft';
			} elseif ( 'publish' === $status ) {
				$entity->post_status = $entity->_importer_old_status;
			}

			wp_update_post( $entity );
		}

		// Old placements rollback.
		if ( 'draft' === $status ) {
			$placements = Advanced_Ads::get_instance()->get_model()->get_ad_placements_array();
			update_option( 'advanced-ads-placements-backup', $placements );
			Advanced_Ads::get_instance()->get_model()->update_ad_placements_array( [] );
		} elseif ( 'publish' === $status ) {
			$placements = get_option( 'advanced-ads-placements-backup', [] );
			Advanced_Ads::get_instance()->get_model()->update_ad_placements_array( $placements );
		}
	}

	/**
	 * Normalize ads
	 *
	 * @param array $ads Ads from api.
	 *
	 * @return array
	 */
	private function normalize_ads( $ads ): array {
		$normalized = [];
		$in_content = [
			'in-content1' => [
				'count'    => 3,
				'position' => 'after',
				'repeat'   => false,
			],
			'in-content2' => [
				'count'    => 5,
				'position' => 'after',
				'repeat'   => false,
			],
			'in-content3' => [
				'count'    => 7,
				'position' => 'after',
				'repeat'   => true,
			],
		];

		foreach ( $ads as $ad ) {
			// already created.
			if ( in_array( $ad['slot'], $this->slots, true ) ) {
				continue;
			}

			foreach ( $in_content as $key => $value ) {
				$matches = [];
				preg_match( '/.*in[-_]?content[_-]?(\d+).*/', $ad['slot'], $matches );
				if ( ! empty( $matches ) && ( 'in-content' . $matches[1] ) === $key ) {
					if ( empty( $ad['in_content_count'] ) ) {
						$ad['in_content_count'] = $value['count'];
					}

					if ( empty( $ad['in_content_position'] ) ) {
						$ad['in_content_position'] = $value['position'];
					}

					if ( empty( $ad['in_content_repeat'] ) ) {
						$ad['in_content_repeat'] = $value['repeat'];
					}
					break;
				}
			}

			$normalized[] = [
				'ad_unit'              => $ad['slot'],
				'device'               => $ad['device'],
				'placement'            => $this->map_placement_type( $ad['slot'] ),
				'placement_conditions' => $this->parse_display_conditions( $data['placement_conditions'] ?? 'all' ),
				'in_content_position'  => $ad['in_content_position'] ?? 'before',
				'in_content_count'     => $ad['in_content_count'] ?? 1,
				'in_content_element'   => $ad['in_content_element'] ?? 'p',
				'in_content_repeat'    => $ad['in_content_repeat'] ?? false,
			];
		}

		return $normalized;
	}
}
