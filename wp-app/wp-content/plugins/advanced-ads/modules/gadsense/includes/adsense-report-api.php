<?php

/**
 * Retrieve report data from Google.
 */
class Advanced_Ads_AdSense_Report_Api {

	/**
	 * Version of the AdSense Management API in use (for getting fresh data).
	 *
	 * @var string
	 */
	const API_VERSION = '2.0';

	/**
	 * Report API endpoint.
	 *
	 * @var string
	 */
	private $endpoint_url;

	/**
	 * Report type
	 *
	 * @var string
	 */
	private $type;

	/**
	 * The API access token or an error array.
	 *
	 * @var array|string
	 */
	private $access_token;

	/**
	 * The current connected AdSense account.
	 *
	 * @var string
	 */
	private $publisher_id;

	/**
	 * Instance constructor.
	 *
	 * @param string $type report type.
	 */
	public function __construct( $type ) {
		$publisher_id       = Advanced_Ads_AdSense_Data::get_instance()->get_adsense_id();
		$this->type         = $type;
		$this->access_token = Advanced_Ads_AdSense_MAPI::get_access_token( $publisher_id );
		$this->publisher_id = $publisher_id;

		$endpoint_args      = [
			'startDate.year'    => '%SY%', // Start date's year - integer (4 digits).
			'startDate.month'   => '%SM%', // Start date's month - integer.
			'startDate.day'     => '%SD%', // Start date's integer - integer.
			'endDate.year'      => '%EY%', // End date's year - integer (4 digits).
			'endDate.month'     => '%EM%', // End date's month - integer.
			'endDate.day'       => '%ED%', // End date's integer - integer.
			'dimension1'        => '%DIM%', // Primary reporting dimension (domain name or ad unit name).
			'dimension2'        => 'DATE', // Secondary reporting dimension.
			'metrics'           => 'ESTIMATED_EARNINGS', // Report metrics.
			'reportingTimeZone' => 'ACCOUNT_TIME_ZONE', // Time zone used in report data.
		];
		$this->endpoint_url = str_replace( [ 'dimension1', 'dimension2' ], 'dimensions', add_query_arg( $endpoint_args, 'https://adsense.googleapis.com/v2/accounts/%pubid%/reports:generate' ) );
	}

	/**
	 * Checks if the current setup has an access token.
	 *
	 * @return bool true if there is a token.
	 */
	public function has_token() {
		return is_string( $this->access_token );
	}

	/**
	 * Get access token related error message.
	 *
	 * @return array Array of error messages.
	 */
	public function get_token_error() {
		return is_string( $this->access_token ) ? [] : $this->access_token;
	}

	/**
	 * Check if there is an error related to access tokens.
	 *
	 * @return bool true if any error happened when requesting an access token.
	 */
	public function has_token_error() {
		return ! is_string( $this->access_token );
	}

	/**
	 * Perform the actual call to Google for fresh data.
	 *
	 * @return array associative array with the response or with error data in case of failure.
	 */
	public function call_google() {
		$dimension  = $this->type === 'unit' ? 'AD_UNIT_ID' : 'DOMAIN_NAME';
		$today      = new DateTimeImmutable();
		$start_date = $today->sub( date_interval_create_from_date_string( '28 days' ) );
		// Replace placeholder in the endpoint with actual arguments.
		$url = str_replace(
			[
				'%pubid%',
				'%DIM%',
				'%SY%',
				'%SM%',
				'%SD%',
				'%EY%',
				'%EM%',
				'%ED%',
			],
			[
				$this->publisher_id,
				$dimension,
				$start_date->format( 'Y' ),
				$start_date->format( 'n' ),
				$start_date->format( 'j' ),
				$today->format( 'Y' ),
				$today->format( 'n' ),
				$today->format( 'j' ),
			],
			$this->endpoint_url
		);

		$headers = [
			'Authorization' => 'Bearer ' . $this->access_token,
		];

		$response = wp_remote_get( $url, [ 'headers' => $headers ] );
		Advanced_Ads_AdSense_MAPI::log( 'Fetched AdSense Report from ' . $url );

		if ( is_wp_error( $response ) ) {
			return [
				'status' => false,
				// translators: AdSense ID.
				'msg'    => sprintf( esc_html__( 'Error while retrieving report for "%s".', 'advanced-ads' ), $this->publisher_id ),
				'raw'    => $response->get_error_message(),
			];
		}

		$response_body = json_decode( $response['body'], true );

		if ( ! isset( $response_body['startDate'] ) ) {
			return [
				'status' => false,
				// translators: AdSense ID.
				'msg'    => sprintf( esc_html__( 'Invalid response while retrieving report for "%s".', 'advanced-ads' ), $this->publisher_id ),
				'raw'    => $response['body'],
			];
		}

		$response_body['api_version'] = self::API_VERSION;
		$response_body['timestamp']   = time();

		return [
			'status'        => true,
			'response_body' => $response_body,
		];
	}

}
