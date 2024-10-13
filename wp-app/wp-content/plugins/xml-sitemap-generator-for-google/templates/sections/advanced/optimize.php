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
		esc_html_e( 'Optimize', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
		?>
	</h3>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p><?php esc_html_e( 'Sitemap source code will be compressed into a single line.', 'xml-sitemap-generator-for-google' ); ?></p>

			<p>
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

			<?php sgg_show_pro_overlay(); ?>
		</div>
	</div>

</div>
