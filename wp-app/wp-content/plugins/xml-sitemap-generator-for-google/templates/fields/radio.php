<?php
/**
 * @var $args
 */
?>
<input type="radio" name="<?php echo esc_attr( $args['name'] ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" <?php checked( esc_attr( $args['value'] ), esc_attr( $args['current_value'] ) ); ?> />
<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>

<?php if ( ! empty( $args['description'] ) ) { ?>
	<div class="field-description"><?php echo wp_kses_post( $args['description'] ); ?></div>
	<?php
}
