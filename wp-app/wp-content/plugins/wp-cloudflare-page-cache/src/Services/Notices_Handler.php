<?php

namespace SPC\Services;

class Notices_Handler {

	public const CONFLICTS_NOTICE = 'conflicts';

	private const REGISTRY = [
		self::CONFLICTS_NOTICE => 'spc_conflicts_notice_dismissed',
	];

	/**
	 * Check if a notice is dismissed.
	 *
	 * @param string $notice Notice key.
	 *
	 * @return bool
	 */
	public static function is_dismissed( string $notice ) {
		if ( ! self::is_valid_notice( $notice ) ) {
			return false;
		}

		return (bool) get_option( self::REGISTRY[ $notice ], false );
	}

	/**
	 * Dismiss a notice.
	 *
	 * @param string $notice Notice key.
	 *
	 * @return bool
	 */
	public static function dismiss( string $notice ) {
		if ( ! self::is_valid_notice( $notice ) ) {
			return false;
		}

		update_option( self::REGISTRY[ $notice ], true );

		return true;
	}

	/**
	 * Check if notice key is valid
	 *
	 * @param string $notice_key
	 * @return bool
	 */
	private static function is_valid_notice( string $notice_key ): bool {
		return array_key_exists( $notice_key, self::REGISTRY );
	}
}
