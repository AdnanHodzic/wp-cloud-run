<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\QueryBuilder;

abstract class MediaSitemap extends Sitemap {
	abstract public function add_urls( string $url, array $media ): void;

	abstract public function filter_value( string $value ): bool;

	/**
	 * Add URLS Callback function
	 */
	public function urlsCallback() {
		return 'addMediaUrl';
	}

	public function get_post_media( int $post_id, string $post_type ): array {
		return apply_filters( 'sgg_media_post_urls', array(), $post_id, $post_type );
	}

	/**
	 * Collect Media URLs for Sitemap
	 */
	public function collect_urls( $template = 'sitemap', $inner_sitemap = null, $current_page = null ) {
		global $wpdb;

		$post_types    = array( 'page', 'post' );
		$template      = sgg_maybe_remove_inner_suffix( $template );
		$cache_enabled = ! $this->settings->disable_media_sitemap_cache;

		if ( $cache_enabled ) {
			$cache = new Cache( "media-$template" );

			// Set URLs from cache if available.
			$cached_urls = $cache->get();
			if ( $cached_urls ) {
				$this->urls = $cached_urls;
			}
		}

		$sitemap_key = 'video-sitemap' === $template ? 'video_sitemap' : 'image_sitemap';

		foreach ( $post_types as $key => $post_type ) {
			if ( isset( $this->settings->{$post_type}->{$sitemap_key} ) && ! $this->settings->{$post_type}->{$sitemap_key} ) {
				unset( $post_types[ $key ] );
			}
		}

		if ( sgg_pro_enabled() ) {
			foreach ( $this->get_cpt() as $cpt ) {
				if ( ! empty( $this->settings->cpt[ $cpt ] ) && ! empty( $this->settings->cpt[ $cpt ]->{$sitemap_key} ) ) {
					$post_types[] = $cpt;
				}
			}
		}

		$sql_post_types   = "('" . implode( "','", $post_types ) . "')";
		$multilingual_sql = $this->multilingual_sql( $post_types );
		$where_clause     = ! empty( $multilingual_sql ) ? 'AND ' : 'WHERE ';
		$limit            = 5000;

		if ( $cache_enabled ) {
			$last_mod_time = get_option( $this->get_option_name( $template, 'latest_mod_time' ), '1970-01-01 00:00:00' );
			$last_id       = get_option( $this->get_option_name( $template, 'latest_post_id' ), 0 );
		}

		while ( true ) {
			$last_mod_sql = '';
			$limit_sql    = '';
			if ( $cache_enabled ) {
				$last_mod_sql = $wpdb->prepare(
					'AND (posts.post_modified > %s OR (posts.post_modified = %s AND posts.ID > %d))',
					$last_mod_time,
					$last_mod_time,
					$last_id
				);
				$limit_sql    = $wpdb->prepare( 'LIMIT %d', $limit );
			}

			$sql = "SELECT posts.ID, posts.post_name, posts.post_parent, posts.post_content, posts.post_type, posts.post_modified
					FROM {$wpdb->posts} as posts $multilingual_sql $where_clause posts.post_status = 'publish'
					AND posts.post_type IN $sql_post_types AND posts.post_password = ''
					$last_mod_sql ORDER BY posts.post_modified ASC, posts.ID ASC $limit_sql";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$posts = $wpdb->get_results( $sql );

			// If no posts are returned, then all posts have been processed.
			if ( empty( $posts ) ) {
				break;
			}

			// Loop through the posts and add the URLs to the sitemap.
			foreach ( $posts as $post ) {
				if ( ! apply_filters( 'xml_sitemap_include_post', true, $post->ID ) ) {
					continue;
				}

				$content = apply_filters( 'xml_media_sitemap_post_content', $post->post_content, $post );
				if ( ! empty( $content ) && preg_match( '/\[.+?\]/im', $content ) ) {
					preg_match_all( '/\[.+?\]/im', $content, $shortcode_matches );

					foreach ( $shortcode_matches as $shortcodes ) {
						foreach ( $shortcodes as $shortcode ) {
							// Skip HTML Sitemap Shortcode
							if ( 0 !== strpos( $shortcode, '[html-sitemap' ) ) {
								ob_start();

								$do_shortcode = do_shortcode( $shortcode );
								$output       = ob_get_clean();
								$final_output = $do_shortcode . $output;
								$content      = str_replace( $shortcode, $final_output, $content );
							}
						}
					}
				}

				$media = $this->get_post_media( $post->ID, $post->post_type );
				$urls  = array();

				if ( preg_match_all( '(https?://[-_.!~*()a-zA-Z0-9;/?:@&=+$%#纊-黑亜-熙ぁ-んァ-ヶ]+)', $content, $result ) !== false ) {
					$unique_urls = array();

					// Remove duplicate Image sizes URLs
					foreach ( array_unique( $result[0] ) as $url ) {
						$base_url = preg_replace( '/-\d+x\d+(?=\.\w{3,4}$)/', '', $url );

						if ( ! isset( $unique_urls[ $base_url ] ) ) {
							$unique_urls[ $base_url ] = strtok( $url, '#' );
						}
					}

					$urls = array_values( $unique_urls );
				}

				$urls = apply_filters( 'sgg_sitemap_post_media_urls', $urls, $post->ID );

				if ( ! empty( $urls ) ) {
					foreach ( $urls as $url ) {
						if ( $this->filter_value( $url ) ) {
							$media[] = $url;
						}
					}
				}

				if ( ! empty( $media ) ) {
					$this->add_urls( get_permalink( $post ), array_unique( $media ) );
				}

				$last_mod_time = $post->post_modified;
				$last_id       = $post->ID;
			}

			if ( $cache_enabled ) {
				// Cache the collected URLs.
				$cache->set( $this->urls, true );

				// Save the new markers for the latest post processed.
				update_option( $this->get_option_name( $template, 'latest_mod_time' ), $last_mod_time );
				update_option( $this->get_option_name( $template, 'latest_post_id' ), $last_id );
			}

			// If fewer posts than the limit were returned, we've reached the final batch.
			if ( ! $cache_enabled || count( $posts ) < $limit ) {
				break;
			}
		}

		// Set cached URLs to the sitemap URLs.
		if ( $cache_enabled ) {
			$this->urls = $cache->get();

			if ( is_array( $this->urls ) ) {
				$this->urls = array_reverse( $this->urls );
			} else {
				$this->urls = array();

				// Delete cache if no URLs are found
				self::delete_all_cache();
			}
		}

		// Check Index Sitemap
		$links_per_page = $this->settings->links_per_page ?? 1000;
		$has_many_links = count( $this->urls ) > $links_per_page;

		// Update Media Sitemap Structure option
		update_option( "sgg_{$template}_structure", $has_many_links ? 'multiple' : 'single' );

		if ( sgg_is_sitemap_index( $template, $this->settings ) && $has_many_links ) {
			if ( ! empty( $inner_sitemap ) && ! empty( $current_page ) ) {
				$chunks     = array_chunk( $this->urls, $links_per_page );
				$this->urls = $chunks[ $current_page - 1 ] ?? array();
			} else {
				$this->urls = array(
					str_replace( '-sitemap', '', $template ) =>
					array_map(
						function ( $chunk ) {
							return $chunk[0] ?? array();
						},
						array_chunk( $this->urls, $links_per_page )
					),
				);
			}
		}
	}

	private function get_option_name( string $template, string $name ): string {
		return "sgg_{$template}_{$name}";
	}

	public static function delete_all_cache(): void {
		delete_option( 'sgg_image-sitemap_latest_mod_time' );
		delete_option( 'sgg_image-sitemap_latest_post_id' );
		delete_option( 'sgg_video-sitemap_latest_mod_time' );
		delete_option( 'sgg_video-sitemap_latest_post_id' );

		Cache::delete( 'image-sitemap' );
		Cache::delete( 'media-image-sitemap' );
		Cache::delete( 'video-sitemap' );
		Cache::delete( 'media-video-sitemap' );
	}
}
