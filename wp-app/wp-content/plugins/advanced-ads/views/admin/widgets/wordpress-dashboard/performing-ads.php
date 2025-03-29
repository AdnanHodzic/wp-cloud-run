<?php
/**
 * Render performing ads section
 *
 * @package AdvancedAds
 */

$periods = [
	'today'     => __( 'today', 'advanced-ads' ),
	'yesterday' => __( 'yesterday', 'advanced-ads' ),
	'last7days' => __( 'last 7 days', 'advanced-ads' ),
	'thismonth' => __( 'this month', 'advanced-ads' ),
	'lastmonth' => __( 'last month', 'advanced-ads' ),
	'thisyear'  => __( 'this year', 'advanced-ads' ),
	'lastyear'  => __( 'last year', 'advanced-ads' ),
	'custom'    => __( 'custom', 'advanced-ads' ),
];
?>
<div class="advads-widget-wrapper">
	<div class="section-title flex flex-row items-center flex-wrap gap-y-2 gap-x-2" style="padding-top: 0.3rem; padding-bottom: 0.3rem;">
		<h3 class="!mt-0">
			<?php esc_html_e( 'Best-performing Ads for', 'advanced-ads' ); ?>
		</h3>
		<select name="advads-performing-ads-period" disabled>
			<?php foreach ( $periods as $key => $period ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php echo 'last7days' === $key ? 'selected' : ''; ?>><?php echo esc_html( $period ); ?></option>
			<?php endforeach; ?>
		</select>
		<div class="advads-custom-period">
			<div class="advads-custom-period-wrapper">
				<fieldset class="flex gap-x-2">
					<input type="text" name="advads-custom-from" autocomplete="off" size="10" maxlength="10" placeholder="<?php esc_html_e( 'from', 'advanced-ads' ); ?>"/>
					<input type="text" name="advads-custom-to" autocomplete="off" size="10" maxlength="10" placeholder="<?php esc_html_e( 'to', 'advanced-ads' ); ?>"/>
				</fieldset>
			</div>
		</div>
	</div>
	<div class="advads-performing-ads-track disabled">
		<ul>
			<li class="active" data-tab="clicks">Clicks</li>
			<li data-tab="impressions">Impressions</li>
			<li data-tab="ctr">CTR</li>
		</ul>
		<a href="<?php echo esc_attr( admin_url( 'admin.php?page=advanced-ads-stats' ) ); ?>"><?php esc_html_e( 'See full statistics', 'advanced-ads' ); ?></a>
	</div>

	<?php if ( ! defined( 'AAT_FILE' ) ) : ?>
		<p>
			<?php esc_html_e( 'No tracking add-on installed.', 'advanced-ads' ); ?>
		</p>
		<p>
			<a class="go-pro" href="https://wpadvancedads.com/pricing/?utm_source=advanced-ads&utm_medium=link&utm_campaign=dashboard" target="_blank">
				<?php esc_html_e( 'Advanced Ads All Access includes the Tracking add-on', 'advanced-ads' ); ?><span aria-hidden="true" class="dashicons dashicons-external"></span>
			</a>
		</p>
	<?php endif; ?>

	<?php do_action( 'advanced-ads-dashboard-performing-ads' ); ?>
</div>
