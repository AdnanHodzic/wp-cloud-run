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
		<h3 class="grim-section-title" data-search-id="general_exclude_posts"><?php esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' ); ?></h3>
	</div>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'Please search and choose here Pages, Posts, Custom Posts, Categories and Tags that should be excluded from Sitemap:', 'xml-sitemap-generator-for-google' ); ?></p>

		<div class="grim-exclude-tab-nav">
			<ul class="grim-exclude-tabs">
				<li class="grim-exclude-tab grim-exclude-tab--active" data-tab="pages">
					<?php esc_html_e( 'Pages/Posts', 'xml-sitemap-generator-for-google' ); ?>
					<span class="grim-term-count">0</span>
				</li>
				<li class="grim-exclude-tab" data-tab="categories">
					<?php esc_html_e( 'Categories and Tags', 'xml-sitemap-generator-for-google' ); ?>
					<span class="grim-term-count">0</span>
				</li>
			</ul>

			<div class="grim-exclude-tab-content">
				<div id="pages" class="grim-exclude-tab-panel grim-exclude-tab-panel--active">
					<?php
					Dashboard::render(
						'fields/autocomplete.php',
						array(
							'label' => esc_html__( 'Exclude Pages/Posts:', 'xml-sitemap-generator-for-google' ),
							'name'  => 'exclude_posts',
							'value' => $settings->exclude_posts ?? '',
						)
					);
					?>
				</div>
				<div id="categories" class="grim-exclude-tab-panel">
					<?php
					Dashboard::render(
						'fields/autocomplete.php',
						array(
							'type'  => 'taxonomy',
							'label' => esc_html__( 'Exclude Categories and Tags:', 'xml-sitemap-generator-for-google' ),
							'name'  => 'exclude_terms',
							'value' => $settings->exclude_terms ?? '',
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php
		sgg_show_pro_overlay();
		?>
	</div>
</div>

<div class="grim-section grim-include <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title" data-search-id="general_include_only_terms"><?php esc_html_e( 'Include only selected Terms', 'xml-sitemap-generator-for-google' ); ?></h3>
		<span class="grim-term-count">0</span>
	</div>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'Include only selected Categories, Tags and exclude all others:', 'xml-sitemap-generator-for-google' ); ?></p>

		<div class="grim-exclude-tab-nav">
			<div class="grim-exclude-tab-content">
				<div id="others" class="grim-exclude-tab-panel grim-exclude-tab-panel--active">
					<?php
					Dashboard::render(
						'fields/autocomplete.php',
						array(
							'type'  => 'taxonomy',
							'label' => esc_html__( 'Include only selected Categories, Tags and exclude all others:', 'xml-sitemap-generator-for-google' ),
							'name'  => 'include_only_terms',
							'value' => $settings->include_only_terms ?? '',
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php
		sgg_show_pro_overlay();
		?>
	</div>
</div>
