<?php
/**
 * Render content index option for placements.
 *
 * @var string $_placement_slug slug of the current placement.
 * @var array $_placement information of the current placement.
 * @var string $option_xpath xpath option.
 * @var string $option_tag tag option.
 * @var string $option_index index option.
 * @var array $positions positions option.
 */
?>
<select name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][position]">
	<?php foreach ( $positions as $_pos_key => $_pos ) : ?>
	<option value="<?php echo esc_attr( $_pos_key ); ?>"
							  <?php
								if ( isset( $_placement['options']['position'] ) ) {
									selected( $_placement['options']['position'], $_pos_key );
								}
								?>
	>
		<?php echo esc_html( $_pos ); ?></option>
	<?php endforeach; ?>
</select>

<input type="number" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][index]" value="<?php echo absint( $option_index ); ?>" min="1"/>.

<?php $tags = Advanced_Ads_Placements::tags_for_content_injection(); ?>
<select class="advads-placements-content-tag" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][tag]">
	<?php foreach ( $tags as $_tag_key => $_tag ) : ?>
	<option value="<?php echo esc_attr( $_tag_key ); ?>"
							  <?php
								if ( $option_tag ) {
									selected( $option_tag, $_tag_key ); }
								?>
		><?php echo esc_html( $_tag ); ?></option>
	<?php endforeach; ?>
</select>

<div id="advads-frontend-element-<?php echo esc_attr( $_placement_slug ); ?>" class="advads-placements-content-custom-xpath<?php echo 'custom' !== $option_tag ? ' hidden' : ''; ?>">
	<input name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][xpath]"
		class="advads-frontend-element "
		type="text"
		value="<?php echo esc_html( $option_xpath ); ?>"
		placeholder="<?php esc_html_e( 'use xpath, e.g. `p[not(parent::blockquote)]`', 'advanced-ads' ); ?>"/>
	<button style="display:none; color: red;" type="button" class="advads-deactivate-frontend-picker button "><?php echo esc_html_x( 'stop selection', 'frontend picker', 'advanced-ads' ); ?></button>
	<button type="button" class="advads-activate-frontend-picker button " data-placementid="<?php echo esc_attr( $_placement_slug ); ?>" data-pathtype="xpath" data-boundary="true"><?php esc_html_e( 'select position', 'advanced-ads' ); ?></button>
</div>

<p><label><input type="checkbox" name="advads[placements][<?php echo esc_attr( $_placement_slug ); ?>][options][start_from_bottom]" value="1"
																	 <?php
																		if ( isset( $_placement['options']['start_from_bottom'] ) ) {
																			checked( $_placement['options']['start_from_bottom'], 1 ); }
																		?>
/><?php esc_html_e( 'start counting from bottom', 'advanced-ads' ); ?></label></p>
