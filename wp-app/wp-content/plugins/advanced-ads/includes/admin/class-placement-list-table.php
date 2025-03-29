<?php
/**
 * Placement List Table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.49.0
 */

namespace AdvancedAds\Admin;

use WP_Query;
use AdvancedAds\Modal;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Admin_List_Table;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Placement List Table.
 */
class Placement_List_Table extends Admin_List_Table {

	/**
	 * Object being shown on the row.
	 *
	 * @var Placement|null
	 */
	protected $object = null;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $object_type = 'placement';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = Constants::POST_TYPE_PLACEMENT;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		parent::hooks();

		add_action( 'manage_posts_extra_tablenav', [ $this, 'placements_list_after' ] );
		add_action( 'manage_posts_extra_tablenav', [ $this, 'display_views' ] );

		// Manage rows and columns.
		add_filter( 'list_table_primary_column', [ $this, 'set_primary_column' ], 10, 0 );
		add_filter( 'post_row_actions', [ $this, 'row_actions' ] );

		// Filters.
		add_filter( 'disable_months_dropdown', '__return_true' );
	}

	/**
	 * Define hidden columns.
	 *
	 * @return array
	 */
	protected function define_hidden_columns(): array {
		return [ 'id', 'title' ];
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function define_columns( $columns ): array {
		return [
			'cb'         => $columns['cb'],
			'type'       => __( 'Type', 'advanced-ads' ),
			'title'      => __( 'Title', 'advanced-ads' ),
			'name'       => __( 'Name', 'advanced-ads' ),
			'ad_group'   => sprintf( '%1$s / %2$s', __( 'Ad', 'advanced-ads' ), __( 'Group', 'advanced-ads' ) ),
			'conditions' => __( 'Delivery', 'advanced-ads' ),
		];
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function define_sortable_columns( $columns ): array {
		$columns['type'] = 'type';
		$columns['name'] = 'title';

		return $columns;
	}

	/**
	 * Pre-fetch any data for the row each column has access to it.
	 *
	 * @param int $post_id Post ID being shown.
	 *
	 * @return void
	 */
	protected function prepare_row_data( $post_id ): void {
		if ( empty( $this->object ) || $this->object->get_id() !== $post_id ) {
			$this->object = wp_advads_get_placement( $post_id );
		}
	}

	/**
	 * Set the primary column.
	 *
	 * @return string
	 */
	public function set_primary_column(): string {
		return 'name';
	}

	/**
	 * Displays the list of views available for Placements.
	 *
	 * @param string $which The location of the extra table nav markup.
	 *
	 * @return void
	 */
	public function display_views( $which ): void {
		global $wp_list_table;

		if ( 'top' !== $which ) {
			return;
		}

		$views = $wp_list_table->get_views();

		/**
		 * Filters the list of available list table views.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen.
		 *
		 * @param string[] $views An array of available list table views.
		 */
		$views = apply_filters( "views_{$wp_list_table->screen->id}", $views );

		if ( empty( $views ) ) {
			return;
		}

		$wp_list_table->screen->render_screen_reader_content( 'heading_views' );

		$is_all = count(
			array_diff_key(
				$_GET, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				[
					'post_type' => Constants::POST_TYPE_AD,
					'orderby'   => '',
					'order'     => '',
					'paged'     => '',
					'mode'      => '',
				]
			)
		) === 0;

		$show_trash_delete_button = 'trash' === Params::get( 'post_status', false ) && have_posts() && current_user_can( get_post_type_object( $wp_list_table->screen->post_type )->cap->edit_others_posts );
		include ADVADS_ABSPATH . 'views/admin/tables/placements/views-list.php';
	}

	/**
	 * Deprecate a catch-all action at the end of the placements list.
	 *
	 * @param string $which should be one of 'top' or 'bottom'.
	 *
	 * @return void
	 */
	public function placements_list_after( string $which ): void {
		if ( 'bottom' !== $which ) {
			return;
		}

		do_action_deprecated( 'advanced-ads-placements-list-after', [ 'placements' => false ], '1.48.0', '', 'Use the API for WP_List_Table.' );
	}

	/**
	 * Filter the row actions for placements.
	 *
	 * @param array $actions Array of actions.
	 *
	 * @return array
	 */
	public function row_actions( array $actions ): array {
		// Remove quick edit.
		unset( $actions['hide-if-no-js'], $actions['inline hide-if-no-js'] );

		if ( $this->object->is_type( 'default' ) ) {
			$actions['usage'] = '<a href="#modal-placement-usage-' . $this->object->get_id() . '" class="edits">' . esc_html__( 'Show Usage', 'advanced-ads' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Order ads by title on ads list
	 *
	 * @param array $query_vars Query vars.
	 *
	 * @return array
	 */
	protected function query_filters( $query_vars ): array {
		// Early bail!!
		if ( wp_doing_ajax() ) {
			return $query_vars;
		}

		// Filter by type.
		$placement_type = sanitize_text_field( Params::get( 'placement-type', '' ) );
		if ( '' !== $placement_type ) {
			$query_vars['meta_key']   = 'type'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query_vars['meta_value'] = $placement_type; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		}

		// Sort by type.
		$order   = Params::get( 'order', 'asc' );
		$orderby = Params::get( 'orderby', 'type' );
		if ( in_array( $orderby, [ 'type' ], true ) ) {
			$query_vars['order'] = $order;

			if ( 'type' === $orderby ) {
				$query_vars['meta_key'] = 'type'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$query_vars['orderby']  = 'meta_value';
				add_filter( 'posts_orderby', [ $this, 'sort_by_type_order' ], 10, 2 );
			}
		}

		return $query_vars;
	}

	/**
	 * Set the ORDER BY clause of the query.
	 *
	 * @param string   $order_sql The ORDER BY clause of the query.
	 * @param WP_Query $wp_query  The current query instance.
	 *
	 * @return string
	 */
	public function sort_by_type_order( string $order_sql, WP_Query $wp_query ): string {
		global $wpdb;

		// Early bail!!
		if ( ! $wp_query->is_main_query() ) {
			return $order_sql;
		}

		$order       = Params::get( 'order', 'asc' );
		$types_order = wp_advads_get_placement_type_manager()->get_order_list();
		$types_order = array_keys( $types_order );

		$order_strings = [
			$wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				sprintf(
					'FIELD(%s.meta_value, %s ) %s',
					$wpdb->postmeta,
					implode( ', ', array_fill( 0, count( $types_order ), '%s' ) ),
					$order // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				),
				$types_order
			),
			"{$wpdb->posts}.post_title {$order}",
		];

		return implode( ', ', $order_strings );
	}

	/**
	 * Add placement type filter to the placements list.
	 *
	 * @return void
	 */
	public function render_filters(): void {
		$current_type = Params::get( 'placement-type', '' );

		include_once ADVADS_ABSPATH . 'views/admin/tables/placements/filter-types.php';
	}

	/**
	 * Render the Type column.
	 *
	 * @return void
	 */
	protected function render_type_column(): void {
		$placement = $this->object;
		( new Placement_Edit_Modal( $placement ) )->hooks();
		$this->render_usage_modal();

		require ADVADS_ABSPATH . 'views/admin/tables/placements/column-type.php';
	}

	/**
	 * Render the Name column.
	 *
	 * @return void
	 */
	protected function render_name_column(): void {
		$placement = $this->object;

		require ADVADS_ABSPATH . 'views/admin/tables/placements/column-name.php';
	}

	/**
	 * Render the Ad/Group column.
	 *
	 * @return void
	 */
	protected function render_ad_group_column(): void {
		$placement = $this->object;

		include ADVADS_ABSPATH . 'views/admin/tables/placements/column-ad-group.php';

		if ( $this->object->is_type( 'header' ) ) {
			include ADVADS_ABSPATH . 'views/admin/tables/placements/header-note.php';
		}
	}

	/**
	 * Render the Conditions column.
	 *
	 * @return void
	 */
	protected function render_conditions_column(): void {
		$placement = $this->object;

		require ADVADS_ABSPATH . 'views/admin/tables/placements/column-conditions.php';
	}

	/**
	 * Render usage form modal
	 *
	 * @return void
	 */
	private function render_usage_modal(): void {
		if ( ! $this->object->is_type( 'default' ) ) {
			return;
		}

		ob_start();
		$placement = $this->object;
		include ADVADS_ABSPATH . 'views/admin/tables/placements/column-usage.php';
		$modal_content = ob_get_clean();

		Modal::create(
			[
				'modal_slug'    => 'placement-usage-' . $placement->get_id(),
				'modal_content' => $modal_content,
				'modal_title'   => __( 'Usage', 'advanced-ads' ),
				'cancel_action' => false,
				'close_action'  => __( 'Close', 'advanced-ads' ),
			]
		);
	}
}
