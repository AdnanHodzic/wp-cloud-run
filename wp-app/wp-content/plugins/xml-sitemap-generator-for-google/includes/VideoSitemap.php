<?php

namespace GRIM_SG;

use SGG_PRO\Classes\Video_Sitemap;

class VideoSitemap extends MediaSitemap {
	public static $template = 'video-sitemap';

	/**
	 * Adding Google News Sitemap Headers
	 */
	public function extraSitemapHeader() {
		return array( 'xmlns:video' => 'http://www.google.com/schemas/sitemap-video/1.1' );
	}

	public function add_urls( string $url, array $media ): void {
		$videos = array();

		foreach ( $media as $video ) {
			$extensions = explode( '.', $video );
			$extension  = end( $extensions );

			if ( 'video' === wp_ext2type( $extension ) ) {
				$attachment_id = attachment_url_to_postid( $video );
				if ( $attachment_id ) {
					$thumbnail = get_the_post_thumbnail_url( $attachment_id, 'thumbnail' );
					$metadata  = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
					$videos[]  = array(
						'thumbnail'   => ! empty( $thumbnail ) ? $thumbnail : trailingslashit( includes_url() ) . 'images/media/video.png',
						'title'       => get_the_title( $attachment_id ),
						'description' => wp_get_attachment_caption( $attachment_id ),
						'player_loc'  => $video,
						'duration'    => $metadata['length'] ?? '',
					);
				}
			} elseif ( sgg_pro_enabled() && class_exists( 'SGG_PRO\Classes\Video_Sitemap' ) ) {
				if ( ! empty( $this->settings->youtube_api_key ) && $this->is_youtube_url( $video ) ) {
					$youtube_data = Video_Sitemap::get_youtube_data( $video, $this->settings->youtube_api_key, $this->settings->enable_video_api_cache );

					if ( ! empty( $youtube_data ) ) {
						$videos[] = $youtube_data;
					}
				} elseif ( ! empty( $this->settings->vimeo_api_key ) && $this->is_vimeo_url( $video ) ) {
					$vimeo_data = Video_Sitemap::get_vimeo_data( $video, $this->settings->vimeo_api_key, $this->settings->enable_video_api_cache );

					if ( ! empty( $vimeo_data ) ) {
						$videos[] = $vimeo_data;
					}
				}
			}
		}

		if ( ! empty( $videos ) ) {
			$this->urls[] = array(
				$url, // URL
				$videos, // Videos
			);
		}
	}

	public function filter_value( string $value ): bool {
		$extensions = explode( '.', $value );
		$extension  = end( $extensions );

		return 'video' === wp_ext2type( $extension ) || ( sgg_pro_enabled() && ( $this->is_youtube_url( $value ) || $this->is_vimeo_url( $value ) ) );
	}

	public function is_youtube_url( $url ) {
		return (
			false !== strpos( $url, 'https://www.youtube.com/embed/' ) ||
			false !== strpos( $url, 'https://youtu.be/' ) ||
			false !== strpos( $url, 'https://www.youtube.com/watch?v=' ) ||
			false !== strpos( $url, '//www.youtube.com/embed/' ) ||
			false !== strpos( $url, '//youtu.be/' ) ||
			false !== strpos( $url, '//www.youtube.com/watch?v=' )
		);
	}

	public function is_vimeo_url( $url ) {
		return (
			false !== strpos( $url, 'https://vimeo.com/' ) ||
			false !== strpos( $url, 'https://player.vimeo.com/video/' )
		);
	}
}
