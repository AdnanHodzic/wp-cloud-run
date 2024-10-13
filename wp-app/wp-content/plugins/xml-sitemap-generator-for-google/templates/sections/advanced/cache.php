<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;
use GRIM_SG\Cache;
use GRIM_SG\GoogleNews;
use GRIM_SG\ImageSitemap;
use GRIM_SG\VideoSitemap;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Cache', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p>
			<?php esc_html_e( 'All below options will be available after enabling Sitemap Cache. Sitemaps Content will be cached for faster loading.', 'xml-sitemap-generator-for-google' ); ?>
		</p>
		<p>
			<strong>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'enable_cache',
						'value' => $settings->enable_cache ?? false,
						'label' => esc_html__( 'Enable Sitemap Cache', 'xml-sitemap-generator-for-google' ),
						'class' => 'has-dependency',
						'data'  => array( 'target' => 'sitemap-cache' ),
					)
				);
				?>
			</strong>
		</p>
		<p class="cache-timeout-group">
			<label for="cache_timeout" class="sitemap-cache"><?php esc_html_e( 'Cache Timeout:', 'xml-sitemap-generator-for-google' ); ?></label>
			<input type="number" id="cache_timeout" name="cache_timeout" class="sitemap-cache" value="<?php echo esc_attr( $settings->cache_timeout ?? 24 ); ?>"/>
			<select name="cache_timeout_period" class="sitemap-cache">
				<option value="60" <?php selected( esc_attr( $settings->cache_timeout_period ?? 3600 ), 60 ); ?>><?php esc_html_e( 'minute(s)', 'xml-sitemap-generator-for-google' ); ?></option>
				<option value="3600" <?php selected( esc_attr( $settings->cache_timeout_period ?? 3600 ), 3600 ); ?>><?php esc_html_e( 'hour(s)', 'xml-sitemap-generator-for-google' ); ?></option>
				<option value="86400" <?php selected( esc_attr( $settings->cache_timeout_period ?? 3600 ), 86400 ); ?>><?php esc_html_e( 'day(s)', 'xml-sitemap-generator-for-google' ); ?></option>
			</select>
		</p>

		<h3 class="hndle"><?php esc_html_e( 'Last Cached Time:', 'xml-sitemap-generator-for-google' ); ?></h3>
		<div>
			<table class="cache-table sitemap-cache" role="presentation">
				<tbody>
					<?php if ( $settings->enable_sitemap ) { ?>
						<tr>
							<th scope="row">
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->sitemap_url, 'sitemap_xml' ) ); ?>" target="_blank"><?php esc_html_e( 'XML Sitemap', 'xml-sitemap-generator-for-google' ); ?></a>:
							</th>
							<td><i><?php echo esc_html( Cache::get_time_formatted( 'sitemap' ) ); ?></i></td>
						</tr>
					<?php } ?>
					<?php if ( sgg_pro_enabled() && $settings->enable_html_sitemap ) { ?>
						<tr>
							<th scope="row">
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->html_sitemap_url, 'sitemap_html' ) ); ?>" target="_blank"><?php esc_html_e( 'HTML Sitemap', 'xml-sitemap-generator-for-google' ); ?></a>:
							</th>
							<td><i><?php echo esc_html( Cache::get_time_formatted( 'sitemap' ) ); ?></i></td>
						</tr>
					<?php } ?>
					<?php if ( $settings->enable_google_news ) { ?>
						<tr>
							<th scope="row">
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->google_news_url, 'google_news' ) ); ?>" target="_blank"><?php esc_html_e( 'Google News', 'xml-sitemap-generator-for-google' ); ?></a>:
							</th>
							<td><i><?php echo esc_html( Cache::get_time_formatted( GoogleNews::$template ) ); ?></i></td>
						</tr>
					<?php } ?>
					<?php if ( $settings->enable_image_sitemap ) { ?>
						<tr>
							<th scope="row">
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ) ); ?>" target="_blank"><?php esc_html_e( 'Image Sitemap', 'xml-sitemap-generator-for-google' ); ?></a>:
							</th>
							<td><i><?php echo esc_html( Cache::get_time_formatted( ImageSitemap::$template ) ); ?></i></td>
						</tr>
					<?php } ?>
					<?php if ( $settings->enable_video_sitemap ) { ?>
						<tr>
							<th scope="row">
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ) ); ?>" target="_blank"><?php esc_html_e( 'Video Sitemap', 'xml-sitemap-generator-for-google' ); ?></a>:
							</th>
							<td><i><?php echo esc_html( Cache::get_time_formatted( VideoSitemap::$template ) ); ?></i></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<span class="sitemap-cache"><?php esc_html_e( 'Note: Sitemap Cache will only be created when someone opens/visits the Sitemap on front-end.', 'xml-sitemap-generator-for-google' ); ?></span>
		</div>

		<p class="sitemap-cache">
			<input type="hidden" name="clear_cache" value="">
			<input type="submit" id="clear-sitemap-cache" class="button sitemap-cache" value="<?php esc_html_e( 'Clear Cache', 'xml-sitemap-generator-for-google' ); ?>">
		</p>

		<h3 class="hndle"><?php
			esc_html_e( 'Smart Caching', 'xml-sitemap-generator-for-google' );

			sgg_show_pro_badge();
			?></h3>
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="sitemap-cache"><?php esc_html_e( 'Advanced Caching features to improve Sitemap data collection.', 'xml-sitemap-generator-for-google' ); ?></p>

			<p>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'clear_cache_on_save_post',
						'class' => 'sitemap-cache',
						'value' => $settings->clear_cache_on_save_post ?? false,
						'label' => esc_html__( 'Clear cache when Page/Post created or updated', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</p>

			<?php sgg_show_pro_overlay(); ?>
		</div>

	</div>
</div>
