<?php
/**
 * Render ad label position option for placements.
 *
 * @var string $_placement_slug slug of the current placement.
 * @var string $_position value of the position option.
 * @var bool $_clearfix value of the position clearfix option.
 */
?>
<label title="<?php esc_html_e( 'default', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][placement_position]" value="" <?php checked( $_position, 'default' ); ?>/>
	<?php esc_html_e( 'default', 'advanced-ads' ); ?>
</label>
<label title="<?php esc_html_e( 'left', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][placement_position]" value="left" <?php checked( $_position, 'left' ); ?>/>
	<?php esc_html_e( 'left', 'advanced-ads' ); ?></label>
<label title="<?php esc_html_e( 'center', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][placement_position]" value="center" <?php checked( $_position, 'center' ); ?>/>
	<?php esc_html_e( 'center', 'advanced-ads' ); ?></label>
<label title="<?php esc_html_e( 'right', 'advanced-ads' ); ?>">
	<input type="radio" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][placement_position]" value="right" <?php checked( $_position, 'right' ); ?>/>
	<?php esc_html_e( 'right', 'advanced-ads' ); ?></label>
<p><label>
	<input type="checkbox" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][placement_clearfix]" value="1" <?php checked( $_clearfix, 1 ); ?>/>
	<?php
	esc_html_e( 'Check this if you don’t want the following elements to float around the ad. (adds a placement_clearfix)', 'advanced-ads' );
	?>
	</label></p>
