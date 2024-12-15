<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;
use GRIM_SG\Vendor\QueryBuilder;
use GRIM_SG\Vendor\SitemapGenerator;

class Sitemap extends Controller {
	public static $template = 'sitemap';

	protected $urls = array();

	protected $settings;

	public function __construct() {
		$this->settings = $this->get_settings();
	}

	/**
	 * Generate Sitemap
	 */
	public function show_sitemap( $template, $is_xml = true, $inner_sitemap = null, $current_page = null ) {
		if ( sgg_is_sitemap_index( $template, $this->settings ) && ! empty( $inner_sitemap ) ) {
			$template = 'inner-sitemap';
		}

		$sitemap = $this->generate_sitemap( $template, $is_xml, $inner_sitemap, $current_page );

		try {
			$sitemap->outputSitemap( $template, $is_xml, $inner_sitemap );
		} catch ( \Exception $exc ) {
			echo $exc->getTraceAsString(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Generate Sitemap
	 */
	public function generate_sitemap( $template = 'sitemap', $is_xml = true, $inner_sitemap = null, $current_page = null ) {
		remove_all_filters( 'pre_get_posts' );

		$sitemap = new SitemapGenerator( sgg_get_home_url() );

		if ( $this->settings->enable_cache ) {
			$cache = new Cache( $template, $inner_sitemap, $current_page );
			$urls  = $cache->get();

			if ( $urls ) {
				$this->urls = $urls;
			} else {
				$this->collect_urls( $template, $inner_sitemap, $current_page );
				$cache->set( $this->urls );
			}
		} else {
			$this->collect_urls( $template, $inner_sitemap, $current_page );
		}

		$sitemap->addUrls( apply_filters( 'sgg_sitemap_urls', $this->urls ), $this->urlsCallback(), $template );

		try {
			$sitemap->createSitemap( $template, $this->extraSitemapHeader(), $is_xml );
		} catch ( \Exception $exc ) {
			echo $exc->getTraceAsString(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $sitemap;
	}

	/**
	 * Generate Sitemap XSL Template
	 */
	public static function generate_sitemap_xsl( $template = 'sitemap' ) {
		ob_get_clean();

		header( 'Content-Type: text/xsl; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );

		ob_start();

		global $wp_version;

		if ( version_compare( $wp_version, '5.5.0', '<' ) ) {
			set_query_var( 'args', array( 'template' => $template ) );
		}

		load_template(
			GRIM_SG_PATH . '/templates/xsl/sitemap.php',
			false,
			compact( 'template' )
		);

		ob_end_flush();
	}

	/**
	 * Get Sitemap Table by Template
	 */
	public static function get_sitemap_table( $template = 'sitemap' ) {
		switch ( $template ) {
			case GoogleNews::$template:
				$table = 'google-news';
				break;
			case ImageSitemap::$template:
				$table = 'image-sitemap';
				break;
			case VideoSitemap::$template:
				$table = 'video-sitemap';
				break;
			case MultilingualSitemap::$template:
				$table = 'sitemap-index';
				break;
			case 'sitemap-index':
				$table = 'sitemap-index';
				break;
			default:
				$table = 'sitemap';
				break;
		}

		load_template( GRIM_SG_PATH . "/templates/xsl/tables/{$table}.php", false );
	}

	/**
	 * Get Sitemap Title by Template
	 */
	public static function get_sitemap_title( $template ) {
		$title = __( 'Sitemap', 'xml-sitemap-generator-for-google' );

		switch ( $template ) {
			case \GRIM_SG\GoogleNews::$template:
				$title = __( 'Google News', 'xml-sitemap-generator-for-google' );
				break;
			case \GRIM_SG\ImageSitemap::$template:
				$title = __( 'Image Sitemap', 'xml-sitemap-generator-for-google' );
				break;
			case \GRIM_SG\VideoSitemap::$template:
				$title = __( 'Video Sitemap', 'xml-sitemap-generator-for-google' );
				break;
			case \GRIM_SG\MultilingualSitemap::$template:
				$title = __( 'Multilingual Sitemap', 'xml-sitemap-generator-for-google' );
				break;
			case 'inner-sitemap':
				$title = __( 'Sitemap', 'xml-sitemap-generator-for-google' );
				break;
			case 'sitemap-index':
				$title = __( 'Sitemap Index', 'xml-sitemap-generator-for-google' );
				break;
		}

		return $title;
	}

	/**
	 * Add URLS Callback function
	 */
	public function urlsCallback() {
		return 'addUrl';
	}

	/**
	 * Adding Google News Sitemap Headers
	 */
	public function extraSitemapHeader() {
		return array();
	}

	/**
	 * Collect Sitemap URLs
	 */
	public function collect_urls( $template = 'sitemap', $inner_sitemap = null, $current_page = null ) {
		if ( $this->settings->home->include && ! $this->settings->page->include ) {
			$this->add_home();
		}
		if ( sgg_is_sitemap_index( $template, $this->settings ) ) {
			$current_page = ! empty( $current_page ) ? intval( $current_page ) - 1 : $current_page;

			if ( 'sitemap' === $template ) {
				$post_types = $this->get_post_types_list( array( 'page', 'post' ), $this->settings );
				foreach ( $post_types as $post_type ) {
					$this->add_posts( $post_type, $current_page, true );
				}
				$this->add_categories( $current_page, true );
				$this->add_authors( $current_page, true );
				$this->add_archives( true );
				$this->add_additional_pages( true );
			} else {
				switch ( $inner_sitemap ) {
					case 'category':
						$this->add_categories( $current_page );
						break;
					case 'author':
						$this->add_authors( $current_page );
						break;
					case 'archive':
						$this->add_archives();
						break;
					case 'additional':
						$this->add_additional_pages();
						break;
					default:
						$this->add_posts( $inner_sitemap, $current_page );
						break;
				}
			}
		} else {
			$this->add_posts();
			$this->add_not_translatable_posts(); // Add Not Translatable Post Types to All Sitemaps
			$this->add_categories();
			$this->add_authors();
			$this->add_archives();
			$this->add_additional_pages();
		}
	}

	/**
	 * Add Home Page to Sitemap
	 */
	public function add_home() {
		$home = $this->settings->home;

		if ( $home->include ) {
			$front_page_id = get_option( 'page_on_front' );
			$last_modified = ( $front_page_id ) ? get_post_modified_time( DATE_W3C, false, $front_page_id ) : gmdate( 'c' );

			$this->add_url(
				sgg_get_home_url(),
				$home->priority,
				$home->frequency,
				$last_modified,
				'page'
			);
		}
	}

	/**
	 * Add all Posts to Sitemap
	 */
	public function add_posts( $post_type = null, $current_page = null, $is_sitemap_index = false ) {
		global $wpdb;

		$front_page_id    = get_option( 'page_on_front' );
		$exclude_post_ids = apply_filters( 'sgg_sitemap_exclude_ids', array(), $this->settings->exclude_posts ?? '' );
		$exclude_term_ids = apply_filters( 'sgg_sitemap_exclude_ids', array(), $this->settings->exclude_terms ?? '' );
		$per_page         = intval( $this->settings->links_per_page ?? 1000 );

		if ( ! empty( $front_page_id ) ) {
			$exclude_post_ids[] = $front_page_id;
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

		if ( ! empty( $post_type ) ) {
			if ( ( isset( $this->settings->{$post_type}->include ) && $this->settings->{$post_type}->include ) 
				|| ( ! empty( $this->settings->cpt[ $post_type ] ) && ! empty( $this->settings->cpt[ $post_type ]->include ) ) ) {
				$post_types = array( $post_type );
			}
		} else {
			$post_types = $this->get_post_types_list( array( 'page', 'post' ), $this->settings );
		}

		if ( empty( $post_types ) ) {
			return;
		}

		if ( in_array( 'page', $post_types, true ) && 1 > $current_page ) {
			$this->add_home();
		}

		$sql_post_types   = "('" . implode( "','", $post_types ) . "')";
		$multilingual_sql = $this->multilingual_sql( $post_types );
		$where_clause     = ! empty( $multilingual_sql ) ? 'AND ' : 'WHERE ';

		$sql = "SELECT
	            posts.ID,
				posts.post_name,
				posts.post_parent,
				posts.post_type,
				posts.post_date,
				posts.post_modified,
				posts.comment_count
				FROM $wpdb->posts as posts
				$exclude_terms_join
				$multilingual_sql
				$where_clause posts.post_status = 'publish' AND posts.post_type IN $sql_post_types AND posts.post_password = ''
				$exclude_posts_sql
				$exclude_terms_sql
				GROUP BY posts.ID
				ORDER BY posts.post_modified DESC";

		if ( is_null( $current_page ) && ! $is_sitemap_index ) {
			$this->add_post_urls( $sql, false );

			return;
		}

		if ( $is_sitemap_index ) {
			// Calculate total number of posts
			$total_posts_sql = "SELECT COUNT(*) FROM $wpdb->posts as posts
			$exclude_terms_join
			$multilingual_sql
			$where_clause posts.post_status = 'publish' AND posts.post_type IN $sql_post_types AND posts.post_password = ''
			$exclude_posts_sql
			$exclude_terms_sql
			ORDER BY posts.post_modified DESC";
			$total_posts     = $wpdb->get_var( $total_posts_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// Calculate the number of chunks
			$num_chunks = ceil( $total_posts / $per_page );

			for ( $chunk_index = 0; $chunk_index < $num_chunks; $chunk_index++ ) {
				$offset    = $chunk_index * $per_page;
				$chunk_sql = $sql . " LIMIT $offset, 1";

				$this->add_post_urls( $chunk_sql, true );
			}
		} else {
			$offset = $current_page * $per_page;
			$sql   .= " LIMIT $offset, $per_page";

			$this->add_post_urls( $sql, false );
		}
	}

	/**
	 * Add Post URLs to Sitemap
	 */
	public function add_post_urls( $sql, $is_sitemap_index ) {
		$priority_provider = $this->get_posts_priority_provider();
		$posts             = QueryBuilder::run_query( $sql );

		foreach ( $posts as $post ) {
			if ( $is_sitemap_index || apply_filters( 'xml_sitemap_include_post', true, $post->ID ) ) {
				$this->add_url(
					get_permalink( $post ),
					( null !== $priority_provider && 'post' === $post->post_type )
						? apply_filters( 'sitemap_post_priority', $priority_provider->get_post_priority( $post->comment_count ), $post->ID )
						: apply_filters( 'sitemap_post_priority', $this->get_post_settings( $post->post_type, 'priority' ), $post->ID ),
					apply_filters( 'sitemap_post_frequency', $this->get_post_settings( $post->post_type, 'frequency' ), $post->ID ),
					gmdate( DATE_W3C, strtotime( $post->post_modified ) ),
					$post->post_type
				);
			}
		}
	}

	/**
	 * Add all Categories & Tags
	 */
	public function add_categories( $current_page = null, $is_sitemap_index = false ) {
		global $wpdb;

		$taxonomy_types = array();
		$per_page       = intval( $this->settings->links_per_page ?? 1000 );

		foreach ( $this->get_taxonomy_types( 'names' ) as $taxonomy_type ) {
			if ( ! empty( $this->settings->taxonomies[ $taxonomy_type ] ) && $this->settings->taxonomies[ $taxonomy_type ]->include ) {
				$taxonomy_types[] = $taxonomy_type;
			}
		}

		if ( empty( $taxonomy_types ) ) {
			return;
		}

		$exclude_term_ids  = apply_filters( 'sgg_sitemap_exclude_ids', array(), $this->settings->exclude_terms ?? '' );
		$exclude_terms_sql = '';
		if ( ! empty( $exclude_term_ids ) ) {
			$exclude_terms_sql = 'AND terms.term_id NOT IN (' . implode( ',', array_unique( $exclude_term_ids ) ) . ')';
		}

		$post_types       = $this->get_post_types_list( array( 'post' ), $this->settings );
		$sql_post_types   = "('" . implode( "','", $post_types ) . "')";
		$sql_taxonomies   = "('" . implode( "','", $taxonomy_types ) . "')";
		$multilingual_sql = $this->multilingual_sql( $taxonomy_types, true );
		$where_clause     = ! empty( $multilingual_sql ) ? 'AND ' : 'WHERE ';
		$terms_query      = "
			SELECT terms.*, term_taxonomy.taxonomy, term_taxonomy.count, (
				SELECT MAX(p.post_modified)
				FROM $wpdb->posts AS p
				INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id
				WHERE p.post_type IN $sql_post_types
				AND p.post_status = 'publish'
				AND tr.term_taxonomy_id = term_taxonomy.term_taxonomy_id
			) AS post_modified
			FROM $wpdb->terms AS terms
			INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON terms.term_id = term_taxonomy.term_id
			$multilingual_sql
			$where_clause term_taxonomy.taxonomy IN $sql_taxonomies
			$exclude_terms_sql
			AND term_taxonomy.count > 0
			GROUP BY terms.term_id
			ORDER BY post_modified DESC, terms.name ASC
		";

		if ( is_null( $current_page ) && ! $is_sitemap_index ) {
			$this->add_category_urls( $terms_query, false );

			return;
		}

		if ( $is_sitemap_index ) {
			// Calculate total number of terms
			$total_terms_sql = "
				SELECT COUNT(*) FROM $wpdb->terms AS terms
				INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON terms.term_id = term_taxonomy.term_id
				$multilingual_sql
				$where_clause term_taxonomy.taxonomy IN $sql_taxonomies
				$exclude_terms_sql
				AND term_taxonomy.count > 0
			";
			$total_terms     = $wpdb->get_var( $total_terms_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			// Calculate the number of chunks
			$num_chunks = ceil( $total_terms / $per_page );

			for ( $chunk_index = 0; $chunk_index < $num_chunks; $chunk_index++ ) {
				$offset    = $chunk_index * $per_page;
				$chunk_sql = $terms_query . " LIMIT $offset, 1";

				$this->add_category_urls( $chunk_sql, true );
			}
		} else {
			$offset       = $current_page * $per_page;
			$terms_query .= " LIMIT $offset, $per_page";

			$this->add_category_urls( $terms_query, false );
		}
	}

	/**
	 * Add Category URLs to Sitemap
	 */
	public function add_category_urls( $terms_query, $is_sitemap_index ) {
		global $wpdb;

		$terms = $wpdb->get_results( $terms_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		foreach ( $terms as $term ) {
			if ( ! $is_sitemap_index && apply_filters( 'sgg_sitemap_exclude_single_term', false, intval( $term->term_id ), $term->taxonomy ) ) {
				continue;
			}

			$term_modified = ! empty( $term->post_modified )
				? strtotime( $term->post_modified )
				: time();

			$this->add_url(
				get_term_link( $term ),
				apply_filters( 'sitemap_term_priority', $this->get_taxonomy_settings( $term->taxonomy, 'priority' ), $term->term_id ),
				apply_filters( 'sitemap_term_frequency', $this->get_taxonomy_settings( $term->taxonomy, 'frequency' ), $term->term_id ),
				gmdate( DATE_W3C, $term_modified ),
				'category'
			);
		}
	}

	/**
	 * Add all Authors of Posts
	 */
	public function add_authors( $current_page = null, $is_sitemap_index = false ) {
		if ( ! $this->settings->authors->include ) {
			return;
		}

		$args = array(
			'has_published_posts' => $this->get_post_types_list( array( 'post' ), $this->settings ),
			'fields'              => 'ids',
			'orderby'             => 'post_count',
			'order'               => 'DESC',
			'number'              => -1,
		);

		if ( is_null( $current_page ) && ! $is_sitemap_index ) {
			$this->add_author_urls( $args );

			return;
		}

		$per_page = intval( $this->settings->links_per_page ?? 1000 );

		if ( $is_sitemap_index ) {
			$authors_query = new \WP_User_Query( $args );

			if ( empty( $authors_query->get_total() ) ) {
				return;
			}

			$num_chunks = ceil( $authors_query->get_total() / $per_page );

			for ( $chunk_index = 0; $chunk_index < $num_chunks; $chunk_index++ ) {
				$offset = $chunk_index * $per_page;
				$args   = array_merge(
					$args,
					array(
						'number' => 1,
						'offset' => $offset,
					)
				);

				$this->add_author_urls( $args );
			}
		} else {
			$offset = $current_page * $per_page;
			$args   = array_merge(
				$args,
				array(
					'number' => $per_page,
					'offset' => $offset,
				)
			);

			$this->add_author_urls( $args );
		}
	}

	public function add_author_urls( $args ) {
		$authors_query = new \WP_User_Query( $args );
		$authors       = $authors_query->get_results();

		if ( ! empty( $authors ) ) {
			global $wpdb;

			foreach ( $authors as $author_id ) {
				$latest_post_query = "SELECT ID, post_modified
							FROM {$wpdb->posts}
							WHERE post_author = {$author_id}
							AND post_type = 'post' AND post_status = 'publish'
							ORDER BY post_modified DESC
							LIMIT 1";

				$latest_post   = $wpdb->get_row( $latest_post_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$modified_time = ! empty( $latest_post->post_modified )
					? gmdate( DATE_W3C, strtotime( $latest_post->post_modified ) )
					: gmdate( DATE_W3C );

				$this->add_url(
					get_author_posts_url( $author_id ),
					$this->settings->authors->priority,
					$this->settings->authors->frequency,
					$modified_time,
					'author'
				);
			}
		}
	}

	/**
	 * Add all Archives
	 */
	public function add_archives( $is_sitemap_index = false ) {
		global $wpdb;

		$sql_multilingual = $this->multilingual_sql( array( 'post' ) );
		$where_clause     = ! empty( $sql_multilingual ) ? 'AND ' : 'WHERE ';
		$sql              = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, post_date AS post_date, count(ID) as count_posts
			FROM $wpdb->posts as posts
			$sql_multilingual
			$where_clause post_type = 'post' AND post_status = 'publish' AND post_password = ''
			GROUP BY YEAR(post_date), MONTH(post_date)
			ORDER BY post_date DESC";

		if ( $is_sitemap_index ) {
			$sql .= ' LIMIT 1';
		}

		$archives = QueryBuilder::run_query( $sql );

		foreach ( $archives as $archive ) {
			$option = ( gmdate( 'n' ) === $archive->month && gmdate( 'Y' ) === $archive->year ) ? 'archive' : 'archive_older';
			if ( $this->settings->{$option}->include ) {
				$this->add_url(
					get_month_link( $archive->year, $archive->month ),
					$this->settings->{$option}->priority,
					$this->settings->{$option}->frequency,
					gmdate( DATE_W3C, strtotime( $archive->post_date ) ),
					'archive'
				);
			}
		}
	}

	/**
	 * Add Additional Pages
	 */
	public function add_additional_pages( $is_sitemap_index = false ) {
		$pages = $this->settings->additional_pages;

		if ( empty( $pages ) ) {
			return;
		}

		if ( $is_sitemap_index ) {
			usort(
				$pages,
				function ( $a, $b ) {
					return strtotime( $b['lastmod'] ) - strtotime( $a['lastmod'] );
				}
			);

			$pages = array( $pages[0] );
		}

		foreach ( $pages as $page ) {
			if ( empty( $page['url'] ) ) {
				continue;
			}

			$last_modified = ! empty( $page['lastmod'] )
				? gmdate( DATE_W3C, strtotime( $page['lastmod'] ) )
				: gmdate( DATE_W3C );

			$this->add_url(
				$page['url'],
				$page['priority'],
				$page['frequency'],
				$last_modified,
				'additional'
			);
		}
	}

	/**
	 * Add Sitemap Url
	 *
	 * @param $url
	 * @param $priority
	 * @param $frequency
	 * @param string $last_modified
	 */
	public function add_url( $url, $priority, $frequency, $last_modified = '', $inner_sitemap = '' ) {
		$item = array(
			$url, // URL
			$last_modified, // Last Modified
			$frequency, // Frequency
			number_format( floatval( $priority / 10 ), 1, '.', '' ), // Priority
		);

		if ( sgg_is_sitemap_index( 'sitemap', $this->settings ) ) {
			$this->urls[ $inner_sitemap ][] = $item;
		} else {
			$this->urls[] = $item;
		}
	}

	public function multilingual_sql( $element_types, $is_taxonomy = false ) {
		global $wpdb;

		$multilingual_sql  = '';
		$element_id_column = $is_taxonomy ? 'term_taxonomy.term_taxonomy_id' : 'posts.ID';

		if ( function_exists( 'pll_languages_list' ) && ! empty( $GLOBALS['polylang'] ) ) {
			global $polylang;

			$current_language  = pll_current_language();
			$polylang_model    = $polylang->model;
			$lang_model        = $polylang_model->get_language( $current_language );
			$tax_language      = $is_taxonomy ? $polylang_model->term->get_tax_language() : $polylang_model->post->get_tax_language();
			$polylang_term_ids = array();

			foreach ( $element_types as $key => $element_type ) {
				$translatable = $is_taxonomy
					? $polylang_model->is_translated_taxonomy( $element_type )
					: $polylang_model->is_translated_post_type( $element_type );

				if ( ! $translatable ) {
					continue;
				}

				$polylang_term_ids[] = absint( $lang_model->get_tax_prop( $tax_language, 'term_taxonomy_id' ) );
			}

			if ( ! empty( $polylang_term_ids ) ) {
				$polylang_term_ids = '(' . implode( ',', array_unique( $polylang_term_ids ) ) . ')';
				$multilingual_sql  = "INNER JOIN {$wpdb->term_relationships} AS pll_tr ON $element_id_column = pll_tr.object_id WHERE ( pll_tr.term_taxonomy_id IN $polylang_term_ids )";
			}
		}

		if ( defined( 'ICL_SITEPRESS_VERSION' ) && ! empty( $element_types ) ) {
			$current_language   = apply_filters( 'wpml_current_language', null );
			$element_type       = $is_taxonomy ? 'tax' : 'post';
			$wpml_element_types = array_map(
				function ( $post_type ) use ( $element_type ) {
					return "{$element_type}_{$post_type}";
				},
				$element_types
			);
			$sql_element_types  = "('" . implode( "','", $wpml_element_types ) . "')";
			$multilingual_sql   = "INNER JOIN {$wpdb->prefix}icl_translations AS translations ON $element_id_column = translations.element_id
			WHERE translations.language_code = '$current_language' AND translations.element_type IN $sql_element_types";
		}

		return $multilingual_sql;
	}

	public function add_not_translatable_posts() {
		// Add Polylang Not Translatable Post Types
		if ( function_exists( 'pll_languages_list' ) && ! empty( $GLOBALS['polylang'] ) ) {
			$options          = get_option( 'polylang' );
			$post_types       = $this->get_post_types_list( array(), $this->settings );
			$not_translatable = ! empty( $options['post_types'] ) && ! empty( $post_types )
				? array_diff( $post_types, $options['post_types'] )
				: array();

			if ( ! empty( $not_translatable ) ) {
				foreach ( $not_translatable as $post_type ) {
					$this->add_posts( $post_type );
				}
			}
		}
	}

	/**
	 * Posts Priority
	 */
	public function get_posts_priority_provider() {
		$class_name = str_replace( '/', '\\', $this->settings->posts_priority ?? '' );

		return class_exists( $class_name ) ? new $class_name( $this->get_comments_count(), $this->get_posts_count() ) : null;
	}

	public function get_comments_count() {
		global $wpdb;

		$cache_key      = self::$slug . '_comments_count';
		$comments_count = wp_cache_get( $cache_key, self::$slug );

		if ( false === $comments_count ) {
			$comments_count = $wpdb->get_var( "SELECT COUNT(*) as `comments_count` FROM {$wpdb->comments} WHERE `comment_approved`='1'" );
			wp_cache_set( $cache_key, $comments_count, self::$slug, 20 );
		}

		return $comments_count;
	}

	public function get_posts_count() {
		global $wpdb;

		$cache_key   = self::$slug . '_posts_count';
		$posts_count = wp_cache_get( $cache_key, self::$slug );

		if ( false === $posts_count ) {
			$posts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} p WHERE p.post_password = '' AND p.post_type = 'post' AND p.post_status = 'publish' " );
			wp_cache_set( $cache_key, $posts_count, self::$slug, 20 );
		}

		return $posts_count;
	}

	/**
	 * Get Post Field Value
	 *
	 * @param $post_type
	 * @param $field
	 * @return mixed
	 */
	public function get_post_settings( $post_type, $field ) {
		if ( ! empty( $this->settings->cpt ) && in_array( $post_type, array_keys( $this->settings->cpt ), true ) ) {
			return $this->settings->cpt[ $post_type ]->{$field} ?? null;
		}

		return $this->settings->{$post_type}->{$field} ?? null;
	}

	/**
	 * Get Taxonomy Field Value
	 *
	 * @param $taxonomy_type
	 * @param $field
	 * @return mixed
	 */
	public function get_taxonomy_settings( $taxonomy_type, $field ) {
		if ( ! empty( $this->settings->taxonomies ) && in_array( $taxonomy_type, array_keys( $this->settings->taxonomies ), true ) ) {
			return $this->settings->taxonomies[ $taxonomy_type ]->{$field} ?? null;
		}

		return $this->settings->{$taxonomy_type}->{$field} ?? null;
	}
}
