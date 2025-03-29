<?php
/**
 * Admin Groups List Table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

use WP_Meta_Query;
use WP_Term_Query;
use AdvancedAds\Modal;
use WP_Terms_List_Table;
use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Groups List Table.
 */
class Groups_List_Table extends WP_Terms_List_Table {
	/**
	 * Missing type error.
	 *
	 * @var string
	 */
	private $type_error = '';

	/**
	 * Array with all ads.
	 *
	 * @var $all_ads
	 */
	private $all_ads = [];

	/**
	 * Array of group ads info.
	 *
	 * @var array
	 */
	private $group_ads_info = [];

	/**
	 * Hints html.
	 *
	 * @var string
	 */
	private $hints_html = null;

	/**
	 * Construct the current list
	 */
	public function __construct() {
		parent::__construct();
		$this->prepare_items();
		add_action( 'pre_get_terms', [ $this, 'sorting_query' ] );
		add_filter( 'default_hidden_columns', [ $this, 'default_hidden_columns' ] );
	}

	/**
	 * Modify sorting query
	 *
	 * @param WP_Term_Query $query Query object.
	 *
	 * @return WP_Term_Query
	 */
	public function sorting_query( $query ) {
		switch ( $query->query_vars['orderby'] ) {
			case 'date':
				// phpcs:ignore Generic.Formatting.MultipleStatementAlignment.NotSameWarning
				$meta_query_args = [
					'relation' => 'OR',
					[
						'key'     => 'modified_date',
						'compare' => 'EXISTS',
					],
					[
						'key'     => 'modified_date',
						'compare' => 'NOT EXISTS',
					],
				]; // include all groups with and without a modified date so empty date groups are shown.
				$meta_query                   = new WP_Meta_Query( $meta_query_args );
				$query->meta_query            = $meta_query;
				$query->query_vars['orderby'] = 'meta_value';
				break;
			case 'details':
				$query->query_vars['orderby'] = 'term_id';
				break;
		}

		return $query;
	}

	/**
	 * Load groups
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		parent::prepare_items();

		$args               = $this->callback_args;
		$args['taxonomy']   = $this->screen->taxonomy;
		$args['hide_empty'] = 0;
		$args['offset']     = ( $args['page'] - 1 ) * $args['number'];

		// Save the values because 'number' and 'offset' can be subsequently overridden.
		$this->callback_args = $args;

		if ( is_taxonomy_hierarchical( $args['taxonomy'] ) && ! isset( $args['orderby'] ) ) {
			// We'll need the full set of terms then.
			$args['number'] = 0;
			$args['offset'] = $args['number'];
		}

		$args = $this->query_filters( $args );

		$this->items = get_terms( $args );

		$this->set_pagination_args(
			[
				'total_items' => wp_count_terms(
					[
						'taxonomy' => $args['taxonomy'],
						'search'   => $args['search'],
					]
				),
				'per_page'    => $args['number'],
			]
		);

		$this->all_ads = wp_advads_get_ads_dropdown();
		$this->items   = array_map(
			function ( $term_id ) {
				return wp_advads_get_group( $term_id );
			},
			$this->items ?? []
		);
	}

	/**
	 * Gets the number of items to display on a single page.
	 *
	 * @param string $option        User option name.
	 * @param int    $default_value The number of items to display.
	 *
	 * @return int
	 */
	protected function get_items_per_page( $option, $default_value = 20 ): int {
		return 10000;
	}

	/**
	 * Gets a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return array
	 */
	protected function get_table_classes(): array {
		return array_merge( parent::get_table_classes(), [ 'advads-table' ] );
	}

	/**
	 * Renders filters
	 *
	 * @return void
	 */
	public function render_filters(): void {
		include ADVADS_ABSPATH . 'views/admin/tables/groups/filters.php';
	}

	/**
	 * No groups found
	 *
	 * @return void
	 */
	public function no_items(): void {
		esc_html_e( 'No Ad Group found', 'advanced-ads' );
	}

	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function get_columns(): array {
		return [
			'type'    => __( 'Type', 'advanced-ads' ),
			'name'    => _x( 'Name', 'term name', 'advanced-ads' ),
			'details' => __( 'Details', 'advanced-ads' ),
			'ads'     => __( 'Ads', 'advanced-ads' ),
			'date'    => __( 'Date', 'advanced-ads' ),
		];
	}

	/**
	 * Hidden columns
	 *
	 * @param string[] $hidden Column list.
	 *
	 * @return array
	 */
	public function default_hidden_columns( $hidden ): array {
		$hidden[] = 'date';
		return $hidden;
	}

	/**
	 * Sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns(): array {
		return [
			'date'    => 'date',
			'name'    => 'name',
			'details' => 'details',
		];
	}

	/**
	 * Displays the table.
	 *
	 * @return void
	 */
	public function display(): void {
		$singular = $this->_args['singular'];

		$this->screen->render_screen_reader_content( 'heading_list' );
		?>
		<table class="<?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
			<?php $this->print_table_description(); ?>
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody id="the-list"
				<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
				>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Display rows or placeholder
	 *
	 * @return void
	 */
	public function display_rows_or_placeholder(): void {
		if ( empty( $this->items ) || ! is_array( $this->items ) ) {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$this->no_items();
			echo '</td></tr>';
			return;
		}

		foreach ( $this->items as $term ) {
			$this->single_row( $term );
		}
	}

	/**
	 * Render single row.
	 *
	 * @param Group $group Term object.
	 * @param int   $level Depth level.
	 *
	 * @return void
	 */
	public function single_row( $group, $level = 0 ): void {
		$this->type_error = '';

		// Set the group to behave as default, if the original type is not available.
		$group_type = $group->get_type_object();
		if ( $group_type->is_premium() ) {
			$this->type_error = sprintf(
			/* translators: %s is the group type string */
				__( 'The originally selected group type “%s” is not enabled.', 'advanced-ads' ),
				$group_type->get_title()
			);
		}

		echo '<tr id="tag-' . $group->get_id() . '" class="' . $level . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$this->single_row_columns( $group );
		echo '</tr>';
	}

	/**
	 * Column type
	 *
	 * @param Group $group Group instance.
	 *
	 * @return void
	 */
	public function column_type( $group ): void {
		include ADVADS_ABSPATH . 'views/admin/tables/groups/column-type.php';
	}

	/**
	 * Column name
	 *
	 * @param Group $group Group instance.
	 *
	 * @return void
	 */
	public function column_name( $group ): void {
		$this->render_edit_modal( $group );
		$this->render_usage_modal( $group );
		include ADVADS_ABSPATH . 'views/admin/tables/groups/column-name.php';
	}

	/**
	 * Column details
	 *
	 * @param Group $group Group instance.
	 *
	 * @return void
	 */
	public function column_details( $group ): void {
		include ADVADS_ABSPATH . 'views/admin/tables/groups/column-details.php';
	}

	/**
	 * Column ads
	 *
	 * @param Group $group Group instance.
	 *
	 * @return void
	 */
	public function column_ads( $group ): void {
		$template = empty( $group->get_ad_weights() ) ? 'list-row-loop-none.php' : 'list-row-loop.php';

		include ADVADS_ABSPATH . 'views/admin/tables/groups/' . $template;
	}

	/**
	 * Column date
	 *
	 * @param Group $group Group instance.
	 *
	 * @return void
	 */
	public function column_date( $group ) {
		$publish_date  = $group->get_publish_date();
		$modified_date = $group->get_modified_date();

		if ( ! $publish_date && ! $modified_date ) {
			return;
		}

		$date_time_regex = get_option( 'date_format' ) . ' \\a\\t ' . get_option( 'time_format' );
		$date_prefix     = $publish_date === $modified_date ? __( 'Published', 'advanced-ads' ) : __( 'Last Modified', 'advanced-ads' );
		$date_to_show    = get_date_from_gmt( $publish_date === $modified_date ? $publish_date : $modified_date, $date_time_regex );

		echo esc_html( $date_prefix ) . '<br>' . esc_html( $date_to_show );
	}

	/**
	 * Generates and displays row action links.
	 *
	 * @param Group  $group Group instance.
	 * @param string $column_name Column name.
	 * @param string $primary     Primary column name.
	 *
	 * @return string
	 */
	protected function handle_row_actions( $group, $column_name, $primary ): string {
		global $tax;

		if ( $primary !== $column_name ) {
			return '';
		}

		$actions = [];
		if ( ! $this->type_error && current_user_can( $tax->cap->edit_terms ) ) {
			// edit group link.
			$actions['edit'] = '<a href="#modal-group-edit-' . $group->get_id() . '"
								class="edits">' . esc_html__( 'Edit', 'advanced-ads' ) . '</a>';

			// duplicate group upgrade link.
			if ( ! defined( 'AAP_VERSION' ) ) {
				$actions['duplicate-group'] = ( new Upgrades() )->create_duplicate_link();
			}
		}

		$actions['usage'] = '<a href="#modal-group-usage-' . $group->get_id() . '" class="edits">' . esc_html__( 'Show Usage', 'advanced-ads' ) . '</a>';

		if ( current_user_can( $tax->cap->delete_terms ) ) {
			$args              = [
				'action'   => 'group',
				'action2'  => 'delete',
				'group_id' => $group->get_id(),
				'page'     => 'advanced-ads-groups',
			];
			$delete_link       = add_query_arg( $args, admin_url( 'admin.php' ) );
			$actions['delete'] = "<a class='delete-tag' href='" . wp_nonce_url( $delete_link, 'delete-tag_' . $group->get_id() ) . "'>" . __( 'Delete', 'advanced-ads' ) . '</a>';
		}

		$actions = apply_filters( Constants::TAXONOMY_GROUP . '_row_actions', $actions, $group );

		return $this->row_actions( $actions );
	}

	/**
	 * Render edit form modal
	 *
	 * @param Group $group Group instance.
	 *
	 * @return void
	 */
	private function render_edit_modal( $group ): void {
		ob_start();
		require ADVADS_ABSPATH . 'views/admin/tables/groups/edit-form-modal.php';
		$modal_content = ob_get_clean();

		Modal::create(
			[
				'modal_slug'    => 'group-edit-' . $group->get_id(),
				'modal_content' => $modal_content,
				'modal_title'   => sprintf( '%s %s', __( 'Edit', 'advanced-ads' ), $group->get_name() ),
			]
		);
	}

	/**
	 * Render usage form modal
	 *
	 * @param Group $group Group instance.
	 *
	 * @return void
	 */
	private function render_usage_modal( $group ): void {
		ob_start();
		include ADVADS_ABSPATH . 'views/admin/tables/groups/column-usage.php';
		$modal_content = ob_get_clean();

		Modal::create(
			[
				'modal_slug'    => 'group-usage-' . $group->get_id(),
				'modal_content' => $modal_content,
				'modal_title'   => __( 'Usage', 'advanced-ads' ),
				'cancel_action' => false,
				'close_action'  => __( 'Close', 'advanced-ads' ),
			]
		);
	}

	/**
	 * Check filters before loading group.
	 *
	 * @param array $args The arguments array for the groups list table.
	 *
	 * @return array The modified arguments array with the added meta query.
	 */
	private function query_filters( $args ): array {
		$group_type = Params::get( 'group_type' );

		if ( $group_type ) {
			$args['meta_query'] = [ // phpcs:ignore
				[
					'key'     => '_advads_group_type',
					'value'   => $group_type,
					'compare' => '=',
				],
			];
		}

		return $args;
	}
}
