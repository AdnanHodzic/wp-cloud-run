<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<p class="grim-toggle-section">
		<?php
		Dashboard::render(
			'fields/checkbox.php',
			array(
				'name'  => 'minimize_sitemap',
				'value' => $settings->minimize_sitemap ?? false,
				'label' => esc_html__( 'Minimize Sitemap source code', 'xml-sitemap-generator-for-google' ),
			)
		);
		?>
	</p>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="grim-section-desc"><?php esc_html_e( 'Sitemap source code will be compressed into a single line.', 'xml-sitemap-generator-for-google' ); ?></p>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>
