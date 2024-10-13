<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Image Sitemap', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p>
			<?php esc_html_e( 'All below options will be available after enabling Image Sitemap. Default Sitemap will only include Images that are used in Content.', 'xml-sitemap-generator-for-google' ); ?>
		</p>
		<div>
			<strong>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'enable_image_sitemap',
						'value' => $settings->enable_image_sitemap ?? false,
						'label' => esc_html__( 'Enable Image Sitemap', 'xml-sitemap-generator-for-google' ),
						'class' => 'has-dependency',
						'data'  => array( 'target' => 'image-sitemap-depended' ),
					)
				);
				?>
			</strong>
		</div>
		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'  => 'image_sitemap_url',
					'value' => $settings->image_sitemap_url,
					'label' => esc_html__( 'Image Sitemap URL:', 'xml-sitemap-generator-for-google' ),
					'class' => 'image-sitemap-depended',
				)
			);
			?>
		</p>
		<p class="image-sitemap-depended">
			<?php esc_html_e( 'Preview your Image Sitemap:', 'xml-sitemap-generator-for-google' ); ?>
			<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ) ); ?>" target="_blank">
				<?php echo esc_url( sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ) ); ?>
			</a>
		</p>

		<h3 class="hndle"><?php
			esc_html_e( 'Image MIME Types', 'xml-sitemap-generator-for-google' );

			sgg_show_pro_badge();
			?></h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="image-sitemap-depended"><?php esc_html_e( 'Allow Image Types in your Image Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>

			<ul>
				<li>
					<?php
					Dashboard::render(
						'fields/checkbox.php',
						array(
							'name'  => 'image_mime_types[image/jpeg]',
							'class' => 'image-sitemap-depended',
							'value' => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/jpeg'] ?? false : true,
							'label' => esc_html__( 'JPEG', 'xml-sitemap-generator-for-google' ),
						)
					);
					?>
				</li>
				<li>
					<?php
					Dashboard::render(
						'fields/checkbox.php',
						array(
							'name'  => 'image_mime_types[image/png]',
							'class' => 'image-sitemap-depended',
							'value' => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/png'] ?? false : true,
							'label' => esc_html__( 'PNG', 'xml-sitemap-generator-for-google' ),
						)
					);
					?>
				</li>
				<li>
					<?php
					Dashboard::render(
						'fields/checkbox.php',
						array(
							'name'  => 'image_mime_types[image/bmp]',
							'class' => 'image-sitemap-depended',
							'value' => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/bmp'] ?? false : true,
							'label' => esc_html__( 'BMP', 'xml-sitemap-generator-for-google' ),
						)
					);
					?>
				</li>
				<li>
					<?php
					Dashboard::render(
						'fields/checkbox.php',
						array(
							'name'  => 'image_mime_types[image/gif]',
							'class' => 'image-sitemap-depended',
							'value' => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/gif'] ?? false : true,
							'label' => esc_html__( 'GIF', 'xml-sitemap-generator-for-google' ),
						)
					);
					?>
				</li>
				<li>
					<?php
					Dashboard::render(
						'fields/checkbox.php',
						array(
							'name'  => 'image_mime_types[image/webp]',
							'class' => 'image-sitemap-depended',
							'value' => isset( $settings->image_mime_types ) ? $settings->image_mime_types['image/webp'] ?? false : true,
							'label' => esc_html__( 'WEBP', 'xml-sitemap-generator-for-google' ),
						)
					);
					?>
				</li>
			</ul>

			<?php if ( sgg_pro_enabled() ) { ?>
				<input type="hidden" class="image-sitemap-depended" name="image_mime_types[not-image]" value="1">
			<?php } ?>

			<?php sgg_show_pro_overlay(); ?>
		</div>

		<h3 class="hndle"><?php
			esc_html_e( 'Broken Images', 'xml-sitemap-generator-for-google' );

			sgg_show_pro_badge();
			?></h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="image-sitemap-depended"><?php esc_html_e( 'This option allows to exclude Broken or Not Existing 404 images. Recommended to use this option with Sitemap Cache, as it affects to Sitemap Generating Speed.', 'xml-sitemap-generator-for-google' ); ?></p>

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

			<?php sgg_show_pro_overlay(); ?>
		</div>

		<h3 class="hndle"><?php
			esc_html_e( 'Featured Images', 'xml-sitemap-generator-for-google' );

			sgg_show_pro_badge();
			?></h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="image-sitemap-depended"><?php esc_html_e( 'Enabling this option includes Featured Images from Pages, Posts, and Custom Posts to your Image Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>

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

			<?php sgg_show_pro_overlay(); ?>
		</div>

		<?php if ( class_exists( 'WooCommerce' ) ) { ?>
			<h3 class="hndle"><?php
				esc_html_e( 'WooCommerce Gallery', 'xml-sitemap-generator-for-google' );

				sgg_show_pro_badge();
				?></h3>
			<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
				<p class="image-sitemap-depended"><?php esc_html_e( 'Enabling this option includes WooCommerce Gallery Images from Products to your Image Sitemap.', 'xml-sitemap-generator-for-google' ); ?></p>

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

				<?php sgg_show_pro_overlay(); ?>
			</div>
		<?php } ?>

	</div>
</div>
