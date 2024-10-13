<?php
/**
 * The view to render the option.
 *
 * @var string $ad_blocker_notice_id Randomised ad blocker id.
 */
?>
<div id="<?php echo esc_attr( $ad_blocker_notice_id ); ?>" class="message error update-message notice notice-alt notice-error" style="display: none;">
	<p>
		<?php echo wp_kses_post( __( 'Please disable your <strong>AdBlocker</strong>. Otherwise, the features of Advanced Ads and the layout are broken.', 'advanced-ads' ) ); ?>
		<a href="https://wpadvancedads.com/manual/ad-blockers/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adblock-enabled#How_do_you_know_if_you_are_using_an_ad_blocker" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
	</p>
</div>
