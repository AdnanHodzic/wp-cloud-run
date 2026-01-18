<?php

namespace SPC\Services;

use SPC\Models\Asset_Rules;
use SPC\Modules\Assets_Manager;

/**
 * Service class for handling asset exclusions in the assets manager.
 */
class Asset_Exclusion_Handler {
	/**
	 * Get other context exclusions for assets.
	 *
	 * This method analyzes existing asset rules and returns information about
	 * other pages/contexts where assets are disabled, excluding the current page.
	 *
	 * @return array Array of exclusions organized by asset hash
	 */
	public function get_other_context_exclusions() {
		$results                = Asset_Rules::get_asset_rules();
		$data                   = [];
		$current_queried_object = get_queried_object();

		foreach ( $results as $result ) {
			$asset_rules = json_decode( $result->rules, true );
			$exclusions  = [];

			foreach ( $asset_rules as $rule ) {
				$exclusion = $this->get_exclusion_for_rule( $rule, $current_queried_object );
				if ( $exclusion ) {
					$exclusions[] = $exclusion;
				}
			}

			if ( ! empty( $exclusions ) ) {
				$data[ $result->asset_hash ] = $exclusions;
			}
		}

		// Add asset manager query var to all exclusion URLs
		foreach ( $data as $asset_hash => $exclusions ) {
			foreach ( $exclusions as $idx => $exclusion ) {
				$data[ $asset_hash ][ $idx ] = [
					'url'   => add_query_arg( Assets_Manager::ASSETS_MANAGER_QUERY_VAR, 'yes', $exclusion['url'] ),
					'label' => $exclusion['label'],
				];
			}
		}

		return $data;
	}

	/**
	 * Dispatch rule to the appropriate exclusion handler.
	 *
	 * @param string $rule
	 * @param object|null $current_queried_object
	 * @return array|null
	 */
	private function get_exclusion_for_rule( $rule, $current_queried_object ) {
		if ( strpos( $rule, 'is_singular:id:' ) !== false ) {
			return $this->handle_singular_id( $rule );
		}
		if ( strpos( $rule, 'is_singular:post_type:' ) !== false ) {
			return $this->handle_singular_post_type( $rule );
		}
		if ( strpos( $rule, 'is_tax:id:' ) !== false ) {
			return $this->handle_tax_id( $rule, $current_queried_object );
		}
		if ( strpos( $rule, 'is_tax:taxonomy:' ) !== false ) {
			return $this->handle_tax_taxonomy( $rule, $current_queried_object );
		}
		if ( strpos( $rule, 'is_author:id:' ) !== false ) {
			return $this->handle_author_id( $rule, $current_queried_object );
		}
		if ( $rule === 'is_author:all' ) {
			return $this->handle_author_all();
		}
		if ( strpos( $rule, 'is_date:' ) !== false ) {
			return $this->handle_date( $rule );
		}
		if ( strpos( $rule, 'is_search:' ) !== false ) {
			return $this->handle_search();
		}
		if ( $rule === 'is_404:all' ) {
			return $this->handle_404();
		}
		if ( $rule === 'is_front_page:true' ) {
			return $this->handle_front_page();
		}
		if ( $rule === 'is_home:true' ) {
			return $this->handle_home();
		}
		return null;
	}

	private function handle_singular_id( $rule ) {
		$id = (int) str_replace( 'is_singular:id:', '', $rule );
		if ( $id === get_the_ID() ) {
			return null;
		}
		return [
			'url'   => get_permalink( $id ),
			'label' => sprintf( '%s (%s)', get_the_title( $id ), get_post_type( $id ) ),
		];
	}

	private function handle_singular_post_type( $rule ) {
		$post_type = str_replace( 'is_singular:post_type:', '', $rule );
		if ( get_post_type() === $post_type ) {
			return null;
		}
		$posts = get_posts(
			[
				'post_type'   => $post_type,
				'numberposts' => 1,
			]
		);
		if ( empty( $posts ) ) {
			return null;
		}
		return [
			'url'   => get_permalink( $posts[0]->ID ),
			'label' => sprintf( 'Single %s', get_post_type_object( $post_type )->labels->name ),
		];
	}

	private function handle_tax_id( $rule, $current_queried_object ) {
		$term_id = (int) str_replace( 'is_tax:id:', '', $rule );
		if ( $current_queried_object && $current_queried_object->term_id === $term_id ) {
			return null;
		}
		$excluded_taxonomy = get_term( $term_id );
		if ( ! $excluded_taxonomy || is_wp_error( $excluded_taxonomy ) ) {
			return null;
		}
		return [
			'url'   => get_term_link( $excluded_taxonomy->term_id, $excluded_taxonomy->taxonomy ),
			'label' => sprintf( '%s (%s)', $excluded_taxonomy->name, $excluded_taxonomy->taxonomy ),
		];
	}

	private function handle_tax_taxonomy( $rule, $current_queried_object ) {
		$taxonomy = str_replace( 'is_tax:taxonomy:', '', $rule );
		if ( $current_queried_object && $current_queried_object->taxonomy === $taxonomy ) {
			return null;
		}
		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			]
		);
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}
		return [
			'url'   => get_term_link( $terms[0]->term_id, $taxonomy ),
			'label' => sprintf( '%s (%s)', $taxonomy, __( 'Taxonomy Archive', 'wp-cloudflare-page-cache' ) ),
		];
	}

	private function handle_author_id( $rule, $current_queried_object ) {
		$author_id = (int) str_replace( 'is_author:id:', '', $rule );
		if ( $current_queried_object && $author_id === $current_queried_object->ID ) {
			return null;
		}
		return [
			'url'   => get_author_posts_url( $author_id ),
			'label' => sprintf( '%s (%s)', get_the_author_meta( 'display_name', $author_id ), __( 'Author Archive', 'wp-cloudflare-page-cache' ) ),
		];
	}

	private function handle_author_all() {
		if ( is_author() ) {
			return null;
		}
		return [
			'url'   => get_author_posts_url( get_current_user_id() ),
			'label' => __( 'All Author Archives', 'wp-cloudflare-page-cache' ),
		];
	}

	private function handle_date( $rule ) {
		if ( is_date() ) {
			return null;
		}
		$date = str_replace( 'is_date:', '', $rule );
		if ( $date === 'all' ) {
			$posts = get_posts(
				[
					'numberposts' => 1,
					'orderby'     => 'date',
					'order'       => 'ASC',
					'post_type'   => 'post',
					'post_status' => 'publish',
				]
			);
			if ( empty( $posts ) ) {
				return null;
			}
			$post_date = $posts[0]->post_date;
		} else {
			$post_date = $date;
		}
		$timestamp = strtotime( $post_date );
		if ( $timestamp === false ) {
			return null;
		}
		list($year, $month, $day) = array_map( 'intval', explode( '-', gmdate( 'Y-n-j', $timestamp ) ) );
		return [
			'url'   => get_day_link( $year, $month, $day ),
			'label' => $date === 'all' ? __( 'All Date Archives', 'wp-cloudflare-page-cache' ) : sprintf( '%s (%s)', $date, __( 'Date Archive', 'wp-cloudflare-page-cache' ) ),
		];
	}

	private function handle_search() {
		if ( is_search() ) {
			return null;
		}
		return [
			'url'   => get_search_link(),
			'label' => __( 'Search Results Page', 'wp-cloudflare-page-cache' ),
		];
	}

	private function handle_404() {
		if ( is_404() ) {
			return null;
		}
		return [
			'url'   => home_url() . '/spc-404-page-test',
			'label' => __( '404 Page', 'wp-cloudflare-page-cache' ),
		];
	}

	private function handle_front_page() {
		if ( is_front_page() ) {
			return null;
		}

		if ( get_option( 'show_on_front' ) !== 'page' ) {
			return null;
		}

		return [
			'url'   => get_option( 'page_on_front' ) ? get_permalink( get_option( 'page_on_front' ) ) : home_url(),
			'label' => __( 'Front Page', 'wp-cloudflare-page-cache' ),
		];
	}


	private function handle_home() {
		if ( is_home() ) {
			return null;
		}

		// Handle blog page when front page is set to a static page
		if ( get_option( 'show_on_front' ) === 'page' && get_option( 'page_for_posts' ) ) {
			return [
				'url'   => get_permalink( get_option( 'page_for_posts' ) ),
				'label' => __( 'Blog Home Page', 'wp-cloudflare-page-cache' ),
			];
		}

		// Handle regular posts page (when show_on_front is 'posts')
		if ( get_option( 'show_on_front' ) === 'posts' ) {
			return [
				'url'   => home_url(),
				'label' => __( 'Blog Home Page', 'wp-cloudflare-page-cache' ),
			];
		}

		return [
			'url'   => home_url(),
			'label' => __( 'Home Page', 'wp-cloudflare-page-cache' ),
		];
	}
}
