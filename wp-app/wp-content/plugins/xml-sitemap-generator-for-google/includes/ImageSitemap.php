<?php

namespace GRIM_SG;

class ImageSitemap extends MediaSitemap {
	public static $template = 'image-sitemap';

	private $image_mime_types = array(
		'image/jpeg',
		'image/png',
		'image/bmp',
		'image/gif',
		'image/webp',
	);

	/**
	 * Adding Google News Sitemap Headers
	 */
	public function extraSitemapHeader() {
		return array( 'xmlns:image' => 'http://www.google.com/schemas/sitemap-image/1.1' );
	}

	public function get_post_media( int $post_id, string $post_type ): array {
		return apply_filters( 'sgg_image_sitemap_urls', array(), $post_id, $post_type );
	}

	public function add_urls( string $url, array $media ): void {
		$this->urls[] = array(
			$url, // URL
			$media, // Images
		);
	}

	public function filter_value( string $value ): bool {
		$value    = strtok( $value, '?' );
		$filetype = wp_check_filetype( $value );
		$filtered = in_array( $filetype['type'], $this->image_mime_types, true );

		return apply_filters( 'sgg_filter_image_url', $filtered, $value );
	}
}
