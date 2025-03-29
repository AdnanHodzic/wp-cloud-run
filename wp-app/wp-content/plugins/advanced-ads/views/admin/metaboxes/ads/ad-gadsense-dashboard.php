<?php
/**
 * Render Adsense meta box on ad edit screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var Ad     $ad            Ad instance.
 * @var string $report_type   Value is 'domain'.
 * @var string $report_filter Filter to be used.
 * @var string $pub_id        Adsense ID.
 */

$pub_id = Advanced_Ads_AdSense_Data::get_instance()->get_adsense_id();

// Early bail!!
if ( ! $pub_id ) {
	esc_html_e( 'There is an error in your AdSense setup.', 'advanced-ads' );
	return;
}

Advanced_Ads_Overview_Widgets_Callbacks::adsense_stats_js( $pub_id );
$arguments = [
	'type'   => $report_type,
	'filter' => $report_filter,
];
$report    = new Advanced_Ads_AdSense_Report( $report_type, $report_filter );

echo '<div class="advanced-ads-adsense-dashboard" data-arguments="' . esc_js( wp_json_encode( $arguments ) ) . '">';
	echo wp_kses_post( $report->get_markup() );
echo '</div>';
