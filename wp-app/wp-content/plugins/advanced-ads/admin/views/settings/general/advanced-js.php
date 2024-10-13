<?php

/**
 * The view to render the option.
 *
 * @var int $checked Value of 1, when the option is checked.
 */

// display notice if js file was overridden.
if ( ! $checked && apply_filters( 'advanced-ads-activate-advanced-js', $checked ) ) : ?>
	<p><?php esc_html_e( 'The file is currently enabled by an add-on that needs it.', 'advanced-ads' ); ?></p>
<?php endif; ?>
<label>
	<input id="advanced-ads-advanced-js" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[advanced-js]" <?php checked( $checked, 1 ); ?>>
	<?php
	printf(
		wp_kses(
		// translators: %s is a URL.
			__( 'Enable advanced JavaScript functions (<a href="%s" target="_blank">here</a>). Some features and add-ons might override this setting if they need features from this file.', 'advanced-ads' ),
			[
				'a' => [
					'href'   => [],
					'target' => [],
				],
			]
		),
		'https://wpadvancedads.com/javascript-functions/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings'
	);
	?>
</label>
