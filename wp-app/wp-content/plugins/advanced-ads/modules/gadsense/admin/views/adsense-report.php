<?php
/**
 * AdSense report markup.
 *
 * @var Advanced_Ads_AdSense_Report $this           report instance.
 * @var array                       $report_domains domain names included in the report.
 * @var string                      $report_filter  ad unit or domain name to filter the output with.
 * @var array                       $sums           daily sums of earnings.
 * @var string                      $earning_cells  markup for each earning period.
 */
$time_zone      = Advanced_Ads_Utils::get_wp_timezone();
$data_timestamp = date_create( '@' . $this->get_data()->get_timestamp() );
$data_timestamp->setTimezone( $time_zone );

?>
<div class="advads-flex">
	<?php echo wp_kses_post( $earning_cells ); ?>
	<div class="advads-flex1 advads-stats-box">
		<?php if ( $this->type === 'domain' ) : // Adds the dropdown list of domain names. ?>
			<div class="advads-stats-dd-container">
				<div class="advads-stats-dd-button"><span class="dashicons dashicons-admin-multisite"></span>
					<div class="advads-stats-dd-items">
						<div class="advads-stats-dd-item<?php echo in_array( $report_filter, [ '*', '' ], true ) ? ' current-filter' : ''; ?>" data-domain="*">
							<?php esc_html_e( 'All', 'advanced-ads' ); ?>
						</div><!-- .advads-stats-dd-item -->
						<?php foreach ( $report_domains as $domain_name ) : ?>
							<div class="advads-stats-dd-item<?php echo ( $domain_name === $report_filter ) ? ' current-filter' : ''; ?>" data-domain="<?php echo esc_attr( $domain_name ); ?>">
								<?php echo esc_html( $domain_name ); ?>
							</div><!-- .advads-stats-dd-item -->
						<?php endforeach; ?>
					</div><!-- .advads-stats-dd-items -->
				</div><!-- .advads-stats-dd-button -->
			</div><!-- .advads-stats-dd-container -->
		<?php endif; ?>
		<div class="advads-stats-age"><?php echo esc_html( $data_timestamp->format( get_option( 'time_format' ) ) ); ?></div>
	</div><!-- .advads-stats-box-->
</div><!-- .advads-flex -->
