<?php
/**
 * Render all placement types for forms.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

$placement_types = wp_advads_get_placement_types();
if ( empty( $placement_types ) ) {
	return '';
}

usort(
	$placement_types,
	function ( $a, $b ) {
		return $a->get_order() - $b->get_order();
	}
);
?>

<div class="advads-form-types advads-buttonset">
	<?php foreach ( $placement_types as $placement_type ) : ?>
		<div class="advads-form-type advads-placement-type">
			<label for="advads-form-type-<?php echo esc_attr( $placement_type->get_id() ); ?>">
				<?php if ( ! empty( $placement_type->get_image() ) ) : ?>
					<img src="<?php echo esc_attr( $placement_type->get_image() ); ?>" alt="<?php echo esc_attr( $placement_type->get_title() ); ?>"/>
				<?php else : ?>
					<strong><?php echo esc_html( $placement_type->get_title() ); ?></strong><br/>
					<p class="description"><?php echo esc_html( $placement_type->get_description() ); ?></p>
				<?php endif; ?>
			</label>
			<input type="radio" id="advads-form-type-<?php echo esc_attr( $placement_type->get_id() ); ?>" name="advads[placement][type]" value="<?php echo esc_attr( $placement_type->get_id() ); ?>"/>
			<div class="advads-form-description">
				<h4><?php echo esc_html( $placement_type->get_title() ); ?></h4>
				<?php echo esc_html( $placement_type->get_description() ); ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
