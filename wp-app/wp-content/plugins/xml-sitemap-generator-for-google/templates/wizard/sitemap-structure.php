<?php
/**
 * @var $args
 */

$settings = $args['settings'] ?? new stdClass();
?>
<div class="sitemap-view-section">
	<div>
		<input id="sitemap-index" type="radio" name="sitemap_view" value="sitemap-index" <?php checked( 'sitemap-index', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
		<label class="sitemap-view-label sitemap-index" for="sitemap-index">
			<b><?php esc_html_e( 'Sitemap Index', 'xml-sitemap-generator-for-google' ); ?></b>
			<?php esc_html_e( 'with Inner Sitemaps', 'xml-sitemap-generator-for-google' ); ?>
		</label>
	</div>
	<div>
		<input id="single-sitemap" type="radio" name="sitemap_view" value="" <?php checked( '', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
		<label class="sitemap-view-label single-sitemap" for="single-sitemap">
			<b><?php esc_html_e( 'Single Sitemap', 'xml-sitemap-generator-for-google' ); ?></b>
			<?php esc_html_e( 'with all links', 'xml-sitemap-generator-for-google' ); ?>
		</label>
	</div>
</div>

<div class="sitemap-structure-description">
	<?php echo wp_kses_post( 'You can choose either <b>Single Sitemap</b> structure with all links or split links into <b>Multiple Sitemaps</b> for Pages, Posts, Custom Posts, etc, by creating Sitemap Index.' ); ?>
</div>

