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
	<a href="https://wpadvancedads.com/open-ad-in-new-window/?utm_source=advanced-ads&utm_medium=link&utm_campaign=open-new-window" target="_blank" class="advads-manual-link">
		<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
	</a>
</label>
