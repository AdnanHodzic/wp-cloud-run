<?php
/**
 * Placement CRUD Helpers
 *
 * @since 2.0.0
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * Content:
 *   1. Template
 *   2. Repositories functions
 *   3. CRUD functions
 *   4. Conditional functions
 *   5. Getter functions
 *   6. Finder functions
 */

use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Placement;
use AdvancedAds\Interfaces\Placement_Type;
use AdvancedAds\Placements\Placement_Types;
use AdvancedAds\Placements\Placement_Factory;
use AdvancedAds\Placements\Placement_Repository;

/* 1. Template ------------------- */

/**
 * Get the placement object.
 *
 * @param Placement|WP_Post|int|bool $placement_id Placement instance, post instance, numeric or false to use global $post.
 * @param string                     $new_type     Change type of placement.
 * @param array                      $args         Additional arguments.
 *
 * @return string|mixed placement output string or an entity.
 */
function get_the_placement( $placement_id = 0, $new_type = '', $args = [] ) {
	if ( ! \Advanced_Ads::get_instance()->can_display_ads() ) {
		return '';
	}
	if ( defined( 'ADVANCED_ADS_DISABLE_CHANGE' ) && ADVANCED_ADS_DISABLE_CHANGE ) {
		$args = [];
	}
	// Early bail!!
	// TODO: remove later once we move to new hooks.
	if ( isset( $args['override'] ) ) {
		return $args['override'];
	}

	$placement = is_a( $placement_id, Placement::class ) ? $placement_id : wp_advads_get_placement( $placement_id, $new_type );
	if ( ! $placement || 0 === $placement->get_id() ) {
		return '';
	}

	$args = wp_advads_default_entity_arguments( 'placement', $placement->get_id(), $args );
	wp_advads_set_additional_args( $placement, $args );

	if ( isset( $args['override'] ) ) {
		return $args['override'];
	}

	return $placement->output();
}

/**
 * Return content of an ad placement
 *
 * @since 1.1.0
 *
 * @param Placement|WP_Post|int|bool $placement_id Placement instance, post instance, numeric or false to use global $post.
 * @param array                      $args         Additional arguments.
 *
 * @return void
 */
function the_ad_placement( $placement_id = '', $args = [] ): void {
	echo get_the_placement( $placement_id, '', $args ); // phpcs:ignore
}

/* 2. Repositories ------------------- */

/**
 * Get Placement Factory
 *
 * @return Placement_Factory
 */
function wp_advads_get_placement_factory(): Placement_Factory {
	return wp_advads()->placements->factory;
}

/**
 * Get Placement Repository
 *
 * @return Placement_Repository
 */
function wp_advads_get_placement_repository(): Placement_Repository {
	return wp_advads()->placements->repository;
}

/**
 * Get Placement Types
 *
 * @return Placement_Types
 */
function wp_advads_get_placement_type_manager(): Placement_Types {
	return wp_advads()->placements->types;
}

/* 3. CRUD ------------------- */

/**
 * Create an empty placement object
 *
 * @param string $type Type of placement to create.
 *
 * @return Placement|bool Placement object or false if the placement type not found.
 */
function wp_advads_create_new_placement( $type = 'default' ) {
	return wp_advads_get_placement_factory()->create_placement( $type );
}

/**
 * Delete an placement from the database.
 *
 * @param int|Placement $placement    Placement object or id.
 * @param bool          $force_delete Whether to bypass Trash and force deletion. Default false.
 *
 * @return void
 */
function wp_advads_delete_placement( &$placement, $force_delete = false ): void {
	if ( ! $placement instanceof Placement ) {
		$placement = wp_advads_get_placement( $placement );
	}

	$placement->delete( $force_delete );
}

/**
 * Create missing placement type.
 *
 * @param string $type Missing type.
 *
 * @return void
 */
function wp_advads_create_placement_type( $type ): void {
	wp_advads_get_placement_type_manager()->create_missing( $type );
}

/**
 * Register custom placement type.
 *
 * @param string $classname Type class name.
 *
 * @return void
 */
function wp_advads_register_placement_type( $classname ): void {
	wp_advads_get_placement_type_manager()->register_type( $classname );
}

/* 4. Conditional ------------------- */

/**
 * Has placement type.
 *
 * @param string $type Type to check.
 *
 * @return bool
 */
function wp_advads_has_placement_type( $type ): bool {
	return wp_advads_get_placement_type_manager()->has_type( $type );
}

/**
 * Checks whether the given variable is a placement.
 *
 * @param mixed $thing The variable to check.
 *
 * @return bool
 */
function is_a_placement( $thing ): bool {
	return $thing instanceof Placement;
}

/* 5. Getter ------------------- */

/**
 * Get array of placements.
 *
 * @return Placement[]
 */
function wp_advads_get_placements(): array {
	$placements = is_admin()
		? wp_advads_get_all_placements()
		: wp_advads_get_published_placements();

	return $placements;
}

/**
 * Get array of all placements.
 *
 * @return Placement[]
 */
function wp_advads_get_all_placements(): array {
	return wp_advads_get_placement_repository()->get_all_placements();
}

/**
 * Get array of all published placements.
 *
 * @return Placement[]
 */
function wp_advads_get_published_placements(): array {
	return wp_advads_get_placement_repository()->get_all_published();
}

/**
 * Get all placement as ID => Post Title pair.
 *
 * @return array<int, string>
 */
function wp_advads_get_placements_dropdown(): array {
	return wp_advads_get_placement_repository()->get_placements_dropdown();
}

/**
 * Get the registered placement type.
 *
 * @param string $type Type to get.
 *
 * @return Placement_Type|bool
 */
function wp_advads_get_placement_type( $type ) {
	return wp_advads_get_placement_type_manager()->get_type( $type );
}

/**
 * Get the registered placement types.
 *
 * @param bool $with_unknown Include unknown type placements.
 *
 * @return Placement_Type[]
 */
function wp_advads_get_placement_types( $with_unknown = true ): array {
	return wp_advads_get_placement_type_manager()->get_types( $with_unknown );
}

/* 6. Finder ------------------- */

/**
 * Get the placement object.
 *
 * @param Placement|WP_Post|int|bool $placement_id Placement instance, post instance, numeric or false to use global $post.
 * @param string                     $new_type     Change type of placement.
 *
 * @return Placement|bool Placement object or false if the placement cannot be loaded.
 */
function wp_advads_get_placement( $placement_id = false, $new_type = '' ) {
	$int_placement_id = intval( $placement_id );

	if ( is_int( $placement_id ) && $int_placement_id < 0 ) {
		return false;
	}

	return is_int( $placement_id ) && $int_placement_id > 0
		? wp_advads_get_placement_by_id( $int_placement_id, $new_type )
		: wp_advads_get_placement_by_slug( $placement_id, $new_type );
}

/**
 * Get an placement by ID.
 *
 * @param int    $id       The ID of the placement to retrieve.
 * @param string $new_type Change type of placement.
 *
 * @return Placement|bool
 */
function wp_advads_get_placement_by_id( $id, $new_type = '' ) {
	return wp_advads_get_placement_factory()->get_placement( $id, $new_type );
}

/**
 * Get an placement by slug.
 *
 * @param string $slug     The slug the placement to retrieve.
 * @param string $new_type Change type of placement.
 *
 * @return Placement|bool
 */
function wp_advads_get_placement_by_slug( $slug, $new_type = '' ) {
	global $wpdb;

	$post_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_name = %s and post_type = %s",
			$slug,
			Constants::POST_TYPE_PLACEMENT
		)
	);

	if ( $post_id ) {
		return wp_advads_get_placement_by_id( absint( $post_id ), $new_type );
	}

	return false;
}

/**
 * Find placement by item id.
 *
 * Example: Find all placements that are connected to a specific ad.
 *          In this case $item_id = "ad_1234"
 *
 * Example: Find all placements that are connected to a specific group.
 *          In this case $item_id = "group_1234"
 *
 * @param string $item_id Item id to search for.
 *
 * @return array
 */
function wp_advads_placements_by_item_id( $item_id ): array {
	return wp_advads_get_placement_repository()->find_by_item_id( $item_id );
}

/**
 * Retrieves placements by type.
 *
 * This method queries the database to retrieve placements based on their type.
 *
 * @param string|array $types  Placement types to query.
 * @param string       $output The required return type. One of OBJECT or ids,
 *                             which correspond to an Placement object or an array containing post ids respectively.
 *
 * @return array An associative array of placement IDs as keys and their corresponding placement objects as values.
 */
function wp_advads_get_placements_by_types( $types, $output = OBJECT ): array {
	return wp_advads_get_placement_repository()->find_by_types( $types, $output );
}
