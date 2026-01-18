<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings     = $args['settings'] ?? new stdClass();
$color_fields = array(
	'header_background_color' => array(
		'label'   => __( 'Header Background Color:', 'xml-sitemap-generator-for-google' ),
		'default' => '#82a745',
	),
	'header_text_color' => array(
		'label'   => __( 'Header Text Color:', 'xml-sitemap-generator-for-google' ),
		'default' => '#ffffff',
	),
	'sitemap_background_color' => array(
		'label'   => __( 'Sitemap Background Color:', 'xml-sitemap-generator-for-google' ),
		'default' => '#ecf4db',
	),
	'sitemap_text_color' => array(
		'label'   => __( 'Sitemap Text Color:', 'xml-sitemap-generator-for-google' ),
		'default' => '#444444',
	),
	'sitemap_link_color' => array(
		'label'   => __( 'Sitemap Link Color:', 'xml-sitemap-generator-for-google' ),
		'default' => '#0073aa',
	),
	'footer_text_color' => array(
		'label'   => __( 'Footer Text Color:', 'xml-sitemap-generator-for-google' ),
		'default' => '#666666',
	),
);

?>
<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title" data-search-id="advanced_sitemap_styles"><?php esc_html_e( 'Sitemap Styles', 'xml-sitemap-generator-for-google' ); ?></h3>
	</div>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?> colors-section">
			<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'Customize colors of your Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>
			<table class="grim-table grim-mb-20 wp-list-table widefat fixed striped">
				<tbody>
				<?php foreach ( $color_fields as $name => $field ) : ?>
					<tr>
						<td><?php echo esc_html( $field['label'] ); ?></td>
						<td>
							<?php
							Dashboard::render(
								'fields/color.php',
								array(
									'name'  => "colors[$name]",
									'value' => $settings->colors[$name] ?? $field['default'],
								)
							);
							?>
							<span><?php echo esc_html( $settings->colors[$name] ) ?? esc_html( $field['default'] ); ?></span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<br>
		<div class="grim-toggle-section">
			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'hide_branding',
						'label' => __( 'Hide Branding Marks', 'xml-sitemap-generator-for-google' ),
						'value' => $settings->hide_branding ?? true,
					)
				);
				?>
			</p>
			<p class="grim-section-desc grim-mt-10">
				<?php esc_html_e( 'Hides all plugin author and name references from generated XML Sitemaps.', 'xml-sitemap-generator-for-google' ); ?>
			</p>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>
