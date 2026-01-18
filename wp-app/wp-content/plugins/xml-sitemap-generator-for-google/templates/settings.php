<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;
?>

<div class="grim-container grim-mb-20">
	<?php
		settings_errors( Dashboard::$slug );
	?>
</div>

<div class="wrap grim-wrapper grim-container">
	<div id="poststuff" class="metabox-holder">
		<div>
			<form method="post" enctype="multipart/form-data" id="settings-form">
			<?php
			Dashboard::render( 'partials/navigation.php', $args );
			?>
				<?php wp_nonce_field( GRIM_SG_BASENAME . '-settings', 'sgg_settings_nonce' ); ?>
				<div id="post-body-content" class="has-sidebar-content">
					<div class="meta-box-sortabless">
						<div class="nav-tabs-content">
							<div class="section">
								<!-- General -->
								<?php Dashboard::render( 'sections/general/general-settings.php', $args ); ?>

								<!-- Output URLs -->
								<?php Dashboard::render( 'sections/general/xml-sitemap.php', $args ); ?>

								<!-- HTML Sitemap -->
								<?php Dashboard::render( 'sections/general/html-sitemap.php', $args ); ?>

								<!-- Webserver Configuration -->
								<?php
								if ( sgg_is_nginx() && sgg_is_using_mod_rewrite() ) {
									Dashboard::render( 'sections/general/webserver-configuration.php', $args );
								}
								?>

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
								<?php Dashboard::render( 'sections/image-sitemap/general-settings.php', $args ); ?>

								<!-- Content -->
								<?php Dashboard::render( 'sections/image-sitemap/content.php', $args ); ?>
							</div>

							<div class="section">
								<!-- Video Sitemap -->
								<?php Dashboard::render( 'sections/video-sitemap/general-settings.php', $args ); ?>

								<!-- Content -->
								<?php Dashboard::render( 'sections/video-sitemap/content.php', $args ); ?>
							</div>

							<div class="section">
								<!-- Cache -->
								<?php Dashboard::render( 'sections/advanced/cache.php', $args ); ?>

								<!-- Minimize -->
								<?php Dashboard::render( 'sections/advanced/optimize.php', $args ); ?>

								<!-- Styles -->
								<?php Dashboard::render( 'sections/advanced/styles.php', $args ); ?>

								<!-- Cron Job -->
								<?php Dashboard::render( 'sections/advanced/cronjob.php', $args ); ?>

								<!-- WP CLI -->
								<?php Dashboard::render( 'sections/advanced/wp-cli.php', $args ); ?>

								<!-- Import & Export -->
								<?php Dashboard::render( 'sections/advanced/import-export.php', $args ); ?>
							</div>
						</div>
						<div class="tools-sidebar">
							<?php Dashboard::render( 'partials/sidebar.php', $args ); ?>
						</div>
					</div>
				</div>
				<input type="hidden" name="save_settings">
			</form>
		</div>
	</div>
</div>
