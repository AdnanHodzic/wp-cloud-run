<?php
/**
 * Markup for the placement item select box.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

$placement_type = wp_advads_get_placement_type( 'default' );
?>
<select id="advads-placement-item" name="advads[placement][item]" class="advads-placement-item-select">
	<option value=""><?php esc_html_e( '--not selected--', 'advanced-ads' ); ?></option>

	<?php foreach ( $placement_type->get_allowed_items() as $item_group ) : ?>
		<optgroup label="<?php echo esc_attr( $item_group['label'] ); ?>">
			<?php foreach ( $item_group['items'] as $item_id => $item_name ) : ?>
				<option value="<?php echo esc_attr( $item_id ); ?>">
					<?php echo esc_html( $item_name ); ?>
				</option>
			<?php endforeach; ?>
		</optgroup>
	<?php endforeach; ?>
</select>
