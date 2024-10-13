<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

settings_errors( Dashboard::$slug );
?>
<div class="wrap grim-wrapper">
	<h1><?php esc_html_e( 'Google XML Sitemaps Generator Settings', 'xml-sitemap-generator-for-google' ); ?></h1>

	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="has-sidebar">
			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( GRIM_SG_BASENAME . '-settings', 'sgg_settings_nonce' ); ?>
				<div id="post-body-content" class="has-sidebar-content">
					<div class="meta-box-sortabless">
						<nav class="nav-tab-wrapper">
							<a href="#" class="nav-tab nav-tab-active" data-id="general"><?php esc_html_e( 'General', 'xml-sitemap-generator-for-google' ); ?></a>
							<a href="#" class="nav-tab" data-id="google-news"><?php esc_html_e( 'Google News', 'xml-sitemap-generator-for-google' ); ?></a>
							<a href="#" class="nav-tab" data-id="media-sitemaps"><?php esc_html_e( 'Media Sitemaps', 'xml-sitemap-generator-for-google' ); ?></a>
							<a href="#" class="nav-tab" data-id="advanced"><?php esc_html_e( 'Advanced', 'xml-sitemap-generator-for-google' ); ?></a>
						</nav>

						<div class="nav-tabs-content">
							<div class="section">
								<!-- General -->
								<?php Dashboard::render( 'sections/general/general-settings.php', $args ); ?>

								<!-- Output URLs -->
								<?php Dashboard::render( 'sections/general/xml-sitemap.php', $args ); ?>

								<!-- Webserver Configuration -->
								<?php
								if ( sgg_is_nginx() && sgg_is_using_mod_rewrite() ) {
									Dashboard::render( 'sections/general/webserver-configuration.php', $args );
								}
								?>

								<!-- HTML Sitemap -->
								<?php Dashboard::render( 'sections/general/html-sitemap.php', $args ); ?>

								<!-- Sitemap Structure -->
								<?php Dashboard::render( 'sections/general/sitemap-structure.php', $args ); ?>

								<!-- Custom Sitemaps -->
								<?php Dashboard::render( 'sections/general/custom-sitemaps.php', $args ); ?>

								<!-- Additional Pages -->
								<?php Dashboard::render( 'sections/general/additional.php', $args ); ?>

								<!-- Exclude -->
								<?php Dashboard::render( 'sections/general/exclude.php', $args ); ?>

								<!-- Posts Priority -->
								<?php Dashboard::render( 'sections/general/posts-priority.php', $args ); ?>

								<!-- Sitemap -->
								<?php Dashboard::render( 'sections/general/sitemap-options.php', $args ); ?>
							</div>

							<div class="section">
								<!-- General -->
								<?php Dashboard::render( 'sections/google-news/general-settings.php', $args ); ?>

								<!-- Output URLs -->
								<?php Dashboard::render( 'sections/google-news/output.php', $args ); ?>

								<!-- Keywords -->
								<?php Dashboard::render( 'sections/google-news/keywords.php', $args ); ?>

								<!-- Stock Tickers -->
								<?php Dashboard::render( 'sections/google-news/stock-tickers.php', $args ); ?>

								<!-- Exclude -->
								<?php Dashboard::render( 'sections/google-news/exclude.php', $args ); ?>

								<!-- Content -->
								<?php Dashboard::render( 'sections/google-news/content.php', $args ); ?>
							</div>

							<div class="section">
								<!-- Image Sitemap -->
								<?php Dashboard::render( 'sections/media-sitemaps/image-sitemap.php', $args ); ?>

								<!-- Video Sitemap -->
								<?php Dashboard::render( 'sections/media-sitemaps/video-sitemap.php', $args ); ?>

								<!-- Content -->
								<?php Dashboard::render( 'sections/media-sitemaps/content.php', $args ); ?>
							</div>

							<div class="section">
								<!-- Cache -->
								<?php Dashboard::render( 'sections/advanced/cache.php', $args ); ?>

								<!-- Minimize -->
								<?php Dashboard::render( 'sections/advanced/optimize.php', $args ); ?>

								<!-- Import & Export -->
								<?php Dashboard::render( 'sections/advanced/import-export.php', $args ); ?>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" name="save_settings">
				<?php submit_button(); ?>
			</form>

			<?php Dashboard::render( 'partials/sidebar.php', $args ); ?>
		</div>
	</div>
</div>
