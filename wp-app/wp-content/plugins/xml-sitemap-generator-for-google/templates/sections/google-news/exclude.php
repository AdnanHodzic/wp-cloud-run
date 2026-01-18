<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-google-news <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title google-news-depended" data-search-id="google_exclude"><?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?></h3>
	</div>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="grim-section-desc grim-mb-20 google-news-depended"><?php esc_html_e( 'Please search and choose here Posts that should be excluded from Google News:', 'xml-sitemap-generator-for-google' ); ?></p>

		<div class="grim-exclude-tab-nav">
			<ul class="grim-exclude-tabs">
				<li class="grim-exclude-tab grim-exclude-tab--active google-news-depended" data-tab="google-pages">
					<?php esc_html_e( 'Posts', 'xml-sitemap-generator-for-google' ); ?>
					<span class="grim-term-count">0</span>
				</li>
				<li class="grim-exclude-tab google-news-depended" data-tab="google-categories">
					<?php esc_html_e( 'Categories and Tags', 'xml-sitemap-generator-for-google' ); ?>
					<span class="grim-term-count">0</span>
				</li>
			</ul>

			<div class="grim-exclude-tab-content">
				<div id="google-pages" class="grim-exclude-tab-panel grim-exclude-tab-panel--active">
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
				</div>
				<div id="google-categories" class="grim-exclude-tab-panel">
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
					?>
				</div>
			</div>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>

<div class="grim-section grim-include <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title google-news-depended" data-search-id="google_include"><?php esc_html_e( 'Include only selected Terms', 'xml-sitemap-generator-for-google' ); ?></h3>
		<span class="grim-term-count">0</span>
	</div>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="grim-section-desc grim-mb-20 google-news-depended"><?php esc_html_e( 'Include only selected Categories, Tags and exclude all others:', 'xml-sitemap-generator-for-google' ); ?></p>

		<div class="grim-exclude-tab-nav">
			<div class="grim-exclude-tab-content">
				<div id="google-others" class="grim-exclude-tab-panel grim-exclude-tab-panel--active">
					<?php
					Dashboard::render(
						'fields/autocomplete.php',
						array(
							'type'  => 'taxonomy',
							'label' => esc_html__( 'Include only selected Categories, Tags and exclude all others:', 'xml-sitemap-generator-for-google' ),
							'name'  => 'google_news_include_only_terms',
							'value' => $settings->google_news_include_only_terms ?? '',
							'class' => 'google-news-depended',
						)
					);
					?>
				</div>
			</div>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>