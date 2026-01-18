<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-preview-section">
	<p class="grim-toggle-section">
		<strong>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'enable_sitemap',
					'value' => $settings->enable_sitemap ?? true,
					'label' => esc_html__( 'XML Sitemap', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'xml-sitemap-depended' ),
				)
			);
			?>
		</strong>
	</p>
	<div class="inside">
		<p class="grim-section-desc"><?php esc_html_e( 'Here you can enable XML Sitemap and customize Output URL.', 'xml-sitemap-generator-for-google' ); ?></p>

		<div class="grim-mt-20">
			<?php
			Dashboard::render(
				'partials/preview-urls.php',
				array(
					'languages_label' => esc_html__( 'Sitemaps for other languages:', 'xml-sitemap-generator-for-google' ),
					'sitemap_url'     => $settings->sitemap_url,
					'sitemap_type'    => 'sitemap_xml',
					'class'           => 'xml-sitemap-depended',
					'input_name'      => 'sitemap_url',
					'input_value'     => $settings->sitemap_url,
					'input_label'     => esc_html__( 'XML Sitemap URL:', 'xml-sitemap-generator-for-google' ),
					'input_class'     => 'xml-sitemap-depended',
					'notice_show'     => true,
				)
			);
			?>
		</div>
	</div>
</div>
