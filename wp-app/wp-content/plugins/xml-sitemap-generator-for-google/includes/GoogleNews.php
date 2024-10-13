<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\QueryBuilder;

class GoogleNews extends Sitemap {
	public static $template = 'google-news';

	private $blog_language = null;

	/**
	 * Add URLS Callback function
	 */
	public function urlsCallback() {
		return 'addNewsUrl';
	}

	/**
	 * Adding Google News Sitemap Headers
	 */
	public function extraSitemapHeader() {
		return array( 'xmlns:news' => 'http://www.google.com/schemas/sitemap-news/0.9' );
	}

	/**
	 * Collect Sitemap URLs
	 */
	public function collect_urls( $template = 'sitemap', $inner_sitemap = null, $current_page = null ) {
		$this->add_posts();
	}

	/**
	 * Add all Posts to Sitemap
	 */
	public function add_posts( $post_type = null, $current_page = null, $is_sitemap_index = false ) {
		global $wpdb;

		$front_page_id    = get_option( 'page_on_front' );
		$post_types       = array( 'page', 'post' );
		$exclude_post_ids = apply_filters( 'sgg_sitemap_exclude_ids', array(), $this->settings->google_news_exclude ?? '' );
		$exclude_term_ids = apply_filters( 'sgg_sitemap_exclude_ids', array(), $this->settings->google_news_exclude_terms ?? '' );

		if ( ! empty( $front_page_id ) ) {
			$exclude_post_ids[] = $front_page_id;
		}

		foreach ( $post_types as $key => $post_type ) {
			if ( isset( $this->settings->{$post_type}->google_news ) && ! $this->settings->{$post_type}->google_news ) {
				unset( $post_types[ $key ] );
			}
		}

		if ( sgg_pro_enabled() ) {
			foreach ( $this->get_cpt() as $cpt ) {
				if ( ! empty( $this->settings->cpt[ $cpt ] ) && ! empty( $this->settings->cpt[ $cpt ]->google_news ) ) {
					$post_types[] = $cpt;
				}
			}
		}

		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}

		$exclude_old_posts_sql = '';
		if ( empty( $this->settings->google_news_old_posts ) ) {
			$exclude_old_posts_sql = 'AND post_date_gmt >= DATE_SUB(NOW(), INTERVAL 48 HOUR)';
		}

		$exclude_posts_sql = '';
		if ( ! empty( $exclude_post_ids ) ) {
			$exclude_posts_sql = 'AND posts.ID NOT IN (' . implode( ',', array_unique( $exclude_post_ids ) ) . ')';
		}

		$exclude_terms_join = '';
		$exclude_terms_sql  = '';
		if ( ! empty( $exclude_term_ids ) ) {
			$exclude_terms_join = "LEFT JOIN (
				SELECT DISTINCT tr.object_id
				FROM {$wpdb->prefix}term_relationships tr
				INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				WHERE tt.term_id IN (" . implode( ',', array_unique( $exclude_term_ids ) ) . ')
			) excluded_posts ON posts.ID = excluded_posts.object_id';
			$exclude_terms_sql  = ' AND excluded_posts.object_id IS NULL';
		}

		$sql_post_types   = "('" . implode( "','", $post_types ) . "')";
		$multilingual_sql = $this->multilingual_sql( $post_types );
		$where_clause     = ! empty( $multilingual_sql ) ? 'AND ' : 'WHERE ';
		$sql              = "SELECT
	            posts.ID,
				posts.post_title,
				posts.post_name,
				posts.post_parent,
				posts.post_type,
				posts.post_date,
				posts.post_date_gmt,
				posts.comment_count
				FROM $wpdb->posts as posts
				$exclude_terms_join
				$multilingual_sql
				$where_clause post_status = 'publish' AND post_type IN $sql_post_types AND posts.post_password = ''
				$exclude_old_posts_sql
				$exclude_posts_sql
				$exclude_terms_sql
				GROUP BY posts.ID
				ORDER BY posts.post_modified DESC";

		$posts = QueryBuilder::run_query( $sql );

		foreach ( $posts as $post ) {
			if ( apply_filters( 'xml_sitemap_include_post', true, $post->ID ) ) {
				$post_date = '0000-00-00 00:00:00' !== $post->post_date_gmt ? $post->post_date_gmt : $post->post_date;
				$this->add_url(
					get_permalink( $post ),
					$post->ID,
					$post->post_title,
					gmdate( DATE_W3C, strtotime( $post_date ) ),
					$post->post_type
				);
			}
		}
	}

	/**
	 * Add Google News Sitemap Url
	 *
	 * @param string $url
	 * @param int $id
	 * @param string $title
	 * @param string $last_modified
	 * @param string $post_type
	 */
	public function add_url( $url, $id, $title, $last_modified = '', $post_type = 'post' ) {
		$this->urls[] = array(
			$url, // URL
			! empty( $this->settings->google_news_name ) ? $this->settings->google_news_name : get_bloginfo( 'name' ), // Publication Name
			apply_filters( 'xml_sitemap_news_language', $this->get_blog_language(), $id, $post_type ), // Publication Language
			$title, // Title
			$last_modified, // Last Modified
			$id, // ID
		);
	}

	/**
	 * Get Blog Language
	 */
	public function get_blog_language() {
		if ( null === $this->blog_language ) {
			$this->blog_language = sgg_parse_language( get_bloginfo( 'language' ) );
		}

		return $this->blog_language;
	}

	public static function is_older_than_48h( $date ) {
		$accepted_time = time() - ( 48 * 3600 );
		$last_modified = strtotime( $date );

		return $last_modified < $accepted_time;
	}
}
