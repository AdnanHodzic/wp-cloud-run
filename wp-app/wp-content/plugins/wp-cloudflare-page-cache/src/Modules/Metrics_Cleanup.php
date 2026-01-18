<?php

namespace SPC\Modules;

use SPC\Constants;
use DateTime;
use DateTimeZone;
use SPC\Services\Metrics;

class Metrics_Cleanup implements Module_Interface {
	const HOOK = 'spc/prune_metric_files';

	/**
	 * Register the recurring task and callback.
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'schedule_cleanup' ] );

		add_action( self::HOOK, [ $this, 'prune_files' ] );
	}

	/**
	 * Schedule the daily cleanup.
	 *
	 * @return void
	 */
	public function schedule_cleanup() {
		if ( ! is_dir( SPC_METRICS_DIR ) ) {
			return;
		}

		if ( as_next_scheduled_action( self::HOOK ) ) {
			return;
		}

		as_schedule_recurring_action(
			(int) gmdate( 'U', strtotime( 'tomorrow 00:07 UTC' ) ),
			DAY_IN_SECONDS,
			self::HOOK,
			[],
			Constants::ACTION_SCHEDULER_GROUP
		);
	}

	/**
	 * Delete old bucket files based on their hour‐stamp.
	 *
	 * @return void
	 */
	public static function prune_files(): void {
		if ( ! is_dir( SPC_METRICS_DIR ) ) {
			return;
		}

		$cfg               = Metrics::get_config();
		$window_hours      = isset( $cfg['window'] ) ? max( 1, (int) $cfg['window'] ) : 24;
		$cutoff_in_seconds = time() - $window_hours * HOUR_IN_SECONDS;

		foreach ( glob( SPC_METRICS_DIR . '/*.txt' ) as $file ) {
			$base = basename( $file );

			// Extract 10-digit hour stamp: hit_2025061213.txt -> 2025061213
			if ( ! preg_match( '/^(?:hit|miss)_(\d{10})\.txt$/', $base, $m ) ) {
				continue;
			}

			$bucket_timestamp = DateTime::createFromFormat(
				'YmdH',
				$m[1],
				new DateTimeZone( 'UTC' )
			)->getTimestamp();

			if ( $bucket_timestamp >= $cutoff_in_seconds ) {
				continue;
			}

			@unlink( $file );
		}
	}
}
