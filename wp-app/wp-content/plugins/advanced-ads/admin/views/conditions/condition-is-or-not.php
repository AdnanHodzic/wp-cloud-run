<?php
/**
 * Template for a condition that only contains of an is/is_not choice.
 *
 * @package AdvancedAds
 *
 * @var string $name form field name attribute.
 * @var string $operator operator.
 * @var array $type_options additional options for the condition.
 */

?>
<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $options['type'] ); ?>"/>

<?php // Note: placeholder tag so it's not considered empty condition. ?>
<input type="hidden" name="<?php echo esc_attr( $name ); ?>[value]" value="1">

<?php
require ADVADS_ABSPATH . 'admin/views/conditions/condition-operator.php';
?>
<p class="description">
	<?php echo esc_html( $type_options[ $options['type'] ]['description'] ); ?>
	<?php
	if ( isset( $type_options[ $options['type'] ]['helplink'] ) ) {
		printf(
			'<a href="%1$s" class="advads-manual-link" target="_blank">%2$s</a>',
			esc_url( $type_options[ $options['type'] ]['helplink'] ),
			esc_html__( 'Manual', 'advanced-ads' )
		);
	}
	?>
</p>
