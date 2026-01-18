<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-preview-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<p class="grim-toggle-section">
		<strong>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'enable_html_sitemap',
					'value' => $settings->enable_html_sitemap ?? false,
					'label' => esc_html__( 'HTML Sitemap', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'html-sitemap-depended' ),
				)
			);
			?>
		</strong>
	</p>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="grim-section-desc"><?php esc_html_e( 'Here you can enable HTML Sitemap, customize Output URL and preview.', 'xml-sitemap-generator-for-google' ); ?></p>
		<div class="grim-mt-20">
			<?php
			Dashboard::render(
				'partials/preview-urls.php',
				array(
					'languages_label' => esc_html__( 'HTML Sitemaps for other languages:', 'xml-sitemap-generator-for-google' ),
					'sitemap_url'     => $settings->html_sitemap_url,
					'sitemap_type'    => 'sitemap_html',
					'class'           => 'html-sitemap-depended',
					'input_name'      => 'html_sitemap_url',
					'input_value'     => $settings->html_sitemap_url,
					'input_label'     => esc_html__( 'HTML Sitemap URL:', 'xml-sitemap-generator-for-google' ),
					'input_class'     => 'html-sitemap-depended',
					'notice_show'     => true,
				)
			);
			?>
		</div>
		<br>
		<p class="grim-section-desc line-height-2">
			<?php esc_html_e( 'HTML Sitemap can be displayed using Page Builder Widget for Elementor, Gutenberg, WPBakery or Shortcode:', 'xml-sitemap-generator-for-google' ); ?>
			<br>
			<strong id="grim-shortcode">[html-sitemap post-types="page,post,.." show-featured-image="true" show-date="true" date-format="F j, Y"]</strong>
			<span class="grim-btn-copied" data-target="grim-shortcode">
				<i class="grim-icon-copy"></i>
				<span class="grim-tooltip"><?php esc_html_e( 'Copied!', 'xml-sitemap-generator-for-google' ); ?></span>
			</span>
		</p>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>
