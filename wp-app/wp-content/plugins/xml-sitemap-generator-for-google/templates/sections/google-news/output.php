<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Google News URL', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="google-news-depended"><?php esc_html_e( 'Here you can preview your Google News and customize Output URL.', 'xml-sitemap-generator-for-google' ); ?></p>
		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'  => 'google_news_url',
					'value' => $settings->google_news_url,
					'label' => esc_html__( 'Google News URL:', 'xml-sitemap-generator-for-google' ),
					'class' => 'google-news-depended',
				)
			);
			?>
		</p>

		<?php
		Dashboard::render(
			'partials/preview-urls.php',
			array(
				'label'           => esc_html__( 'Preview your Google News:', 'xml-sitemap-generator-for-google' ),
				'languages_label' => esc_html__( 'Google News for other languages:', 'xml-sitemap-generator-for-google' ),
				'sitemap_url'     => $settings->google_news_url,
				'sitemap_type'    => 'google_news',
				'class'           => 'google-news-depended',
			)
		);
		?>
	</div>
</div>
