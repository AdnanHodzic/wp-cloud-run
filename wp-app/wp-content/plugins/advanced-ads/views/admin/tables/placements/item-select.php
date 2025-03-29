<?php
/**
 * Markup for the placement item select box.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var Placement $placement Placement instance.
 */

?>
<select
	id="advads-placements-item-<?php echo esc_attr( $placement->get_id() ); ?>"
	name="item"
	class="advads-placement-item-select js-update-placement-item"
	data-placement-id="<?php echo esc_attr( $placement->get_id() ); ?>"
>
	<option value=""><?php esc_html_e( '--not selected--', 'advanced-ads' ); ?></option>

	<?php foreach ( $placement->get_type_object()->get_allowed_items() as $item_group ) : ?>
		<optgroup label="<?php echo esc_attr( $item_group['label'] ); ?>">
			<?php foreach ( $item_group['items'] as $item_id => $item_name ) : ?>
				<option value="<?php echo esc_attr( $item_id ); ?>"<?php selected( $placement->get_item(), $item_id ); ?>>
					<?php echo esc_html( $item_name ); ?>
				</option>
			<?php endforeach; ?>
		</optgroup>
	<?php endforeach; ?>
</select>
