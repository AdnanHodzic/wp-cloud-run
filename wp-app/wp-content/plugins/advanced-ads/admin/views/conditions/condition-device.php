<?php
/**
 * Template for the Device visitor condition
 *
 * @var string $name         Form name attribute.
 * @var string $operator     The operator, should be one of `is` or `is_not`.
 * @var array  $type_options Array with additional information.
 * @var array  $options      The options for the current condition.
 * @var int    $index        The zero-based index for the current condition.
 */
?>
	<input type="hidden" name="<?php echo esc_attr( $name ); ?>[type]" value="<?php echo esc_attr( $options['type'] ); ?>"/>

	<div class="advads-conditions-single advads-buttonset">
		<?php
		$rand = md5( $name );
		foreach ( $type_options[ $options['type'] ]['device_types'] as $device_type ) :
			$input_id = 'advads-visitor-conditions-device-' . $index . '-' . $device_type['id'] . '-' . $rand;
			?>
			<label for="<?php echo esc_attr( $input_id ); ?>" class="button advads-button">
				<?php echo esc_html( $device_type['label'] ); ?>
			</label>
			<input type="checkbox" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $name ); ?>[value][]" value="<?php echo esc_attr( $device_type['id'] ); ?>" <?php checked( $device_type['checked'] ); ?>>
		<?php endforeach; ?>
		<?php include ADVADS_ABSPATH . 'admin/views/conditions/not-selected.php'; ?>
	</div>
<?php
printf(
	'<p class="description"><a href="%1$s" class="advads-manual-link" target="_blank">%2$s</a></p>',
	esc_url( $type_options[ $options['type'] ]['helplink'] ),
	esc_html__( 'Manual', 'advanced-ads' )
);
