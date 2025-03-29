<?php
/**
 * Render IP Address settings
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

?>

<input
	id="<?php echo esc_attr( ADVADS_SLUG ); ?>-disable-ads-ip-address"
	type="checkbox"
	value="1"
	name="<?php echo esc_attr( ADVADS_SLUG ); ?>[hide-for-ip-address][enabled]"
	class="advads-has-sub-settings"
	<?php checked( $disable_ip_addr, 1 ); ?>
>
<label for="<?php echo esc_attr( ADVADS_SLUG ); ?>-disable-ads-ip-address">
	<?php esc_html_e( 'Activate module', 'advanced-ads' ); ?>
</label>
<span class="advads-help">
	<span class="advads-tooltip">
		<?php esc_html_e( 'Enter one IP address per line for which no ads are displayed.', 'advanced-ads' ); ?>
	</span>
</span>

<div class="advads-sub-settings" style="margin-top: 1em;">
	<textarea
		cols="50"
		rows="5"
		name="<?php echo esc_attr( ADVADS_SLUG ); ?>[hide-for-ip-address][ips]"
		placeholder="<?php esc_html_e( 'Enter one IP address per line for which no ads are displayed.', 'advanced-ads' ); ?>"
	><?php echo esc_textarea( $ip_address ); ?></textarea>
</div>
