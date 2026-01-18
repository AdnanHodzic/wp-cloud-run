<?php
/**
 * @var $args
 */

$settings = $args['settings'] ?? new stdClass();
?>
<div class="sitemap-view-section-title">Sitemap Structure</div>
<div class="sitemap-view-section-desc">
	<?php
	printf(
		wp_kses_post( 'You can choose either Single Sitemap structure with all links or split links into Multiple Sitemaps for Pages, Posts, Custom Posts, etc, by creating <a href="%s" target="_blank">Sitemap Index</a>.' ),
		'https://developers.google.com/search/docs/crawling-indexing/sitemaps/large-sitemaps'
	)
	?>
</div>
<div class="wizard-sitemap-structure-view-section">
	<label class="wizard-sitemap-structure-view-section-item sitemap-view-label sitemap-index" for="sitemap-index">
		<input id="sitemap-index" type="radio" name="sitemap_view" value="sitemap-index" <?php checked( 'sitemap-index', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
		<i class="grim-icon-sitemap-index"></i>
		<p>
			<b><?php esc_html_e( 'Sitemap Index', 'xml-sitemap-generator-for-google' ); ?></b><br>
			<?php esc_html_e( 'will be generated with Inner Sitemaps', 'xml-sitemap-generator-for-google' ); ?>
		</p>
	</label>
	<label class="wizard-sitemap-structure-view-section-item sitemap-view-label single-sitemap" for="single-sitemap">
		<input id="single-sitemap" type="radio" name="sitemap_view" value="" <?php checked( '', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
		<i class="grim-icon-single-sitemap"></i>
		<p>
			<b><?php esc_html_e( 'Single Sitemap', 'xml-sitemap-generator-for-google' ); ?></b><br>
			<?php esc_html_e( 'will be generated with all links', 'xml-sitemap-generator-for-google' ); ?>
		</p>
	</label>
</div>