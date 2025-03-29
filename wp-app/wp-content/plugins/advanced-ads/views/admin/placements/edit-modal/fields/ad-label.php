<?php
/**
 * Render ad label option for placements.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var string $label value of the label option.
 */

?>
<label title="<?php esc_html_e( 'default', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][options][ad_label]" value="default" <?php checked( $label, 'default' ); ?>/>
	<?php esc_html_e( 'default', 'advanced-ads' ); ?>
</label>
<label title="<?php esc_html_e( 'enabled', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][options][ad_label]" value="enabled" <?php checked( $label, 'enabled' ); ?>/>
	<?php esc_html_e( 'enabled', 'advanced-ads' ); ?>
</label>
<label title="<?php esc_html_e( 'disabled', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][options][ad_label]" value="disabled" <?php checked( $label, 'disabled' ); ?>/>
	<?php esc_html_e( 'disabled', 'advanced-ads' ); ?>
</label>
