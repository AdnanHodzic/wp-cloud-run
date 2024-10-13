<?php

namespace Advanced_Ads\Admin;

/**
 * Display ad-related information on the post and page overview page.
 */
class Post_List {

	/**
	 * Register filters
	 */
	public function __construct() {
		add_action( 'restrict_manage_posts', [ $this, 'add_ads_filter_dropdown' ] );
		add_action( 'pre_get_posts', [ $this, 'filter_posts_by_ads_status' ] );
		add_filter( 'manage_posts_columns', [ $this, 'ads_column_init' ] );
		add_filter( 'manage_pages_columns', [ $this, 'ads_column_init' ] );
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

		$viewability = $_GET['ad-viewability'] ?? '';
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

		$viewability = $_GET['ad-viewability'] ?? '';
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
	 * Adds a new column to the post overview page for public post types.
	 *
	 * @param array $columns An array of column names.
	 *
	 * @return array The modified array of column names.
	 */
	public function ads_column_init( array $columns ): array {
		$post_type_object = get_post_type_object( get_current_screen()->post_type );

		if ( ! wp_doing_ajax() && ! $post_type_object->public ) {
			return $columns;
		}

		$columns['ad-status'] = __( 'Ad injection', 'advanced-ads' );

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
		if ( $column !== 'ad-status' ) {
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


