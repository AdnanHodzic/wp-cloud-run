<?php
/**
 * The view to render the option.
 *
 * @var int $checked Value of 1, when the option is checked.
 */
?>
<label>
	<input id="advanced-ads-content-injection-level-disabled" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[content-injection-level-disabled]" <?php checked( $checked, 1 ); ?>>
	<?php esc_html_e( 'Advanced Ads ignores paragraphs and other elements in containers when injecting ads into the post content. Check this option to ignore this limitation and ads might show up again.', 'advanced-ads' ); ?>
</label>
