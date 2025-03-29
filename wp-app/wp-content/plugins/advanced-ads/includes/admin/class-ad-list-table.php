<?php
/**
 * Admin Ad List Table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Admin;

defined( 'ABSPATH' ) || exit;

use AdvancedAds\Options;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Abstracts\Admin_List_Table;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Ad List Table.
 */
class Ad_List_Table extends Admin_List_Table {

	/**
	 * Object being shown on the row.
	 *
	 * @var Ad|null
	 */
	protected $object = null;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $object_type = 'ad';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = Constants::POST_TYPE_AD;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		parent::hooks();
		add_filter( 'pre_get_posts', [ $this, 'posts_ordering' ] );
		add_action( 'manage_posts_extra_tablenav', [ $this, 'display_views' ] );
		add_filter( 'views_edit-' . $this->list_table_type, [ $this, 'add_views' ] );
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function define_columns( $columns ): array {
		// Remove the group taxonomy column as we have custom 'Used' column.
		unset( $columns[ 'taxonomy-' . Constants::TAXONOMY_GROUP ] );

		$new_columns = [];

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			if ( 'cb' === $key ) {
				$new_columns['ad_type'] = __( 'Type', 'advanced-ads' );
				continue;
			}

			if ( 'title' === $key ) {
				$new_columns['title']          = __( 'Name', 'advanced-ads' );
				$new_columns['ad_size']        = __( 'Size', 'advanced-ads' );
				$new_columns['ad_timing']      = __( 'Ad Planning', 'advanced-ads' );
				$new_columns['ad_shortcode']   = __( 'Ad Shortcode', 'advanced-ads' );
				$new_columns['ad_adsense_id']  = __( 'AdSense ID', 'advanced-ads' );
				$new_columns['ad_date']        = __( 'Date', 'advanced-ads' );
				$new_columns['ad_description'] = __( 'Notes', 'advanced-ads' );
				$new_columns['ad_preview']     = __( 'Preview', 'advanced-ads' );
				$new_columns['ad_used']        = __( 'Used', 'advanced-ads' );
				$new_columns['ad_debugmode']   = __( 'Debug Mode', 'advanced-ads' );

				// show only when privacy setting is enabled.
				if ( Options::instance()->get( 'privacy.enabled' ) ) {
					$new_columns['ad_privacyignore'] = __( 'Privacy Ignore', 'advanced-ads' );
				}
			}
		}

		unset( $new_columns['date'] );

		return apply_filters( 'advanced-ads-ad-columns', $new_columns );
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array
	 */
	public function define_sortable_columns( $columns ): array {
		$columns['ad_date'] = 'ad_date';

		return $columns;
	}

	/**
	 * Define hidden columns.
	 *
	 * @return array
	 */
	protected function define_hidden_columns(): array {
		$hidden[] = 'ad_description';
		$hidden[] = 'author';
		$hidden[] = 'ad_size';
		$hidden[] = 'ad_shortcode';
		$hidden[] = 'ad_date';
		$hidden[] = 'ad_preview';
		$hidden[] = 'ad_adsense_id';
		$hidden[] = 'ad_debugmode';
		$hidden[] = 'ad_privacyignore';

		return $hidden;
	}

	/**
	 * Render any custom filters and search inputs for the list table.
	 *
	 * @return void
	 */
	protected function render_filters(): void {
		$addate = Params::get( 'addate' );
		if ( ! empty( $addate ) ) {
			printf( '<input type="hidden" name="addate" value="%s">', esc_attr( $addate ) );
		}

		include ADVADS_ABSPATH . 'views/admin/tables/ads/filters.php';
	}

	/**
	 * Add expiring and expired ads view.
	 *
	 * @param array $views Available list table views.
	 *
	 * @return array
	 */
	public function add_views( $views ): array {
		$counts   = wp_count_posts( $this->list_table_type, 'readable' );
		$expired  = $counts->{Constants::AD_STATUS_EXPIRED};
		$expiring = $counts->{Constants::AD_STATUS_EXPIRING};

		if ( $expiring > 0 ) {
			$views[ Constants::AD_STATUS_EXPIRING ] = sprintf(
				'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
				add_query_arg(
					[
						'post_type' => Constants::POST_TYPE_AD,
						'addate'    => 'advads-filter-expiring',
						'orderby'   => 'expiry_date',
						'order'     => 'ASC',
					],
					'edit.php'
				),
				'advads-filter-expiring' === Params::request( 'addate' ) ? 'class="current" aria-current="page"' : '',
				esc_attr_x( 'Expiring', 'Post list header for ads expiring in the future.', 'advanced-ads' ),
				$expiring
			);
		}

		if ( $expired > 0 ) {
			$views[ Constants::AD_STATUS_EXPIRED ] = sprintf(
				'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
				add_query_arg(
					[
						'post_type' => Constants::POST_TYPE_AD,
						'addate'    => 'advads-filter-expired',
						'orderby'   => 'expiry_date',
						'order'     => 'DESC',
					],
					'edit.php'
				),
				'advads-filter-expired' === Params::request( 'addate' ) ? 'class="current" aria-current="page"' : '',
				esc_attr_x( 'Expired', 'Post list header for expired ads.', 'advanced-ads' ),
				$expired
			);
		}

		return $views;
	}

	/**
	 * Displays the list of views available for Ads.
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

		include ADVADS_ABSPATH . 'views/admin/tables/ads/view-list.php';
	}

	/**
	 * Query filters.
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

		if ( 'expiry_date' === $query_vars['orderby'] ) {
			$query_vars['orderby']  = 'meta_value';
			$query_vars['meta_key'] = Constants::AD_META_EXPIRATION_TIME; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$query_vars['order']    = strtoupper( $query_vars['order'] ) === 'DESC' ? 'DESC' : 'ASC';

			if ( 'advads-filter-expired' === Params::get( 'addate' ) ) {
				$query_vars['post_status'] = Constants::AD_STATUS_EXPIRED;
			}
		}

		$filter_author = Params::get( 'ad_author' );
		if ( $filter_author ) {
			$query_vars['author'] = $filter_author;
		}

		return $query_vars;
	}

	/**
	 * Modify the post listing order in the admin panel for a specific custom post type.
	 *
	 * @param WP_Query $query The WP_Query object.
	 *
	 * @return void
	 */
	public function posts_ordering( $query ): void {
		global $typenow;

		// Early bail!!
		if ( ! $query->is_main_query() ) {
			return;
		}

		if ( $this->list_table_type === $typenow ) {
			$orderby = Params::get( 'orderby', 'title' );
			$order   = strtoupper( Params::get( 'order', 'ASC' ) );

			if ( 'ad_date' === $orderby ) {
				$orderby = 'post_modified';
			}

			$query->set( 'orderby', $orderby );
			$query->set( 'order', $order );
		}
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
			$this->object = wp_advads_get_ad( $post_id );
		}
	}

	/**
	 * Display the ad type icon in the ads list.
	 *
	 * @return void
	 */
	protected function render_ad_type_column(): void {
		$ad          = $this->object;
		$size_string = $this->get_ad_size_string();

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-type.php';
	}

	/**
	 * Display the ad description in the ads list
	 *
	 * @return void
	 */
	protected function render_ad_description_column(): void {
		$description = wp_trim_words( $this->object->get_description(), 50 );

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-description.php';
	}

	/**
	 * Display an ad preview in ads list.
	 *
	 * @return void
	 */
	protected function render_ad_preview_column(): void {
		$type_object = $this->object->get_type_object();

		// Early bail!!
		if ( ! $type_object ) {
			return;
		}

		if ( is_callable( [ $type_object, 'render_preview' ] ) ) {
			$type_object->render_preview( $this->object );
		}

		do_action( 'advanced-ads-ad-list-details-column-after', $this->object, $type_object );
	}

	/**
	 * Display the ad size in the ads list
	 *
	 * @return void
	 */
	protected function render_ad_size_column(): void {
		$size = $this->get_ad_size_string();

		// Early bail!!
		if ( empty( $size ) ) {
			return;
		}

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-size.php';
	}

	/**
	 * Display ad timing in ads list
	 *
	 * @return void
	 */
	protected function render_ad_timing_column(): void {
		list(
			'status_strings' => $status_strings,
			'html_classes'   => $html_classes,
		) = $this->object->get_ad_schedule_details();

		ob_start();
		do_action_ref_array(
			'advanced-ads-ad-list-timing-column-after',
			[
				$this->object,
				&$html_classes,
			]
		);
		$content_after = ob_get_clean();

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-timing.php';
	}

	/**
	 * Display ad shortcode in ads list
	 *
	 * @return void
	 */
	protected function render_ad_shortcode_column(): void {
		$ad_id = $this->object->get_id();
		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-shortcode.php';
	}

	/**
	 * Display an ad date in ads list.
	 *
	 * @return void
	 */
	protected function render_ad_date_column(): void {
		$id = $this->object->get_id();

		if ( ! $id ) {
			return;
		}

		$datetime_regex = get_option( 'date_format' ) . ' \\a\\t ' . get_option( 'time_format' );
		$published_date = get_the_date( $datetime_regex, $id );
		$modified_date  = get_the_modified_date( $datetime_regex, $id );

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-date.php';
	}

	/**
	 * Display the adsense id.
	 *
	 * @return void
	 */
	protected function render_ad_adsense_id_column(): void {
		if ( null === $this->object->get_content() ) {
			return;
		}

		$content = json_decode( $this->object->get_content() );
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$slotid = $content->slotId ?? null;

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-adsense.php';
	}

	/**
	 * Display ad usage in groups & placements.
	 *
	 * @return void
	 */
	protected function render_ad_used_column(): void {
		$ad_id = $this->object->get_id();

		if ( ! $ad_id ) {
			return;
		}

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-used.php';
	}

	/**
	 * Display the debug mode in the ads list.
	 *
	 * @return void
	 */
	protected function render_ad_debugmode_column(): void {
		$debug_mode = $this->object->is_debug_mode();

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-debug.php';
	}

	/**
	 * Display the privacy ignore status in the ads list.
	 *
	 * @return void
	 */
	protected function render_ad_privacyignore_column(): void {
		$privacyignore = $this->object->get_prop( 'privacy.ignore-consent' ) ?? false;

		include ADVADS_ABSPATH . 'views/admin/tables/ads/column-privacyignore.php';
	}

	/**
	 * Get the ad size string to display in post list.
	 *
	 * @return string
	 */
	private function get_ad_size_string(): string {
		$size = '';
		if ( ! empty( $this->object->get_width() ) || ! empty( $this->object->get_height() ) ) {
			$size = sprintf( '%d &times; %d', $this->object->get_width(), $this->object->get_height() );
		}

		/**
		 * Filter the ad size string to display in the ads post list.
		 *
		 * @param string $size Size string.
		 * @param Ad     $ad   Ad instance.
		 */
		return (string) apply_filters( 'advanced-ads-list-ad-size', $size, $this->object );
	}
}
