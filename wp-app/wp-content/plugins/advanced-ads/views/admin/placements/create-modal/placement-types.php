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

// Placement icons display order.
$icons_order = [
	'post_top',
	'post_content',
	'post_bottom',
	'sidebar_widget',
	'default',
	'header',
	'footer',
	'genesis',
	'sticky_header',
	'sticky_footer',
	'sticky_left_sidebar',
	'sticky_right_sidebar',
	'sticky_left_window',
	'sticky_right_window',
	'layer',
	'background',
	'post_content_random',
	'post_above_headline',
	'post_content_middle',
	'custom_position',
	'archive_pages',
	'adsense_in_feed',
];

uksort(
	$placement_types,
	function ( $a, $b ) use ( $icons_order ) {
		return array_search( $a, $icons_order, true ) > array_search( $b, $icons_order, true ) ? 1 : - 1;
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
