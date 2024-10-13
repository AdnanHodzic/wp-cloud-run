<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Sitemap Structure', 'xml-sitemap-generator-for-google' ); ?></h3>

	<div class="inside">
		<p>
			<?php
			printf(
				/* translators: %s Google Index Sitemap URL */
				wp_kses_post( 'You can choose either <b>Single Sitemap</b> structure with all links or split links into <b>Multiple Sitemaps</b> for Pages, Posts, Custom Posts, etc, by creating <a href="%s" target="_blank">Sitemap Index</a>.' ),
				'https://developers.google.com/search/docs/crawling-indexing/sitemaps/large-sitemaps'
			)
			?>
			<br>
			<?php esc_html_e( 'Choose Sitemap Structure:', 'xml-sitemap-generator-for-google' ); ?>
		</p>
		<div class="sitemap-view-section">
			<div>
				<input id="sitemap-index" class="has-dependency" data-target="sitemap-index-depended" type="radio" name="sitemap_view" value="sitemap-index" <?php checked( 'sitemap-index', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
				<label class="sitemap-view-label sitemap-index" for="sitemap-index">
					<b><?php esc_html_e( 'Sitemap Index', 'xml-sitemap-generator-for-google' ); ?></b>
					<?php esc_html_e( 'will be generated with Inner Sitemaps', 'xml-sitemap-generator-for-google' ); ?>
				</label>
			</div>
			<div>
				<input id="single-sitemap" class="has-dependency" data-target="single-sitemap-depended" type="radio" name="sitemap_view" value="" <?php checked( '', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
				<label class="sitemap-view-label single-sitemap" for="single-sitemap">
					<b><?php esc_html_e( 'Single Sitemap', 'xml-sitemap-generator-for-google' ); ?></b>
					<?php esc_html_e( 'will be generated with all links', 'xml-sitemap-generator-for-google' ); ?>
				</label>
			</div>
		</div>
	</div>
</div>
