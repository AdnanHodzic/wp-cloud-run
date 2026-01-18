<?php
/**
 * Group Repository.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Groups;

use Exception;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Group;
use AdvancedAds\Framework\Utilities\Formatting;

defined( 'ABSPATH' ) || exit;

/**
 * Group Repository.
 */
class Group_Repository {

	/**
	 * Group options metakey
	 *
	 * @var string
	 */
	const OPTION_METAKEY = 'advanced_ads_group_options';

	/**
	 * Group type metakey
	 *
	 * @var string
	 */
	const TYPE_METAKEY = '_advads_group_type';

	/* CRUD Methods ------------------- */

	/**
	 * Create a new group in the database.
	 *
	 * @param Group $group Group object.
	 *
	 * @return Group
	 */
	public function create( &$group ): Group {
		apply_filters( 'advanced-ads-group-pre-save', $group );

		$ids = wp_insert_term(
			$group->get_title(),
			Constants::TAXONOMY_GROUP,
			[
				'description' => $group->get_content(),
				'slug'        => $group->get_slug(),
			]
		);

		if ( $ids && ! is_wp_error( $ids ) ) {
			$group->set_id( $ids['term_id'] );
			$this->update_term_meta( $group );
			$group->apply_changes();
		}

		return $group;
	}

	/**
	 * Read an group from the database.
	 *
	 * @param Group $group Group object.
	 * @throws Exception If invalid group.
	 *
	 * @return void
	 */
	public function read( &$group ): void {
		$group->set_defaults();
		$term_object = get_term( $group->get_id(), Constants::TAXONOMY_GROUP );

		if ( null === $term_object || is_wp_error( $term_object ) ) {
			throw new Exception( esc_html__( 'Invalid group.', 'advanced-ads' ) );
		}

		$group->set_title( $term_object->name );
		$group->set_slug( $term_object->slug );
		$group->set_content( $term_object->description );

		$this->read_group_data( $group );
		$group->set_object_read( true );
	}

	/**
	 * Update an existing group in the database.
	 *
	 * @param Group $group Group object.
	 *
	 * @return void
	 */
	public function update( &$group ): void {
		apply_filters( 'advanced-ads-group-pre-save', $group );

		$changed = array_keys( $group->get_changes() );

		// Only update term when the term data changes.
		if ( in_array( 'name', $changed, true ) ) {
			wp_update_term( $group->get_id(), Constants::TAXONOMY_GROUP, [ 'name' => $group->get_name( 'edit' ) ] );
		}

		if ( in_array( 'title', $changed, true ) ) {
			wp_update_term( $group->get_id(), Constants::TAXONOMY_GROUP, [ 'name' => $group->get_title( 'edit' ) ] );
		}

		// Only update weights when there is a change.
		if ( in_array( 'ad_weights', $changed, true ) ) {
			( new Group_Ad_Relation() )->relate( $group );
		}

		$this->update_term_meta( $group );
		$group->apply_changes();
	}

	/**
	 * Delete an group from the database.
	 *
	 * @param Group $group Group object or Id.
	 *
	 * @return void
	 */
	public function delete( &$group ): void {
		// Early bail!!
		if ( ! $group || ! $group->get_id() ) {
			return;
		}

		wp_delete_term( $group->get_id(), Constants::TAXONOMY_GROUP );
		$this->update_old_storage( $group->get_id() );

		$group->set_id( 0 );
		$group->set_status( 'trash' );
	}

	/* Finder Methods ------------------- */

	/**
	 * Get all groups object.
	 *
	 * @return Group[]
	 */
	public function get_all_groups(): array {
		static $advads_all_groups;

		if ( isset( $advads_all_groups ) ) {
			return $advads_all_groups;
		}

		$advads_all_groups = [];
		foreach ( $this->get_groups_dropdown() as $term_id => $name ) {
			$advads_all_groups[ $term_id ] = wp_advads_get_group( $term_id );
		}

		return $advads_all_groups;
	}

	/**
	 * Get all group as dropdown.
	 *
	 * @return array
	 */
	public function get_groups_dropdown(): array {
		$terms = get_terms(
			[
				'taxonomy'               => Constants::TAXONOMY_GROUP,
				'hide_empty'             => false,
				'number'                 => 0,
				'orderby'                => 'name',
				'update_term_meta_cache' => false,
			]
		);

		return ! empty( $terms ) && ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'name', 'term_id' ) : [];
	}

	/**
	 * Get groups associated with a given ad id.
	 *
	 * @param int $ad_id The ID of the ad.
	 *
	 * @return Group[] Groups array
	 */
	public function get_groups_by_ad_id( $ad_id ) {
		$terms = $ad_id ? wp_get_object_terms( $ad_id, Constants::TAXONOMY_GROUP ) : false;

		// Early bail!!
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return [];
		}

		$groups = [];

		foreach ( $terms as $group_id ) {
			$groups[] = wp_advads_get_group( $group_id );
		}

		return $groups;
	}

	/* Additional Methods ------------------- */

	/**
	 * Read group data. Can be overridden by child classes to load other props.
	 *
	 * @param Group $group Group object.
	 *
	 * @return void
	 */
	private function read_group_data( &$group ): void {
		$type          = get_term_meta( $group->get_id(), self::TYPE_METAKEY, true );
		$meta_values   = get_term_meta( $group->get_id(), self::OPTION_METAKEY, true );
		$publish_date  = get_term_meta( $group->get_id(), 'publish_date', true );
		$modified_date = get_term_meta( $group->get_id(), 'modified_date', true );

		if ( empty( $meta_values ) ) {
			$meta_values = $this->migrate_values( $group );
			$type        = $meta_values['type'] ?? $type;
		}

		if ( 'ordered' === $type || 'default' === $type ) {
			$type = 'refresh';
		}

		if ( isset( $meta_values['options'], $meta_values['options'][ $type ] ) ) {
			$meta_values = array_merge( $meta_values['options'][ $type ], $meta_values );
		}

		$meta_values['publish_date']  = $publish_date ?? '';
		$meta_values['modified_date'] = $modified_date ?? '';

		$group->set_props( $meta_values );

		foreach ( [ 'random', 'enabled' ] as $prop ) {
			if ( array_key_exists( $prop, $meta_values ) ) {
				$value = $meta_values[ $prop ];
				$value = Formatting::string_to_bool( $value );
				$group->set_prop( $prop, $value );
			}
		}
	}

	/**
	 * Update group data. Can be overridden by child classes to load other props.
	 *
	 * @param Group $group Group object.
	 *
	 * @return void
	 */
	private function update_term_meta( &$group ): void {
		$current_date = current_time( 'mysql', true );
		$meta_values  = [
			'type'       => $group->get_type(),
			'ad_count'   => $group->get_ad_count(),
			'options'    => $group->get_options(),
			'ad_weights' => $group->get_ad_weights(),
		];

		update_term_meta( $group->get_id(), self::TYPE_METAKEY, $group->get_type() );
		update_term_meta( $group->get_id(), self::OPTION_METAKEY, $meta_values );

		update_term_meta( $group->get_id(), 'modified_date', $current_date );
		if ( empty( $group->get_publish_date() ) ) {
			update_term_meta( $group->get_id(), 'publish_date', $current_date );
		}
	}

	/**
	 * Migrate values to new version
	 *
	 * @param Group $group Group object.
	 *
	 * @return array
	 */
	private function migrate_values( $group ): array {
		$values = [];

		$all_groups = get_option( 'advads-ad-groups', [] );
		$ad_weights = get_option( 'advads-ad-weights', [] );

		if ( isset( $all_groups[ $group->get_id() ] ) && is_array( $all_groups[ $group->get_id() ] ) ) {
			$values = $all_groups[ $group->get_id() ];
		}

		if ( isset( $ad_weights[ $group->get_id() ] ) && is_array( $ad_weights[ $group->get_id() ] ) ) {
			$values['ad_weights'] = $ad_weights[ $group->get_id() ];
		}

		return $values;
	}

	/**
	 * Update old storage.
	 *
	 * TODO: Remove it later
	 *
	 * @param int $id Group ID.
	 *
	 * @return void
	 */
	private function update_old_storage( $id ): void {
		$all_groups  = get_option( 'advads-ad-groups', [] );
		$all_weights = get_option( 'advads-ad-weights', [] );

		if ( $all_groups && isset( $all_groups[ $id ] ) ) {
			unset( $all_groups[ $id ] );
			update_option( 'advads-ad-groups', $all_groups );
		}

		if ( $all_weights && isset( $all_weights[ $id ] ) ) {
			unset( $all_weights[ $id ] );
			update_option( 'advads-ad-weights', $all_weights );
		}
	}
}
