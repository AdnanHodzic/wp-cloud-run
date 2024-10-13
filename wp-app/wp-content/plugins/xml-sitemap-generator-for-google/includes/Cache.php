<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class Cache extends Controller {
	private static $prefix = 'sgg_cache_';

	public static $sitemaps = array(
		'sitemap',
		'inner-sitemap',
		'google-news',
		'image-sitemap',
		'video-sitemap',
	);

	public $sitemap;

	public $inner_sitemap;
	public $current_page;

	public function __construct( $sitemap = 'sitemap', $inner_sitemap = null, $current_page = null ) {
		$this->sitemap       = $this->get_multilingual_sitemap_name( $sitemap );
		$this->inner_sitemap = $inner_sitemap;
		$this->current_page  = $current_page ?? '';
	}

	public function set( $urls ) {
		$expiration = self::get_expiration( $this->get_settings() );

		if ( $this->inner_sitemap ) {
			$cached_urls = get_transient( self::$prefix . $this->sitemap );

			if ( empty( $cached_urls ) ) {
				$cached_urls = array();
			}

			$cached_urls[ $this->inner_sitemap . $this->current_page ] = $urls[ $this->inner_sitemap ];

			$urls = $cached_urls;
		}

		set_transient( self::$prefix . $this->sitemap, $urls, $expiration );
		set_transient( self::$prefix . $this->sitemap . '_time', time(), $expiration );
	}

	public function get() {
		$urls = get_transient( self::$prefix . $this->sitemap );

		if ( $this->inner_sitemap ) {
			if ( empty( $urls[ $this->inner_sitemap . $this->current_page ] ) ) {
				return null;
			}

			return array(
				$this->inner_sitemap => $urls[ $this->inner_sitemap . $this->current_page ],
			);
		}

		return $urls;
	}

	public static function get_time( $sitemap ) {
		return get_transient( self::$prefix . $sitemap . '_time' );
	}

	public static function get_time_formatted( $sitemap ) {
		$time = self::get_time( $sitemap );

		return $time
			// translators: %s is Cached Time
			? sprintf( __( '%s ago', 'xml-sitemap-generator-for-google' ), human_time_diff( $time, time() ) )
			: __( 'No Cache', 'xml-sitemap-generator-for-google' );
	}

	public static function delete( $sitemap ): void {
		delete_transient( self::$prefix . $sitemap );
		delete_transient( self::$prefix . $sitemap . '_time' );
	}

	public static function clear(): void {
		foreach ( self::$sitemaps as $sitemap ) {
			self::delete( $sitemap );
		}
	}

	public static function maybe_clear( $expiration ): void {
		foreach ( self::$sitemaps as $sitemap ) {
			if ( $expiration < time() - self::get_time( $sitemap ) ) {
				self::delete( $sitemap );
			}
		}
	}

	public static function get_expiration( $settings ) {
		return intval( $settings->cache_timeout ?? 24 ) * intval( $settings->cache_timeout_period ?? 3600 );
	}

	public function get_multilingual_sitemap_name( $sitemap ) {
		$suffix = '';

		if ( function_exists( 'pll_current_language' ) ) {
			$suffix = pll_current_language();
		}

		if ( function_exists( 'trp_get_languages' ) ) {
			$trp_settings = get_option( 'trp_settings' );
			$suffix       = $trp_settings['default-language'] ?? null;
		}

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$suffix = apply_filters( 'wpml_current_language', null );
		}

		if ( ! empty( $suffix ) ) {
			$sitemap = "{$sitemap}_{$suffix}";
		}

		return $sitemap;
	}
}
