<?php
/**
 * Input for Google Analytics property ID.
 *
 * @package AdvancedAds
 * @var string $ga_uid Google Analytics property ID
 */

?>
<label>
	<input type="text" name="<?php echo esc_attr( ADVADS_SETTINGS_ADBLOCKER ); ?>[ga-UID]" value="<?php echo esc_attr( $ga_uid ); ?>"/>
	<?php esc_html_e( 'Google Analytics Tracking ID', 'advanced-ads' ); ?>
</label>

<p class="description">
	<?php
	printf(
		/* translators: %s is demo GA4 ID. */
		esc_html__(
			'Enter your Google Analytics property ID (e.g. %s) above to track the page views of visitors who use an ad blocker.',
			'advanced-ads'
		),
		'<code>G-A12BC3D456</code>'
	);
	?>
</p>
