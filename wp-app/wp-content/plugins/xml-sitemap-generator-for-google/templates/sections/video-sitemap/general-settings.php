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
					'name'  => 'enable_video_sitemap',
					'value' => $settings->enable_video_sitemap ?? false,
					'label' => esc_html__( 'Enable Video Sitemap', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'video-sitemap-depended' ),
				)
			);
			?>
		</strong>
	</div>
	<div class="inside">
		<p class="grim-mb-20"><?php esc_html_e( 'All below options will be available after enabling Video Sitemap. Sitemap will only include Videos that are used in Content.', 'xml-sitemap-generator-for-google' ); ?></p>
		<p class="video-sitemap-depended">
			<?php
			Dashboard::render(
				'partials/preview-urls.php',
				array(
					'languages_label' => esc_html__( 'Video Sitemap for other languages:', 'xml-sitemap-generator-for-google' ),
					'sitemap_url'  => $settings->video_sitemap_url,
					'sitemap_type' => 'video_sitemap',
					'input_name'   => 'video_sitemap_url',
					'input_value'  => $settings->video_sitemap_url,
					'input_label'  => esc_html__( 'Video Sitemap URL:', 'xml-sitemap-generator-for-google' ),
					'input_class'  => 'video-sitemap-depended',
					'class'        => 'video-sitemap-depended',
					'notice_show'  => false,
				)
			);
			?>
		</p>
	</div>
	<div class="inside">
		<div class="grim-mt-20">
			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'hide_video_sitemap_xsl',
						'class' => 'video-sitemap-depended',
						'value' => $settings->hide_video_sitemap_xsl ?? false,
						'label' => esc_html__( 'Disable XSL Stylesheet', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</p>
			<p class="grim-section-desc grim-ml-45 video-sitemap-depended">
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

<div class="grim-section grim-preview-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title video-sitemap-depended" data-search-id="sgg_youtube"><?php esc_html_e( 'YouTube', 'xml-sitemap-generator-for-google' ); ?></h3>
	</div>

	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="grim-section-desc grim-mb-20 video-sitemap-depended"><?php esc_html_e( 'This is required field for retrieving the data from Youtube embeds if you are using them in Contents.', 'xml-sitemap-generator-for-google' ); ?></p>
		<p class="video-sitemap-depended">
			<?php
			Dashboard::render(
				'partials/check-button.php',
				array(
					'input_name'  => 'youtube_api_key',
					'input_value' => $settings->youtube_api_key,
					'input_label' => esc_html__( 'YouTube Data API v3 Key:', 'xml-sitemap-generator-for-google' ),
					'input_class' => 'video-sitemap-depended',
					'placeholder' => 'Enter API key',
					'description' => 'Get your <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">YouTube Data API key</a> on <a href="https://console.cloud.google.com/apis/" target="_blank">Google Cloud Platform</a>',
					'button_name' => 'youtube_check_api_key',
					'button_id'   => 'youtube-check-api-key',
					'button_text' => esc_html__( 'Check YouTube API Key', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>
		<?php
		if ( sgg_pro_enabled() ) {
			$sgg_errors  = get_settings_errors( Dashboard::$slug );
			$youtube_key = array_search( 'youtube_api_key_error', array_column( $sgg_errors, 'code' ), true );

			if ( false !== $youtube_key && ! empty( $sgg_errors[ $youtube_key ]['message'] ) ) {
				?>
				<div class="grim-notice grim-notice-error">
					<i class="grim-icon-information"></i>
					<p>
						<span><?php echo wp_kses_post( $sgg_errors[ $youtube_key ]['message'] ); ?></span>
					</p>
				</div>
				<?php
			}
		}
		?>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>


<div class="grim-section grim-preview-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title video-sitemap-depended" data-search-id="sgg_vimeo"><?php esc_html_e( 'Vimeo', 'xml-sitemap-generator-for-google' ); ?></h3>
	</div>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="grim-section-desc grim-mb-20 video-sitemap-depended"><?php esc_html_e( 'This is required field for retrieving the data from Vimeo embeds if you are using them in Contents.', 'xml-sitemap-generator-for-google' ); ?></p>
		<p class="video-sitemap-depended">
			<?php
			Dashboard::render(
				'partials/check-button.php',
				array(
					'input_name'  => 'vimeo_api_key',
					'placeholder' => 'Enter access token',
					'input_value' => $settings->vimeo_api_key,
					'input_label' => esc_html__( 'Vimeo Access Token:', 'xml-sitemap-generator-for-google' ),
					'input_class' => 'video-sitemap-depended',
					'description' => 'Get your <a href="https://developer.vimeo.com/api/guides/start#generate-access-token" target="_blank">Vimeo Access Token</a> from <a href="https://developer.vimeo.com/apps" target="_blank">Vimeo Developer Apps</a>',
					'button_name' => 'vimeo_check_api_key',
					'button_id'   => 'vimeo-check-api-key',
					'button_text' => esc_html__( 'Check Vimeo Access Token', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>
		<?php
		if ( sgg_pro_enabled() ) {
			$sgg_errors = get_settings_errors( Dashboard::$slug );
			$vimeo_key  = array_search( 'vimeo_api_key_error', array_column( $sgg_errors, 'code' ), true );

			if ( false !== $vimeo_key && ! empty( $sgg_errors[ $vimeo_key ]['message'] ) ) {
				?>
				<div class="grim-notice grim-notice-error">
					<i class="grim-icon-information"></i>
					<p>
						<span><?php echo wp_kses_post( $sgg_errors[ $vimeo_key ]['message'] ); ?></span>
					</p>
				</div>
				<?php
			}
		}
		?>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>

<div class="grim-section grim-button-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-toggle-section">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'enable_video_api_cache',
						'value' => $settings->enable_video_api_cache ?? true,
						'label' => esc_html__( 'API Data Cache', 'xml-sitemap-generator-for-google' ),
						'class' => 'video-sitemap-depended',
					)
				);
				?>
			</p>
			<div class="inside">
				<p class="video-sitemap-depended grim-mb-20"><?php esc_html_e( 'Caching API Data improves performance by storing and reusing requested Video data from YouTube, Vimeo, and Twitter API.', 'xml-sitemap-generator-for-google' ); ?></p>

				<p class="video-sitemap-depended">
					<input type="hidden" name="clear_video_api_cache" value="">
					<button type="submit" id="clear-video-api-cache" class="grim-button white video-sitemap-depended">
						<span>
							<i class="grim-icon-trash"></i><?php esc_html_e( 'Clear API Data Cache', 'xml-sitemap-generator-for-google' ); ?>
						</span>
					</button>
				</p>
			</div>
		</div>
	</div>
		<?php sgg_show_pro_overlay(); ?>
</div>
