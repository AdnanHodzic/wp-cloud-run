<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title google-news-depended" data-search-id="google_keywords"><?php esc_html_e( 'Keywords', 'xml-sitemap-generator-for-google' );?></h3>
	</div>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="grim-section-desc grim-mb-20 google-news-depended"><?php esc_html_e( 'Please select the source from which the Google News Keywords should be extracted.', 'xml-sitemap-generator-for-google' ); ?></p>

		<p>
			<?php
			Dashboard::render(
				'fields/select.php',
				array(
					'label'       => esc_html__( 'Keywords from:', 'xml-sitemap-generator-for-google' ),
					'name'        => 'google_news_keywords',
					'class'       => 'google-news-depended',
					'select_size' => 'grim-select-wide',
					'value'       => $settings->google_news_keywords ?? '',
					'options'     => array(
						'post_tag'     => esc_html__( 'Tags', 'xml-sitemap-generator-for-google' ),
						'category'     => esc_html__( 'Categories', 'xml-sitemap-generator-for-google' ),
						'sgg_keywords' => esc_html__( 'Keywords Taxonomy', 'xml-sitemap-generator-for-google' ),
					),
				)
			);
			?>
		</p>

		<p class="grim-section-desc grim-mt-8 google-news-depended"><?php esc_html_e( 'Custom Keywords Taxonomy will be available for Posts after enabling this option.', 'xml-sitemap-generator-for-google' ); ?></p>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>
