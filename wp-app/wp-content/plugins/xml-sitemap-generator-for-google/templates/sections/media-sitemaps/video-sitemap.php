<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Video Sitemap', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'All below options will be available after enabling Video Sitemap. Sitemap will only include Videos that are used in Content.', 'xml-sitemap-generator-for-google' ); ?></p>
		<div>
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
		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'  => 'video_sitemap_url',
					'value' => $settings->video_sitemap_url,
					'label' => esc_html__( 'Video Sitemap URL:', 'xml-sitemap-generator-for-google' ),
					'class' => 'video-sitemap-depended',
				)
			);
			?>
		</p>
		<p class="video-sitemap-depended">
			<?php esc_html_e( 'Preview your Video Sitemap:', 'xml-sitemap-generator-for-google' ); ?>
			<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ) ); ?>" target="_blank">
				<?php echo esc_url( sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ) ); ?>
			</a>
		</p>

		<hr>

		<h3 class="hndle"><?php
			esc_html_e( 'YouTube Data API', 'xml-sitemap-generator-for-google' );

			sgg_show_pro_badge();
			?></h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="video-sitemap-depended"><?php esc_html_e( 'This is required field for retrieving the data from Youtube embeds if you are using them in Contents.', 'xml-sitemap-generator-for-google' ); ?></p>

			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'        => 'youtube_api_key',
					'value'       => $settings->youtube_api_key,
					'label'       => esc_html__( 'YouTube Data API v3 Key:', 'xml-sitemap-generator-for-google' ),
					'class'       => 'video-sitemap-depended',
					'description' => 'Get your <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">YouTube Data API key</a> on <a href="https://console.cloud.google.com/apis/" target="_blank">Google Cloud Platform</a>',
				)
			);

			if ( sgg_pro_enabled() ) {
				$sgg_errors  = get_settings_errors( Dashboard::$slug );
				$youtube_key = array_search( 'youtube_api_key_error', array_column( $sgg_errors, 'code' ), true );

				if ( false !== $youtube_key && ! empty( $sgg_errors[ $youtube_key ]['message'] ) ) {
					?>
						<div class="inline-error">
							<?php echo wp_kses_post( $sgg_errors[ $youtube_key ]['message'] ); ?>
						</div>
					<?php
				}
			}
			?>

			<p class="video-sitemap-depended">
				<input type="hidden" name="youtube_check_api_key" value="">
				<input type="submit" id="youtube-check-api-key" class="button video-sitemap-depended" value="<?php esc_html_e( 'Check YouTube API Key', 'xml-sitemap-generator-for-google' ); ?>">
			</p>

			<?php sgg_show_pro_overlay(); ?>
		</div>

		<hr>

		<h3 class="hndle"><?php
			esc_html_e( 'Vimeo Data API', 'xml-sitemap-generator-for-google' );

			sgg_show_pro_badge();
			?></h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="video-sitemap-depended"><?php esc_html_e( 'This is required field for retrieving the data from Vimeo embeds if you are using them in Contents.', 'xml-sitemap-generator-for-google' ); ?></p>

			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'        => 'vimeo_api_key',
					'value'       => $settings->vimeo_api_key,
					'label'       => esc_html__( 'Vimeo Access Token:', 'xml-sitemap-generator-for-google' ),
					'class'       => 'video-sitemap-depended',
					'description' => 'Get your <a href="https://developer.vimeo.com/api/guides/start#generate-access-token" target="_blank">Vimeo Access Token</a> from <a href="https://developer.vimeo.com/apps" target="_blank">Vimeo Developer Apps</a>',
				)
			);

			if ( sgg_pro_enabled() ) {
				$sgg_errors = get_settings_errors( Dashboard::$slug );
				$vimeo_key  = array_search( 'vimeo_api_key_error', array_column( $sgg_errors, 'code' ), true );

				if ( false !== $vimeo_key && ! empty( $sgg_errors[ $vimeo_key ]['message'] ) ) {
					?>
					<div class="inline-error">
						<?php echo wp_kses_post( $sgg_errors[ $vimeo_key ]['message'] ); ?>
					</div>
					<?php
				}
			}
			?>

			<p class="video-sitemap-depended">
				<input type="hidden" name="vimeo_check_api_key" value="">
				<input type="submit" id="vimeo-check-api-key" class="button video-sitemap-depended" value="<?php esc_html_e( 'Check Vimeo Access Token', 'xml-sitemap-generator-for-google' ); ?>">
			</p>

			<?php sgg_show_pro_overlay(); ?>
		</div>

		<hr>

		<h3 class="hndle"><?php
			esc_html_e( 'API Data Cache', 'xml-sitemap-generator-for-google' );

			sgg_show_pro_badge();
			?></h3>

		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="video-sitemap-depended"><?php esc_html_e( 'Caching API Data improves performance by storing and reusing requested Video data from YouTube & Vimeo API.', 'xml-sitemap-generator-for-google' ); ?></p>

			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'enable_video_api_cache',
						'value' => $settings->enable_video_api_cache ?? true,
						'label' => esc_html__( 'Enable API Data Cache', 'xml-sitemap-generator-for-google' ),
						'class' => 'video-sitemap-depended',
					)
				);
				?>
			</p>

			<p class="video-sitemap-depended">
				<input type="hidden" name="clear_video_api_cache" value="">
				<input type="submit" id="clear-video-api-cache" class="button video-sitemap-depended" value="<?php esc_html_e( 'Clear API Data Cache', 'xml-sitemap-generator-for-google' ); ?>">
			</p>

			<?php sgg_show_pro_overlay(); ?>
		</div>

	</div>
</div>
