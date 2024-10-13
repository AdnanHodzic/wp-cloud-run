<?php if ( $adsense_line ) : ?>
<p>

	<?php
	echo wp_kses(
		sprintf(
			/* translators: %s: The adsense line added automically by Advanced Ads. */
			__( 'The following line will be added automatically because you connected your AdSense account with Advanced Ads: %s', 'advanced-ads' ),
			'<br><code>' . $adsense_line . '</code>'
		),
		[
			'br'   => [],
			'code' => [],
		]
	);
	?>
</p>
<?php endif; ?>

<br />
<textarea cols="50" rows="5" id="advads-ads-txt-additional-content" name="advads-ads-txt-additional-content"><?php echo esc_textarea( $content ); ?></textarea>
<p class="description"><?php esc_html_e( 'Additional records to add to the file, one record per line. AdSense is added automatically.', 'advanced-ads' ); ?></p>
<div id="advads-ads-txt-notice-wrapper">
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $notices;
?>
</div>
<p class="advads-notice-inline advads-error hidden" id="advads-ads-txt-notice-error">
	<?php
	// translators: %s is replaced with an error message.
	esc_html_e( 'An error occured: %s.', 'advanced-ads' );
	?>
</p>
<button class="button advads-ads-txt-action" type="button" id="advads-ads-txt-notice-refresh"><?php esc_html_e( 'Check for problems', 'advanced-ads' ); ?></button>
<a href="<?php echo esc_url( $link ); ?>" class="button" target="_blank"><?php esc_html_e( 'Preview', 'advanced-ads' ); ?></button>
