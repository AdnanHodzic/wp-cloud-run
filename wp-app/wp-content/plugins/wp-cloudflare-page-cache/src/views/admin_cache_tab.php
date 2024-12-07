<?php

use SPC\Constants;
use function SPC\Views\Functions\load_view;
use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_header;
use function SPC\Views\Functions\render_switch;
use function SPC\Views\Functions\render_textarea;

/**
 * @var $sw_cloudflare_pagecache SW_CLOUDFLARE_PAGECACHE
 */
global $sw_cloudflare_pagecache;

$nginx_instructions_page_url = add_query_arg( [ 'page' => 'wp-cloudflare-super-page-cache-nginx-settings' ], admin_url( 'options-general.php' ) );
?>

<?php render_header( __( 'Cache Settings', 'wp-cloudflare-page-cache' ), true ); ?>

<?php if ( ! $sw_cloudflare_pagecache->get_fallback_cache_handler()->fallback_cache_is_wp_config_writable() ) { ?>
	<div class="description_section highlighted"><?php _e( 'The file wp-config.php is not writable. Please add write permission to activate the fallback cache.', 'wp-cloudflare-page-cache' ); ?></div>
<?php } ?>

<?php if ( ! $sw_cloudflare_pagecache->get_fallback_cache_handler()->fallback_cache_is_wp_content_writable() ) { ?>
	<div class="description_section highlighted"><?php _e( 'The directory wp-content is not writable. Please add write permission or you have to use the fallback cache with cURL.', 'wp-cloudflare-page-cache' ); ?></div>
<?php } ?>

<?php load_view( 'cloudflare_disconnect' ); ?>

<!-- Enable Disk Page cache -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Enable Disk Page cache', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Disable this option if you want to use only Cloudflare Cache.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php
		render_switch( 'cf_fallback_cache', 0, 'fallbackcache' );
		render_description( __( 'If you enable the DIsk Page cache is strongly recommended disable all page caching functions of other plugins.', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- ByPass Cache when cookies are present -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Bypass Page cache when these cookies are present in the request packet', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'One cookie name per line. These strings will be used by preg_grep.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php render_textarea( Constants::SETTING_EXCLUDED_COOKIES, '', Constants::DEFAULT_EXCLUDED_COOKIES ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Prevent the following URIs to be cached -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Prevent the following URIs to be cached', 'wp-cloudflare-page-cache' ); ?></label>
		<?php
		render_description( __( 'One URI per line. You can use the * for wildcard URLs.', 'wp-cloudflare-page-cache' ), false, false, true );
		render_description( __( 'Example', 'wp-cloudflare-page-cache' ) . ':<br/>/my-page<br/>/my-main-page/my-sub-page<br/>/my-main-page*' );
		?>
	</div>
	<div class="right_column">
		<?php render_textarea( Constants::SETTING_EXCLUDED_URLS, '', Constants::DEFAULT_EXCLUDED_URLS ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- ByPass Cache when Query Params -->
<?php load_view( 'excluded_url_params' ); ?>

<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Show advanced settings', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Enable to display the Advanced Settings tab (optional, recommended only for advanced configurations).', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( Constants::SETTING_SHOW_ADVANCED, 0, 'show_advanced' ); ?>
	</div>
	<div class="clear"></div>
</div>

