<?php

use GRIM_SG\Dashboard;
/**
 * @var $args
 */

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-webserver">
	<h3 class="grim-section-title" data-search-id="advanced_wp_cli"><?php esc_html_e( 'WP CLI', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20">
			<?php esc_html_e( 'WP CLI is a feature that allows you to generate sitemaps using the command line. You can use the following command to generate sitemaps:', 'xml-sitemap-generator-for-google' ); ?>
		</p>
		<div class="grim-wp-cli grim-mb-20">
			<code>
				<p id="wp-cli">
					$ wp sitemap generate --template=sitemap
					<br>
					$ wp sitemap generate --template=image-sitemap
					<br>
					$ wp sitemap generate --template=video-sitemap
					<br>
					$ wp sitemap generate --template=google-news
				</p>
				<div class="grim-code-copied">
					<span class="grim-btn-copied" data-target="wp-cli">
						<i class="grim-icon-copy"></i>
						<span class="grim-tooltip"><?php esc_html_e( 'Copied!', 'xml-sitemap-generator-for-google' ); ?></span>
					</span>
				</div>
			</code>
			<p>
				<?php esc_html_e( 'You can find more information about WP CLI in', 'xml-sitemap-generator-for-google' ); ?>
				<a href="https://wp-cli.org/" target="_blank">https://wp-cli.org/</a>.
			</p>
		</div>
		<div class="grim-wp-cli-code">
			<p class="grim-section-desc grim-mb-10">
				<?php esc_html_e( 'Also, you can set Cron Job to generate sitemaps automatically using WP CLI command. Here is an example for daily cron job:', 'xml-sitemap-generator-for-google' ); ?>
			</p>
			<code>
				<p id="sitemap-generate">
					0 0 * * * /usr/local/bin/wp sitemap generate --template=image-sitemap --path=/var/www/html --quiet
				</p>
				<div class="grim-code-copied">
					<span class="grim-btn-copied" data-target="sitemap-generate">
						<i class="grim-icon-copy"></i>
						<span class="grim-tooltip"><?php esc_html_e( 'Copied!', 'xml-sitemap-generator-for-google' ); ?></span>
					</span>
				</div>
			</code>
		</div>
	</div>
</div>
