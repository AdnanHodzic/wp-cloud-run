<?php
/**
 * Update routine
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0.0
 */

use AdvancedAds\Constants;

/**
 * Check for existing placements in posts.
 *
 * @param array $slugs Slugs to check.
 *
 * @return array
 */
function advads_get_existing_placement_posts( $slugs ): array {
	global $wpdb;

	foreach ( $slugs as &$slug ) {
		$slug = sanitize_title_for_query( $slug );
		$slug = str_replace( '_', '-', $slug );
		$slug = esc_sql( $slug );
	}

	$in_string = "'" . implode( "','", $slugs ) . "'";

	// phpcs:disable
	$posts = $wpdb->get_results(
		$wpdb->prepare(
			"
				SELECT ID, post_name
				FROM $wpdb->posts
				WHERE post_name IN ($in_string)
				AND post_type = %s
			",
			Constants::POST_TYPE_PLACEMENT
		)
	);
	// phpcs:enable

	return empty( $posts ) ? [] : wp_list_pluck( $posts, 'ID', 'post_name' );
}

/**
 * Save a placement as post.
 *
 * @param array $data Placement data array.
 *
 * @return int|WP_Error
 */
function advads_save_new_placement( $data ) {
	// Normalize options.
	$options            = $data['options'] ?? [];
	$display_conditions = $options['placement_conditions']['display'] ?? [];
	$visitor_conditions = $options['placement_conditions']['visitors'] ?? [];
	unset( $options['placement_conditions'] );

	// Find placement type.
	$placement_type = empty( $data['type'] ) ? 'default' : $data['type'];
	if ( ! wp_advads_has_placement_type( $placement_type ) ) {
		wp_advads_create_placement_type( $placement_type );
	}

	$placement = wp_advads_create_new_placement( $placement_type );
	$placement->set_type( $data['type'] );
	$placement->set_item( $data['item'] );
	$placement->set_slug( $data['slug'] );
	$placement->set_title( $data['name'] );
	$placement->set_status( 'publish' );
	$placement->set_display_conditions( $display_conditions );
	$placement->set_visitor_conditions( $visitor_conditions );
	$placement->set_props( $options );

	$placement->save();

	return $placement->get_id();
}

/**
 * Backup old Placements
 *
 * @param array $placements Placements.
 *
 * @return void
 */
function advads_upgrade_2_0_0_make_backup( $placements ): void {
	$backup_key = 'advads-ads-placements_backup';

	if ( false !== get_option( $backup_key ) ) {
		return;
	}

	update_option( $backup_key, $placements );
}

/**
 * Migrate placements from options to custom post type.
 *
 * @since 2.0.0
 *
 * @return void
 */
function advads_upgrade_2_0_0_placement_migration(): void {
	$option_key = 'advads-ads-placements';
	$placements = get_option( $option_key, [] );

	// Early bail!!
	if ( ! is_array( $placements ) || empty( $placements ) ) {
		return;
	}

	advads_upgrade_2_0_0_make_backup( $placements );
	$existing_posts = advads_get_existing_placement_posts( array_keys( $placements ) );

	foreach ( $placements as $slug => $placement ) {
		if ( ! is_array( $placement ) || array_key_exists( $slug, $existing_posts ) ) {
			continue;
		}

		$post_id = advads_save_new_placement( array_merge( $placement, [ 'slug' => $slug ] ) );
		if ( 0 === $post_id ) {
			continue;
		}

		unset( $placements[ $slug ] );
	}

	update_option( $option_key, $placements );
}

advads_upgrade_2_0_0_placement_migration();
