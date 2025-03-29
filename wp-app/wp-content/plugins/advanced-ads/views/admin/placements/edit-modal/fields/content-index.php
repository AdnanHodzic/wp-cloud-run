<?php
/**
 * Render content index option for placements.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var string $placement_slug    Placement slug.
 * @var string $xpath             xpath option.
 * @var string $selected_tag      The currently selected tag.
 * @var array  $tags              Array of tags; indexded by tag value is label. cf. Content_Injection::get_tags().
 * @var string $index             The currently selected index.
 * @var array  $positions         Available positions.
 * @var string $selected_position The currently selected position, defaults to 'after'.
 * @var bool   $start_from_bottom Whether to start counting from bottom.
 */

?>
<select name="advads[placements][options][position]">
	<?php foreach ( $positions as $position => $position_label ) : ?>
		<option value="<?php echo esc_attr( $position ); ?>" <?php selected( $selected_position, $position ); ?>>
			<?php echo esc_html( $position_label ); ?>
		</option>
	<?php endforeach; ?>
</select>

<input type="number" name="advads[placements][options][index]" value="<?php echo (int) $index; ?>" min="1"/>.

<select class="advads-placements-content-tag" name="advads[placements][options][tag]">
	<?php foreach ( $tags as $tag => $tag_label ) : // phpcs:ignore ?>
		<option value="<?php echo esc_attr( $tag ); ?>" <?php selected( $selected_tag, $tag ); ?>>
			<?php echo esc_html( $tag_label ); ?>
		</option>
	<?php endforeach; ?>
</select>

<div id="advads-frontend-element-<?php echo esc_attr( $placement_slug ); ?>" class="advads-placements-content-custom-xpath<?php echo 'custom' !== $selected_tag ? ' hidden' : ''; ?>">
	<input name="advads[placements][options][xpath]" class="advads-frontend-element" type="text" value="<?php echo esc_html( $xpath ); ?>" placeholder="<?php esc_html_e( 'use xpath, e.g. `p[not(parent::blockquote)]`', 'advanced-ads' ); ?>"/>

	<button style="display:none; color: red;" type="button" class="advads-deactivate-frontend-picker button ">
		<?php echo esc_html_x( 'stop selection', 'frontend picker', 'advanced-ads' ); ?>
	</button>

	<button type="button" class="advads-activate-frontend-picker button " data-placementid="<?php echo esc_attr( $placement_slug ); ?>" data-pathtype="xpath" data-boundary="true">
		<?php esc_html_e( 'select position', 'advanced-ads' ); ?>
	</button>
</div>

<p>
	<label>
		<input type="checkbox" name="advads[placements][options][start_from_bottom]" value="1" <?php checked( $start_from_bottom ); ?>>
		<?php esc_html_e( 'start counting from bottom', 'advanced-ads' ); ?>
	</label>
</p>
