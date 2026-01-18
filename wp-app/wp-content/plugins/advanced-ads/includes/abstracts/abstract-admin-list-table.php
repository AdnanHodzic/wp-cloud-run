<?php
/**
 * This class is serving as the base for admin tables and providing a foundation.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Abstracts;

use WP_Screen;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin List Table.
 */
abstract class Admin_List_Table implements Integration_Interface {

	/**
	 * Object being shown on the row.
	 *
	 * @var object|null
	 */
	protected $object = null;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $object_type = 'unknown';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = '';

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		if ( $this->list_table_type ) {
			// Columns.
			add_filter( 'default_hidden_columns', [ $this, 'default_hidden_columns' ], 10, 2 );
			add_filter( 'manage_' . $this->list_table_type . '_posts_columns', [ $this, 'define_columns' ] );
			add_filter( 'manage_edit-' . $this->list_table_type . '_sortable_columns', [ $this, 'define_sortable_columns' ] );
			add_action( 'manage_' . $this->list_table_type . '_posts_custom_column', [ $this, 'render_columns' ], 10, 2 );

			// Views.
			add_filter( 'view_mode_post_types', [ $this, 'disable_view_mode' ] );
			add_action( 'restrict_manage_posts', [ $this, 'restrict_manage_posts' ] );

			// Query.
			add_filter( 'request', [ $this, 'request_query' ] );
			add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );

			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
		}
	}

	/**
	 * Add a custom class to the body tag of Advanced Ads post type lists.
	 *
	 * @param string $classes Space-separated class list.
	 *
	 * @return string
	 */
	public function add_body_class( string $classes ): string {
		$screen = get_current_screen();

		if ( isset( $screen->id ) && 'edit-' . $this->list_table_type === $screen->id ) {
			$classes .= ' advanced-ads-post-type-list';
		}

		return $classes;
	}

	/**
	 * Custom scripts and styles for the ad list page
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		global $wp_query;

		// show label before the search form if this is a search.
		if ( Params::get( 's' ) ) {
			wp_advads()->registry->inline_style(
				'admin',
				"
				.post-type-{$this->list_table_type} .search-box:before { content: '" . esc_html__( 'Showing search results for', 'advanced-ads' ) . "'; float: left; margin-right: 8px; line-height: 30px; font-weight: 700; }
				.post-type-{$this->list_table_type} .subtitle { display: none; }
				"
			);
		}

		// Adjust search form when there are no results.
		if ( Conditional::has_filter_or_search() && 0 === $wp_query->found_posts ) {
			wp_advads()->registry->inline_style( 'admin', ".post-type-{$this->list_table_type} .search-box { display: block !important; }" );
			return;
		}

		// Show filters, if the option to show them is enabled or a search is running.
		if ( get_current_screen()->get_option( 'show-filters' ) || Conditional::has_filter_or_search() ) {
			wp_advads()->registry->inline_style( 'admin', ".post-type-{$this->list_table_type} .search-box { display: block !important; }" );
			if ( isset( $wp_query->found_posts ) && $wp_query->found_posts > 0 ) {
				wp_advads()->registry->inline_style( 'admin', ".post-type-{$this->list_table_type} .tablenav.top .alignleft.actions:not(.bulkactions) { display: block; }" );
			}
		}
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function define_columns( $columns ): array {
		return $columns;
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function define_sortable_columns( $columns ): array {
		return $columns;
	}

	/**
	 * Define hidden columns.
	 *
	 * @return array
	 */
	protected function define_hidden_columns(): array {
		return [];
	}

	/**
	 * Adjust which columns are displayed by default.
	 *
	 * @param array     $hidden Current hidden columns.
	 * @param WP_Screen $screen Current screen.
	 *
	 * @return array
	 */
	public function default_hidden_columns( $hidden, $screen ): array {
		if ( isset( $screen->id ) && 'edit-' . $this->list_table_type === $screen->id ) {
			$hidden = array_merge( $hidden, $this->define_hidden_columns() );
		}

		return $hidden;
	}

	/**
	 * Pre-fetch any data for the row each column has access to it.
	 *
	 * @param int $post_id Post ID being shown.
	 *
	 * @return void
	 */
	protected function prepare_row_data( $post_id ): void {}

	/**
	 * Render individual columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID being shown.
	 *
	 * @return void
	 */
	public function render_columns( $column, $post_id ): void {
		$this->prepare_row_data( $post_id );

		if ( ! $this->object ) {
			return;
		}

		if ( is_callable( [ $this, 'render_' . $column . '_column' ] ) ) {
			$this->{"render_{$column}_column"}();
		}

		do_action( 'advanced-ads-ad-render-columns', $column, $this->object );
		do_action( "advanced-ads-{$this->object_type}-render-column-{$column}", $this->object );
	}

	/**
	 * Removes this type from list of post types that support "View Mode" switching.
	 * View mode is seen on posts where you can switch between list or excerpt. Our post types don't support
	 * it, so we want to hide the useless UI from the screen options tab.
	 *
	 * @param array $post_types Array of post types supporting view mode.
	 *
	 * @return array             Array of post types supporting view mode, without this type.
	 */
	public function disable_view_mode( $post_types ): array {
		unset( $post_types[ $this->list_table_type ] );

		return $post_types;
	}

	/**
	 * Render any custom filters and search inputs for the list table.
	 *
	 * @return void
	 */
	protected function render_filters(): void {}

	/**
	 * See if we should render search filters or not.
	 *
	 * @return void
	 */
	public function restrict_manage_posts(): void {
		global $typenow;

		if ( $this->list_table_type === $typenow ) {
			$this->render_filters();
		}
	}

	/**
	 * Handle any filters.
	 *
	 * @param array $query_vars Query vars.
	 *
	 * @return array
	 */
	public function request_query( $query_vars ): array {
		global $typenow;

		if ( $this->list_table_type === $typenow ) {
			return $this->query_filters( $query_vars );
		}

		return $query_vars;
	}

	/**
	 * Handle any custom filters.
	 *
	 * @param array $query_vars Query vars.
	 *
	 * @return array
	 */
	protected function query_filters( $query_vars ): array {
		return $query_vars;
	}
}
