<?php
/**
 * Ad CRUD Helpers
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

use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Ads\Ad_Types;
use AdvancedAds\Ads\Ad_Factory;
use AdvancedAds\Ads\Ad_Repository;
use AdvancedAds\Interfaces\Ad_Type;

/** 1. Template --------------- */

/**
 * Get the ad object.
 *
 * @param Ad|WP_Post|int|bool $ad_id    Ad instance, post instance, numeric or false to use global $post.
 * @param string              $new_type Change type of ad.
 * @param array               $args     Additional arguments.
 *
 * @return string|mixed The ad content or whatever entity or string that is overriding the return value.
 */
function get_the_ad( $ad_id = 0, $new_type = '', $args = [] ) {
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

	$ad = is_an_ad( $ad_id ) ? $ad_id : wp_advads_get_ad( $ad_id, $new_type );
	if ( ! $ad || 0 === $ad->get_id() ) {
		return '';
	}

	$args = wp_advads_default_entity_arguments( 'id', $ad->get_id(), $args );
	wp_advads_set_additional_args( $ad, $args );

	$override = apply_filters( 'advanced-ads-ad-select-override-by-ad', false, $ad, $args );

	if ( false !== $override ) {
		// Return an ad object.
		return $override;
	}

	return $ad->output();
}

/**
 * Echo an ad
 *
 * @since 1.0.0
 *
 * @param int   $id   Id of the ad (post).
 * @param array $args Additional arguments.
 *
 * @return void
 */
function the_ad( $id = 0, $args = [] ): void {
	echo get_the_ad( $id, '', $args ); // phpcs:ignore
}

/* 2. Repositories ------------------- */

/**
 * Get Ad Factory
 *
 * @return Ad_Factory
 */
function wp_advads_get_ad_factory(): Ad_Factory {
	return wp_advads()->ads->factory;
}

/**
 * Get Ad Repository
 *
 * @return Ad_Repository
 */
function wp_advads_get_ad_repository(): Ad_Repository {
	return wp_advads()->ads->repository;
}

/**
 * Get Ad Types
 *
 * @return Ad_Types
 */
function wp_advads_get_ad_type_manager(): Ad_Types {
	return wp_advads()->ads->types;
}

/* 3. CRUD ------------------- */

/**
 * Create an empty ad object
 *
 * @param string $type Type of ad to create.
 *
 * @return Ad|bool Ad object or false if the ad type not found.
 */
function wp_advads_create_new_ad( $type = 'dummy' ) {
	return wp_advads_get_ad_factory()->create_ad( $type );
}

/**
 * Delete an ad from the database.
 *
 * @param int|Ad $ad           Ad object or Id.
 * @param bool   $force_delete Whether to bypass Trash and force deletion. Default false.
 *
 * @return void
 */
function wp_advads_delete_ad( &$ad, $force_delete = false ): void {
	if ( ! $ad instanceof Ad ) {
		$ad = wp_advads_get_ad( $ad );
	}

	$ad->delete( $force_delete );
}

/**
 * Create missing ad type.
 *
 * @param string $type Missing type.
 *
 * @return void
 */
function wp_advads_create_ad_type( $type ): void {
	wp_advads_get_ad_type_manager()->create_missing( $type );
}

/**
 * Register custom ad type.
 *
 * @param string $classname Type class name.
 *
 * @return void
 */
function wp_advads_register_ad_type( $classname ): void {
	wp_advads_get_ad_type_manager()->register_type( $classname );
}

/* 4. Conditional ------------------- */

/**
 * Has ad type.
 *
 * @param string $type Type to check.
 *
 * @return bool
 */
function wp_advads_has_ad_type( $type ): bool {
	return wp_advads_get_ad_type_manager()->has_type( $type );
}

/**
 * Checks whether the given variable is an ad.
 *
 * @param mixed $thing The variable to check.
 *
 * @return bool
 */
function is_an_ad( $thing ): bool {
	return $thing instanceof Ad;
}

/* 5. Getter ------------------- */

/**
 * Get array of ads.
 *
 * @return Ad[]
 */
function wp_advads_get_all_ads(): array {
	return wp_advads_get_ad_repository()->get_all_ads();
}

/**
 * Get all ads.
 *
 * @return array
 */
function wp_advads_get_ads_dropdown(): array {
	return wp_advads_get_ad_repository()->get_ads_dropdown();
}

/**
 * Get the registered ad type.
 *
 * @param string $type Type to get.
 *
 * @return Ad_Type|bool
 */
function wp_advads_get_ad_type( $type ) {
	return wp_advads_get_ad_type_manager()->get_type( $type );
}

/**
 * Get the registered ad types.
 *
 * @return Ad_Type[]
 */
function wp_advads_get_ad_types(): array {
	return wp_advads_get_ad_type_manager()->get_types();
}

/* 6. Finder ------------------- */

/**
 * Get the ad object.
 *
 * @param Ad|WP_Post|int|bool $ad_id    Ad instance, post instance, numeric or false to use global $post.
 * @param string              $new_type Change type of ad.
 *
 * @return Ad|bool Ad object or false if the ad cannot be loaded.
 */
function wp_advads_get_ad( $ad_id = false, $new_type = '' ) {
	return wp_advads_get_ad_factory()->get_ad( $ad_id, $new_type );
}

/**
 * Get ads belonging to a specific group.
 *
 * @param int $group_id The ID of the group.
 *
 * @return Ad[]
 */
function wp_advads_get_ads_by_group_id( $group_id ): array {
	return wp_advads_get_ad_repository()->get_ads_by_group_id( $group_id );
}

/**
 * Get ads associated with a specific placement.
 *
 * @param int $placement_id The ID of the placement.
 *
 * @return array
 */
function wp_advads_get_ads_by_placement_id( $placement_id ): array {
	return wp_advads_get_ad_repository()->get_ads_by_placement_id( $placement_id );
}

/**
 * Get ads of a specific type.
 *
 * @param string $type The type of ads to retrieve.
 *
 * @return array
 */
function wp_advads_get_ads_by_type( $type ): array {
	return wp_advads_get_ad_repository()->get_ads_by_type( $type );
}

/**
 * Query ads based on the provided arguments.
 *
 * @param array $args          The arguments to customize the query.
 * @param bool  $improve_query Whether to improve the query speed.
 *
 * @return WP_Query The WP_Query object containing the results of the query.
 */
function wp_advads_ad_query( $args = [], $improve_query = false ): WP_Query {
	return wp_advads_get_ad_repository()->query( $args, $improve_query );
}
