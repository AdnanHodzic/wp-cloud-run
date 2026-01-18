<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-preview-section">
	<h3 class="grim-section-title google-news-depended" data-search-id="google__url"><?php esc_html_e( 'Google News URL', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20 google-news-depended"><?php esc_html_e( 'Here you can preview your Google News and customize Output URL.', 'xml-sitemap-generator-for-google' ); ?></p>
		<?php
		Dashboard::render(
			'partials/preview-urls.php',
			array(
				'languages_label' => esc_html__( 'Google News for other languages:', 'xml-sitemap-generator-for-google' ),
				'sitemap_url'     => $settings->google_news_url,
				'sitemap_type'    => 'google_news',
				'class'           => 'google-news-depended',
				'input_name'      => 'google_news_url',
				'input_value'     => $settings->google_news_url,
				'input_label'     => esc_html__( 'Google News URL:', 'xml-sitemap-generator-for-google' ),
				'input_class'     => 'google-news-depended',
				'notice_show'     => false,
			)
		);
		?>
	</div>
</div>
