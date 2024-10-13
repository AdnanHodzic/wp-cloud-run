<?php
/**
 * Template for the is/is_not operator used in many conditions.
 *
 * @var string $name form field name attribute.
 * @var string $operator operator value.
 */
?>
<select name="<?php echo esc_attr( $name ); ?>[operator]">
	<option value="is" <?php selected( 'is', $operator ); ?>><?php esc_html_e( 'is', 'advanced-ads' ); ?></option>
	<option
		value="is_not" <?php selected( 'is_not', $operator ); ?>><?php esc_html_e( 'is not', 'advanced-ads' ); ?></option>
</select>
