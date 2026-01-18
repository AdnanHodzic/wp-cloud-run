<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-sitemap-structure">
	<h3 class="grim-section-title" data-search-id="sitemap_structure"><?php esc_html_e( 'Sitemap Structure', 'xml-sitemap-generator-for-google' ); ?></h3>

	<div class="inside">
		<p class="grim-section-desc grim-mb-20">
			<?php
			printf(
				/* translators: %s Google Index Sitemap URL */
				wp_kses_post( 'You can choose either Single Sitemap structure with all links or split links into Multiple Sitemaps for Pages, Posts, Custom Posts, etc, by creating <a href="%s" target="_blank">Sitemap Index</a>.' ),
				'https://developers.google.com/search/docs/crawling-indexing/sitemaps/large-sitemaps'
			)
			?>
		</p>
		<div class="grim-sitemap-structure-view-section grim-mb-20">
			<label class="grim-sitemap-structure-view-section-item sitemap-view-label sitemap-index" for="sitemap-index">
				<input id="sitemap-index" class="has-dependency" data-target="sitemap-index-depended" type="radio" name="sitemap_view" value="sitemap-index" <?php checked( 'sitemap-index', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
				<i class="grim-icon-sitemap-index"></i>
				<p>
					<b><?php esc_html_e( 'Sitemap Index', 'xml-sitemap-generator-for-google' ); ?></b>
					<?php esc_html_e( 'will be generated with Inner Sitemaps', 'xml-sitemap-generator-for-google' ); ?>
				</p>
			</label>
			<label class="grim-sitemap-structure-view-section-item sitemap-view-label single-sitemap" for="single-sitemap">
				<input id="single-sitemap" class="has-dependency" data-target="single-sitemap-depended" type="radio" name="sitemap_view" value="" <?php checked( '', esc_attr( $settings->sitemap_view ?? '' ) ); ?>/>
				<i class="grim-icon-single-sitemap"></i>
				<p>
					<b><?php esc_html_e( 'Single Sitemap', 'xml-sitemap-generator-for-google' ); ?></b>
					<?php esc_html_e( 'will be generated with all links', 'xml-sitemap-generator-for-google' ); ?>
				</p>
			</label>
		</div>
		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'type'        => 'number',
					'name'        => 'links_per_page',
					'class'       => 'grim-input sitemap-index-depended',
					'value'       => $settings->links_per_page ?? 1000,
					'label'       => esc_html__( 'Links per page:', 'xml-sitemap-generator-for-google' ),
					'description' => esc_html__( 'Number of links per page in Sitemap Index. Note: Setting a low limit per page may affect the speed of generating the Sitemap Index.', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>
	</div>
</div>
