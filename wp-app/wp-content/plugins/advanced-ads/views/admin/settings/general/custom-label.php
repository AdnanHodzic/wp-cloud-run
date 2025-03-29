<?php
/**
 * Ad label settings
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var bool $enabled   If label setting is enabled.
 * @var string $label   Label input text.
 * @var bool $html_enabled HTML allowed or not.
 */

?>
<fieldset>
	<input type="checkbox" <?php checked( $enabled, true ); ?> value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[custom-label][enabled]"/>
	<input id="advads-custom-label" type="text" value="<?php echo esc_html( $label ); ?>" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[custom-label][text]"/>
</fieldset>
<p class="description">
	<?php esc_html_e( 'Displayed above ads.', 'advanced-ads' ); ?>&nbsp;
	<a class="advads-manual-link" href="https://wpadvancedads.com/manual/advertisement-label/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-advertisement-label" target="_blank">
		<?php esc_html_e( 'Manual', 'advanced-ads' ); ?>
	</a>
</p>

<label>
	<input type="checkbox" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[custom-label][html_enabled]" value="1" <?php checked( $html_enabled, true ); ?> />
	<?php esc_html_e( 'Enable HTML for the field', 'advanced-ads' ); ?>
</label>
