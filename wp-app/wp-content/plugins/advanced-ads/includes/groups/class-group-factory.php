<?php
/**
 * The group Factory.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Groups;

use WP_Term;
use Exception;
use AdvancedAds\Abstracts\Group;
use AdvancedAds\Abstracts\Factory;

defined( 'ABSPATH' ) || exit;

/**
 * Groups Factory.
 */
class Group_Factory extends Factory {

	/**
	 * Create an empty group object
	 *
	 * @param string $type Type of group to create.
	 *
	 * @return Group|bool Group object or false if the group type not found.
	 */
	public function create_group( $type = 'default' ) {
		$group_type = wp_advads_get_group_type( $type );

		if ( ! $group_type ) {
			return false;
		}

		$classname = $group_type->get_classname();

		// Create group.
		$group = new $classname( 0 );
		$group->set_type( $group_type->get_id() );

		return $group;
	}

	/**
	 * Get the group object.
	 *
	 * @param Group|WP_Term|int|bool $group_id Group instance, term instance or numeric.
	 * @param string                 $new_type Change type of group.
	 *
	 * @return Group|bool Group object or false if the group cannot be loaded.
	 */
	public function get_group( $group_id, $new_type = '' ) {
		$group_id = $this->get_group_id( $group_id );

		if ( ! $group_id ) {
			return false;
		}

		$group_type = '' !== $new_type ? $new_type : $this->get_group_type( $group_id );
		$classname  = $this->get_classname( wp_advads_get_group_type_manager(), $group_type );

		try {
			return new $classname( $group_id );
		} catch ( Exception $e ) {
			return false;
		}

		return new Group_Standard();
	}

	/**
	 * Get the type of the group.
	 *
	 * @param int $group_id Group ID.
	 *
	 * @return string The type of the group.
	 */
	private function get_group_type( $group_id ): string {
		// Allow the overriding of the lookup in this function. Return the group type here.
		$override = apply_filters( 'advanced-ads-group-type', false, $group_id );
		if ( $override ) {
			return $override;
		}

		$type = get_term_meta( $group_id, Group_Repository::TYPE_METAKEY, true );
		if ( empty( $type ) ) {
			$options = get_option( 'advads-ad-groups', [] );
			$type    = $options[ $group_id ]['type'] ?? 'default';
			update_term_meta( $group_id, Group_Repository::TYPE_METAKEY, $type );
		}

		return $type ?? 'default';
	}

	/**
	 * Get the group ID depending on what was passed.
	 *
	 * @param Group|WP_Term|int|bool $group Group instance, term instance or numeric.
	 *
	 * @return int|bool false on failure
	 */
	private function get_group_id( $group ) {
		if ( is_numeric( $group ) ) {
			return $group;
		}

		if ( is_a_group( $group ) ) {
			return $group->get_id();
		}

		if ( ! empty( $group->term_id ) ) {
			return $group->term_id;
		}

		return false;
	}
}
