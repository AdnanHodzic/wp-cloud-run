<?php
/**
 * Filter placement types.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var string $current_type Currently filtered placement type.
 */

?>
<label class="screen-reader-text" for="advads_filter_placement_type">
	<?php esc_html_e( 'Placement Type', 'advanced-ads' ); ?>
</label>
<select class="advads_filter_placement_type" id="advads_filter_placement_type" name="placement-type">
	<option value=""><?php esc_html_e( '- show all types -', 'advanced-ads' ); ?></option>
	<?php
	$types = wp_advads_get_placement_type_manager()->get_dropdown_options();
	foreach ( $types as $id => $title ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		?>
		<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $current_type ); ?>>
			<?php echo esc_html( $title ); ?>
		</option>
	<?php endforeach; ?>
</select>
