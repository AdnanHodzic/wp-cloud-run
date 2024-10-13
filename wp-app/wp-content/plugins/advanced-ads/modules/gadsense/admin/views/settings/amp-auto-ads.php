<?php
/**
 * Option to enable AdSense Auto ads on AMP pages
 * located under Advanced Ads > Settings > AdSense > Auto ads
 *
 * @var string $option_name name of the option.
 * @var bool $auto_ads_enabled true if the AMP Auto ads option is enabled.
 */
?>
<p>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[auto_ads_enabled]" value="1" <?php checked( $auto_ads_enabled ); ?>/>
		<?php esc_html_e( 'Enable AMP Auto ads', 'advanced-ads' ); ?>
	</label>
</p>
