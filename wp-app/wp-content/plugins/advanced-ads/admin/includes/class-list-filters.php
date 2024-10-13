<?php
// phpcs:ignoreFile

use AdvancedAds\Entities;
use AdvancedAds\Utilities\Conditional;

/**
 * Container class for custom filters on admin ad list page.
 *
 * @package WordPress
 * @subpackage Advanced Ads Plugin
 */
class Advanced_Ads_Ad_List_Filters {
	/**
	 * The unique instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Ads data for the ad list table
	 *
	 * @var     array
	 */
	protected $all_ads = [];

	/**
	 * Ads ad groups
	 *
	 * @var     array
	 */
	protected $all_groups = [];

	/**
	 * Ads in each group
	 *
	 * @var     array
	 */
	protected $ads_in_groups = [];

	/**
	 * Ads array with ID as key
	 *
	 * @var     array
	 * @deprecated 1.31.0 -- we don't needs ads indexed by id, since we have all ads.
	 */
	protected $adsbyid = [];

	/**
	 * All filters available in the current ad list table
	 *
	 * @var     array
	 */
	protected $all_filters = [];

	/**
	 * All ad options for the ad list table
	 *
	 * @var     array
	 */
	protected $all_ads_options = [];

	/**
	 * Constructs the unique instance.
	 */
	private function __construct() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			add_filter( 'posts_results', [ $this, 'post_results' ], 10, 2 );
			add_filter( 'post_limits', [ $this, 'limit_filter' ], 10, 2 );
		}

		add_filter( 'views_edit-' . Entities::POST_TYPE_AD, [ $this, 'add_expired_view' ] );
		add_filter( 'views_edit-' . Entities::POST_TYPE_AD, [ $this, 'add_expiring_view' ] );
		add_action( 'restrict_manage_posts', [ $this, 'send_addate_in_filter' ] );

		add_action( 'manage_posts_extra_tablenav', [ $this, 'ad_views' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'ad_list_scripts' ], 11 );
	}

	/**
	 * Return true if the current screen is the ad list.
	 *
	 * @return bool
	 */
	private function is_ad_list_screen() {
		$screen = get_current_screen();
		return isset( $screen->id ) && $screen->id === 'edit-advanced_ads';
	}

	/**
	 * Check if the current screen uses a search or filter.
	 *
	 * @deprecated 1.47.0
	 *
	 * @return bool
	 */
	public static function uses_filter_or_search() {
		_deprecated_function( __METHOD__, '1.47.0', '\AdvancedAds\Utilities\Conditional::has_filter_or_search()' );
		return Conditional::has_filter_or_search();
	}

	/**
	 * Collect available filters for ad overview page.
	 *
	 * @param array $posts array of ads.
	 *
	 * @return null
	 */
	private function collect_filters( $posts ) {
		$all_sizes  = [];
		$all_types  = [];
		$all_dates  = [];
		$all_groups = [];

		$all_filters = [
			'all_sizes'  => [],
			'all_types'  => [],
			'all_dates'  => [],
			'all_groups' => [],
		];

		// can not filter correctly with "trashed" posts. Do not display any filtering option in this case.
		if ( isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] === 'trash' ) {
			$this->all_filters = $all_filters;

			return;
		}

		$advads = Advanced_Ads::get_instance();

		// put potential groups in another array which we later reduce so that we only check groups we don’t know, yet.
		$groups_to_check = $this->ads_in_groups;

		foreach ( $posts as $post ) {
			$ad_option = $this->all_ads_options[ $post->ID ];

			/**
			 * Check if this particular ad belongs to a group and if so,
			 * add the group to the list of filterable groups
			 * skip when the group is already known
			 */
			foreach ( $groups_to_check as $key => $ads ) {
				if ( ! isset( $all_filters['all_groups'][ $key ] ) // skip if this group is already known.
					&& in_array( $post->ID, $ads, true )
					&& isset( $this->all_groups[ $key ] ) ) {
					$all_filters['all_groups'][ $key ] = $this->all_groups[ $key ]['name'];
					// remove groups that are already selected for the filter.
					unset( $groups_to_check[ $key ] );
					continue;
				}
			}

			if ( isset( $ad_option['width'], $ad_option['height'] ) && $ad_option['width'] && $ad_option['height'] ) {
				if ( ! array_key_exists( $ad_option['width'] . 'x' . $ad_option['height'], $all_filters['all_sizes'] ) ) {
					$all_filters['all_sizes'][ $ad_option['width'] . 'x' . $ad_option['height'] ] = $ad_option['width'] . ' x ' . $ad_option['height'];
				}
			}

			if ( isset( $ad_option['type'] ) && 'adsense' === $ad_option['type'] ) {
				$content = $this->all_ads[ array_search( $post->ID, wp_list_pluck( $this->all_ads, 'ID' ), true ) ]->post_content;
				try {
					$adsense_obj = json_decode( $content, true );
				} catch ( Exception $e ) {
					$adsense_obj = false;
				}

				if ( $adsense_obj ) {
					if ( 'responsive' === $adsense_obj['unitType'] ) {
						if ( ! array_key_exists( 'responsive', $all_filters['all_sizes'] ) ) {
							$all_filters['all_sizes']['responsive'] = __( 'Responsive', 'advanced-ads' );
						}
					}
				}
			}

			if ( isset( $ad_option['type'] ) // could be missing for new ads that are stored only by WP auto-save.
				&& ! array_key_exists( $ad_option['type'], $all_filters['all_types'] )
				&& isset( $advads->ad_types[ $ad_option['type'] ] )
			) {
				$all_filters['all_types'][ $ad_option['type'] ] = $advads->ad_types[ $ad_option['type'] ]->title;
			}

			$all_filters = apply_filters( 'advanced-ads-ad-list-column-filter', $all_filters, $post, $ad_option );
		}

		$this->all_filters = $all_filters;
	}

	/**
	 * Collects all ads data.
	 *
	 * @param WP_Post[] $posts array of ads.
	 */
	public function collect_all_ads( $posts ) {
		foreach ( $posts as $post ) {
			$this->adsbyid[ $post->ID ]         = $post;
			$this->all_ads_options[ $post->ID ] = get_post_meta( $post->ID, 'advanced_ads_ad_options', true );
			if ( empty( $this->all_ads_options[ $post->ID ] ) ) {
				$this->all_ads_options[ $post->ID ] = [];
			}

			// convert all expiration dates.
			$ad         = \Advanced_Ads\Ad_Repository::get( $post->ID );
			$expiration = new Advanced_Ads_Ad_Expiration( $ad );
			$expiration->save_expiration_date( $this->all_ads_options[ $post->ID ], $ad );
			$expiration->is_ad_expired();
		}

		$this->all_ads = $posts;
	}

	/**
	 * Collects all ads groups, fills the $all_groups class property.
	 */
	private function collect_all_groups() {
		global $wpdb;

		$groups  = [];

		/**
		 * It looks like there might be a third-party conflict we haven’t been able to reproduce that causes the group
		 * objects to stay empty. Hence, we introduced the `empty` check.
		 */
		foreach ( Advanced_Ads::get_instance()->get_model()->get_ad_groups() as $group ) {
			if ( empty( $group->term_id ) ) {
				continue;
			}
			$groups[ $group->term_id ] = [
				'name' => $group->name,
				'slug' => $group->slug,
			];
		}

		$group_ids      = array_keys( $groups );
		$group_ids_str  = implode( ',', $group_ids );
		$term_relations = [];

		/**
		 * We need to use %1$s below, because when using %s the $wpdb->prepare function adds quotation marks around the value,
		 * which breaks the SQL, because the numbers are no longer recognised as such
		 */
		if ( ! empty( $group_ids ) ) {
			$term_relations = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT object_id, term_taxonomy_id FROM `' . $wpdb->prefix . 'term_relationships` WHERE `term_taxonomy_id` IN (' .
					'SELECT term_taxonomy_id FROM `' . $wpdb->prefix . 'term_taxonomy` WHERE `taxonomy` = %s' .
					')',
					Entities::TAXONOMY_AD_GROUP
				),
				'ARRAY_A'
			);
		}
		foreach ( $term_relations as $value ) {
			if ( isset( $value['term_taxonomy_id'] ) && isset( $value['object_id'] ) ) {
				$this->ads_in_groups[ absint( $value['term_taxonomy_id'] ) ][] = absint( $value['object_id'] );
			}
		}

		$this->all_groups = $groups;
	}

	/**
	 * Retrieve the stored ads list.
	 */
	public function get_all_ads() {
		return $this->all_ads;
	}

	/**
	 * Retrieve all filters that can be applied.
	 */
	public function get_all_filters() {
		return $this->all_filters;
	}

	/**
	 * Remove limits because we need to get all ads.
	 *
	 * @param string   $limits The LIMIT clause of the query.
	 * @param WP_Query $the_query the current WP_Query object.
	 * @return string $limits The LIMIT clause of the query.
	 */
	public function limit_filter( $limits, $the_query ) {
		// Execute only in the main query.
		if ( ! $the_query->is_main_query() ) {
			return $limits;
		}

		if ( ! function_exists( 'get_current_screen' ) ) {
			return $limits;
		}

		$scr = get_current_screen();
		// Execute only in the ad list page.
		if ( ! $scr || 'edit-advanced_ads' !== $scr->id ) {
			return $limits;
		}

		return '';
	}

	/**
	 * Edit the query for list table.
	 *
	 * @param array    $posts the posts array from the query.
	 * @param WP_Query $the_query the current WP_Query object.
	 *
	 * @return array with posts
	 */
	public function post_results( $posts, $the_query ) {
		// Execute only in the main query.
		if ( ! function_exists( 'get_current_screen' ) || ! $the_query->is_main_query() ) {
			return $posts;
		}

		$scr = get_current_screen();
		// Execute only in the ad list page.
		if ( ! $scr || 'edit-advanced_ads' !== $scr->id ) {
			return $posts;
		}

		// Searching an ad ID.
		if ( (int) $the_query->query_vars['s'] !== 0 ) {
			global $wpdb;
			$single_ad = ( new Advanced_Ads_Model( $wpdb ) )->get_ads(
				[
					'p'           => (int) $the_query->query_vars['s'],
					'post_status' => [ 'any' ],
				]
			);

			if ( ! empty( $single_ad ) ) {
				// Head to the ad edit page if one and only one ad found.
				if ( empty( $posts ) ) {
					wp_safe_redirect( add_query_arg( [
						'post'   => $single_ad[0]->ID,
						'action' => 'edit',
					], admin_url( 'post.php' ) ) );
					die;
				}

				if ( ! in_array( $single_ad[0]->ID, wp_list_pluck( $posts, 'ID' ), true ) ) {
					$posts[] = $single_ad[0];
				}
			}
		}

		$this->collect_all_ads( $posts );
		$this->collect_all_groups();

		// the new post list.
		if ( isset( $_REQUEST['post_status'] ) && 'trash' === $_REQUEST['post_status'] ) {
			// if looking in trash, return the original trashed posts list.
			$new_posts = $posts;
		} else {
			// in other cases, apply our custom filters.
			$new_posts = $this->ad_filters( $this->all_ads, $the_query );
		}

		$per_page = $the_query->query_vars['posts_per_page'] ? $the_query->query_vars['posts_per_page'] : 20;

		if ( $per_page < count( $new_posts ) ) {
			$paged                  = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
			$total                  = count( $new_posts );
			$new_posts              = array_slice( $new_posts, ( $paged - 1 ) * $per_page, $per_page );
			$the_query->found_posts = $total;
			$the_query->post_count  = count( $new_posts );
		}

		// replace the post list.
		$the_query->posts = $new_posts;

		return $new_posts;
	}

	/**
	 * Apply ad filters on post array
	 *
	 * @param array    $posts the original post array.
	 * @param WP_Query $the_query the current WP_Query object.
	 *
	 * @return array with posts
	 */
	private function ad_filters( $posts, &$the_query ) {
		$using_original = true;
		$request        = wp_unslash( $_REQUEST );

		/**
		 *  Filter post status
		 */
		if ( isset( $request['post_status'] ) && '' !== $request['post_status'] && ! in_array( $request['post_status'], [ 'all', 'trash' ], true ) ) {
			$new_posts = [];
			foreach ( $this->all_ads as $post ) {
				if ( $request['post_status'] === $post->post_status ) {
					$new_posts[] = $post;
				}
			}
			$posts                  = $new_posts;
			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		/**
		 *  Filter post author
		 */
		if ( isset( $request['author'] ) && '' !== $request['author'] ) {
			$author    = absint( $request['author'] );
			$new_posts = [];
			$the_list  = $using_original ? $this->all_ads : $posts;
			foreach ( $the_list as $post ) {
				if ( absint( $post->post_author ) === $author ) {
					$new_posts[] = $post;
				}
			}
			$posts                  = $new_posts;
			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		/**
		 *  Filter groups
		 */
		if ( isset( $request['adgroup'] ) && '' !== $request['adgroup'] ) {
			$new_posts = [];
			$the_list  = $using_original ? $this->all_ads : $posts;
			foreach ( $the_list as $post ) {
				if ( isset( $this->ads_in_groups[ absint( $request['adgroup'] ) ] ) && in_array( $post->ID, $this->ads_in_groups[ absint( $request['adgroup'] ) ], true ) ) {
					$new_posts[] = $post;
				}
			}
			$posts                  = $new_posts;
			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		/**
		 * Filter by taxonomy
		 */
		if ( isset( $request['taxonomy'] ) && isset( $request['term'] ) ) {
			$term = $request['term'];
			global $wpdb;
			$q = 'SELECT `object_id` FROM `' . $wpdb->prefix . 'term_relationships` WHERE `term_taxonomy_id` = (' .
				 'SELECT ' . $wpdb->prefix . 'terms.term_id FROM `' . $wpdb->prefix . 'terms` INNER JOIN ' .
				 $wpdb->prefix . 'term_taxonomy on ' . $wpdb->prefix . 'terms.term_id = ' . $wpdb->prefix . 'term_taxonomy.term_id ' .
				 'WHERE ' . $wpdb->prefix . 'terms.slug = %s AND ' . $wpdb->prefix . 'term_taxonomy.taxonomy = %s' .
				 ')';

			$q = $wpdb->prepare( $q, $term, Entities::TAXONOMY_AD_GROUP );

			$object_ids  = $wpdb->get_results( $q, 'ARRAY_A' );
			$ads_in_taxo = [];

			foreach ( $object_ids as $object ) {
				$ads_in_taxo[] = absint( $object['object_id'] );
			}

			$new_posts = [];
			$the_list  = $using_original ? $this->all_ads : $posts;
			foreach ( $the_list as $post ) {
				if ( in_array( $post->ID, $ads_in_taxo, true ) ) {
					$new_posts[] = $post;
				}
			}
			$posts                  = $new_posts;
			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		/**
		 * Filter ad type
		 */
		if ( isset( $request['adtype'] ) && '' !== $request['adtype'] ) {
			$new_posts = [];
			$the_list  = $using_original ? $this->all_ads : $posts;
			foreach ( $the_list as $post ) {
				$option = $this->all_ads_options[ $post->ID ];
				if ( isset( $option['type'] ) && $request['adtype'] === $option['type'] ) {
					$new_posts[] = $post;
				}
			}
			$posts                  = $new_posts;
			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		/**
		 * Filter ad size
		 */
		if ( isset( $request['adsize'] ) && '' !== $request['adsize'] ) {
			$new_posts = [];
			$the_list  = $using_original ? $this->all_ads : $posts;
			foreach ( $the_list as $post ) {
				$option = $this->all_ads_options[ $post->ID ];
				if ( $request['adsize'] === 'responsive' ) {
					if ( isset( $option['type'] ) && $option['type'] === 'adsense' ) {
						$content = false;
						try {
							$content = json_decode( $post->post_content, true );
						} catch ( Exception $e ) {
							$content = false;
						}
						if ( $content && $content['unitType'] === 'responsive' ) {
							$new_posts[] = $post;
						}
					}
				} else {
					$width  = isset( $option['width'] ) ? $option['width'] : 0;
					$height = isset( $option['height'] ) ? $option['height'] : 0;
					if ( $request['adsize'] === $width . 'x' . $height ) {
						$new_posts[] = $post;
					}
				}
			}
			$posts                  = $new_posts;
			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		if ( isset( $request['addate'] ) ) {
			$filter_value = urldecode( $request['addate'] );
			if ( in_array( $filter_value, [ 'advads-filter-expired', 'advads-filter-expiring' ], true ) ) {
				$posts = $this->filter_expired_ads( $filter_value, $using_original ? $this->all_ads : $posts );
			}
		}

		$posts                  = apply_filters( 'advanced-ads-ad-list-filter', $posts, $this->all_ads_options );
		$the_query->found_posts = count( $posts );

		$this->collect_filters( $posts );

		return $posts;
	}

	/**
	 * Return the instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * If there are expired ads, add an expired view.
	 *
	 * @param array $views currently available views.
	 *
	 * @return array
	 */
	public function add_expired_view( $views ) {
		$count = $this->count_expired_ads();
		if ( empty( $count ) ) {
			return $views;
		}
		$views[ Advanced_Ads_Ad_Expiration::POST_STATUS ] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			add_query_arg( [
				'post_type' => Entities::POST_TYPE_AD,
				'addate'    => 'advads-filter-expired',
				'orderby'   => 'expiry_date',
				'order'     => 'DESC',
			], 'edit.php' ),
			isset( $_REQUEST['addate'] ) && $_REQUEST['addate'] === 'advads-filter-expired' ? 'class="current" aria-current="page"' : '',
			esc_attr_x( 'Expired', 'Post list header for expired ads.', 'advanced-ads' ),
			$count
		);

		return array_replace( array_intersect_key( $this->views_order(), $views ), $views );
	}

	/**
	 * Get the number of ads that have expired.
	 *
	 * @return int
	 */
	private function count_expired_ads() {
		return ( new WP_Query( [
			'post_type'   => Entities::POST_TYPE_AD,
			'post_status' => Advanced_Ads_Ad_Expiration::POST_STATUS,
		] ) )->found_posts;
	}

	/**
	 * If there are ads with an expiration date in the future, add an expiring view.
	 *
	 * @param array $views currently available views.
	 *
	 * @return array
	 */
	public function add_expiring_view( $views ) {
		$count = $this->count_expiring_ads();
		if ( empty( $count ) ) {
			return $views;
		}
		$views['expiring'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			add_query_arg( [
				'post_type' => Entities::POST_TYPE_AD,
				'addate'    => 'advads-filter-expiring',
				'orderby'   => 'expiry_date',
				'order'     => 'ASC',
			], 'edit.php' ),
			isset( $_REQUEST['addate'] ) && $_REQUEST['addate'] === 'advads-filter-expiring' ? 'class="current" aria-current="page"' : '',
			esc_attr_x( 'Expiring', 'Post list header for ads expiring in the future.', 'advanced-ads' ),
			$count
		);

		return array_replace( array_intersect_key( $this->views_order(), $views ), $views );
	}

	/**
	 * Get the number of ads that have an expiration date in the future.
	 *
	 * @return int
	 */
	private function count_expiring_ads() {
		return ( new WP_Query( [
			'post_type'   => Entities::POST_TYPE_AD,
			'post_status' => 'any',
			'meta_query'  => [
				[
					'key'     => Advanced_Ads_Ad_Expiration::POST_META,
					'value'   => current_time( 'mysql', true ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				],
			],
		] ) )->found_posts;
	}

	/**
	 * Our expected order of views.
	 *
	 * @return string[]
	 */
	private function views_order() {
		static $views_order;
		if ( $views_order === null ) {
			$views_order = array_flip( [ 'all', 'mine', 'publish', 'future', 'expiring', Advanced_Ads_Ad_Expiration::POST_STATUS, 'draft', 'pending', 'trash' ] );
		}

		return $views_order;
	}

	/**
	 * Filter by expiring or expired ads.
	 *
	 * @param string    $filter The current filter name, expired or expiring.
	 * @param WP_Post[] $posts  The array of posts.
	 *
	 * @return WP_Post[]
	 */
	private function filter_expired_ads( $filter, $posts ) {
		$now = time();

		return array_filter( $posts, function( WP_Post $post ) use ( $now, $filter ) {
			$option = $this->all_ads_options[ $post->ID ];
			if ( empty( $option['expiry_date'] ) ) {
				return false;
			}

			return (
				// filter by ads already expired.
				( $filter === 'advads-filter-expired' && $option['expiry_date'] <= $now )
				// filter by ads expiring in the future.
				|| ( $filter === 'advads-filter-expiring' && $option['expiry_date'] > $now )
			);
		} );
	}

	/**
	 * Displays the list of views available for Ads.
	 */
	public function ad_views() {
		global $wp_list_table;

		if ( ! $this->is_ad_list_screen() ) {
			return;
		}

		// unregister the hook to prevent the navigation to appear again below the footer
		remove_action( 'manage_posts_extra_tablenav', [ $this, 'ad_views' ] );

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
		$views_new = [];

		$is_all = count( array_diff_key( $_GET, [
			'post_type' => Entities::POST_TYPE_AD,
			'orderby'   => '',
			'order'     => '',
			'paged'     => '',
			'mode'      => '',
		] ) ) === 0;

		foreach ( $views as $class => $view ) {
			$view                = str_replace( [ ')', '(' ], [ '', '' ], $view );
			$class              .= strpos( $view, 'current' ) ? ' advads-button-primary' : ' advads-button-secondary';
			$views_new[ $class ] = $view;
		}

		$show_trash_delete_button = isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'] && have_posts() && current_user_can( get_post_type_object( $wp_list_table->screen->post_type )->cap->edit_others_posts );

		include ADVADS_ABSPATH . 'admin/views/ad-list/view-list.php';
	}

	/**
	 * Custom scripts and styles for the ad list page
	 *
	 * @return void
	 */
	public function ad_list_scripts() {
		if ( ! $this->is_ad_list_screen() ) {
			return;
		}

		// show label before the search form if this is a search
		if ( ! empty( $_GET['s'] ) ) {
			wp_add_inline_style( ADVADS_SLUG . '-admin', "
				.post-type-advanced_ads .search-box:before { content: '" . esc_html__( 'Showing search results for', 'advanced-ads' ) . "'; float: left; margin-right: 8px; line-height: 30px; font-weight: 700; }
				.post-type-advanced_ads .subtitle { display: none; }
			" );
		}

		// adjust search form when there are no results
		if ( Conditional::has_filter_or_search() && 0 === count( $this->all_ads ) ) {
			wp_add_inline_style( ADVADS_SLUG . '-admin', '.post-type-advanced_ads .search-box { display: block; margin-top: 10px; }' );
			return;
		}

		// show filters, if the option to show them is enabled or a search is running
		if ( get_current_screen()->get_option( 'show-filters' ) || Conditional::has_filter_or_search() ) {
			global $wp_query;
			wp_add_inline_style( ADVADS_SLUG . '-admin', '.post-type-advanced_ads .search-box { display: block; }' );
			if (isset($wp_query->found_posts) && $wp_query->found_posts > 0) {
				wp_add_inline_style(ADVADS_SLUG . '-admin', '.post-type-advanced_ads .tablenav.top .alignleft.actions:not(.bulkactions) { display: block; }' );
			}
			return;
		}

		wp_add_inline_script( ADVADS_SLUG . '-admin', "
			jQuery( document ).ready( function ( $ ) {
				$( '#advads-show-filters' ).on( 'click', function() {
					const disabled = $( this ).find( '.dashicons' ).hasClass('dashicons-arrow-up');
					$( '.post-type-advanced_ads .search-box' ).toggle(!disabled);
					$( '.post-type-advanced_ads .tablenav.top .alignleft.actions:not(.bulkactions)' ).toggle(!disabled);
					$( '#advads-show-filters .dashicons' ).toggleClass( 'dashicons-filter', disabled );
					$( '#advads-show-filters .dashicons' ).toggleClass( 'dashicons-arrow-up', ! disabled );
				});
			});
		" );
	}

	/**
	 * If there is an addate dimension, add it to the filter.
	 * This ensures that the "view" buttons for expiring and expired ads
	 * maintain the `.current` class.
	 *
	 * @return void
	 */
	public function send_addate_in_filter() {
		if (
			! isset( $_GET['post_type'] )
			|| $_GET['post_type'] !== Entities::POST_TYPE_AD
			|| empty( $_GET['addate'] )
		) {
			return;
		}

		printf( '<input type="hidden" name="addate" value="%s">', esc_attr( $_GET['addate'] ) );
	}
}
