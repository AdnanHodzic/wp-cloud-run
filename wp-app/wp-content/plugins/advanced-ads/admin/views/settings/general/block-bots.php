<?php
/**
 * The view to render the option.
 *
 * @var int $checked Value of 1, when the option is checked.
 */
?>
<label>
	<input id="advanced-ads-block-bots" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[block-bots]" <?php checked( $checked, 1 ); ?>>
	<?php if ( Advanced_Ads::get_instance()->is_bot() ) : ?>
		<span class="advads-notice-inline advads-error"><?php esc_html_e( 'You look like a bot', 'advanced-ads' ); ?></span>
	<?php endif; ?>
	<?php esc_html_e( 'Hide ads from crawlers, bots and empty user agents.', 'advanced-ads' ); ?>
	<span class="description">
		<a href="https://wpadvancedads.com/hide-ads-from-bots/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings" target="blank" class="advads-external-link">
			<?php esc_html_e( 'Read this first', 'advanced-ads' ); ?>
		</a>
	</span>
</label>
