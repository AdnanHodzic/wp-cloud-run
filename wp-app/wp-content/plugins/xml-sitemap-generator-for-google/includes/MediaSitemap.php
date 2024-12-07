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

		$post_types = array( 'page', 'post' );
		$pattern    = '/\[.+?\]/im';

		foreach ( $post_types as $key => $post_type ) {
			if ( isset( $this->settings->{$post_type}->media_sitemap ) && ! $this->settings->{$post_type}->media_sitemap ) {
				unset( $post_types[ $key ] );
			}
		}

		if ( sgg_pro_enabled() ) {
			foreach ( $this->get_cpt() as $cpt ) {
				if ( ! empty( $this->settings->cpt[ $cpt ] ) && ! empty( $this->settings->cpt[ $cpt ]->media_sitemap ) ) {
					$post_types[] = $cpt;
				}
			}
		}

		$sql_post_types   = "('" . implode( "','", $post_types ) . "')";
		$multilingual_sql = $this->multilingual_sql( $post_types );
		$where_clause     = ! empty( $multilingual_sql ) ? 'AND ' : 'WHERE ';
		$sql              = "SELECT
	            posts.ID,
				posts.post_name,
				posts.post_content,
				posts.post_parent,
				posts.post_type,
				posts.post_date,
				posts.post_modified
				FROM $wpdb->posts as posts
				$multilingual_sql
				$where_clause posts.post_status = 'publish' AND posts.post_type IN $sql_post_types AND posts.post_password = ''
				GROUP BY posts.ID
				ORDER BY posts.post_modified DESC";

		$posts = QueryBuilder::run_query( $sql );

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			if ( ! empty( $content ) && preg_match( $pattern, $content ) ) {
				preg_match_all( $pattern, $content, $shortcode_matches );

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
						$unique_urls[ $base_url ] = $url;
					}
				}

				$urls = array_values($unique_urls);
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
		}
	}
}
