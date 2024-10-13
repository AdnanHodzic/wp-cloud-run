<?php
/**
 * Render ad label option for placements.
 *
 * @var string $_placement_slug slug of the current placement.
 * @var string $_label value of the label option.
 * @package Advanced_Ads_Admin
 */

?>
<label title="<?php esc_html_e( 'default', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][ad_label]" value="default" <?php checked( $_label, 'default' ); ?>/>
	<?php esc_html_e( 'default', 'advanced-ads' ); ?>
</label>
<label title="<?php esc_html_e( 'enabled', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][ad_label]" value="enabled" <?php checked( $_label, 'enabled' ); ?>/>
	<?php esc_html_e( 'enabled', 'advanced-ads' ); ?>
</label>
<label title="<?php esc_html_e( 'disabled', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][ad_label]" value="disabled" <?php checked( $_label, 'disabled' ); ?>/>
	<?php esc_html_e( 'disabled', 'advanced-ads' ); ?>
</label>
