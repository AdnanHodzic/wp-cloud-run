<?php
/**
 * @var array $zone_id_list The list of Cloudflare zone IDs.
 *
 */

use SPC\Constants;
use SPC\Modules\Admin;
use function SPC\Views\Functions\render_checkbox;
use function SPC\Views\Functions\render_description;
use function SPC\Views\Functions\render_description_section;
use function SPC\Views\Functions\render_header;
use function SPC\Views\Functions\render_number_field;
use function SPC\Views\Functions\render_switch;
use function SPC\Views\Functions\render_text_field;
use function SPC\Views\Functions\render_textarea;

/**
 * @var $sw_cloudflare_pagecache SW_CLOUDFLARE_PAGECACHE
 */
global $sw_cloudflare_pagecache;

$zone_id_list               = Admin::get_zone_id_list_for_display();
$preloader_cronjob_url      = Admin::get_cronjob_url( 'preloader' );
$cached_html_pages_list_url = add_query_arg( [ 'page' => 'wp-cloudflare-super-page-cache-cached-html-pages' ], admin_url( 'options-general.php' ) );
$wordpress_menus            = wp_get_nav_menus();
$wordpress_roles            = $sw_cloudflare_pagecache->get_wordpress_roles();

$nginx_instructions_page_url = add_query_arg( [ 'page' => 'wp-cloudflare-super-page-cache-nginx-settings' ], admin_url( 'options-general.php' ) );
$is_token_auth               = $sw_cloudflare_pagecache->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_TOKEN;

$switch_counter = 10000;

?>

<!-- Cache Advanced Settings -->
<?php render_header( __( 'Cache', 'wp-cloudflare-page-cache' ), true ); ?>

<!-- Use cURL -->
<div class="main_section fallbackcache">
	<div class="left_column">
		<label><?php _e( 'Use cURL', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Use cURL instead of WordPress advanced-cache.php to generate the Page page. It can increase the time it takes to generate the Page cache but improves compatibility with other performance plugins.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_fallback_cache_curl' ); ?>
	</div>
	<div class="clear"></div>
</div>

<div class="main_section fallbackcache">
	<div class="left_column">
		<label><?php _e( 'Cache Lifespan', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Enter 0 for no expiration.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php
		render_number_field( 'cf_fallback_cache_ttl' );
		render_description( __( 'Enter a value in seconds.', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Save response headers -->
<div class="main_section fallbackcache">
	<div class="left_column">
		<label><?php _e( 'Save response headers', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description"><?php _e( 'Save response headers together with HTML code.', 'wp-cloudflare-page-cache' ); ?></div>
		<div class="description"><?php _e( 'The following response header will never be saved:', 'wp-cloudflare-page-cache' ); ?>
			cache-control, set-cookie, X-WP-CF-Super-Cache*
		</div>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_fallback_cache_save_headers' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Prevent cache URLs without trailing slash -->
<div class="main_section fallbackcache">
	<div class="left_column">
		<label><?php _e( 'Prevent to cache URLs without trailing slash', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_fallback_cache_prevent_cache_urls_without_trailing_slash' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge single post cache when a new comment -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge single post cache when a new comment is inserted into the database or when a comment is approved or deleted', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( Constants::SETTING_PURGE_ON_COMMENT ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge the cache when the upgrader process is complete -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the cache when the upgrader process is complete', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_auto_purge_on_upgrader_process_complete' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Strip response cookies -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Strip response cookies on pages that should be cached', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description"><?php _e( 'Cloudflare will not cache when there are cookies in responses unless you strip out them to overwrite the behavior.', 'wp-cloudflare-page-cache' ); ?></div>
		<div class="description"><?php _e( 'If the cache does not work due to response cookies and you are sure that these cookies are not essential for the website to works, enable this option.', 'wp-cloudflare-page-cache' ); ?></div>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_strip_cookies' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Overwrite the cache-control header for WordPress's pages using web server rules -->
<div class="main_section cfworker_not">
	<div class="left_column">
		<label><?php _e( 'Overwrite the cache-control header for WordPress\'s pages using web server rules', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description">
			<div class="orange_color"><?php _e( 'Writes into .htaccess', 'wp-cloudflare-page-cache' ); ?></div>
			<br/>
			<?php _e( 'This option is useful if you use Super Page Cache together with other performance plugins that could affect the Cloudflare cache with their cache-control headers. It works automatically if you are using Apache as web server or as backend web server.', 'wp-cloudflare-page-cache' ); ?>
		</div>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_cache_control_htaccess' ); ?>
		<?php render_description( __( 'This option is not essential and must be disabled if enabled the Workers mode option. In most cases this plugin works out of the box. If the page cache does not work after a considerable number of attempts or you see that max-age and s-maxage values of <strong>X-WP-CF-Super-Cache-Cache-Control</strong> response header are not the same of the ones in <strong>Cache-Control</strong> response header, activate this option.', 'wp-cloudflare-page-cache' ) . ': ' . __( 'for overwriting to work, make sure that the rules added by Super Page Cache are placed at the bottom of the htaccess file. If they are present BEFORE other caching rules of other plugins, move them to the bottom manually.', 'wp-cloudflare-page-cache' ) ); ?>
		<?php
		render_description(
			sprintf(
				'<strong>%s</strong>: %s',
				__( 'Read here if you use Apache (htaccess)', 'wp-cloudflare-page-cache' ),
				__( 'for overwriting to work, make sure that the rules added by Super Page Cache are placed at the bottom of the htaccess file. If they are present BEFORE other caching rules of other plugins, move them to the bottom manually.', 'wp-cloudflare-page-cache' )
			)
		);
		?>
		<?php
		render_description(
			sprintf(
				'<strong>%s</strong>: %s',
				__( 'Read here if you only use Nginx', 'wp-cloudflare-page-cache' ),
				__( 'it is not possible for Super Page Cache to automatically change the settings to allow this option to work immediately. For it to work, update these settings and then follow the instructions', 'wp-cloudflare-page-cache' ) . ' <a href="' . $nginx_instructions_page_url . '" target="_blank">' . __( 'on this page', 'wp-cloudflare-page-cache' ) . '.</a>'
			)
		);
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Purge HTML pages only -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Purge HTML pages only', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Purge only the cached HTML pages instead of the whole Cloudflare cache (assets + pages).', 'wp-cloudflare-page-cache' ) ); ?>
		<div class="description purgehtmlonly">
			<br>
			<a href="<?php echo $cached_html_pages_list_url; ?>" target="_blank">
				<?php _e( 'Show cached HTML pages list', 'wp-cloudflare-page-cache' ); ?>
			</a>
		</div>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_purge_only_html', 0, 'purgehtmlonly' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Disable cache purging using queue -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Disable cache purging using queue', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description"><?php _e( 'By default this plugin purge the cache after 10 seconds from the purging action, to avoid a high number of purge requests in case of multiple events triggered by third party plugins. This is done using a classic WordPress scheduled event. If you notice any errors regarding the scheduled intervals, you can deactivate this mode by enabling this option.', 'wp-cloudflare-page-cache' ); ?></div>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_disable_cache_purging_queue' ); ?>
	</div>
	<div class="clear"></div>
</div>


<!-- Don't cache the following dynamic contents -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Don\'t cache the following dynamic contents:', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( 'cf_bypass_404', __( 'Page 404 (is_404)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_single_post', __( 'Single posts (is_single)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_pages', __( 'Pages (is_page)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_front_page', __( 'Front Page (is_front_page)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_home', __( 'Home (is_home)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_archives', __( 'Archives (is_archive)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_tags', __( 'Tags (is_tag)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_category', __( 'Categories (is_category)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_feeds', __( 'Feeds (is_feed)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_search_pages', __( 'Search Pages (is_search)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_author_pages', __( 'Author Pages (is_author)', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_amp', __( 'AMP pages', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_ajax', __( 'Ajax requests', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_query_var', __( 'Pages with query args', 'wp-cloudflare-page-cache' ) );
		render_checkbox( 'cf_bypass_wp_json_rest', __( 'WP JSON endpoints', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Don't cache the following static contents -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Don\'t cache the following static contents:', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description">
			<div class="orange_color"><?php _e( 'Writes into .htaccess', 'wp-cloudflare-page-cache' ); ?></div>
			<br/>
			<strong><?php _e( 'If you only use Nginx', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'it is recommended to add the browser caching rules that you find', 'wp-cloudflare-page-cache' ); ?>
			<a href="<?php echo $nginx_instructions_page_url; ?>"
			   target="_blank"><?php _e( 'on this page', 'wp-cloudflare-page-cache' ); ?></a> <?php _e( 'after saving these settings', 'wp-cloudflare-page-cache' ); ?>
			.
		</div>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( 'cf_bypass_sitemap', __( 'XML sitemaps', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( 'cf_bypass_file_robots', __( 'Robots.txt', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>


<!-- Posts per page -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Posts per page', 'wp-cloudflare-page-cache' ); ?></label>

		<?php
		render_description( __( 'Enter how many posts per page (or category) the theme shows to your users. It will be use to clean up the pagination on cache purge.', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="right_column">
		<?php render_number_field( 'cf_post_per_page' ); ?>
	</div>
	<div class="clear"></div>
</div>


<!-- Browser caching -->
<?php
render_header( __( 'Browser caching', 'wp-cloudflare-page-cache' ) );
render_description_section( __( 'This option is useful if you want to use Super Page Cache to enable browser caching rules for assets such like images, CSS, scripts, etc. It works automatically if you use Apache as web server or as backend web server.', 'wp-cloudflare-page-cache' ), false );
?>

<!-- Add browser caching rules for static assets -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Add browser caching rules for static assets', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description">
			<div class="orange_color"><?php _e( 'Writes into .htaccess', 'wp-cloudflare-page-cache' ); ?></div>
			<br/>
		</div>
		<div class="description">
			<strong><?php _e( 'Read here if you only use Nginx', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'it is not possible for Super Page Cache to automatically change the settings to allow this option to work immediately. For it to work, update these settings and then follow the instructions', 'wp-cloudflare-page-cache' ); ?>

			<a href="<?php echo esc_url( $nginx_instructions_page_url ); ?>" target="_blank">
				<?php _e( 'on this page', 'wp-cloudflare-page-cache' ); ?>.
			</a>
		</div>
	</div>
	<div class="right_column">
		<?php
		render_switch( Constants::SETTING_BROWSER_CACHE_STATIC_ASSETS, 1 );
		render_description( __( 'If you are using Plesk, make sure you have disabled the options "Smart static files processing" and "Serve static files directly by Nginx" on "Apache & Nginx Settings" page of your Plesk panel or ask your hosting provider to update browser caching rules for you.', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>


<!-- Cache TTL -->
<?php render_header( __( 'Cloudflare Cache lifetime settings', 'wp-cloudflare-page-cache' ) ); ?>
<!-- Cloudflare Cache-Control max-age -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Cloudflare Cache-Control max-age', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Don\'t touch if you don\'t know what is it. Must be greater than zero. Recommended 31536000 (1 year)', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php
		render_number_field( 'cf_maxage', 31536000 );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Browser Cache-Control max-age -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Browser Cache-Control max-age', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Don\'t touch if you don\'t know what is it. Must be greater than zero. Recommended a value between 60 and 600', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php
		render_number_field( 'cf_browser_maxage' );
		?>
	</div>
	<div class="clear"></div>
</div>


<?php $hide = empty( $sw_cloudflare_pagecache->get_single_config( 'cf_page_rule_id', '' ) ); ?>

<?php render_header( __( 'Cloudflare Cache behavior', 'wp-cloudflare-page-cache' ), false, '', $hide ? 'swcfpc_hide' : '' ); ?>
<!-- Automatically purge the Cloudflare's cache -->
<div class="main_section <?php echo $hide ? 'swcfpc_hide' : ''; ?>">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the Cloudflare\'s cache when something changes on the website', 'wp-cloudflare-page-cache' ); ?></label>
		<?php
		render_description( sprintf( '<strong>%s</strong>', __( 'Example: update/publish a post/page', 'wp-cloudflare-page-cache' ) ), false, false, true );
		/* translators: %s: link to Nginx instructions page (on this page) */
		render_description(
			sprintf(
				__( 'It is recommended to add the browser caching rules that you find %s.', 'wp-cloudflare-page-cache' ),
				'<a href="' . $nginx_instructions_page_url . '" target="_blank">' . __( 'on this page', 'wp-cloudflare-page-cache' ) . '</a>'
			)
		);
		?>
	</div>
	<div class="right_column">
		<?php
		render_checkbox( Constants::SETTING_AUTO_PURGE, __( 'Purge cache for related pages only', 'wp-cloudflare-page-cache' ), true );
		render_checkbox( Constants::SETTING_AUTO_PURGE_WHOLE, __( 'Purge whole cache', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge the Page cache when Cloudflare cache is purged -->
<div class="main_section <?php echo $hide ? 'swcfpc_hide' : 'fallbackcache'; ?>">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the Page cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_fallback_cache_auto_purge' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Force cache bypassing for backend with an additional Cloudflare page e rule -->
<div class="main_section <?php echo $hide ? 'swcfpc_hide' : 'cfworker_not'; ?>">
	<div class="left_column">
		<label><?php _e( 'Force cache bypassing for backend with an additional Cloudflare page rule', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( '<strong>Read here:</strong> by default, all back-end URLs are not cached thanks to some response headers, but if for some circumstances your backend pages are still cached, you can enable this option which will add an <strong>additional page rule on Cloudflare</strong> to force cache bypassing for the whole WordPress backend directly from Cloudflare. This option will be ignored if worker mode is enabled.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_bypass_backend_page_rule', 0, '', false, true ); ?>
	</div>
	<div class="clear"></div>
</div>

<?php if ( (int) $sw_cloudflare_pagecache->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) { ?>
	<!-- CF Worker -->
	<?php
	render_header( __( 'Cloudflare Workers', 'wp-cloudflare-page-cache' ) );
	render_description_section( __( 'This is a different way of using Cloudflare as a page caching system. Instead of page rules, you can use Cloudflare workers. This mode is only recommended if there are conflicts with the current web server or other plugins, as it is not 100% free.', 'wp-cloudflare-page-cache' ), false );
	?>

	<!-- Enable Cloudflare Worker -->
	<div class="main_section">
		<div class="left_column">
			<label><?php _e( 'Worker mode', 'wp-cloudflare-page-cache' ); ?></label>
			<?php render_description( __( 'Use Cloudflare Worker instead of page rule.', 'wp-cloudflare-page-cache' ) ); ?>
		</div>
		<div class="right_column">
			<?php
			render_switch( 'cf_woker_enabled', 0, 'cfworker', false, true );
			render_description( sprintf( __( 'If you are using an API Token, make sure you have enabled the permissions %1$s and %2$s', 'wp-cloudflare-page-cache' ), '<strong>' . __( 'Zone - Worker Routes - Edit', 'wp-cloudflare-page-cache' ) . '</strong>', '<strong>' . __( 'Account - Worker Scripts - Edit', 'wp-cloudflare-page-cache' ) . '</strong>' ), true, ! $is_token_auth );
			render_description( __( 'After enabled this option, enter to <strong>Workers</strong> section of your domain on Cloudflare, click on Edit near to Worker <strong>swcfpc_worker</strong> than select <strong>Fail open</strong> as <em>Request limit failure mode</em> and click on Save', 'wp-cloudflare-page-cache' ), true, ! $is_token_auth );
			?>
		</div>
		<div class="clear"></div>
	</div>

	<!-- CF Worker Bypass Cookies -->
	<div class="main_section cfworker">
		<div class="left_column">
			<label><?php _e( 'Bypass cache for the following cookies', 'wp-cloudflare-page-cache' ); ?></label>
			<?php
			render_description( __( 'One cookie per line.', 'wp-cloudflare-page-cache' ), false, false, true );
			render_description( '<strong>' . __( 'Read Here', 'wp-cloudflare-page-cache' ) . '</strong>: ' . __( 'to apply the changes you will need to purge the cache after saving.', 'wp-cloudflare-page-cache' ) );
			?>
		</div>
		<div class="right_column">
			<?php render_textarea( 'cf_worker_bypass_cookies' ); ?>
		</div>
		<div class="clear"></div>
	</div>
<?php } ?>

<?php render_header( __( 'Preloader', 'wp-cloudflare-page-cache' ) ); ?>

<!-- Enable Preloader -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Enable preloader', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_preloader', 1, 'preloader' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically preload the pages you have purged from cache -->
<div class="main_section preloader">
	<div class="left_column">
		<label><?php _e( 'Automatically preload the pages you have purged from cache.', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_preloader_start_on_purge' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Preloader operation -->
<div class="main_section preloader">
	<div class="left_column">
		<label><?php _e( 'Preloader operation', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description"><?php _e( 'Choose the URLs preloading logic that the preloader must use. If no option is chosen, the most recently published URLs and the home page will be preloaded.', 'wp-cloudflare-page-cache' ); ?></div>
	</div>
	<div class="right_column">
		<?php
		if ( is_array( $wordpress_menus ) && ! empty( $wordpress_menus ) ) {
			foreach ( $wordpress_menus as $idx => $single_nav_menu ) {
				?>
				<div>
					<input
							type="checkbox"
							id="swcfpc_cf_preloader_nav_menus_<?php echo $idx; ?>"
							name="swcfpc_cf_preloader_nav_menus[]"
							value="<?php echo $single_nav_menu->term_id; ?>"
						<?php echo in_array( $single_nav_menu->term_id, $sw_cloudflare_pagecache->get_single_config( 'cf_preloader_nav_menus', [] ) ) ? 'checked' : ''; ?> />
					<label for="swcfpc_cf_preloader_nav_menus_<?php echo $idx; ?>">
						<?php echo sprintf( __( 'Preload all internal links in <strong>%s</strong> WP menu', 'wp-cloudflare-page-cache' ), $single_nav_menu->name ); ?>
					</label>
				</div>
				<?php
			}
		}
		render_checkbox( 'cf_preload_last_urls', __( 'Preload last 20 published/updated posts, pages & CPTs combined', 'wp-cloudflare-page-cache' ) );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Preload all URLs into the following sitemaps -->
<div class="main_section preloader">
	<div class="left_column">
		<label><?php _e( 'Preload all URLs into the following sitemaps', 'wp-cloudflare-page-cache' ); ?></label>
		<?php
		render_description( __( 'One sitemap per line.', 'wp-cloudflare-page-cache' ), false, false, true );
		render_description( __( 'Example', 'wp-cloudflare-page-cache' ) . ':<br/>/post-sitemap.xml<br/>/page-sitemap.xml', false, false, true );
		?>
	</div>
	<div class="right_column">
		<?php render_textarea( Constants::SETTING_PRELOAD_SITEMAPS_URLS, '', [] ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Start the preloader manually -->
<div class="main_section preloader">
	<div class="left_column">
		<label><?php _e( 'Start the preloader manually', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description"><?php _e( 'Start preloading the pages of your website to speed up their inclusion in the cache. Make sure the cache is working first.', 'wp-cloudflare-page-cache' ); ?></div>
	</div>
	<div class="right_column">
		<button type="button" id="swcfpc_start_preloader"
				class="button button-primary"><?php _e( 'Start preloader', 'wp-cloudflare-page-cache' ); ?></button>
	</div>
	<div class="clear"></div>
</div>

<!-- Start the preloader via Cronjob -->
<div class="main_section preloader">
	<div class="left_column">
		<label><?php _e( 'Start the preloader via Cronjob', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<p><?php _e( 'If you want start the preloader at specific intervals decided by you, you can create a cronjob that hits the following URL', 'wp-cloudflare-page-cache' ); ?>
			:</p>
		<p><strong><?php echo esc_url_raw( $preloader_cronjob_url ); ?></strong></p>
	</div>
	<div class="clear"></div>
</div>

<!-- Cronjob secret key -->
<div class="main_section preloader">
	<div class="left_column">
		<label><?php _e( 'Cronjob secret key', 'wp-cloudflare-page-cache' ); ?></label>
		<div class="description"><?php _e( 'Secret key to use to start the preloader via URL. Don\'t touch if you don\'t know how to use it.', 'wp-cloudflare-page-cache' ); ?></div>
	</div>
	<div class="right_column">
		<?php render_text_field( 'cf_preloader_url_secret_key', $sw_cloudflare_pagecache->get_single_config( 'cf_preloader_url_secret_key', wp_generate_password( 20, false ) ) ); ?>
	</div>
	<div class="clear"></div>
</div>

<?php if ( ! $sw_cloudflare_pagecache->get_cache_controller()->can_i_start_preloader() ) : ?>
	<!-- Manually unlock preloader -->
	<div class="main_section preloader">
		<div class="left_column">
			<label><?php _e( 'Manually unlock preloader', 'wp-cloudflare-page-cache' ); ?></label>
		</div>
		<div class="right_column">
			<button type="button" id="swcfpc_unlock_preloader"
					class="button button-primary"><?php _e( 'Unlock preloader', 'wp-cloudflare-page-cache' ); ?></button>
		</div>
		<div class="clear"></div>
	</div>

<?php endif; ?>


<!-- Varnish Options -->
<?php render_header( __( 'Varnish settings', 'wp-cloudflare-page-cache' ) ); ?>

<!-- Enable Varnish Support -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Varnish Support', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_varnish_support', 0, 'varnish' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Varnish Hostname -->
<div class="main_section varnish">
	<div class="left_column">
		<label><?php _e( 'Hostname', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<div class="right_column">
			<?php render_text_field( 'cf_varnish_hostname', 'localhost' ); ?>
		</div>
	</div>
	<div class="clear"></div>
</div>

<!-- Varnish Port -->
<div class="main_section varnish">
	<div class="left_column">
		<label><?php _e( 'Port', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<div class="right_column">
			<?php
			render_number_field( 'cf_varnish_port', 6081 );
			?>
		</div>
	</div>
	<div class="clear"></div>
</div>

<!-- Varnish HTTP method for single page cache purge -->
<div class="main_section varnish">
	<div class="left_column">
		<label><?php _e( 'HTTP method for single page cache purge', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<div class="right_column">
			<?php render_text_field( 'cf_varnish_purge_method', 'PURGE' ); ?>
		</div>
	</div>
	<div class="clear"></div>
</div>

<!-- Varnish HTTP method for whole page cache purge -->
<div class="main_section varnish">
	<div class="left_column">
		<label><?php _e( 'HTTP method for whole page cache purge', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<div class="right_column">
			<?php render_text_field( 'cf_varnish_purge_all_method', 'PURGE' ); ?>
		</div>
	</div>
	<div class="clear"></div>
</div>

<!-- Varnish Cloudways -->
<div class="main_section varnish">
	<div class="left_column">
		<label><?php _e( 'Cloudways Varnish', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Enable this option if you are using Varnish on Cloudways.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_varnish_cw' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge Varnish cache when the cache is purged -->
<div class="main_section varnish">
	<div class="left_column">
		<label><?php _e( 'Automatically purge Varnish cache when the cache is purged.', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_varnish_auto_purge' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Purge Varnish cache -->
<div class="main_section varnish">
	<div class="left_column">
		<label><?php _e( 'Purge Varnish cache', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<button type="button" id="swcfpc_varnish_cache_purge"
				class="button button-primary"><?php _e( 'Purge cache', 'wp-cloudflare-page-cache' ); ?></button>
	</div>
	<div class="clear"></div>
</div>


<!-- Logs -->
<?php render_header( __( 'Logs', 'wp-cloudflare-page-cache' ), false, '', 'logs' ); ?>


<!-- Log Mode -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Log mode', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Enable this option if you want log all communications between Cloudflare and this plugin.', 'wp-cloudflare-page-cache' ), false, false, true ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( 'log_enabled', 0, 'logs', false, true ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Clear logs -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Clear logs manually', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Delete all the logs currently stored and optimize the log table.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<button type="button" id="swcfpc_clear_logs"
				class="button button-primary"><?php _e( 'Clear logs now', 'wp-cloudflare-page-cache' ); ?></button>
	</div>
	<div class="clear"></div>
</div>

<!-- Download / view logs -->
<div class="main_section logs">
	<div class="left_column">
		<label><?php _e( 'Download logs', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<a class="button button-primary"
		   href="<?php echo add_query_arg( [ 'swcfpc_download_log' => 1 ], admin_url() ); ?>"
		   target="_blank">
			<?php _e( 'Download log file', 'wp-cloudflare-page-cache' ); ?>
		</a>
		<a class="button button-primary"
		   href="<?php echo add_query_arg( [ 'swcfpc_download_log' => 'view' ], admin_url() ); ?>"
		   target="_blank">
			<?php _e( 'View log file', 'wp-cloudflare-page-cache' ); ?>
		</a>
	</div>
	<div class="clear"></div>
</div>

<!-- Max log file size -->
<div class="main_section logs">
	<div class="left_column">
		<label><?php _e( 'Max log file size in MB', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Automatically reset the log file when it exceeded the max file size. Set 0 to never reset it.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_number_field( 'log_max_file_size', 2 ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Log verbosity -->
<div class="main_section logs">
	<div class="left_column">
		<label><?php _e( 'Log verbosity', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<select name="swcfpc_log_verbosity">
			<option value="<?php echo SWCFPC_LOGS_STANDARD_VERBOSITY; ?>"
				<?php
				if ( $sw_cloudflare_pagecache->get_single_config( 'log_verbosity', SWCFPC_LOGS_STANDARD_VERBOSITY ) == SWCFPC_LOGS_STANDARD_VERBOSITY ) {
					echo 'selected';
				}
				?>
			><?php _e( 'Standard', 'wp-cloudflare-page-cache' ); ?></option>
			<option value="<?php echo SWCFPC_LOGS_HIGH_VERBOSITY; ?>"
				<?php
				if ( $sw_cloudflare_pagecache->get_single_config( 'log_verbosity', SWCFPC_LOGS_STANDARD_VERBOSITY ) == SWCFPC_LOGS_HIGH_VERBOSITY ) {
					echo 'selected';
				}
				?>
			><?php _e( 'High', 'wp-cloudflare-page-cache' ); ?></option>
		</select>
	</div>
	<div class="clear"></div>
</div>


<!-- Import/Export settings -->
<?php render_header( __( 'Import/Export', 'wp-cloudflare-page-cache' ), false, '', 'importexport' ); ?>

<!-- Export config file -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Export config file', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<a href="<?php echo add_query_arg( [ 'swcfpc_export_config' => 1 ], admin_url() ); ?>"
		   target="_blank">
			<button type="button" class="button button-primary">
				<?php _e( 'Export', 'wp-cloudflare-page-cache' ); ?>
			</button>
		</a>
	</div>
	<div class="clear"></div>
</div>

<!-- Import config file -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Import config file', 'wp-cloudflare-page-cache' ); ?></label>
		<?php
		render_description( __( 'Import the options of the previously exported configuration file.', 'wp-cloudflare-page-cache' ) );
		render_description(
			sprintf(
				'<strong>%s</strong>: %s',
				__( 'Read here', 'wp-cloudflare-page-cache' ),
				__( 'after the import you will be forced to re-enter the authentication data to Cloudflare and to manually enable the cache.', 'wp-cloudflare-page-cache' )
			)
		);
		?>
	</div>
	<div class="right_column">
		<textarea
				name="swcfpc_import_config"
				id="swcfpc_import_config_content"
				placeholder="<?php _e( 'Copy and paste here the content of the swcfpc_config.json file', 'wp-cloudflare-page-cache' ); ?>"></textarea>
		<button type="button" id="swcfpc_import_config_start" class="button button-primary">
			<?php _e( 'Import', 'wp-cloudflare-page-cache' ); ?>
		</button>
	</div>
	<div class="clear"></div>
</div>


<!-- Other settings -->
<?php render_header( __( 'Other settings', 'wp-cloudflare-page-cache' ) ); ?>

<!-- Automatically purge the OPCache when cache is purged -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the OPcache cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_opcache_purge_on_flush' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Automatically purge the object cache when Cloudflare cache is purged -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Automatically purge the object cache when cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_object_cache_purge_on_flush' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Purge the whole Cloudflare cache via Cronjob -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Purge the whole Cloudflare cache via Cronjob', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<p><?php _e( 'If you want purge the whole Cloudflare cache at specific intervals decided by you, you can create a cronjob that hits the following URL', 'wp-cloudflare-page-cache' ); ?>
			:</p>
		<p><strong><?php echo esc_url( Admin::get_cronjob_url( 'purge' ) ); ?></strong></p>
	</div>
	<div class="clear"></div>
</div>

<!-- Purge cache URL secret key -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Purge cache URL secret key', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Secret key to use to purge the whole Cloudflare cache via URL. Don\'t touch if you don\'t know how to use it.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_text_field( 'cf_purge_url_secret_key', $sw_cloudflare_pagecache->get_single_config( 'cf_purge_url_secret_key', wp_generate_password( 20, false ) ) ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Remove purge option from toolbar -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Remove purge option from toolbar', 'wp-cloudflare-page-cache' ); ?></label>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_remove_purge_option_toolbar' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Disable metaboxes on single pages and posts -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Disable metaboxes on single pages and posts', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Disable the metaboxes on single pages and posts to avoid conflicts with other plugins.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_disable_single_metabox' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- SEO redirect -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'SEO redirect', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Redirect 301 for all URLs that for any reason have been indexed together with the cache buster. Works for logged out users only.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( 'cf_seo_redirect' ); ?>
	</div>
	<div class="clear"></div>
</div>

<!-- Select user roles allowed to purge the cache -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Select user roles allowed to purge the cache', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Admins are always allowed.', 'wp-cloudflare-page-cache' ) ); ?>

	</div>
	<div class="right_column">
		<?php
		if ( is_array( $wordpress_roles ) && count( $wordpress_roles ) > 0 ) :
			foreach ( $wordpress_roles as $single_role_name ) :
				if ( $single_role_name == 'administrator' ) {
					continue;
				}
				?>
				<div><input type="checkbox" name="swcfpc_purge_roles[]"
							value="<?php echo $single_role_name; ?>" <?php echo in_array( $single_role_name, $sw_cloudflare_pagecache->get_single_config( 'cf_purge_roles', [] ) ) ? 'checked' : ''; ?> /> <?php echo $single_role_name; ?>
				</div>
				<?php
			endforeach;
		endif;
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Auto prefetch URLs in viewport -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Auto prefetch URLs in viewport', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'If enabled, the browser prefetches in background all the internal URLs found in the viewport.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php
		render_switch( 'cf_prefetch_urls_viewport' );
		render_description( __( 'Purge the cache and wait about 30 seconds after enabling/disabling this option.', 'wp-cloudflare-page-cache' ), true );
		render_description( __( 'URIs in <em>Prevent the following URIs to be cached</em> will not be prefetched.', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Auto prefetch URLs on mouse hover -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Auto prefetch URLs on mouse hover', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'If enabled, it preloads a page right before a user clicks on it. It uses instant.page just-in-time preloading.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php
		render_switch( Constants::SETTING_PREFETCH_ON_HOVER );
		render_description( __( 'Purge the cache and wait about 30 seconds after enabling/disabling this option.', 'wp-cloudflare-page-cache' ), true );
		render_description( __( 'URIs in <em>Prevent the following URIs to be cached</em> will not be prefetched.', 'wp-cloudflare-page-cache' ), true );
		?>
	</div>
	<div class="clear"></div>
</div>

<!-- Remove Cache Buster Query Parameter - disabled for new users -->
<?php if ( (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_REMOVE_CACHE_BUSTER, 1 ) === 0 ) { ?>
	<div class="main_section cfworker_not">
		<div class="left_column">
			<label><?php _e( 'Remove Cache Buster Query Parameter', 'wp-cloudflare-page-cache' ); ?></label>
			<?php render_description( __( 'Stop adding cache buster query parameter when using the default page rule mode.', 'wp-cloudflare-page-cache' ) ); ?>
		</div>
		<div class="right_column">
			<div class="switch-field">
				<?php render_switch( Constants::SETTING_REMOVE_CACHE_BUSTER ); ?>
			</div>
			<?php
			render_description( __( '<strong>DO NOT ENABLE this option</strong> unless you are an advanced user confortable with creating advanced Cloudflare rules. Otherwise caching system will break on your website.', 'wp-cloudflare-page-cache' ), true );
			render_description( __( 'Check <strong><a href="https://gist.github.com/isaumya/af10e4855ac83156cc210b7148135fa2" target="_blank" rel="external noopener noreferrer">this implementation guide</a></strong> first before enabling this option.', 'wp-cloudflare-page-cache' ), true );
			?>
		</div>
		<div class="clear"></div>
	</div>
<?php } ?>

<!-- Keep settings on deactivation -->
<div class="main_section">
	<div class="left_column">
		<label><?php _e( 'Keep settings on deactivation', 'wp-cloudflare-page-cache' ); ?></label>
		<?php render_description( __( 'Keep settings on plugin deactivation.', 'wp-cloudflare-page-cache' ) ); ?>
	</div>
	<div class="right_column">
		<?php render_switch( 'swcfpc_keep_settings_on_deactivation' ); ?>
	</div>
	<div class="clear"></div>
</div>
				
