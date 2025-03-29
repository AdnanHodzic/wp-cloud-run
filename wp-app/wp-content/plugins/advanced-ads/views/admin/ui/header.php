<?php
/**
 * Header on admin pages
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var string    $title               page title.
 * @var WP_Screen $screen              Current screen
 * @var string    $reset_href          href attribute for the reset button
 * @var bool      $show_filter_button  if the filter button is visible
 * @var string    $filter_disabled     if the visible filter button is disabled
 * @var string    $new_button_label    text displayed on the New button
 * @var string    $new_button_href     href of the New button
 * @var string    $new_button_id       id of the New button
 * @var string    $show_screen_options if to show the Screen Options button
 * @var string    $manual_url          target of the manual link
 * @var string    $tooltip             description that will show in a tooltip
 */

?>
<div id="advads-header" class="advads-header">
	<div id="advads-header-wrapper">
		<div>
			<svg class="advads-header-logo" xmlns="http://www.w3.org/2000/svg" x="0" y="0" height="30" width="30" viewBox="0 0 351.7 352" xml:space="preserve"><path d="M252.2 149.6v125.1h-174.9v-174.9H202.4c-5.2-11.8-8-24.7-8-38.5s3-26.7 8-38.5h-37.7H0v267.9l8.8 8.8 -8.8-8.8C0 324.5 27.5 352 61.3 352l0 0h103.4 164.5V149.3c-11.8 5.2-25 8.3-38.8 8.3C276.9 157.6 264 154.6 252.2 149.6z" fill="#1C1B3A"/><circle cx="290.4" cy="61.3" r="61.3" fill="#0E75A4"/></svg>
			<h1><?php echo esc_html( $title ); ?></h1>
		</div>
		<div id="advads-header-actions">
		</div>
		<div id="advads-header-links">
			<?php if ( ! defined( 'AAP_VERSION' ) ) : ?>
				<a href="https://wpadvancedads.com/add-ons/?utm_source=advanced-ads&utm_medium=link&utm_campaign=header-upgrade-<?php echo esc_attr( $screen->id ); ?>" target="_blank" class="advads-upgrade button button-primary">
					<span class="dashicons dashicons-star-filled"></span><span><?php esc_html_e( 'See all Add-ons', 'advanced-ads' ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( '' !== $manual_url ) : ?>
			<a href="<?php echo esc_url( $manual_url ); ?>?utm_source=advanced-ads&utm_medium=link&utm_campaign=header-manual-<?php echo esc_attr( $screen->id ); ?>" target="_blank" class="button advads-icon-help">
				<i class="dashicons dashicons-editor-help"></i>
			</a>
			<?php endif; ?>
		</div>
	</div>
</div>
