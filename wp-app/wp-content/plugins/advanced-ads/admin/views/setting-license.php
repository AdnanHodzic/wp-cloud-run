<?php // phpcs:ignoreFile
/**
 * Template for license fields.
 *
 * Do not move since it could be used by add-ons.
 *
 * @var string $index internal name of the add-on.
 * @var string $plugin_name name of the add-on.
 * @var string $options_slug slug of the add-on.
 * @var string $license_status status code of the license.
 */
$errortext     = false;
$expires       = Advanced_Ads_Admin_Licenses::get_instance()->get_license_expires( $options_slug );
$expired       = false;
$expired_error = __( 'Your license expired.', 'advanced-ads' );

ob_start();
?>
<button type="button" class="button-secondary advads-license-activate"
		data-addon="<?php echo esc_attr( $index ); ?>"
		data-pluginname="<?php echo esc_attr( $plugin_name ); ?>"
		data-optionslug="<?php echo esc_attr( $options_slug ); ?>"
		name="advads_license_activate"><?php esc_html_e( 'Update expiry date', 'advanced-ads' ); ?></button>
<?php
$update_button = ob_get_clean();

$license_key_for_expired_link = $license_key ? $license_key : '%LICENSE_KEY%';
//phpcs:ignore
$expired_error               .= $expired_renew_link = ' ' . sprintf(
	/* translators: 1: is a URL, 2: is HTML of a button. */
	__( 'Click on %2$s if you renewed it or have a subscription or <a href="%1$s" class="advads-renewal-link" target="_blank">renew your license</a>.', 'advanced-ads' ), // phpcs:ignore
	'https://wpadvancedads.com/checkout/?edd_license_key=' . esc_attr( $license_key_for_expired_link ) . '&utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-licenses',
	$update_button
);
if ( 'lifetime' !== $expires ) {
	$expires_time = strtotime( $expires );
	$days_left    = ( $expires_time - time() ) / DAY_IN_SECONDS;
}
// phpcs:ignore
if ( 'lifetime' === $expires ) {
	// do nothing.
} elseif ( $expired && $days_left <= 0 ) {
	$plugin_url = isset( $plugin_url ) ? $plugin_url : 'https://wpadvancedads.com/';
	$errortext  = $expired_error;
	$expired    = true;
} elseif ( 0 < $days_left && 31 > $days_left ) {
	$errortext = sprintf(
		// translators: %d is a number of days.
		esc_html__( '(%d days left)', 'advanced-ads' ),
		$days_left
	);
}
$show_active = ( false !== $license_status && 'valid' === $license_status && ! $expired ) ? true : false;
?>
<input type="text" class="regular-text advads-license-key" placeholder="<?php esc_html_e( 'License key', 'advanced-ads' ); ?>"
	name="<?php echo esc_attr( ADVADS_SLUG ) . '-licenses'; ?>[<?php echo esc_attr( $index ); ?>]"
	value="<?php echo esc_attr( $license_key ); ?>"
	<?php
	if ( false !== $license_status && 'valid' === $license_status && ! $expired ) :
		?>
		readonly="readonly"<?php endif; ?>/>

<button type="button" class="button-secondary advads-license-deactivate"
	<?php
	if ( 'valid' !== $license_status ) {
		echo ' style="display: none;" ';
	}
	?>
		data-addon="<?php echo esc_attr( $index ); ?>"
		data-pluginname="<?php echo esc_attr( $plugin_name ); ?>"
		data-optionslug="<?php echo esc_attr( $options_slug ); ?>"
		name="advads_license_activate"><?php esc_html_e( 'Deactivate License', 'advanced-ads' ); ?></button>

<button type="button" class="button-primary advads-license-activate"
		data-addon="<?php echo esc_attr( $index ); ?>"
		data-pluginname="<?php echo esc_attr( $plugin_name ); ?>"
		data-optionslug="<?php echo esc_attr( $options_slug ); ?>"
		name="advads_license_activate">
		<?php
	// phpcs:ignore
	echo ( 'valid' === $license_status && ! $expired ) ? esc_html__( 'Update License', 'advanced-ads' ) : esc_html__( 'Activate License', 'advanced-ads' ); ?></button>
<?php
if ( '' === trim( $license_key ) ) {
	$errortext = __( 'Please enter a valid license key', 'advanced-ads' );
} elseif ( ! $expired && ! $errortext ) {
	$errortext = ( 'invalid' === $license_status ) ? esc_html__( 'License key invalid', 'advanced-ads' ) : '';
}
?>
&nbsp;
<span class="advads-license-activate-active advads-notice-inline advads-check" <?php echo ( ! $show_active ) ? 'style="display: none;"' : ''; ?>><?php esc_html_e( 'active', 'advanced-ads' ); ?></span>
<span class="advads-license-activate-error advads-notice-inline advads-error" <?php echo ( ! $errortext ) ? 'style="display: none;"' : ''; ?>>
<?php
	// phpcs:ignore
	echo $errortext;
?>
	</span>
<span class="advads-license-expired-error advads-notice-inline advads-error" style="display: none;">
	  <?php
	// phpcs:ignore
	echo $expired_error;
		?>
	</span>
