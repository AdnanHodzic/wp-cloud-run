<?php
/**
 * Container class for custom filters on admin ad list page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 */

namespace AdvancedAds\Admin;

use WP_Post;
use Exception;
use AdvancedAds\Constants;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class List_Filters
 */
class List_Filters implements Integration_Interface {
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
	 * All filters available in the current ad list table
	 *
	 * @var array
	 */
	protected $all_filters = [];

	/**
	 * All ad options for the ad list table
	 *
	 * @var     array
	 */
	protected $all_ads_options = [];

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'posts_results', [ $this, 'post_results' ], 10, 2 );
		add_filter( 'post_limits', [ $this, 'limit_filter' ], 10, 2 );
	}

	/**
	 * Collect available filters for ad overview page.
	 *
	 * @param array $posts array of ads.
	 *
	 * @return void
	 */
	private function collect_filters( $posts ): void {
		$all_filters = [
			'all_sizes'   => [],
			'all_types'   => [],
			'all_dates'   => [],
			'all_groups'  => [],
			'all_authors' => $this->collect_authors(),
		];

		// Can not filter correctly with "trashed" posts. Do not display any filtering option in this case.
		if ( Params::request( 'post_status' ) === 'trash' ) {
			$this->all_filters = $all_filters;
			return;
		}

		$groups_to_check = $this->ads_in_groups;

		foreach ( $posts as $post ) {
			$ad_option = $this->all_ads_options[ $post->ID ];

			foreach ( $groups_to_check as $key => $ads ) {
				if ( ! isset( $all_filters['all_groups'][ $key ] ) // skip if this group is already known.
					&& isset( $this->all_groups[ $key ] ) ) {
					$all_filters['all_groups'][ $key ] = $this->all_groups[ $key ]['name'];
					// remove groups that are already selected for the filter to reduce loop items next time.
					unset( $groups_to_check[ $key ] );
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

			if (
				isset( $ad_option['type'] ) // could be missing for new ads that are stored only by WP auto-save.
				&& ! array_key_exists( $ad_option['type'], $all_filters['all_types'] )
				&& wp_advads_has_ad_type( $ad_option['type'] )
			) {
				$all_filters['all_types'][ $ad_option['type'] ] = wp_advads_get_ad_type( $ad_option['type'] )->get_title();
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
			$this->all_ads_options[ $post->ID ] = get_post_meta( $post->ID, 'advanced_ads_ad_options', true );
			if ( empty( $this->all_ads_options[ $post->ID ] ) ) {
				$this->all_ads_options[ $post->ID ] = [];
			}
		}

		$this->all_ads = $posts;
	}

	/**
	 * Collects all ads groups, fills the $all_groups class property.
	 */
	private function collect_all_groups() {
		global $wpdb;

		$groups = wp_advads_get_all_groups();

		foreach ( $groups as $group ) {
			$group_id   = $group->get_id();
			$ad_weights = $group->get_ad_weights();
			if ( ! empty( $ad_weights ) ) {
				$groups[ $group_id ] = [ 'name' => $group->get_name() ];
				foreach ( $ad_weights as $ad_id => $weight ) {
					$this->ads_in_groups[ $group_id ][] = $ad_id;
				}
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

		$screen = get_current_screen();
		// Execute only in the ad list page.
		if ( ! $screen || 'edit-advanced_ads' !== $screen->id ) {
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
	public function post_results( $posts, $the_query ): array {
		// Execute only in the main query.
		if ( ! function_exists( 'get_current_screen' ) || ! $the_query->is_main_query() ) {
			return $posts;
		}

		$screen = get_current_screen();
		// Execute only in the ad list page.
		if ( ! $screen || 'edit-advanced_ads' !== $screen->id ) {
			return $posts;
		}

		// Searching an ad ID.
		if ( 0 !== (int) $the_query->query_vars['s'] ) {
			$single_ad = wp_advads_ad_query(
				[
					'p'           => (int) $the_query->query_vars['s'],
					'post_status' => [ 'any' ],
				]
			)->posts;

			if ( ! empty( $single_ad ) ) {
				// Head to the ad edit page if one and only one ad found.
				$redirect = add_query_arg(
					[
						'post'   => $single_ad[0]->ID,
						'action' => 'edit',
					],
					admin_url( 'post.php' )
				);
				if ( empty( $posts ) && wp_safe_redirect( $redirect ) ) {
					exit;
				}

				if ( ! in_array( $single_ad[0]->ID, wp_list_pluck( $posts, 'ID' ), true ) ) {
					$posts[] = $single_ad[0];
				}
			}
		}

		$this->collect_all_ads( $posts );
		$this->collect_all_groups();

		$new_posts = Params::request( 'post_status' ) === 'trash'
			? $posts
			: $this->ad_filters( $this->all_ads, $the_query );

		$per_page = $the_query->query_vars['posts_per_page'] ? $the_query->query_vars['posts_per_page'] : 20;

		if ( $per_page < count( $new_posts ) ) {
			$paged                  = Params::request( 'paged', 1, FILTER_VALIDATE_INT );
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
		global $wpdb;

		$using_original = true;
		$request        = wp_unslash( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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
				if ( isset( $this->ads_in_groups[ absint( $request['adgroup'] ) ] ) &&
					in_array( $post->ID, $this->ads_in_groups[ absint( $request['adgroup'] ) ], true ) ) {
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
			$term  = $request['term'];
			$query = "SELECT object_id
				FROM {$wpdb->term_relationships}
          		WHERE term_taxonomy_id = (
					SELECT terms.term_id
					FROM {$wpdb->terms} AS terms
					INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy
					ON terms.term_id = term_taxonomy.term_id
					WHERE terms.slug = %s AND term_taxonomy.taxonomy = %s
				)";

			$object_ids      = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare( $query, $term, Constants::TAXONOMY_GROUP ), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				'ARRAY_A'
			);
			$ads_in_taxonomy = [];

			foreach ( $object_ids as $object ) {
				$ads_in_taxonomy[] = absint( $object['object_id'] );
			}

			$new_posts = [];
			$the_list  = $using_original ? $this->all_ads : $posts;
			foreach ( $the_list as $post ) {
				if ( in_array( $post->ID, $ads_in_taxonomy, true ) ) {
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
		 * Filter debug mode
		 */
		$debugmode = Params::request( 'ad_debugmode', false );
		if ( $debugmode ) {
			$posts = array_filter(
				$using_original ? $this->all_ads : $posts,
				function ( $post ) use ( $debugmode ) {
					$option = $this->all_ads_options[ $post->ID ]['debugmode'] ?? '';
					return ( 'yes' === $debugmode && ! empty( $option ) ) || ( 'no' === $debugmode && empty( $option ) );
				}
			);

			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		/**
		 * Filter displayonce
		 */
		$displayonce = Params::request( 'ad_displayonce', false );
		if ( $displayonce ) {
			$posts = array_filter(
				$using_original ? $this->all_ads : $posts,
				function ( $post ) use ( $displayonce ) {
					$option = $this->all_ads_options[ $post->ID ]['once_per_page'] ?? '';
					return ( 'yes' === $displayonce && ! empty( $option ) ) || ( 'no' === $displayonce && empty( $option ) );
				}
			);

			$the_query->found_posts = count( $posts );
			$using_original         = false;
		}

		/**
		 * Filter privacyignore
		 */
		$privacyignore = Params::request( 'ad_privacyignore', false );
		if ( $privacyignore ) {
			$posts = array_filter(
				$using_original ? $this->all_ads : $posts,
				function ( $post ) use ( $privacyignore ) {
					$option = $this->all_ads_options[ $post->ID ]['privacy']['ignore-consent'] ?? '';
					return ( 'yes' === $privacyignore && ! empty( $option ) ) || ( 'no' === $privacyignore && empty( $option ) );
				}
			);

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
				if ( 'responsive' === $request['adsize'] ) {
					if ( isset( $option['type'] ) && 'adsense' === $option['type'] ) {
						$content = false;
						try {
							$content = json_decode( $post->post_content, true );
						} catch ( Exception $e ) {
							$content = false;
						}
						if ( $content && 'responsive' === $content['unitType'] ) {
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
	 * Filter by expiring or expired ads.
	 *
	 * @param string    $filter The current filter name, expired or expiring.
	 * @param WP_Post[] $posts  The array of posts.
	 *
	 * @return WP_Post[]
	 */
	private function filter_expired_ads( $filter, $posts ) {
		$now = time();

		return array_filter(
			$posts,
			function ( WP_Post $post ) use ( $now, $filter ) {
				$option = $this->all_ads_options[ $post->ID ];
				if ( empty( $option['expiry_date'] ) ) {
					return false;
				}

				$is_expired = 'advads-filter-expired' === $filter && $option['expiry_date'] <= $now;
				$in_future  = 'advads-filter-expiring' === $filter && $option['expiry_date'] > $now;

				return $is_expired || $in_future;
			}
		);
	}

	/**
	 * Author filter dropdown data.
	 *
	 * @return array An associative array of authors, keys are the author IDs and values are the author display names.
	 */
	private function collect_authors(): array {
		$ads     = wp_advads_get_all_ads();
		$authors = [];

		foreach ( $ads as $ad ) {
			if ( ! isset( $authors[ $ad->get_author_id() ] ) ) {
				$authors[ $ad->get_author_id() ] = get_the_author_meta( 'display_name', $ad->get_author_id() );
			}
		}

		return $authors;
	}
}
