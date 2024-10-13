<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php
		esc_html_e( 'HTML Sitemap', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
	?></h3>

	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p><?php esc_html_e( 'Here you can enable HTML Sitemap, customize Output URL and preview.', 'xml-sitemap-generator-for-google' ); ?></p>
		<p>
			<strong>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'enable_html_sitemap',
						'value' => $settings->enable_html_sitemap ?? false,
						'label' => esc_html__( 'Enable HTML Sitemap', 'xml-sitemap-generator-for-google' ),
						'class' => 'has-dependency',
						'data'  => array( 'target' => 'html-sitemap-depended' ),
					)
				);
				?>
			</strong>
		</p>
		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'  => 'html_sitemap_url',
					'value' => $settings->html_sitemap_url,
					'label' => esc_html__( 'HTML Sitemap URL:', 'xml-sitemap-generator-for-google' ),
					'class' => 'html-sitemap-depended',
				)
			);
			?>
		</p>

		<?php
		Dashboard::render(
			'partials/preview-urls.php',
			array(
				'label'           => esc_html__( 'Preview your HTML Sitemap:', 'xml-sitemap-generator-for-google' ),
				'languages_label' => esc_html__( 'HTML Sitemaps for other languages:', 'xml-sitemap-generator-for-google' ),
				'sitemap_url'     => $settings->html_sitemap_url,
				'sitemap_type'    => 'sitemap_html',
				'class'           => 'html-sitemap-depended',
			)
		);
		?>

		<?php sgg_show_pro_overlay(); ?>
	</div>
</div>
