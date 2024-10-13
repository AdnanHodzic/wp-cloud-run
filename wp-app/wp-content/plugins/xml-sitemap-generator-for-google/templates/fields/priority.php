<?php
/**
 * @var $args
 */
?>
<select name="<?php echo esc_attr( $args['name'] ); ?>">
	<option value="0" <?php selected( $args['value'] ?? '', '0' ); ?>>0.0</option>
	<option value="1" <?php selected( $args['value'] ?? '', '1' ); ?>>0.1</option>
	<option value="2" <?php selected( $args['value'] ?? '', '2' ); ?>>0.2</option>
	<option value="3" <?php selected( $args['value'] ?? '', '3' ); ?>>0.3</option>
	<option value="4" <?php selected( $args['value'] ?? '', '4' ); ?>>0.4</option>
	<option value="5" <?php selected( $args['value'] ?? '', '5' ); ?>>0.5</option>
	<option value="6" <?php selected( $args['value'] ?? '', '6' ); ?>>0.6</option>
	<option value="7" <?php selected( $args['value'] ?? '', '7' ); ?>>0.7</option>
	<option value="8" <?php selected( $args['value'] ?? '', '8' ); ?>>0.8</option>
	<option value="9" <?php selected( $args['value'] ?? '', '9' ); ?>>0.9</option>
	<option value="10" <?php selected( $args['value'] ?? '', '10' ); ?>>1.0</option>
</select>