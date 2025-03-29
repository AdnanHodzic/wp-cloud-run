<?php
/**
 * Display ad-related information on the post and page overview page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 */

namespace AdvancedAds\Admin;

use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class Post_List
 */
class Post_List implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'restrict_manage_posts', [ $this, 'add_ads_filter_dropdown' ] );
		add_action( 'pre_get_posts', [ $this, 'filter_posts_by_ads_status' ] );
		add_filter( 'manage_posts_columns', [ $this, 'ads_column_init' ] );
		add_filter( 'manage_pages_columns', [ $this, 'ads_column_init' ] );
		add_filter( 'manage_edit-post_sortable_columns', [ $this, 'sortable_ads_column' ] );
		add_filter( 'manage_edit-page_sortable_columns', [ $this, 'sortable_ads_column' ] );
		add_filter( 'posts_clauses', [ $this, 'request_clauses' ], 10, 2 );
		add_action( 'manage_posts_custom_column', [ $this, 'ads_column_content' ], 10, 2 );
		add_action( 'manage_pages_custom_column', [ $this, 'ads_column_content' ], 10, 2 );
		add_filter( 'default_hidden_columns', [ $this, 'hide_ads_column_by_default' ], 10, 2 );
	}

	/**
	 * Add a filter dropdown to the post and pages lists.
	 *
	 * @param string $post_type current post type.
	 *
	 * @return void
	 */
	public function add_ads_filter_dropdown( string $post_type ): void {
		if ( ! in_array( $post_type, [ 'post', 'page' ], true ) ) {
			return;
		}

		$viewability = Params::get( 'ad-viewability', '' );
		include ADVADS_ABSPATH . 'admin/views/post-list-filter-dropdown.php';
	}

	/**
	 * Filter the list of posts and pages based on their ads settings
	 *
	 * @param \WP_Query $query The WP_Query object.
	 *
	 * @return void
	 */
	public function filter_posts_by_ads_status( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() || ! $query->get( 'post_type' ) || ! in_array( $query->get( 'post_type' ), [ 'post', 'page' ], true ) ) {
			return;
		}

		$viewability = Params::get( 'ad-viewability', '' );
		if ( ! $viewability ) {
			return;
		}

		if ( in_array( $viewability, [ 'disable_ads', 'disable_the_content' ], true ) ) {
			$query->set( 'meta_key', '_advads_ad_settings' );
			$query->set( 'meta_compare', 'LIKE' );
			$query->set( 'meta_value', '"' . $viewability . '";i:1;' );
		}
	}

	/**
	 * Order post list by ad status
	 *
	 * @param array     $clauses existing request clauses.
	 * @param \WP_Query $query   the current WP Query.
	 *
	 * @return array
	 */
	public function request_clauses( $clauses, $query ) {
		global $wpdb;

		if ( empty( $query->query_vars['orderby'] ) || 'ad-status' !== $query->query_vars['orderby'] || ! $query->is_main_query() ) {
			// No need to order by ad status.
			return $clauses;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return $clauses;
		}

		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, [ 'edit-post', 'edit-page' ], true ) ) {
			// Not the page we're interested in.
			return $clauses;
		}

		// Create aliases for ads disabled on the post/page and injection into the content disabled.
		$clauses['join'] .= " LEFT JOIN (SELECT post_id, IF(meta_value LIKE '%disable_ads\";i:1%', 1, 0) as ads, IF(meta_value LIKE '%disable_the_content\";s:1%', 1, 0) as content FROM {$wpdb->postmeta}"
							. " WHERE meta_key = '_advads_ad_settings') as advads_meta on {$wpdb->posts}.ID = advads_meta.post_id";

		$order = 'asc' === strtolower( $query->query_vars['order'] ) ? 'DESC' : 'ASC';

		$clauses['orderby'] = "advads_meta.ads {$order}, advads_meta.content {$order}";

		return $clauses;
	}

	/**
	 * Make the ad status column sortable
	 *
	 * @param array $columns columns list.
	 *
	 * @return array
	 */
	public function sortable_ads_column( $columns ) {
		$columns['ad-status'] = 'ad-status';

		return $columns;
	}

	/**
	 * Adds a new column to the post overview page for public post types.
	 *
	 * @param array $columns An array of column names.
	 *
	 * @return array The modified array of column names.
	 */
	public function ads_column_init( array $columns ): array {
		$post_type = wp_doing_ajax() ? Params::post( 'post_type', '' ) : get_current_screen()->post_type;

		if ( $post_type && get_post_type_object( $post_type )->public ) {
			$columns['ad-status'] = __( 'Ad injection', 'advanced-ads' );
		}

		return $columns;
	}

	/**
	 * Displays the value of the "ads" post meta in the "Ads" column.
	 *
	 * @param string $column  The name of the column.
	 * @param int    $post_id The ID of the post.
	 *
	 * @return void
	 */
	public function ads_column_content( string $column, int $post_id ): void {
		// Early bail!!
		if ( 'ad-status' !== $column ) {
			return;
		}

		$ads_post_meta = get_post_meta( $post_id, '_advads_ad_settings', true );

		if ( ! empty( $ads_post_meta['disable_ads'] ) ) {
			echo '<p>' . esc_html__( 'All ads disabled', 'advanced-ads' ) . '</p>';
		}

		if ( defined( 'AAP_VERSION' ) && ! empty( $ads_post_meta['disable_the_content'] ) ) {
			echo '<p>' . esc_html__( 'Ads in content disabled', 'advanced-ads' ) . '</p>';
		}
	}

	/**
	 * Hide the Ads column by default
	 *
	 * @param string[]   $hidden hidden columns.
	 * @param \WP_Screen $screen screen object.
	 *
	 * @return string[]
	 */
	public function hide_ads_column_by_default( array $hidden, \WP_Screen $screen ): array {
		$post_type_object = get_post_type_object( $screen->post_type );

		if ( ! $post_type_object || ! $post_type_object->public ) {
			return $hidden;
		}

		$hidden[] = 'ad-status';

		return $hidden;
	}
}
