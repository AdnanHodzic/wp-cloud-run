<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;
use GRIM_SG\PTSettings;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="inner-sidebar">
	<div class="sidebar-section">
		<h3>
			<span class="dashicons dashicons-admin-tools"></span>
			<?php esc_html_e( 'Tools', 'xml-sitemap-generator-for-google' ); ?>
		</h3>
		<form method="post">
			<?php wp_nonce_field( GRIM_SG_BASENAME . '-tools', 'sgg_tools_nonce' ); ?>
			<?php if ( $settings->enable_indexnow ) { ?>
				<p>
					<input type="submit" name="sgg-indexnow" class="button" value="<?php esc_html_e( 'Ping IndexNow Protocol', 'xml-sitemap-generator-for-google' ); ?>">
				</p>
			<?php } ?>
			<p>
				<input type="submit" name="sgg-flush-rewrite-rules" class="button" value="<?php esc_html_e( 'Flush Rewrite Rules', 'xml-sitemap-generator-for-google' ); ?>">
			</p>
			<?php if ( $settings->enable_cache ) { ?>
				<p>
					<input type="submit" name="sgg-clear-cache" class="button button-link-delete" value="<?php esc_html_e( 'Clear Sitemaps Cache', 'xml-sitemap-generator-for-google' ); ?>">
				</p>
			<?php } ?>
		</form>
	</div>
	<div class="sidebar-section">
		<?php if ( $settings->enable_sitemap || $settings->enable_html_sitemap || $settings->enable_google_news || $settings->enable_image_sitemap || $settings->enable_video_sitemap ) { ?>
			<h3>
				<span class="dashicons dashicons-welcome-view-site"></span>
				<?php esc_html_e( 'Preview', 'xml-sitemap-generator-for-google' ); ?>
			</h3>
		<?php } ?>
		<?php if ( $settings->enable_sitemap ) { ?>
			<p>
				<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->sitemap_url, 'sitemap_xml' ) ); ?>" target="_blank" class="button">
					<?php esc_html_e( 'XML Sitemap', 'xml-sitemap-generator-for-google' ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</p>
		<?php } ?>
		<?php if ( sgg_pro_enabled() && $settings->enable_html_sitemap ) { ?>
			<p>
				<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->html_sitemap_url, 'sitemap_html' ) ); ?>" target="_blank" class="button">
					<?php esc_html_e( 'HTML Sitemap', 'xml-sitemap-generator-for-google' ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</p>
		<?php } ?>
		<?php if ( $settings->enable_google_news ) { ?>
			<p>
				<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->google_news_url, 'google_news' ) ); ?>" target="_blank" class="button">
					<?php esc_html_e( 'Google News', 'xml-sitemap-generator-for-google' ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</p>
		<?php } ?>
		<?php if ( $settings->enable_image_sitemap ) { ?>
			<p>
				<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ) ); ?>" target="_blank" class="button">
					<?php esc_html_e( 'Image Sitemap', 'xml-sitemap-generator-for-google' ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</p>
		<?php } ?>
		<?php if ( $settings->enable_video_sitemap ) { ?>
			<p>
				<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ) ); ?>" target="_blank" class="button">
					<?php esc_html_e( 'Video Sitemap', 'xml-sitemap-generator-for-google' ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</p>
		<?php } ?>
	</div>
	<div class="sidebar-section">
		<h3>
			<span class="dashicons dashicons-sos"></span>
			<?php esc_html_e( 'Tips', 'xml-sitemap-generator-for-google' ); ?>
		</h3>
		<p>
			✅ <strong><?php esc_html_e( 'HTML Sitemap', 'xml-sitemap-generator-for-google' ); ?></strong>
			<?php esc_html_e( 'can be displayed using Page Builder Widget or Shortcode', 'xml-sitemap-generator-for-google' ); ?>:
			<br>
			<?php if ( sgg_pro_enabled() ) { ?>
				📄 <?php esc_html_e( 'Elementor', 'xml-sitemap-generator-for-google' ); ?>
				<br>
				📄 <?php esc_html_e( 'Gutenberg', 'xml-sitemap-generator-for-google' ); ?>
				<br>
				📄 <?php esc_html_e( 'WPBakery (Visual Composer)', 'xml-sitemap-generator-for-google' ); ?>
				<br>
				📄
			<?php } ?>
			<strong class="shortcode">[html-sitemap post-types="page,post,.."]</strong>
		</p>
		<?php if ( ! sgg_pro_enabled() ) { ?>
			<hr>
			<p class="pro-section">
				🔓 <a href="<?php echo esc_url( sgg_get_pro_url( 'toolbar' ) ); ?>" target="_blank"><?php esc_html_e( 'Get Pro Version now', 'xml-sitemap-generator-for-google' ); ?></a>
				<?php esc_html_e( ' to take your Search Engine Optimization to the Next Level with unlocking Premium Features.', 'xml-sitemap-generator-for-google' ); ?>
				<a href="<?php echo esc_url( sgg_get_pro_url( 'toolbar' ) ); ?>" class="pro-button" target="_blank"><?php esc_html_e( 'Read More', 'xml-sitemap-generator-for-google' ); ?></a>
			</p>
			<hr>
		<?php } ?>
	</div>
	<div class="sidebar-section">
		<h3>
			<span class="dashicons dashicons-admin-links"></span>
			<?php esc_html_e( 'Links', 'xml-sitemap-generator-for-google' ); ?>
		</h3>
		<ul>
			<?php if ( sgg_pro_enabled() ) { ?>
				<li>
					👤 <a href="https://wpgrim.com/account?utm_source=sgg-plugin&utm_medium=account&utm_campaign=xml_sitemap" target="_blank"><?php esc_html_e( 'Account & Premium Support', 'xml-sitemap-generator-for-google' ); ?></a>
				</li>
			<?php } ?>
			<li>
				🎓 <a href="https://wpgrim.com/docs/google-xml-sitemaps-generator/?utm_source=sgg-plugin&utm_medium=documentation&utm_campaign=xml_sitemap" target="_blank"><?php esc_html_e( 'Documentation', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🛟 <a href="<?php echo esc_url( sgg_get_support_url() ); ?>" target="_blank"><?php esc_html_e( 'Support Forum', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				⭐️ <a href="<?php echo esc_url( sgg_get_review_url() ); ?>" target="_blank"><?php esc_html_e( 'Rate ★★★★★', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🔗 <a href="https://search.google.com/search-console" target="_blank"><?php esc_html_e( 'Google Search Console', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🔗 <a href="https://support.google.com/googlenews/" target="_blank"><?php esc_html_e( 'Google News Help Center', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🔗 <a href="https://support.google.com/news/publisher-center/answer/9607025" target="_blank"><?php esc_html_e( 'Show up in Google News', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🔗 <a href="https://www.indexnow.org/" target="_blank"><?php esc_html_e( 'IndexNow Protocol', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🔗 <a href="https://www.bing.com/webmasters" target="_blank"><?php esc_html_e( 'Bing Webmaster Tools', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🔗 <a href="https://webmaster.yandex.com/sites/" target="_blank"><?php esc_html_e( 'Yandex Webmaster', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
			<li>
				🔗 <a href="https://www.xml-sitemaps.com/validate-xml-sitemap.html" target="_blank"><?php esc_html_e( 'XML Sitemap Validator', 'xml-sitemap-generator-for-google' ); ?></a>
			</li>
		</ul>
	</div>
</div>
