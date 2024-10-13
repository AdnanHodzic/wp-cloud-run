<?php
/**
 * The class manages various admin action links, feedback submission and text overrides in footer.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use Advanced_Ads_Ad_Expiration;
use WP_Term;
use WP_Query;
use WP_Terms_List_Table;
use Advanced_Ads;
use Advanced_Ads_Group;
use Advanced_Ads_Modal;
use AdvancedAds\Entities;
use Advanced_Ads\Group_Repository;
use AdvancedAds\Interfaces\Group_Type;
use AdvancedAds\Utilities\Groups;

defined( 'ABSPATH' ) || exit;

/**
 * Action Links.
 */
class Groups_List_Table extends WP_Terms_List_Table {

	/**
	 * Current row item.
	 *
	 * @var Advanced_Ads_Group
	 */
	private $current = null;

	/**
	 * Current group type.
	 *
	 * @var Group_Type
	 */
	private $group_type = null;

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

		// TODO: replace with Ad repository.
		$this->all_ads = $this->ads_for_select();
	}

	/**
	 * Load groups
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		parent::prepare_items();

		$taxonomy = $this->screen->taxonomy;

		$this->callback_args['taxonomy']   = $taxonomy;
		$this->callback_args['hide_empty'] = 0;
		$this->callback_args['offset']     = ( $this->callback_args['page'] - 1 ) * $this->callback_args['number'];

		$this->items = get_terms( $this->callback_args );

		$this->items = array_map(
			function ( WP_Term $term ) {
				return Group_Repository::get( $term );
			},
			$this->items ?? []
		);

		$this->_column_headers = [ $this->get_columns(), [], [], 'name' ];
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
		return 0;
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
		];
	}

	/**
	 * Displays the table.
	 *
	 * @since 3.1.0
	 */
	public function display() {
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
	 * Gets a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return array
	 */
	protected function get_table_classes(): array {
		$mode = get_user_setting( 'posts_list_mode', 'list' );

		$mode_class = esc_attr( 'table-view-' . $mode );

		return [ 'wp-list-table', 'widefat', 'fixed', 'advads-table', $mode_class, $this->_args['plural'] ];
	}

	/**
	 * No groups found
	 *
	 * @return void
	 */
	public function no_items(): void {
		echo esc_html_e( 'No Ad Group found', 'advanced-ads' );
	}

	/**
	 * Render single row.
	 *
	 * @param Advanced_Ads_Group $group Term object.
	 * @param int                $level Depth level.
	 */
	public function single_row( $group, $level = 0 ) {
		$this->type_error = '';
		$this->hints_html = '';

		// Set the group to behave as default, if the original type is not available.
		if ( ! wp_advads()->group_manager->has_type( $group->type ) ) {
			$this->type_error = sprintf(
			/* translators: %s is the group type string */
				__( 'The originally selected group type “%s” is not enabled.', 'advanced-ads' ),
				$group->type
			);
			$group->type = 'default';
		}

		$this->hints_html = Groups::build_hints_html( $group );
		$this->current    = $group;
		$this->group_type = wp_advads()->group_manager->get_type( $group->type );

		parent::single_row( $group, $level );
		$this->current = null;
	}

	/**
	 * Column type
	 *
	 * @param Advanced_Ads_Group $group Group Instance.
	 *
	 * @return void
	 */
	public function column_type( $group ) {
		?>
		<div class="advads-form-type">
			<?php if ( ! $this->type_error ) : ?>
				<img src="<?php echo esc_url( $this->group_type->get_image() ); ?>" alt="<?php echo esc_attr( $this->group_type->get_title() ); ?>">
			<?php endif; ?>
			<p class="advads-form-description">
				<strong><?php echo esc_html( $this->group_type->get_title() ); ?></strong>
			</p>
		</div>
		<?php
	}

	/**
	 * Column name
	 *
	 * @param Advanced_Ads_Group $group Group Instance.
	 *
	 * @return void
	 */
	public function column_name( $group ) {
		?>
		<div class="advads-table-name">
			<a class="row-title" href="#modal-group-edit-<?php echo absint( $group->id ); ?>"><?php echo esc_html( $group->name ); ?></a>
		</div>
		<?php if ( $this->type_error ) : ?>
		<p class="advads-notice-inline advads-error"><?php echo esc_html( $this->type_error ); ?></p>
			<?php
		endif;
		$this->render_edit_modal( $group );
		$this->render_usage_modal( $group );
	}

	/**
	 * Column details
	 *
	 * @param Advanced_Ads_Group $group Group Instance.
	 *
	 * @return void
	 */
	public function column_details( $group ) {
		?>
		<ul>
			<li>
				<strong>
				<?php
				/* translators: %s is the name of a group type */
				printf( esc_html__( 'Type: %s', 'advanced-ads' ), esc_html( $this->group_type->get_title() ) );
				?>
				</strong>
			</li>
			<li>
			<?php
			/*
			 * translators: %s is the ID of an ad group
			 */
			printf( esc_attr__( 'ID: %s', 'advanced-ads' ), absint( $group->id ) );
			?>
			</li>
		</ul>
		<?php
	}

	/**
	 * Column ads
	 *
	 * @param Advanced_Ads_Group $group Group Instance.
	 *
	 * @return void
	 */
	public function column_ads( $group ) {
		$group_ads_info = $this->get_group_ads_info( $group );
		$ads            = $group_ads_info['ads'] ?? null;
		$weights        = $group_ads_info['weights'] ?? null;
		$weight_sum     = $group_ads_info['weight_sum'] ?? null;

		if ( $ads->have_posts() ) {
			include ADVADS_ABSPATH . 'views/admin/tables/groups/list-row-loop.php';
		} else {
			include ADVADS_ABSPATH . 'views/admin/tables/groups/list-row-loop-none.php';
		}

		wp_reset_postdata();
	}

	/**
	 * Generates and displays row action links.
	 *
	 * @param Advanced_Ads_Group $group       Group Instance.
	 * @param string             $column_name Column name.
	 * @param string             $primary     Primary column name.
	 *
	 * @return string
	 */
	protected function handle_row_actions( $group, $column_name, $primary ): string {
		global $tax;

		if ( $primary !== $column_name ) {
			return '';
		}

		$actions = [];
		if ( current_user_can( $tax->cap->edit_terms ) ) {
			$actions['edit']  = '<a href="#modal-group-edit-' . $group->id . '" class="edits">' . esc_html__( 'Edit', 'advanced-ads' ) . '</a>';
			$actions['usage'] = '<a href="#modal-' . (int) $group->id . '-usage" class="usage-modal-link">' . esc_html__( 'show usage', 'advanced-ads' ) . '</a>';
		}

		if ( current_user_can( $tax->cap->delete_terms ) ) {
			$args              = [
				'action'   => 'delete',
				'group_id' => $group->id,
				'page'     => 'advanced-ads-groups',
			];
			$delete_link       = add_query_arg( $args, admin_url( 'admin.php' ) );
			$actions['delete'] = "<a class='delete-tag' href='" . wp_nonce_url( $delete_link, 'delete-tag_' . $group->id ) . "'>" . __( 'Delete', 'advanced-ads' ) . '</a>';
		}

		return $this->row_actions( $actions );
	}

	/**
	 * Render edit form modal
	 *
	 * @param Advanced_Ads_Group $group Group Instance.
	 *
	 * @return void
	 */
	private function render_edit_modal( $group ): void {
		$group_ads_info = $this->get_group_ads_info( $group, 'modal' );
		$ads            = $group_ads_info['ads'] ?? null;
		$weights        = $group_ads_info['weights'] ?? null;
		$ad_form_rows   = $this->get_weighted_ad_order( $weights );
		$max_weight     = Advanced_Ads_Group::get_max_ad_weight( $ads->post_count );

		// The Loop.
		if ( $ads->post_count ) {
			foreach ( $ads->posts as $ad ) {
				$ad_id    = absint( $ad->ID );
				$group_id = absint( $group->id );
				$ad_url   = add_query_arg(
					[
						'post'   => $ad_id,
						'action' => 'edit',
					],
					admin_url( 'post.php' )
				);
				// translators: %s is the title for ad.
				$link_title = sprintf( esc_attr__( 'Opens ad %s in a new tab', 'advanced-ads' ), $ad->post_title );
				$ad_weight  = ( isset( $weights[ $ad_id ] ) ) ? $weights[ $ad_id ] : Advanced_Ads_Group::MAX_AD_GROUP_DEFAULT_WEIGHT;

				$row  = '<tr data-ad-id="' . $ad_id . '" data-group-id="' . $group_id . '">';
				$row .= '<td class="ad-list-entry-name">';
				$row .= '<a target="_blank" href="' . esc_url( $ad_url ) . '" title="' . $link_title . '">' . esc_html( $ad->post_title ) . '</a></td>';
				$row .= '<td>' . \Advanced_Ads_Admin_Ad_Type::get_ad_schedule_output( $ad_id ) . '</td>';
				$row .= '<td><select name="advads-groups[' . $group_id . '][ads][' . $ad_id . ']">';

				$options = [];
				for ( $i = 0; $i <= $max_weight; $i++ ) {
					$selected  = selected( $ad_weight, $i, false );
					$options[] = '<option ' . $selected . '>' . $i . '</option>';
				}

				$row .= implode( '', $options );
				$row .= '</select</td><td><button type="button" class="advads-remove-ad-from-group button">x</button></td></tr>';

				$ad_form_rows[ $ad->ID ] = $row;
			}
		}
		$ad_form_rows = $this->remove_empty_weights( $ad_form_rows );

		ob_start();
		require ADVADS_ABSPATH . 'views/admin/tables/groups/edit-form-modal.php';
		$modal_content = ob_get_clean();

		Advanced_Ads_Modal::create(
			[
				'modal_slug'       => 'group-edit-' . $this->current->id,
				'modal_content'    => $modal_content,
				'modal_title'      => __( 'Edit', 'advanced-ads' ) . ' ' . $this->current->name,
				'close_action'     => __( 'Save', 'advanced-ads' ) . ' ' . $this->current->name,
				'close_form'       => 'advads-form-groups',
				'close_validation' => 'advads_group_edit_submit',
			]
		);
	}

	/**
	 * Render usage modal
	 *
	 * @param Advanced_Ads_Group $group Group Instance.
	 *
	 * @return void
	 */
	private function render_usage_modal( $group ): void {
		ob_start();
		?>
		<div class="advads-usage">
			<h2><?php esc_html_e( 'shortcode', 'advanced-ads' ); ?></h2>
			<code><input type="text" onclick="this.select();" value='[the_ad_group id="<?php echo absint( $group->id ); ?>"]' readonly /></code>
			<h2><?php esc_html_e( 'template (PHP)', 'advanced-ads' ); ?></h2>
			<code><input type="text" onclick="this.select();" value="the_ad_group(<?php echo absint( $group->id ); ?>);" readonly /></code>
		</div>

		<?php
		$modal_content = ob_get_clean();
		Advanced_Ads_Modal::create(
			[
				'modal_slug'    => $group->id . '-usage',
				'modal_content' => $modal_content,
				'modal_title'   => __( 'Usage', 'advanced-ads' ),
			]
		);
	}

	/**
	 * List of all ads to display in select dropdown
	 *
	 * @return array
	 */
	private function ads_for_select() {
		$model = Advanced_Ads::get_instance()->get_model();

		// load all ads.
		$ads = $model->get_ads(
			[
				'orderby' => 'title',
				'order'   => 'ASC',
			]
		);

		return wp_list_pluck( $ads, 'post_title', 'ID' );
	}

	/**
	 * Get ads information for this group.
	 *
	 * @param Advanced_Ads_Group $group group object.
	 * @param string             $view  View parameter to manipulate the output.
	 *
	 * @return array
	 */
	private function get_group_ads_info( $group, $view = 'list' ) {
		// TODO: reenable the following caching mechanism when switching to AA 2.0.

		$weights = $group->get_ad_weights();
		$args    = [
			'post_type'      => Entities::POST_TYPE_AD,
			'post_status'    => [ 'publish', 'future', 'pending', 'private' ],
			'taxonomy'       => $group->taxonomy,
			'term'           => $group->slug,
			'posts_per_page' => -1,
		];

		$ads    = new WP_Query( $args );
		$ad_ids = wp_list_pluck( $ads->posts, 'ID' );

		$weights = array_reduce(
			$ads->posts,
			function ( $carry, $item ) use ( $weights, $view ) {
				$weight             = $weights[ $item->ID ] ?? Advanced_Ads_Group::MAX_AD_GROUP_DEFAULT_WEIGHT;
				$carry[ $item->ID ] = ( 'modal' === $view || 'publish' === $item->post_status ) ? $weight : 0;

				return $carry;
			},
			[]
		);

		arsort( $weights );
		$weight_sum = array_sum( array_intersect_key( $weights, array_flip( $ad_ids ) ) );

		$this->group_ads_info[ $group->id ] = compact( 'weights', 'ads', 'weight_sum' );

		return $this->group_ads_info[ $group->id ];
	}

	/**
	 * Return the displayed ad count string
	 *
	 * @param Advanced_Ads_Group $group     the ad group.
	 * @param WP_Query           $ads_query list of ads in group.
	 *
	 * @return string
	 */
	private function get_ad_count_string( $group, $ads_query ) {
		$ad_count = 'all' === $group->ad_count ? $ads_query->post_count : $group->ad_count;

		/**
		 * Filters the displayed ad count on the ad groups page.
		 *
		 * @param int                $ad_count the amount of displayed ads.
		 * @param Advanced_Ads_Group $group    the current ad group.
		 */
		$ad_count = (int) apply_filters( 'advanced-ads-group-displayed-ad-count', $ad_count, $group );

		/* translators: amount of ads displayed */
		return sprintf( _n( 'Up to %d ad displayed.', 'Up to %d ads displayed', $ad_count, 'advanced-ads' ), $ad_count );
	}

	/**
	 * Function to calculate weight percentage
	 *
	 * @param int $weight     Ad weight.
	 * @param int $weight_sum Sum of all ad weights.
	 *
	 * @return string
	 */
	private function calculate_weight_percentage( $weight, $weight_sum ): string {
		$percentage = ( $weight / $weight_sum ) * 100;
		return number_format( $percentage, 2 ) . '%';
	}

	/**
	 * Remove entries from the ad weight array that are just id
	 *
	 * @param array $ads_output array with any output other that an integer.
	 *
	 * @return array $ads_output array with ad output.
	 * @since 1.5.1
	 */
	private function remove_empty_weights( array $ads_output ) {
		foreach ( $ads_output as $key => $value ) {
			if ( is_int( $value ) ) {
				unset( $ads_output[ $key ] );
			}
		}

		return $ads_output;
	}

	/**
	 * Order the ad list by weight first and then by title.
	 *
	 * @param array<int, int> $weights indexed by ad_id, weight as value.
	 *
	 * @return array<int, int>
	 */
	private function get_weighted_ad_order( array $weights ) {
		arsort( $weights );
		$ad_title_weights = [];

		// index ads with the same weight by weight.
		foreach ( $weights as $ad_id => $weight ) {
			$ad_title_weights[ $weight ][ $ad_id ] = get_the_title( $ad_id );
		}

		// Order them by title.
		array_walk(
			$ad_title_weights,
			function ( &$weight_group ) {
				natsort( $weight_group );
			}
		);

		// Flatten the array with the ad_id as key and the weight as value.
		$ad_order = [];
		foreach ( $ad_title_weights as $weight => $ad_array ) {
			$ad_order += array_fill_keys( array_keys( $ad_array ), $weight );
		}

		return $ad_order;
	}
}
