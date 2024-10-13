<?php
/**
 * WARNING: be careful when modifying the DOM of this document!
 * there are some jquery calls that rely on this structure!
 */
$is_account_connected = $network->is_account_connected();
?><p>
	<span class="mapi-insert-code">

		<a href="#"><?php
            /* translators: 1: The name of an ad network. */
            printf(__( 'Insert new %1$s code', 'advanced-ads' ), $network->get_display_name()) ?></a>
	</span>
    <?php if ( Advanced_Ads_Checks::php_version_minimum() ) : ?>
        <?php if ( $is_account_connected ) : ?>
            <span class="mapi-open-selector">
				<span class="mapi-optional-or"><?php _e( 'or', 'advanced-ads' ); ?></span>
				<a href="#" class="prevent-default"><?php _e( 'Get ad code from your linked account', 'advanced-ads' ); ?></a>
            </span>
            <?php if ($network->supports_manual_ad_setup()):?>
                <span class="mapi-close-selector-link"><?php
                    _e( 'or', 'advanced-ads' ); ?><a href="#" class="prevent-default"><?php
                        /* translators: 1: The name of an ad network. */
                        printf(__( 'Set up %1$s code manually', 'advanced-ads' ), $network->get_display_name());
                    ?></a>
                </span>
            <?php endif;?>
        <?php else : ?>
            <?php _e( 'or', 'advanced-ads' );
            /* translators: 1: The name of an ad network. */
            $connect_link_label = sprintf(__( 'Connect to %1$s', 'advanced-ads' ), $network->get_display_name());
            ?>
            <a href="<?php echo $network->get_settings_href() ?>" style="padding:0 10px;font-weight:bold;"><?php echo $connect_link_label ?></a>
        <?php endif; ?>
    <?php endif; ?>
</p>
<?php if ( $is_account_connected && ! Advanced_Ads_Checks::php_version_minimum() ) : ?>
<p class="advads-notice-inline advads-error"><?php esc_html_e( 'Can not connect AdSense account. PHP version is too low.', 'advanced-ads' ); ?></p>
<?php endif; ?>

