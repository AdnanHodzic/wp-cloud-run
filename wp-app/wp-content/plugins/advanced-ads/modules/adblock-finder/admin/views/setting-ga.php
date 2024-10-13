<?php
/**
 * Input for Google Analytics property ID.
 *
 * @package Advanced_Ads_Admin
 * @var string $ga_uid Google Analytics property ID
 */

?>
<label>
	<input type="text" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[ga-UID]" value="<?php echo esc_attr( $ga_uid ); ?>"/>
	<?php esc_html_e( 'Google Analytics Tracking ID', 'advanced-ads' ); ?>
</label>

<p class="description">
	<?php
	printf(
		// translators: %s is demo GA4 ID.
		esc_html__(
			'Do you want to know how many visitors use an ad blocker? Enter your Google Analytics property ID (%s) above to count them.',
			'advanced-ads'
		),
		'<code>G-A12BC3D456</code>'
	);
	?>
</p>
