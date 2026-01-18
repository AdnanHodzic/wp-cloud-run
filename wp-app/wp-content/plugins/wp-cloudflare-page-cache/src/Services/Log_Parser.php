<?php

namespace SPC\Services;

/**
 * Log parser.
 *
 * Parses log data and returns it in a structured format.
 */
class Log_Parser {
	private const LINES_TO_RETRIEVE = 100;
	private const MAX_LINES_OUTPUT  = 20;

	/**
	 * Parse log data.
	 *
	 * @return array {
	 *  seconds_ago:int,
	 *  identifier:string,
	 *  message:string,
	 * }[]
	 */
	private function get_parsed_logs() {
		/**
		 * @var \SW_CLOUDFLARE_PAGECACHE $sw_cloudflare_pagecache
		 */
		global $sw_cloudflare_pagecache;

		$log_data = $sw_cloudflare_pagecache->get_logger()->get_logs( self::LINES_TO_RETRIEVE );

		$log_data = explode( PHP_EOL, $log_data );

		// remove duplicate entries
		$log_data = array_unique( $log_data );

		// filter out non-log lines (we have print_r's inside log lines).
		$log_data = array_filter(
			$log_data,
			function ( $line ) {
				return strpos( $line, '[' ) === 0 && strpos( $line, 'Array' ) === false;
			}
		);

		// parse log line
		$log_data = array_map( [ $this, 'parse_log_line' ], $log_data );

		// Remove any lines that have JSON data in them.
		$log_data = array_filter(
			$log_data,
			function ( $line ) {
				return strpos( $line['message'], '{' ) === false
					&& strpos( $line['message'], '}' ) === false
					&& strpos( $line['message'], '[' ) === false
					&& strpos( $line['message'], ']' ) === false;
			}
		);

		// limit the number of lines to output and order by most recent
		$log_data = array_slice( array_reverse( $log_data ), 0, self::MAX_LINES_OUTPUT );

		return $log_data;
	}

	/**
	 * Parse log line.
	 *
	 * @param string $line
	 *
	 * @return array {
	 *  seconds_ago:int,
	 *  identifier:string,
	 *  message:string,
	 * }
	 */
	private function parse_log_line( $line ) {
		// [timestamp] [identifier] Message
		preg_match( '/^\[(.*?)\]\s+\[(.*?)\]\s+(.*?)$/', $line, $matches );
		$timestamp  = $matches[1];
		$identifier = $matches[2];
		$message    = $matches[3];

		// calculate time ago - both timestamps should be UTC
		$seconds_ago = (int) gmdate( 'U' ) - strtotime( $timestamp . ' UTC' );

		return [
			'seconds_ago' => $seconds_ago,
			'identifier'  => $identifier,
			'message'     => trim( $message ),
		];
	}

	/**
	 * Get parsed log data.
	 *
	 * @return array
	 */
	public function get_log_data() {
		return $this->get_parsed_logs();
	}
}
