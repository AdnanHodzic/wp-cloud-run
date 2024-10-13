<?php
/**
 * @var $args
 */
?>
<label for="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ?? '' ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
<input type="<?php echo esc_attr( $args['type'] ?? 'text' ); ?>" id="<?php echo esc_attr( $args['name'] ); ?>"
	name="<?php echo esc_attr( $args['name'] ); ?>" size="50"
	class="<?php echo esc_attr( $args['class'] ?? '' ); ?>"
	value="<?php echo esc_attr( $args['value'] ); ?>"/>

<?php if ( ! empty( $args['description'] ) ) { ?>
	<span class="field-description <?php echo esc_attr( $args['class'] ?? '' ); ?>"><?php echo wp_kses_post( $args['description'] ); ?></span>
	<?php
}
