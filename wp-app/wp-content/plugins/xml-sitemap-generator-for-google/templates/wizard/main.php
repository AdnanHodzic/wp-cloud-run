<?php
/**
 * @var array $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'];

Dashboard::render( 'wizard/header.php' );
?>

<div class="wizard-main-wrapper">
	<div class="wizard-form-wrapper">
		<form id="wizard-form" action="" method="POST">
			<?php wp_nonce_field( 'sgg_wizard_nonce', 'sgg_wizard_nonce' ); ?>

			<div class="wizard-steps">
				<ul>
					<li class="wizard-step-menu-1 active">
						<span>1</span>
						<?php echo esc_html__( 'General', 'xml-sitemap-generator-for-google' ) ?>
					</li>
					<li class="wizard-step-menu-2">
						<span>2</span>
						<?php echo esc_html__( 'Sitemap Structure', 'xml-sitemap-generator-for-google' ) ?>
					</li>
					<li class="wizard-step-menu-3">
						<span>3</span>
						<?php echo esc_html__( 'Advanced', 'xml-sitemap-generator-for-google' ) ?>
					</li>
				</ul>
			</div>

			<div class="wizard-form-step-1 active">
				<?php
				Dashboard::render(
					'wizard/sitemap-toggle.php',
					array(
						'title'       => esc_html__( 'XML Sitemap', 'xml-sitemap-generator-for-google' ),
						'description' => esc_html__( "XML Sitemap is a structured list of your website's URLs designed to help Search Engines efficiently index your site's content.", 'xml-sitemap-generator-for-google' ),
						'name'        => 'enable_sitemap',
						'checked'     => $settings->enable_sitemap ?? true,
					)
				);

				Dashboard::render(
					'wizard/sitemap-toggle.php',
					array(
						'title'       => esc_html__( 'HTML Sitemap', 'xml-sitemap-generator-for-google' ),
						'description' => esc_html__( "HTML Sitemap is a collection of your website’s URLs created to assist Users in navigating through your site’s content.", 'xml-sitemap-generator-for-google' ),
						'name'        => 'enable_html_sitemap',
						'checked'     => $settings->enable_html_sitemap ?? true,
						'is_pro'      => true,
					)
				);

				Dashboard::render(
					'wizard/sitemap-toggle.php',
					array(
						'title'       => esc_html__( 'Google News', 'xml-sitemap-generator-for-google' ),
						'description' => esc_html__( "Google News Sitemap is a structured list of your Post's URLs designed to help Google News efficiently index your site's news.", 'xml-sitemap-generator-for-google' ),
						'name'        => 'enable_google_news',
						'checked'     => $settings->enable_google_news ?? false,
					)
				);

				Dashboard::render(
					'wizard/sitemap-toggle.php',
					array(
						'title'       => esc_html__( 'Image Sitemap', 'xml-sitemap-generator-for-google' ),
						'description' => esc_html__( "Image Sitemap is a detailed list of image URLs from your website's content, specifically designed to help Search Engines effectively index your site's images, enhancing their visibility in search results.", 'xml-sitemap-generator-for-google' ),
						'name'        => 'enable_image_sitemap',
						'checked'     => $settings->enable_image_sitemap ?? false,
					)
				);

				Dashboard::render(
					'wizard/sitemap-toggle.php',
					array(
						'title'       => esc_html__( 'Video Sitemap', 'xml-sitemap-generator-for-google' ),
						'description' => esc_html__( "Video Sitemap is a structured list of video URLs from your website's content, specifically designed to help Search Engines effectively index your site's videos, enhancing their visibility in search results.", 'xml-sitemap-generator-for-google' ),
						'name'        => 'enable_video_sitemap',
						'checked'     => $settings->enable_video_sitemap ?? false,
					)
				);
				?>
			</div>

			<div class="wizard-form-step-2">
				<?php
				Dashboard::render(
					'wizard/sitemap-structure.php',
					array(
						'settings' => $settings,
					)
				);
				?>
			</div>

			<div class="wizard-form-step-3">
				<div class="sitemap-cache-toggle">
					<?php
					Dashboard::render(
						'wizard/sitemap-toggle.php',
						array(
							'title'       => esc_html__( 'Enable Sitemap Cache', 'xml-sitemap-generator-for-google' ),
							'description' => esc_html__( 'Sitemap Cache improves the loading performance of your Sitemaps by storing links in the cache.', 'xml-sitemap-generator-for-google' ),
							'name'        => 'enable_cache',
							'checked'     => $settings->enable_cache ?? false,
						)
					);
					?>
				</div>

				<div class="cache-timeout">
					<label for="cache_timeout" class="form-label"><?php esc_html_e( 'Cache Expiration Time:', 'xml-sitemap-generator-for-google' ); ?></label>
					<input type="number" id="cache_timeout" name="cache_timeout" class="sitemap-cache form-input" value="<?php echo esc_attr( $settings->cache_timeout ?? 24 ); ?>" <?php disabled( ! $settings->enable_cache ); ?>/>
					<select name="cache_timeout_period" class="sitemap-cache form-select" <?php disabled( ! $settings->enable_cache ); ?>>
						<option value="60" <?php selected( esc_attr( $settings->cache_timeout_period ?? 3600 ), 60 ); ?>><?php esc_html_e( 'minute(s)', 'xml-sitemap-generator-for-google' ); ?></option>
						<option value="3600" <?php selected( esc_attr( $settings->cache_timeout_period ?? 3600 ), 3600 ); ?>><?php esc_html_e( 'hour(s)', 'xml-sitemap-generator-for-google' ); ?></option>
						<option value="86400" <?php selected( esc_attr( $settings->cache_timeout_period ?? 3600 ), 86400 ); ?>><?php esc_html_e( 'day(s)', 'xml-sitemap-generator-for-google' ); ?></option>
					</select>
				</div>

				<div class="pro-version-banner">
					<img src="<?php echo esc_url( plugins_url( 'assets/images/pro-banner.png', GRIM_SG_FILE ) ); ?>" alt="<?php esc_attr_e( 'Pro Version', 'xml-sitemap-generator-for-google' ); ?>" class="pro-version-image">
					<div class="pro-version-content">
						<h3><?php esc_html_e( 'Upgrade to Pro', 'xml-sitemap-generator-for-google' ); ?></h3>
						<p><?php esc_html_e( 'Unlock advanced features and enhance your sitemap with the Pro version.', 'xml-sitemap-generator-for-google' ); ?></p>
						<a href="https://wpgrim.com/docs/google-xml-sitemaps-generator/general/settings/?utm_source=sgg-plugin&utm_medium=documentation&utm_campaign=wizard" class="pro-version-link" target="_blank"><?php esc_html_e( 'Documentation', 'xml-sitemap-generator-for-google' ); ?></a>
						<a href="https://wpgrim.com/google-xml-sitemaps-generator-pro/?utm_source=sgg-plugin&utm_medium=get-now&utm_campaign=wizard" class="pro-version-btn" target="_blank"><?php esc_html_e( 'Get Pro Now', 'xml-sitemap-generator-for-google' ); ?></a>
					</div>
				</div>
			</div>

			<div class="wizard-form-btn-wrapper">
				<button class="wizard-back-btn">
					<?php echo esc_html__( 'Back', 'xml-sitemap-generator-for-google' ) ?>
				</button>

				<button class="wizard-btn">
					<?php echo esc_html__( 'Continue', 'xml-sitemap-generator-for-google' ) ?>
				</button>
			</div>
		</form>
	</div>
</div>
