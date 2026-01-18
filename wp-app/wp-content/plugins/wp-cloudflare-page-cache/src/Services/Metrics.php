<?php

namespace SPC\Services;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

/**
 *  Metrics
 *  ------------------------------------------------------------------
 *  Built-in metrics
 *    - cache.hitmiss — Rolling 24-h hits / misses / ratio
 *    - cache.files   — Number of cached *.html files
 *    - cache.size    — Total size of those files (MB)
 *    - cache.ttfb    — TTFB of home page (or fallback test file) in ms
 *
 *  All numeric fields return "n/a" when the metric cannot
 *  be calculated (errors, zero files, missing paths, etc.).
 *
 *  Extend via:  Metrics::register( 'my.metric', fn() => $value )
 *  Fetch via:   Metrics::get( 'cache.ttfb' ) / Metrics::all()
 */
class Metrics {

	public const HITMISS = 'cache.hitmiss';
	public const FILES   = 'cache.files';
	public const SIZE    = 'cache.size';
	public const TTFB    = 'cache.ttfb';
	/**
	 * Configuration
	 *
	 * @var array
	 */
	private static array $cfg;

	/**
	 * Backend
	 *
	 * @var string
	 */
	private static string $backend;

	/**
	 * Providers
	 *
	 * @var array
	 */
	private static array $providers = [];

	/**
	 * Cache directory
	 *
	 * @var string
	 */
	private static string $cache_directory;

	/**
	 * Bootstrap
	 *
	 * @return void
	 */
	private static function bootstrap(): void {
		if ( isset( self::$cfg ) ) {
			return;
		}

		self::$cfg           = self::get_config();
		self::$cfg['window'] = max( 1, (int) self::$cfg['window'] );

		self::$backend = ( function_exists( 'apcu_fetch' ) ? 'apcu' : 'file' );

		$host                  = preg_replace( '/[^A-Za-z0-9.\-_]/', '', $_SERVER['HTTP_HOST'] ?? 'cli' );
		self::$cache_directory = WP_CONTENT_DIR . "/wp-cloudflare-super-page-cache/{$host}";

		// Register built-ins
		self::register( self::HITMISS, [ __CLASS__, 'provide_hitmiss' ] );
		self::register( self::FILES, [ __CLASS__, 'provide_filecount' ] );
		self::register( self::SIZE, [ __CLASS__, 'provide_cachesize' ] );
		self::register( self::TTFB, [ __CLASS__, 'provide_ttfb' ] );
	}

	/**
	 * Register a new metric
	 *
	 * @param string $name
	 * @param callable $cb
	 * @return void
	 */
	public static function register( string $name, callable $cb ): void {
		self::bootstrap();
		self::$providers[ $name ] = $cb;
	}

	/**
	 * Get a metric
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function get( string $name ) {
		self::bootstrap();
		return isset( self::$providers[ $name ] ) ? ( self::$providers[ $name ] )() : null;
	}

	/**
	 * Get all metrics
	 *
	 * @return array
	 */
	public static function all(): array {
		self::bootstrap();
		$out = [];
		foreach ( self::$providers as $k => $cb ) {
			$out[ $k ] = $cb();
		}
		return $out;
	}

	/**
	 * Provide hit / miss
	 *
	 * @return array
	 */
	private static function provide_hitmiss(): array {

		if ( ! self::$cfg['enabled'] ) {
			return [
				'hits'   => 'n/a',
				'misses' => 'n/a',
				'ratio'  => 'n/a',
			];
		}

		$window    = self::$cfg['window'];
		$now       = time();
		$hit_keys  = [];
		$miss_keys = [];

		for ( $i = 0; $i < $window; $i++ ) {
			$b           = gmdate( 'YmdH', $now - $i * HOUR_IN_SECONDS );
			$hit_keys[]  = self::$backend === 'apcu' ? "spc_hit_{$b}" : "hit_{$b}";
			$miss_keys[] = self::$backend === 'apcu' ? "spc_miss_{$b}" : "miss_{$b}";
		}

		$hits   = 0;
		$misses = 0;
		$ok     = true;

		switch ( self::$backend ) {
			case 'apcu':
				$h_ok = false;
				$m_ok = false;
				$h    = \apcu_fetch( $hit_keys, $h_ok );
				$m    = \apcu_fetch( $miss_keys, $m_ok );

				if ( ! $h_ok && ! $m_ok ) {
					$ok = false;
					break;
				}
				$hits   = is_array( $h ) ? array_sum( $h ) : 0;
				$misses = is_array( $m ) ? array_sum( $m ) : 0;
				break;
			default:
				if ( ! is_dir( SPC_METRICS_DIR ) ) {
					$ok = false;
					break;
				}

				foreach ( $hit_keys as $k ) {
					$file = SPC_METRICS_DIR . "/{$k}.txt";

					$hits += is_file( $file ) ? filesize( $file ) : 0;
				}

				foreach ( $miss_keys as $k ) {
					$file = SPC_METRICS_DIR . "/{$k}.txt";

					$misses += is_file( $file ) ? filesize( $file ) : 0;
				}
		}

		if ( ! $ok || ( $hits + $misses ) === 0 ) {
			return [
				'hits'   => 'n/a',
				'misses' => 'n/a',
				'ratio'  => 'n/a',
			];
		}

		return [
			'hits'   => $hits,
			'misses' => $misses,
			'ratio'  => round( $hits / ( $hits + $misses ) * 100, 1 ),
		];
	}

	/**
	 * Provide HTML file count
	 *
	 * @return array
	 */
	private static function provide_filecount(): array {

		if ( ! is_dir( self::$cache_directory ) ) {
			return [ 'html_files' => 'n/a' ];
		}

		$count = 0;
		$it    = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				self::$cache_directory,
				FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
			)
		);
		foreach ( $it as $f ) {
			if ( $f->isFile() && strtolower( $f->getExtension() ) === 'html' ) {
				$count++;
			}
		}

		return $count > 0
		? [ 'html_files' => $count ]
		: [ 'html_files' => 'n/a' ];
	}

	/**
	 * Provide HTML total size
	 *
	 * @return array
	 */
	private static function provide_cachesize(): array {

		if ( ! is_dir( self::$cache_directory ) ) {
			return [
				'size' => 'n/a',
			];
		}

		$bytes = 0;
		$it    = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				self::$cache_directory,
				FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
			)
		);
		foreach ( $it as $f ) {
			if ( $f->isFile() && strtolower( $f->getExtension() ) === 'html' ) {
				$bytes += $f->getSize();
			}
		}

		return [
			'size' => $bytes > 0 ? $bytes : 'n/a',
		];
	}

	/**
	 * Provide TTFB
	 *
	 * @return array
	 */
	private static function provide_ttfb(): array {

		if ( ! function_exists( 'wp_remote_get' ) ) {
			return [ 'ttfb_ms' => 'n/a' ];
		}

		$home = home_url( '/' );
		$ttfb = self::measure_ttfb( $home, true );

		/* fallback test file if header check fails */
		if ( $ttfb === null && defined( 'SWCFPC_PLUGIN_URL' ) ) {
			$fallback = untrailingslashit( SWCFPC_PLUGIN_URL ) . '/assets/testcache.html'; // @phpstan-ignore-line
			$ttfb     = self::measure_ttfb( $fallback, false );
		}

		return $ttfb !== null
		? [ 'ttfb_ms' => $ttfb ]
		: [ 'ttfb_ms' => 'n/a' ];
	}

	/**
	 * Measure TTFB to $url.
	 *
	 * @param string $url
	 * @param bool   $require_disk_hit  Require 'X-WP-SPC-Disk-Cache: HIT'?
	 * @return float|null  TTFB in ms (1-dec) or null on failure.
	 */
	private static function measure_ttfb( string $url, bool $require_disk_hit ): ?float {
		$start = microtime( true );
		$r     = wp_remote_get(
			$url,
			[
				'timeout'     => 5,
				'redirection' => 2,
				'sslverify'   => WP_DEBUG ? false : true,  // Needs to be off for local debug
			]
		);
		$ttfb  = ( microtime( true ) - $start ) * 1000;

		if ( is_wp_error( $r ) ) {
			return null;
		}

		if ( $require_disk_hit ) {
			$headers  = wp_remote_retrieve_headers( $r );
			$disk_hit = '';

			foreach ( $headers as $k => $v ) {
				if ( strtolower( $k ) === 'x-wp-spc-disk-cache' ) {
					$disk_hit = $v;
					break;
				}
			}

			if ( is_array( $disk_hit ) ) {
				$disk_hit = reset( $disk_hit );
			}

			if ( ! is_string( $disk_hit ) || strtoupper( $disk_hit ) !== 'HIT' ) {
				return null;
			}
		}

		return round( $ttfb, 1 );
	}

	/**
	 * Get the metrics configuration.
	 *
	 * @return array|array{enabled: bool, sampling: int, window: int}
	 */
	public static function get_config() {
		return array_merge(
			[
				'enabled'  => true,
				'window'   => 24,
				'sampling' => 10,
			],
			defined( 'SPC_METRICS_CONFIG' ) && is_array( SPC_METRICS_CONFIG ) ? SPC_METRICS_CONFIG : []
		);
	}
}
