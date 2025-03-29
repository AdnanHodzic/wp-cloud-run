<?php
/**
 * Ad Repository.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads;

use WP_Query;
use Exception;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Admin\Metabox_Ad;
use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Framework\Utilities\Arr;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Utilities\Formatting;

defined( 'ABSPATH' ) || exit;

/**
 * Ad Repository.
 *
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.Found -- remove it later
 */
class Ad_Repository {

	/**
	 * Ad options metakey
	 *
	 * @var string
	 */
	const OPTION_METAKEY = 'advanced_ads_ad_options';

	/* CRUD Methods ------------------- */

	/**
	 * Create a new ad in the database.
	 *
	 * @param Ad $ad Ad object.
	 *
	 * @return Ad
	 */
	public function create( &$ad ): Ad {
		$id = wp_insert_post(
			apply_filters(
				'advanced-ads-new-ad-data',
				[
					'post_type'      => Constants::POST_TYPE_AD,
					'post_status'    => $ad->get_status() ? $ad->get_status() : 'publish',
					'post_author'    => ! empty( $ad->get_author_id() ) ? $ad->get_author_id() : get_current_user_id(),
					'post_title'     => $ad->get_title() ? $ad->get_title() : __( 'New Ad', 'advanced-ads' ),
					'post_content'   => $ad->get_content() ? $ad->get_content() : __( 'New ad content goes here', 'advanced-ads' ),
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				],
				$ad
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$ad->set_id( $id );

			$this->update_post_meta( $ad );
			$this->update_version( $ad );

			$ad->apply_changes();
		}

		return $ad;
	}

	/**
	 * Read an ad from the database.
	 *
	 * @param Ad $ad Ad object.
	 * @throws Exception If invalid ad.
	 *
	 * @return void
	 */
	public function read( &$ad ): void {
		$ad->set_defaults();
		$post_object = get_post( $ad->get_id() );

		if ( ! $ad->get_id() || ! $post_object || Constants::POST_TYPE_AD !== $post_object->post_type ) {
			throw new Exception( esc_html__( 'Invalid ad.', 'advanced-ads' ) );
		}

		$ad->set_props(
			[
				'title'     => $post_object->post_title,
				'status'    => $post_object->post_status,
				'slug'      => $post_object->post_name,
				'content'   => $post_object->post_content,
				'author_id' => $post_object->post_author,
			]
		);

		$this->read_ad_data( $ad );
		$ad->set_object_read( true );
	}

	/**
	 * Update an existing ad in the database.
	 *
	 * @param Ad $ad Ad object.
	 *
	 * @return void
	 */
	public function update( &$ad ): void {
		global $wpdb;

		$changes = $ad->get_changes();

		// Only update the post when the post data changes.
		if ( array_intersect( [ 'title', 'status', 'content' ], array_keys( $changes ) ) ) {
			$post_data = [
				'post_title'   => $ad->get_title( 'edit' ),
				'post_status'  => $ad->get_status( 'edit' ) ? $ad->get_status( 'edit' ) : 'publish',
				'post_type'    => Constants::POST_TYPE_AD,
				'post_content' => apply_filters(
					// TODO: useless filter.
					'advanced-ads-pre-ad-save-' . $ad->get_type(),
					wp_unslash(
						apply_filters(
							'content_save_pre',
							$ad->get_content( 'edit' )
						)
					)
				),
			];

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 *
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, [ 'ID' => $ad->get_id() ] );
				clean_post_cache( $ad->get_id() );
			} else {
				wp_update_post( array_merge( [ 'ID' => $ad->get_id() ], $post_data ) );
			}
		} else { // Only update post modified time to record this save event.
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->posts,
				[
					'post_modified'     => current_time( 'mysql' ),
					'post_modified_gmt' => current_time( 'mysql', 1 ),
				],
				[
					'ID' => $ad->get_id(),
				]
			);
			clean_post_cache( $ad->get_id() );
		}

		$this->update_post_meta( $ad );

		$ad->apply_changes();
	}

	/**
	 * Delete an ad from the database.
	 *
	 * @param Ad   $ad           Ad object or Ad id.
	 * @param bool $force_delete Whether to bypass Trash and force deletion. Default false.
	 *
	 * @return void
	 */
	public function delete( &$ad, $force_delete = false ): void {
		// Early bail!!
		if ( ! $ad || ! $ad->get_id() ) {
			return;
		}

		if ( $force_delete ) {
			wp_delete_post( $ad->get_id(), true );
			$ad->set_id( 0 );
		} else {
			wp_trash_post( $ad->get_id() );
			$ad->set_status( 'trash' );
		}
	}

	/* Finder Methods ------------------- */

	/**
	 * Get an ad by its ID.
	 *
	 * @param int $id The ID of the ad to retrieve.
	 *
	 * @return Ad|null
	 */
	public function get_ad_by_id( $id ) {
		return wp_advads_get_ad( $id );
	}

	/**
	 * Get ads belonging to a specific group.
	 *
	 * @param int $group_id The ID of the group.
	 *
	 * @return Ad[]
	 */
	public function get_ads_by_group_id( $group_id ): array {
		$group = wp_advads_get_group( $group_id );
		return $group->get_ads();
	}

	/**
	 * Get ads associated with a specific placement.
	 *
	 * @param int $placement_id The ID of the placement.
	 *
	 * @return array
	 */
	public function get_ads_by_placement_id( $placement_id ): array {
		$placement = wp_advads_get_placement( $placement_id );
		$item      = $placement->get_item_object();

		if ( is_a_group( $item ) ) {
			return $item->get_ads();
		}

		if ( is_an_ad( $item ) ) {
			return [ $item ];
		}

		return [];
	}

	/**
	 * Get ads of a specific type.
	 *
	 * @param string $type The type of ads to retrieve.
	 *
	 * @return array
	 */
	public function get_ads_by_type( $type ): array {
		return [];
	}

	/**
	 * Get all ads object.
	 *
	 * @return array
	 */
	public function get_all_ads(): array {
		static $advads_all_ads;

		if ( isset( $advads_all_ads ) ) {
			return $advads_all_ads;
		}

		$advads_all_ads = [];
		foreach ( $this->get_ads_dropdown() as $post_id => $name ) {
			$advads_all_ads[ $post_id ] = wp_advads_get_ad( $post_id );
		}

		return $advads_all_ads;
	}

	/**
	 * Get all ads as dropdown.
	 *
	 * @return array
	 */
	public function get_ads_dropdown(): array {
		$query = $this->query(
			[
				'orderby' => 'title',
				'order'   => 'ASC',
			],
			true
		);

		return $query->have_posts() ? wp_list_pluck( $query->posts, 'post_title', 'ID' ) : [];
	}

	/**
	 * Query ads based on the provided arguments.
	 *
	 * @param array $args          The arguments to customize the query.
	 * @param bool  $improve_query Whether to improve the query speed.
	 *
	 * @return WP_Query The WP_Query object containing the results of the query.
	 */
	public function query( $args, $improve_query = false ): WP_Query {
		$args = wp_parse_args(
			$args,
			[
				'posts_per_page' => -1,
				'post_status'    => [ 'publish', 'future', 'draft' ],
			]
		);

		// Strict mode.
		$args['post_type'] = Constants::POST_TYPE_AD;

		if ( $improve_query ) {
			$args = WordPress::improve_wp_query( $args );
		}

		return new WP_Query( $args );
	}

	/* Additional Methods ------------------- */

	/**
	 * Read ad data. Can be overridden by child classes to load other props.
	 *
	 * @param Ad $ad Ad object.
	 *
	 * @return void
	 */
	private function read_ad_data( &$ad ): void {
		$post_meta_values = get_post_meta( $ad->get_id(), self::OPTION_METAKEY, true );

		if ( empty( $post_meta_values ) || ! is_array( $post_meta_values ) ) {
			$post_meta_values = [];
		}

		$post_meta_values   = $this->migrate_values( $post_meta_values );
		$display_conditions = $post_meta_values['conditions'] ?? [];
		$visitor_conditions = $post_meta_values['visitors'] ?? [];

		if ( ! Arr::accessible( $display_conditions ) ) {
			$display_conditions = [];
		}

		if ( ! Arr::accessible( $visitor_conditions ) ) {
			$visitor_conditions = [];
		}

		$ad->set_props( $post_meta_values );
		$ad->set_props(
			[
				'display_conditions' => $display_conditions,
				'visitor_conditions' => $visitor_conditions,
				'has_weekdays'       => $post_meta_values['weekdays']['enabled'] ?? false,
				'weekdays'           => $post_meta_values['weekdays']['day_indexes'] ?? [],
			]
		);
	}

	/**
	 * Update ad data. Can be overridden by child classes to load other props.
	 *
	 * @param Ad $ad Ad object.
	 *
	 * @return void
	 */
	private function update_post_meta( &$ad ): void {
		$post_data = Metabox_Ad::get_post_data();
		$ad->set_prop( 'last_save_version', ADVADS_VERSION );

		// Pre save.
		if ( method_exists( $ad, 'pre_save' ) ) {
			$ad->pre_save( $post_data );
		}

		// Filters to manipulate options or add more to be saved.
		do_action( 'advanced-ads-ad-pre-save', $ad, $post_data );

		$meta_keys = $ad->get_data_keys();
		$meta_keys = array_combine( $meta_keys, $meta_keys );

		$meta_values = [];
		foreach ( $meta_keys as $meta_key => $prop ) {
			$value = method_exists( $ad, "get_$prop" )
				? $ad->{"get_$prop"}( 'edit' )
				: $ad->get_prop( $prop, 'edit' );

			$value = is_string( $value ) ? wp_slash( $value ) : $value;

			switch ( $prop ) {
				case 'clearfix':
				case 'allow_php':
				case 'has_weekdays':
				case 'reserve_space':
				case 'allow_shortcodes':
					$value = Formatting::bool_to_string( $value );
					break;
				case 'description':
					$value = esc_textarea( $value );
					break;
				case 'display_conditions':
				case 'visitor_conditions':
					$value = WordPress::sanitize_conditions( $value );
					if (
						'editpost' === Params::post( 'originalaction' ) &&
						! isset( $post_data[ $meta_key ] )
					) {
						$value = [];
					}
					break;
			}

			$meta_values[ $meta_key ] = $value;
		}

		// Convert values to array.
		$meta_values['weekdays'] = [
			'enabled'     => $meta_values['has_weekdays'],
			'day_indexes' => $meta_values['weekdays'],
		];
		unset( $meta_values['has_weekdays'] );

		update_post_meta( $ad->get_id(), self::OPTION_METAKEY, $meta_values );
	}

	/**
	 * Make sure we store the ad version (to track data changes).
	 *
	 * @param Ad $ad Ad object.
	 *
	 * @return void
	 */
	private function update_version( &$ad ): void {
		if ( ! metadata_exists( 'post', $ad->get_id(), '_ad_version' ) ) {
			update_post_meta( $ad->get_id(), '_ad_version', ADVADS_VERSION );
		}
	}

	/**
	 * Migrate values to new version
	 *
	 * @param array $values Values to migrate.
	 *
	 * @return array
	 */
	private function migrate_values( $values ): array {
		$output = wp_parse_args(
			$values['output'] ?? [],
			[
				'position'          => 'none',
				'clearfix'          => false,
				'add_wrapper_sizes' => false,
				'margin'            => [
					'top'    => 0,
					'left'   => 0,
					'bottom' => 0,
					'right'  => 0,
				],
			]
		);

		foreach ( $output as $key => $value ) {
			if ( isset( $values[ $key ] ) ) {
				continue;
			}

			$values[ $key ] = $value;
		}

		$values['reserve_space'] = $values['reserve_space'] ?? $output['add_wrapper_sizes'];

		// Typecast the margin values.
		$values['margin'] = array_map( 'intval', $values['margin'] );

		// Old values are left, center and right, if none of these we've already migrated.
		if ( ! in_array( $values['position'], [ 'left', 'center', 'right' ], true ) ) {
			// Ensure we get an array with min two elements.
			$position = explode( '_', $values['position'] . '_' );

			// Explicitly set clearfix option.
			$values['clearfix'] = 'center' !== $position[0] && 'nofloat' === $position[1];
		} elseif ( 'center' === $values['position'] ) {
			$values['position'] = 'center_nofloat';
		} else {
			$values['position'] .= $values['clearfix'] ? '_nofloat' : '_float';
		}

		if ( isset( $values['visitor'] ) && ! isset( $values['visitors'] ) ) {
			$values['visitors'] = $values['visitor'];
		}

		unset( $values['visitor'], $values['output'] );

		return $values;
	}
}
