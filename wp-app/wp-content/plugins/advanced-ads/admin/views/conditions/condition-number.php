<?php
/**
 * Template to select number-based conditions.
 *
 * @var string $name form name attribute.
 * @var string $operator operator.
 * @var array $type_options array with information. We get the description of the condition from here.
 */
?>
<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $options['type'] ); ?>"/>
<select name="<?php echo esc_attr( $name ); ?>[operator]">
	<option
		value="is_equal" <?php selected( 'is_equal', $operator ); ?>><?php esc_html_e( 'equal', 'advanced-ads' ); ?></option>
	<option
		value="is_higher" <?php selected( 'is_higher', $operator ); ?>><?php esc_html_e( 'equal or higher', 'advanced-ads' ); ?></option>
	<option
		value="is_lower" <?php selected( 'is_lower', $operator ); ?>><?php esc_html_e( 'equal or lower', 'advanced-ads' ); ?></option>
</select><input type="number" name="<?php echo esc_attr( $name ); ?>[value]" value="<?php echo absint( $value ); ?>"/>
<p class="description">
	<?php echo esc_html( $type_options[ $options['type'] ]['description'] ); ?>
	<?php if ( isset( $type_options[ $options['type'] ]['helplink'] ) ) : ?>
		<a href="<?php echo esc_url( $type_options[ $options['type'] ]['helplink'] ); ?>" class="advads-manual-link" target="_blank">
			<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
		</a>
	<?php endif; ?>
</p>
