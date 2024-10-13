<?php

/**
 * Handle all report data received from Google
 */
class Advanced_Ads_AdSense_Report_Data implements Serializable {

	/**
	 * Cached data life span.
	 *
	 * @var integer
	 */
	const CACHE_DURATION = 3600;

	/**
	 * DB option name for report by domain.
	 *
	 * @var string
	 */
	const DOMAIN_OPTION = 'advanced_ads_adsense_report_domain';

	/**
	 * DB option name for report by ad unit.
	 *
	 * @var string
	 */
	const UNIT_OPTION = 'advanced_ads_adsense_report_unit';

	/**
	 * Daily earnings.
	 *
	 * @var null|array
	 */
	private $earnings;

	/**
	 * Report type. 'unit' or 'domain'.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * UNIX timestamp at which the data was obtained from Google.
	 *
	 * @var int
	 */
	private $timestamp = 0;

	/**
	 * Currency used in the report.
	 *
	 * @var string
	 */
	private $currency = '';

	/**
	 * Version of Google AdSense Management API used.
	 *
	 * @var string
	 */
	private $version = '';

	/**
	 * List of domain names found in the report data.
	 *
	 * @var array
	 */
	private $domains = [];

	/**
	 * Instance constructor.
	 *
	 * @param string $type report type.
	 */
	public function __construct( $type = 'unit' ) {
		$this->type = $type;
	}

	/**
	 * Get all domains.
	 *
	 * @return array the domain list.
	 */
	public function get_domains() {
		return $this->domains;
	}

	/**
	 * Get the report timestamp.
	 *
	 * @return int data timestamp.
	 */
	public function get_timestamp() {
		return $this->timestamp;
	}

	/**
	 * Get the currency used in the report.
	 *
	 * @return string the currency code.
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * Serialize an instance of this class into a string. For PHP version >= 7.4
	 *
	 * @return array
	 */
	public function __serialize() {
		return [
			'earnings'  => $this->earnings,
			'type'      => $this->type,
			'timestamp' => $this->timestamp,
			'currency'  => $this->currency,
			'domains'   => $this->domains,
		];
	}

	/**
	 * Recreate an instance of this class from a string. For PHP version >= 7.4
	 *
	 * @param array $data the array from __serialize.
	 *
	 * @return void
	 */
	public function __unserialize( $data ) {
		$this->earnings  = $data['earnings'] ?? null;
		$this->type      = $data['type'] ?? null;
		$this->timestamp = $data['timestamp'] ?? 0;
		$this->currency  = $data['currency'] ?? '';
		$this->domains   = $data['domains'] ?? [];
	}

	/**
	 * Returns serialized object properties. For PHP version < 7.4
	 *
	 * @return string the serialized data.
	 */
	public function serialize() {
		return serialize(
			[
				'earnings'  => $this->earnings,
				'type'      => $this->type,
				'timestamp' => $this->timestamp,
				'currency'  => $this->currency,
				'domains'   => $this->domains,
			]
		);
	}

	/**
	 * Set object properties from serialized data string. For PHP version < 7.4
	 *
	 * @param string $data serilaized data from DB.
	 */
	public function unserialize( $data ) {
		try {
			$unwrapped = unserialize( $data );
		} catch ( Exception $ex ) {
			$unwrapped = [];
		}
		$this->__unserialize( $unwrapped );
	}

	/**
	 * Update object properties and DB record from a API response.
	 *
	 * @param array $response API call response from Google.
	 */
	public function update_data_from_response( $response ) {
		$headers         = [];
		$this->version   = $response['api_version'];
		$this->timestamp = $response['timestamp'];
		foreach ( $response['headers'] as $header ) {
			if ( $header['type'] === 'METRIC_CURRENCY' ) {
				$this->currency = $header['currencyCode'];
			}
			$headers[] = $header['name'];
		}
		$earnings = [];

		if ( ! empty( $response['rows'] ) ) {
			foreach ( $response['rows'] as $row ) {
				$earning = new StdClass();
				foreach ( $row['cells'] as $index => $cell ) {
					switch ( $headers[ $index ] ) {
						case 'DATE':
							$earning->date = new DateTimeImmutable( $cell['value'] );
							break;
						case 'ESTIMATED_EARNINGS':
							$earning->estimated_earning = (float) $cell['value'];
							break;
						default: // "DOMAIN_NAME" or "AD_UNIT_ID".
							$earning->{strtolower( $headers[ $index ] )} = $cell['value'];
							if ( $headers[ $index ] === 'DOMAIN_NAME' && ! in_array( $cell['value'], $this->domains, true ) ) {
								$this->domains[] = $cell['value'];
							}
					}
				}
				$earnings[] = $earning;
			}
		}
		$this->earnings = $earnings;
		$option_name    = $this->type === 'unit' ? self::UNIT_OPTION : self::DOMAIN_OPTION;

		// Delete old options entries.
		delete_option( 'advanced_ads_adsense_report_DATE_AD_UNIT_CODE_EARNINGS_dashboard' );
		delete_option( 'advanced_ads_adsense_report_DATE_DOMAIN_NAME_EARNINGS_dashboard' );

		// Save the data instance in DB.
		update_option( $option_name, $this );
	}

	/**
	 * Returns a data object constructed from saved data. Constructs a new one if there is no usable data.
	 *
	 * @param string $type report type.
	 *
	 * @return Advanced_Ads_AdSense_Report_Data
	 */
	public static function get_data_from_options( $type ) {
		$option_name = $type === 'unit' ? self::UNIT_OPTION : self::DOMAIN_OPTION;
		$option      = get_option( $option_name, false );
		if ( $option === false ) {
			return new self( $type );
		}

		// PHP version < 7.4.
		if ( $option instanceof self ) {
			return $option;
		}

		try {
			$unserialized = unserialize( $option );
			if ( $unserialized instanceof self ) {
				return $unserialized;
			}

			return new self( $type );
		} catch ( Exception $ex ) {
			return new self( $type );
		}
	}

	/**
	 * Checks if cached data need to be updated.
	 *
	 * @return bool true if the stored data has not expired yet.
	 */
	public function is_valid() {
		return $this->timestamp + self::CACHE_DURATION > time();
	}

	/**
	 * Get the earnings sums for display.
	 *
	 * @param string $filter filter sums by a given domain name or ad unit.
	 *
	 * @return int[]
	 */
	public function get_sums( $filter = '' ) {
		$today     = new DateTimeImmutable();
		$yesterday = $today->sub( date_interval_create_from_date_string( '1 day' ) );
		$prev7     = $today->sub( date_interval_create_from_date_string( '7 days' ) );
		$prev28    = $today->sub( date_interval_create_from_date_string( '28 days' ) );
		$sums      = [
			'today'      => 0,
			'yesterday'  => 0,
			'7days'      => 0,
			'this_month' => 0,
			'28days'     => 0,
		];

		// Unit type reports should always have the ad unit id specified.
		if ( $filter === '' && $this->type === 'unit' ) {
			return $sums;
		}

		foreach ( $this->earnings as $value ) {
			if (
				( $this->type === 'unit' && false === strpos( $value->ad_unit_id, $filter ) )
				|| ( ! empty( $filter ) && $this->type === 'domain' && $filter !== $value->domain_name )
			) {
				continue;
			}

			if ( $this->date_ymd( $value->date ) === $this->date_ymd( $today ) ) {
				$sums['today'] += $value->estimated_earning;
			}
			if ( $this->date_ymd( $value->date ) === $this->date_ymd( $yesterday ) ) {
				$sums['yesterday'] += $value->estimated_earning;
			}
			if ( $this->date_ymd( $value->date ) >= $this->date_ymd( $prev7 ) ) {
				$sums['7days'] += $value->estimated_earning;
			}
			if ( $this->date_ymd( $value->date ) >= $this->date_ymd( $prev28 ) ) {
				$sums['28days'] += $value->estimated_earning;
			}
			if ( $value->date->format( 'm' ) === $today->format( 'm' ) ) {
				$sums['this_month'] += $value->estimated_earning;
			}
		}

		return $sums;
	}

	/**
	 * Get an integer representation of a DateTime object to be used in date comparison.
	 *
	 * @param DateTimeInterface $date the date object.
	 *
	 * @return int
	 */
	private function date_ymd( $date ) {
		if ( $date instanceof DateTimeInterface ) {
			return (int) $date->format( 'Ymd' );
		}

		return 0;
	}
}
