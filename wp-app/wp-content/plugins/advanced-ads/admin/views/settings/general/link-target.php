<?php
/**
 * The view to render the option.
 *
 * @var int $target Value of 1, when the option is checked.
 */
?>
<label>
	<input name="<?php echo esc_attr( ADVADS_SLUG ); ?>[target-blank]" type="checkbox" value="1" <?php checked( 1, $target ); ?> />
	<?php echo wp_kses( __( 'Open programmatically created links in a new window (use <code>target="_blank"</code>)', 'advanced-ads' ), [ 'code' => [] ] ); ?>
</label>
