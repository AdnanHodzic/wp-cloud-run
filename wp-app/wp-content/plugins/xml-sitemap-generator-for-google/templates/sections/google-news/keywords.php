<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php
		esc_html_e( 'Keywords', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
		?></h3>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="google-news-depended"><?php esc_html_e( 'Please select the source from which the Google News Keywords should be extracted.', 'xml-sitemap-generator-for-google' ); ?></p>

		<p>
			<?php
			Dashboard::render(
				'fields/select.php',
				array(
					'label'   => esc_html__( 'Keywords from:', 'xml-sitemap-generator-for-google' ),
					'name'    => 'google_news_keywords',
					'class'   => 'google-news-depended',
					'value'   => $settings->google_news_keywords ?? '',
					'options' => array(
						'post_tag'     => esc_html__( 'Tags', 'xml-sitemap-generator-for-google' ),
						'category'     => esc_html__( 'Categories', 'xml-sitemap-generator-for-google' ),
						'sgg_keywords' => esc_html__( 'Keywords Taxonomy', 'xml-sitemap-generator-for-google' ),
					),
				)
			);
			?>
		</p>

		<p class="google-news-depended"><?php esc_html_e( 'Custom Keywords Taxonomy will be available for Posts after enabling this option.', 'xml-sitemap-generator-for-google' ); ?></p>

		<?php sgg_show_pro_overlay(); ?>
	</div>
</div>
