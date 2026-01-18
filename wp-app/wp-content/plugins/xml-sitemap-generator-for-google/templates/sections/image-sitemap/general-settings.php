<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-preview-section">
	<div class="grim-toggle-section">
		<strong>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'enable_image_sitemap',
					'value' => $settings->enable_image_sitemap ?? false,
					'label' => esc_html__( 'Image Sitemap', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'image-sitemap-depended' ),
				)
			);
			?>
		</strong>
		<div class="inside">
			<p class="grim-section-desc grim-mb-20">
				<?php esc_html_e( 'All below options will be available after enabling Image Sitemap. Default Sitemap will only include Images that are used in Content.', 'xml-sitemap-generator-for-google' ); ?>
			</p>
		</div>
	</div>

	<div class="inside">
		<div class="grim-mt-20">
			<?php
			Dashboard::render(
				'partials/preview-urls.php',
				array(
					'languages_label' => esc_html__( 'Image Sitemap for other languages:', 'xml-sitemap-generator-for-google' ),
					'sitemap_url'  => $settings->image_sitemap_url,
					'sitemap_type' => 'image_sitemap',
					'class'        => 'image-sitemap-depended',
					'input_name'   => 'image_sitemap_url',
					'input_value'  => $settings->image_sitemap_url,
					'input_label'  => esc_html__( 'Image Sitemap URL:', 'xml-sitemap-generator-for-google' ),
					'input_class'  => 'image-sitemap-depended',
					'notice_show'  => false,
				)
			);
			?>
		</div>
	</div>
	<div class="inside">
		<div class="grim-mt-20">
			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'hide_image_previews',
						'class' => 'image-sitemap-depended',
						'value' => $settings->hide_image_previews ?? false,
						'label' => esc_html__( 'Hide Image Previews', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</p>
			<p class="grim-section-desc grim-ml-45 image-sitemap-depended"><?php esc_html_e( 'If you are experiencing long loading times, hide image previews in your Sitemap. This will not affect SEO results.', 'xml-sitemap-generator-for-google' ); ?></p>
		</div>
	</div>
	<div class="inside">
		<div class="grim-mt-20">
			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'hide_image_sitemap_xsl',
						'class' => 'image-sitemap-depended',
						'value' => $settings->hide_image_sitemap_xsl ?? false,
						'label' => esc_html__( 'Disable XSL Stylesheet', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</p>
			<p class="grim-section-desc grim-ml-45 image-sitemap-depended">
				<?php
				printf(
					/* translators: %s: Link to Chrome XSLT deprecation documentation */
					esc_html__( 'Remove the XSL stylesheet reference to avoid browser deprecation warnings: %s', 'xml-sitemap-generator-for-google' ),
					'<a href="' . esc_url( 'https://developer.chrome.com/docs/web-platform/deprecating-xslt' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Chrome XSLT Deprecation', 'xml-sitemap-generator-for-google' ) . '</a>'
				);
				?>
			</p>
		</div>
	</div>
</div>

<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title image-sitemap-depended" data-search-id="image_mime_types"><?php esc_html_e( 'Image MIME Types', 'xml-sitemap-generator-for-google' ); ?></h3>
	</div>

	<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<div class="inside">
			<p class="grim-section-desc grim-mb-20 image-sitemap-depended"><?php esc_html_e( 'Allow Image Types in your Image Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>
		</div>
		<ul class="grim-image-types">
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'       => 'image_mime_types[image/jpeg]',
						'class'      => 'image-sitemap-depended',
						'value'      => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/jpeg'] ?? false : true,
						'label'      => esc_html__( 'JPEG', 'xml-sitemap-generator-for-google' ),
						'is_default' => true,				'is_default'
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'       => 'image_mime_types[image/png]',
						'class'      => 'image-sitemap-depended',
						'value'      => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/png'] ?? false : true,
						'label'      => esc_html__( 'PNG', 'xml-sitemap-generator-for-google' ),
						'is_default' => true,
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'       => 'image_mime_types[image/bmp]',
						'class'      => 'image-sitemap-depended',
						'value'      => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/bmp'] ?? false : true,
						'label'      => esc_html__( 'BMP', 'xml-sitemap-generator-for-google' ),
						'is_default' => true,
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'       => 'image_mime_types[image/gif]',
						'class'      => 'image-sitemap-depended',
						'value'      => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/gif'] ?? false : true,
						'label'      => esc_html__( 'GIF', 'xml-sitemap-generator-for-google' ),
						'is_default' => true,

					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'       => 'image_mime_types[image/webp]',
						'class'      => 'image-sitemap-depended',
						'value'      => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/webp'] ?? false : true,
						'label'      => esc_html__( 'WEBP', 'xml-sitemap-generator-for-google' ),
						'is_default' => true,
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'       => 'image_mime_types[image/avif]',
						'class'      => 'image-sitemap-depended',
						'value'      => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/avif'] ?? false : true,
						'label'      => esc_html__( 'AVIF', 'xml-sitemap-generator-for-google' ),
						'is_default' => true,
					)
				);
				?>
			</li>
		</ul>

		<?php if ( sgg_pro_enabled() ) { ?>
			<input type="hidden" class="image-sitemap-depended" name="image_mime_types[not-image]" value="1">
		<?php } ?>

	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>

<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-toggle-section pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'exclude_broken_images',
					'class' => 'image-sitemap-depended',
					'value' => $settings->exclude_broken_images ?? false,
					'label' => esc_html__( 'Exclude Broken Images', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>
		<div class="inside pro-wrapper">
			<p class="grim-section-desc image-sitemap-depended"><?php esc_html_e( 'This option allows to exclude Broken or Not Existing 404 images. Recommended to use this option with Sitemap Cache, as it affects to Sitemap Generating Speed.', 'xml-sitemap-generator-for-google' ); ?></p>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>

<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-toggle-section pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'include_featured_images',
					'class' => 'image-sitemap-depended',
					'value' => $settings->include_featured_images ?? false,
					'label' => esc_html__( 'Include Featured Images', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>
		<div class="inside pro-wrapper">
			<p class="grim-section-desc image-sitemap-depended"><?php esc_html_e( 'Enabling this option includes Featured Images from Pages, Posts, and Custom Posts to your Image Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>
		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>

<?php if ( class_exists( 'WooCommerce' ) ) { ?>
	<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
		<?php sgg_show_pro_badge(); ?>
		<div class="grim-toggle-section pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'include_woo_gallery',
						'class' => 'image-sitemap-depended',
						'value' => $settings->include_woo_gallery ?? false,
						'label' => esc_html__( 'Include WooCommerce Gallery Images', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</p>

			<div class="inside">
				<p class="grim-section-desc image-sitemap-depended"><?php esc_html_e( 'Enabling this option includes WooCommerce Gallery Images from Products to your Image Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>
			</div>
		</div>
		<?php sgg_show_pro_overlay(); ?>
	</div>
<?php } ?>
