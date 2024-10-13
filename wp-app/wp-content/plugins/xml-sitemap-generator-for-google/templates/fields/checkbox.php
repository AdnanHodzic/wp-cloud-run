<?php
/**
 * @var $args
 */
?>
<input type="checkbox" name="<?php echo esc_attr( $args['name'] ); ?>"
	id="<?php echo esc_attr( $args['name'] ); ?>" value="1"
	class="<?php echo esc_attr( $args['class'] ?? '' ); ?>"
	<?php
	checked( esc_attr( $args['value'] ), '1' );

	if ( ! empty( $args['data'] ) ) {
		foreach ( $args['data'] as $attr => $value ) {
			echo " data-{$attr}='$value' ";
		}
	}
	?>  />
<label for="<?php echo esc_attr( $args['name'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>

