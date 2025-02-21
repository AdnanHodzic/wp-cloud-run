<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle">
		<?php
		esc_html_e( 'Sitemap Styles', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
		?>
	</h3>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?> colors-section">
			<p><?php esc_html_e( 'Customize colors of your Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>

			<p>
				<?php
				Dashboard::render(
					'fields/color.php',
					array(
						'name'  => 'colors[header_background_color]',
						'label' => __( 'Header Background Color:', 'xml-sitemap-generator-for-google' ),
						'value' => $settings->colors['header_background_color'] ?? '#82a745',
					)
				);
				?>
			</p>

			<p>
				<?php
				Dashboard::render(
					'fields/color.php',
					array(
						'name'  => 'colors[header_text_color]',
						'label' => __( 'Header Text Color:', 'xml-sitemap-generator-for-google' ),
						'value' => $settings->colors['header_text_color'] ?? '#ffffff',
					)
				);
				?>
			</p>

			<p>
				<?php
				Dashboard::render(
					'fields/color.php',
					array(
						'name'  => 'colors[sitemap_background_color]',
						'label' => __( 'Sitemap Background Color:', 'xml-sitemap-generator-for-google' ),
						'value' => $settings->colors['sitemap_background_color'] ?? '#ecf4db',
					)
				);
				?>
			</p>

			<p>
				<?php
				Dashboard::render(
					'fields/color.php',
					array(
						'name'  => 'colors[sitemap_text_color]',
						'label' => __( 'Sitemap Text Color:', 'xml-sitemap-generator-for-google' ),
						'value' => $settings->colors['sitemap_text_color'] ?? '#444444',
					)
				);
				?>
			</p>

			<p>
				<?php
				Dashboard::render(
					'fields/color.php',
					array(
						'name'  => 'colors[sitemap_link_color]',
						'label' => __( 'Sitemap Link Color:', 'xml-sitemap-generator-for-google' ),
						'value' => $settings->colors['sitemap_link_color'] ?? '#0073aa',
					)
				);
				?>
			</p>

			<p>
				<?php
				Dashboard::render(
					'fields/color.php',
					array(
						'name'  => 'colors[footer_text_color]',
						'label' => __( 'Footer Text Color:', 'xml-sitemap-generator-for-google' ),
						'value' => $settings->colors['footer_text_color'] ?? '#666666',
					)
				);
				?>
			</p>

			<p><?php esc_html_e( 'Customize appearing of Branding Marks.', 'xml-sitemap-generator-for-google' ); ?></p>

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

			<?php sgg_show_pro_overlay(); ?>
		</div>
	</div>

</div>
