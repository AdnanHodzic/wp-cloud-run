<?php
/**
 * Render ad label position option for placements.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var string $position Value of the position option.
 * @var bool   $clearfix Value of the position clearfix option.
 */

?>
<label title="<?php esc_html_e( 'default', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][options][placement_position]" value="default" <?php checked( $position, 'default' ); ?>/>
	<?php esc_html_e( 'default', 'advanced-ads' ); ?>
</label>

<label title="<?php esc_html_e( 'left', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][options][placement_position]" value="left" <?php checked( $position, 'left' ); ?>/>
	<?php esc_html_e( 'left', 'advanced-ads' ); ?>
</label>

<label title="<?php esc_html_e( 'center', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][options][placement_position]" value="center" <?php checked( $position, 'center' ); ?>/>
	<?php esc_html_e( 'center', 'advanced-ads' ); ?>
</label>

<label title="<?php esc_html_e( 'right', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][options][placement_position]" value="right" <?php checked( $position, 'right' ); ?>/>
	<?php esc_html_e( 'right', 'advanced-ads' ); ?>
</label>

<p>
	<label>
		<input type="checkbox" name="advads[placements][options][placement_clearfix]" value="1" <?php checked( $clearfix, 1 ); ?>/>
		<?php esc_html_e( 'Check this if you don’t want the following elements to float around the ad. (adds a placement_clearfix)', 'advanced-ads' ); ?>
	</label>
</p>
