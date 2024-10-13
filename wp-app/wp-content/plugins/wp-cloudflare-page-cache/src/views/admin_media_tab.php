<?php
namespace SPC\Views;

use SPC\Constants;
use SPC\Loader;
use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_header;
use function SPC\Views\Functions\render_number_field;
use function SPC\Views\Functions\render_switch;
use function SPC\Views\Functions\render_textarea;
use function SPC\Views\Functions\render_update_wordpress_notice;

global $sw_cloudflare_pagecache;

?>
<!-- Native lazy load -->
<?php render_header( __( 'Native Lazy Load', 'wp-cloudflare-page-cache' ), true, Loader::can_process_html() ? '' : 'update-wp' ); ?>

<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Native Lazy Load', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( Constants::SETTING_NATIVE_LAZY_LOADING, 1, '', (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOADING ) === 1 ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Lazy load -->
<?php render_header( __( 'Lazy Load', 'wp-cloudflare-page-cache' ), false, Loader::can_process_html() ? '' : 'update-wp' ); ?>

<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Lazy Load', 'wp-cloudflare-page-cache' ); ?></label>
		<p class="description">
			<?php _e( 'Disables native lazy-loading and uses a custom solution for better control over image loading, potentially improving performance.', 'wp-cloudflare-page-cache' ); ?>
			<a href="https://docs.themeisle.com/article/2059-native-lazyloading-vs-spc-lazyloading" target="_blank">
				<?php _e( 'More Info', 'wp-cloudflare-page-cache' ); ?>
			</a>
		</p>
	</div>
	<div class="right_column">
		<?php
		render_switch( Constants::SETTING_LAZY_LOADING, 0, 'media-ll' );
		render_update_wordpress_notice();
		?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section media-ll">
	<div class="left_column">
		<label><?php _e( 'Lazy load videos and iframes', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_switch( Constants::SETTING_LAZY_LOAD_VIDEO_IFRAME, 1 );
		render_description( __( 'By default, lazy loading does not work for embedded videos and iframes. Enable this option to activate the lazy-load on these elements.', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section media-ll">
	<div class="left_column">
		<label><?php _e( 'Bypass lazy load for first images', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_number_field( Constants::SETTING_LAZY_LOAD_SKIP_IMAGES, 2 );
		/* translators: %s: 0 */
		render_description( sprintf( __( 'Indicate how many images at the top of each page should bypass lazy loading, ensuring they\'re instantly visible. Enter %s to not exclude any images from the lazy loading process.', 'wp-cloudflare-page-cache' ), '<code>0</code>' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section media-ll">
	<div class="left_column">
		<label><?php _e( 'Exclusions', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_textarea( Constants::SETTING_LAZY_EXCLUDED, 'logo.jpg&#10;excluded-class' );
		render_description( __( 'Enter one keyword per line to exclude items from lazy loading by checking if URLs, class names, or data attributes contain these keywords.', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section media-ll">
	<div class="left_column">
		<label><?php _e( 'Background images lazy load', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_switch( Constants::SETTING_LAZY_LOAD_BG, 0, 'media-bg-ll-selectors' );
		?>
	</div>
	<div class="clear"></div>
	<br>
	<div class="left_column"></div>
	<div class="right_column media-bg-ll-selectors">
		<?php
		render_textarea( Constants::SETTING_LAZY_LOAD_BG_SELECTORS, '.bg-selector&#10;body > .container' );
		render_description( __( 'Enter CSS selectors for any background images not covered by the default lazy loading. This ensures those images also benefit from the optimized loading process.', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>
