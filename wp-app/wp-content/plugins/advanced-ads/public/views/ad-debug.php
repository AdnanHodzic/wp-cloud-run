<?php
/**
 * Ad debug output template.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.x.x
 *
 * @var string $wrapper_id Wrapper ID.
 * @var string $style      Wrapper style.
 * @var array  $content    Debug content.
 */

if ( ! advads_is_amp() ) :
	ob_start();
		echo Advanced_Ads_Utils::get_inline_asset( ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
endif;
?>
<div id="<?php echo esc_attr( $wrapper_id ); ?>" style="<?php echo esc_attr( $style ); ?>">
	<strong><?php esc_html_e( 'Ad debug output', 'advanced-ads' ); ?></strong>
	<br><br>
	<?php echo implode( '<br><br>', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<br><br>
	<a style="color: green;" href="https://wpadvancedads.com/manual/ad-debug-mode/?utm_source=advanced-ads&utm_medium=link&utm_campaign=ad-debug-mode" target="_blank" rel="nofollow"><?php esc_html_e( 'Find solutions in the manual', 'advanced-ads' ); ?></a>
</div>
