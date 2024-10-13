<?php if ( ! advads_is_amp() ):
ob_start(); ?>
<?php echo Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
endif; ?>
<div id="<?php echo $wrapper_id; ?>" style="<?php echo $style; ?>">
<strong><?php _e( 'Ad debug output', 'advanced-ads' ); ?></strong>
<?php echo '<br /><br />' . implode( '<br /><br />', $content ); ?>
<br /><br /><a style="color: green;" href="https://wpadvancedads.com/manual/ad-debug-mode/?utm_source=advanced-ads&utm_medium=link&utm_campaign=ad-debug-mode" target="_blank" rel="nofollow"><?php esc_html_e( 'Find solutions in the manual', 'advanced-ads' ); ?></a>
</div>
