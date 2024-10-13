<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php
		esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
	?></h3>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="google-news-depended"><?php esc_html_e( 'Please search and choose here Posts that should be excluded from Google News:', 'xml-sitemap-generator-for-google' ); ?></p>

		<?php
		Dashboard::render(
			'fields/autocomplete.php',
			array(
				'label' => esc_html__( 'Exclude Posts:', 'xml-sitemap-generator-for-google' ),
				'name'  => 'google_news_exclude',
				'value' => $settings->google_news_exclude ?? '',
				'class' => 'google-news-depended',
			)
		);
		?>
		<br>
		<?php
		Dashboard::render(
			'fields/autocomplete.php',
			array(
				'type'  => 'taxonomy',
				'label' => esc_html__( 'Exclude Categories and Tags:', 'xml-sitemap-generator-for-google' ),
				'name'  => 'google_news_exclude_terms',
				'value' => $settings->google_news_exclude_terms ?? '',
				'class' => 'google-news-depended',
			)
		);

		sgg_show_pro_overlay();
		?>
	</div>
</div>
