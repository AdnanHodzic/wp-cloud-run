<?php

/**
 * Class Advanced_Ads_AdSense_Report
 *
 * Displays AdSense earnings on the ad overview page or the ad edit page.
 */
class Advanced_Ads_AdSense_Report {

	/**
	 * Domain name or ad unit to filter data with before display.
	 *
	 * @var string
	 */
	private $filter;

	/**
	 * Report type. 'unit' or 'domain'.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Object representing the current report data.
	 *
	 * @var Advanced_Ads_AdSense_Report_Data
	 */
	private $data_object;

	/**
	 * Error from the last attempt to call Google.
	 *
	 * @var string
	 */
	private $last_api_error_message;

	/**
	 * Instance constructor.
	 *
	 * @param string $type   report type.
	 * @param string $filter report filter.
	 */
	public function __construct( $type = 'unit', $filter = '' ) {
		$this->type = $type;

		if ( $type === 'domain' && ! empty( $filter ) ) {
			update_option( 'advanced-ads-adsense-dashboard-filter', $filter );
			// Backward compatibility: "*" was used to display data for all domains if API version prior to 2.0
			if ( $filter === '*' ) {
				$filter = '';
			}
		}

		$this->filter      = $filter;
		$this->data_object = Advanced_Ads_AdSense_Report_Data::get_data_from_options( $type );
	}

	/**
	 * Tries to get fresh data from Google.
	 *
	 * @return bool true if we got fresh data.
	 */
	public function refresh_report() {
		$api_helper = new Advanced_Ads_AdSense_Report_Api( $this->type );
		$error      = [];

		if ( $api_helper->has_token() ) {
			$response = $api_helper->call_google();
			if ( $response['status'] === true ) {
				$this->data_object->update_data_from_response( $response['response_body'] );

				return true;
			}
			if ( isset( $response['msg'] ) ) {
				$this->last_api_error_message = $response['msg'];

				return false;
			}
		}

		if ( $api_helper->has_token_error() ) {
			$error = $api_helper->get_token_error();
		}

		if ( isset( $error['msg'] ) ) {
			$this->last_api_error_message = $error['msg'];

			return false;
		}

		if ( isset( $error['raw'] ) ) {
			$this->last_api_error_message = $error['raw'];

			return false;
		}

		if ( empty( $this->last_api_error_message ) ) {
			$this->last_api_error_message = __( 'No valid tokens', 'advanced-ads' );
		}

		return false;
	}

	/**
	 * Retrieve the error message from the last API call.
	 *
	 * @return string Error message from the last API call.
	 */
	public function get_last_api_error() {
		if ( empty( $this->last_api_error_message ) ) {
			return '';
		}

		return $this->last_api_error_message;
	}

	/**
	 * Returns the report data object.
	 *
	 * @return Advanced_Ads_AdSense_Report_Data
	 */
	public function get_data() {
		return $this->data_object;
	}

	/**
	 * Build an return the HTML markup for display.
	 *
	 * @return string the final markup.
	 */
	public function get_markup() {
		if ( ! $this->get_data()->is_valid() ) {
			return '<p style="text-align:center;"><span class="report-need-refresh spinner advads-ad-parameters-spinner advads-spinner"></span></p>';
		}
		ob_start();
		$report_filter  = $this->filter;
		$report_domains = $this->data_object->get_domains();
		$sums           = $this->data_object->get_sums( $this->filter );
		$earning_cells  = '';
		foreach ( $sums as $index => $sum ) {
			$earning_cells .= $this->get_earning_cell( $sum, $index );
		}

		require_once GADSENSE_BASE_PATH . '/admin/views/adsense-report.php';
		return ob_get_clean();
	}

	/**
	 * Build and return the HTML markup for a given period.
	 *
	 * @param float  $sum   the earning for that period.
	 * @param string $index the period identifier.
	 *
	 * @return string HTML of the individual cell.
	 */
	private function get_earning_cell( $sum, $index ) {
		$period_strings = [
			'today'      => esc_html__( 'Today', 'advanced-ads' ),
			'yesterday'  => esc_html__( 'Yesterday', 'advanced-ads' ),
			/* translators: 1: The number of days. */
			'7days'      => sprintf( esc_html__( 'Last %1$d days', 'advanced-ads' ), 7 ),
			'this_month' => esc_html__( 'This Month', 'advanced-ads' ),
			/* translators: 1: The number of days. */
			'28days'     => sprintf( esc_html__( 'Last %1$d days', 'advanced-ads' ), 28 ),
		];

		$markup = '<div class="advads-flex1 advads-stats-box"><div>' . $period_strings[ $index ] . '</div>';
		$markup .= '<div class="advads-stats-box-main">';
		$markup .= number_format_i18n( ceil( 100 * $sum ) / 100, 2 );
		$markup .= ' ' . $this->get_data()->get_currency();
		$markup .= '</div></div>';

		return $markup;
	}
}
