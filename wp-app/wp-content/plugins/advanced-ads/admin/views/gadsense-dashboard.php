<?php
/**
 * AdSense Earnings Widget Content
 *
 * @package    AdvancedAds
 * @var string $report_type    Value is 'domain'.
 * @var string $report_filter  Filter to be used.
 * @var string $pub_id         Adsense ID.
 */

if ( $pub_id ) {
	Advanced_Ads_Overview_Widgets_Callbacks::adsense_stats_js( $pub_id );
	$arguments = [
		'type'   => $report_type,
		'filter' => $report_filter,
	];
	$report    = new Advanced_Ads_AdSense_Report( $report_type, $report_filter );

	echo '<div class="advanced-ads-adsense-dashboard" data-arguments="' . esc_js( wp_json_encode( $arguments ) ) . '">';
	echo wp_kses_post( $report->get_markup() );
	echo '</div>';
} else {
	echo esc_html__( 'There is an error in your AdSense setup.', 'advanced-ads' );
}
