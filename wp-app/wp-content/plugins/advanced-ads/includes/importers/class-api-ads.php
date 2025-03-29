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
use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Modules\OneClick\Helpers;
use AdvancedAds\Interfaces\Importer as Interface_Importer;
use AdvancedAds\Modules\OneClick\Options;

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
			$this->rollback_preview();
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

		$config = Options::pubguru_config();
		unset( $config['page'], $config['method'], $config['history_key'] );
		Options::pubguru_config( $config );
	}

	/**
	 * Rollback Preview mode
	 *
	 * @return void
	 */
	private function rollback_preview(): void {
		$config = Options::pubguru_config();
		if ( ! $config || ! isset( $config['history_key'] ) ) {
			return;
		}

		parent::rollback( $config['history_key'] );

		// Remove keys.
		$importers = wp_advads()->importers ?? new Manager();
		$importers->delete_session_history( $config['history_key'] );

		unset( $config['history_key'] );
		Options::pubguru_config( $config );
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
		$this->save_history_key( $history_key );

		foreach ( $ads as $data ) {
			$ad = wp_advads_create_new_ad( 'plain' );
			$ad->set_title( '[PubGuru] Ad # ' . $data['ad_unit'] );
			$ad->set_status( 'publish' );
			$ad->set_content( sprintf( '<pubguru data-pg-ad="%s"></pubguru>', $data['ad_unit'] ) );
			$ad->set_author_id( $this->get_author_id() );
			$ad->set_prop( 'pghb_slot_id', $data['ad_unit'] );

			if ( 'all' !== $data['device'] ) {
				$ad->set_visitor_conditions(
					[
						[
							'type'  => 'mobile',
							'value' => [ $data['device'] ],
						],
					]
				);
			}

			$ad_id = $ad->save();

			if ( $ad_id > 0 ) {
				++$count;

				if ( ! wp_advads_has_placement_type( $data['placement'] ) ) {
					wp_advads_create_placement_type( $data['placement'] );
				}
				$placement = wp_advads_create_new_placement( $data['placement'] );
				$placement->set_title( '[PubGuru] Placement # ' . $data['ad_unit'] );
				$placement->set_item( 'ad_' . $ad_id );
				$placement->set_status( 'publish' );
				if ( ! empty( $data['placement_conditions'] ) ) {
					$placement->set_display_conditions( $data['placement_conditions'] );
				}

				if ( $placement->is_type( 'post_content' ) ) {
					$placement->set_prop( 'position', $data['in_content_position'] );
					$placement->set_prop( 'index', $data['in_content_count'] );
					$placement->set_prop( 'tag', $data['in_content_element'] );
					$placement->set_prop( 'repeat', boolval( $data['in_content_repeat'] ) );
				}

				$placement->save();

				$this->add_session_key( $ad, $placement, $history_key );
			}
		}

		$importers = wp_advads()->importers ?? new Manager();
		$importers->add_session_history( $this->get_id(), $history_key, $count );

		/* translators: 1: counts 2: Importer title */
		return sprintf( __( '%1$d ads migrated from %2$s', 'advanced-ads' ), $count, $this->get_title() );
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
		// Early bail!!
		if ( $this->is_preview_mode() ) {
			return;
		}

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
	}

	/**
	 * Normalize ads
	 *
	 * @param array $ads Ads from api.
	 *
	 * @return array
	 */
	public function normalize_ads( $ads ): array {
		$normalized = [];

		foreach ( $ads as $ad ) {
			if ( empty( $ad['slot'] ) ) {
				$ad['slot'] = explode( '/', $ad['id'] );
				$ad['slot'] = array_pop( $ad['slot'] );
			}

			// already created.
			if ( in_array( $ad['slot'], $this->slots, true ) ) {
				continue;
			}

			$advanced_placement = $ad['advanced_placement'] ?? [];
			$placement_type     = $this->map_placement_type( $advanced_placement['placement'] ?? $ad['slot'] );
			$in_content_rules   = $advanced_placement['inContentRule'] ?? [];

			if ( isset( $advanced_placement['pageType'] ) && ! empty( $advanced_placement['pageType'] ) ) {
				$advanced_placement['pageType'] = array_filter( $advanced_placement['pageType'] );
				$advanced_placement['pageType'] = array_keys( $advanced_placement['pageType'] );
			}

			$normalized_ad = [
				'ad_unit'              => $ad['slot'],
				'device'               => $ad['device'],
				'placement'            => $placement_type,
				'placement_conditions' => $this->parse_display_conditions( $advanced_placement['pageType'] ?? 'all' ),
			];

			if ( 'post_content' === $placement_type ) {
				$normalized_ad['in_content_position'] = $in_content_rules['position'] ?? 'before';
				$normalized_ad['in_content_count']    = $in_content_rules['positionCount'] ?? 1;
				$normalized_ad['in_content_repeat']   = $in_content_rules['positionRepeat'] ?? false;
				$normalized_ad['in_content_element']  = $this->map_element( $in_content_rules['positionElement'] ?? 'p' );
			}

			$normalized[] = $normalized_ad;
		}

		return $normalized;
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
			'leaderboard'   => 'post_top',
			'beforecontent' => 'post_top',
			'in_content_1'  => 'post_content',
			'in_content_2'  => 'post_content',
			'incontent'     => 'post_content',
			'sidebar'       => 'sidebar_widget',
			'header'        => 'header',
			'footer'        => 'footer',
			'aboveheadline' => 'post_above_headline',
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
	 * @param array|string $terms Dictionary term.
	 *
	 * @return array|null
	 */
	private function parse_display_conditions( $terms ) {
		// Return for debugging.
		if ( $this->is_preview_mode() ) {
			$config = Options::pubguru_config();
			return [
				[
					'type'     => 'postids',
					'operator' => 'is',
					'value'    => [ absint( $config['page'] ) ],
				],
			];
		}

		$conditions = [];
		$terms      = array_filter( (array) $terms );
		if ( count( $terms ) === 5 ) {
			$terms = [ 'all' ];
		}

		$hash = [
			'all'              => null,
			'homepage'         => [
				[
					'type'  => 'general',
					'value' => [ 'is_front_page' ],
				],
			],
			'post_pages'       => [
				[
					'type'  => 'general',
					'value' => [ 'is_singular' ],
				],
			],
			'pages'            => [
				[
					'type'  => 'general',
					'value' => [ 'is_singular' ],
				],
			],
			'posts'            => [
				[
					'type'  => 'general',
					'value' => [ 'is_singular' ],
				],
				[
					'type'     => 'posttypes',
					'operator' => 'is',
					'value'    => [ 'post' ],
				],
			],
			'category_pages'   => [
				[
					'type'  => 'general',
					'value' => [ 'is_archive' ],
				],
			],
			'categoryPages'    => [
				[
					'type'  => 'general',
					'value' => [ 'is_archive' ],
				],
			],
			'secondaryQueries' => [
				[
					'type'  => 'general',
					'value' => [ 'is_main_query' ],
				],
			],
		];

		foreach ( $terms as $term ) {
			if ( 'all' === $term ) {
				$conditions = [];
				break;
			}

			if ( $hash[ $term ] ) {
				$conditions = array_merge( $conditions, $hash[ $term ] );
			}
		}

		return $conditions;
	}

	/**
	 * Maps the element to a corresponding value.
	 *
	 * @param string $element The element to be mapped.
	 *
	 * @return string The mapped element value.
	 */
	private function map_element( $element ): string {
		$element = strtolower( $element );
		$hash    = [
			'paragraph'             => 'p',
			'paragraphWithoutImage' => 'pwithoutimg',
			'headline2'             => 'h2',
			'headline3'             => 'h3',
			'headline4'             => 'h4',
			'headlineAny'           => 'headlines',
			'image'                 => 'img',
			'table'                 => 'table',
			'listItem'              => 'li',
			'blockquote'            => 'blockquote',
			'iframe'                => 'iframe',
			'div'                   => 'div',
		];

		return $hash[ $element ] ?? 'p';
	}

	/**
	 * Checks if the current mode is preview mode.
	 *
	 * @return bool True if the current mode is preview mode, false otherwise.
	 */
	private function is_preview_mode(): bool {
		$config = Options::pubguru_config();
		if ( $config && 'page' === $config['method'] && absint( $config['page'] ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Save history key
	 *
	 * @param string $key Session key.
	 *
	 * @return void
	 */
	private function save_history_key( $key ): void {
		if ( ! $this->is_preview_mode() ) {
			return;
		}

		$config                = Options::pubguru_config();
		$config['history_key'] = $key;

		Options::pubguru_config( $config );
	}
}
