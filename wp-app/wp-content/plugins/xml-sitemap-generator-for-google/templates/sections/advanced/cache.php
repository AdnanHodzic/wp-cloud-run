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
<div class="grim-section">
	<div class="grim-toggle-section">
		<strong>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'enable_cache',
					'value' => $settings->enable_cache ?? false,
					'label' => esc_html__( 'Cache', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'sitemap-cache' ),
				)
			);
			?>
		</strong>
	</div>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20">
			<?php esc_html_e( 'All below options will be available after enabling Sitemap Cache. Sitemaps Content will be cached for faster loading.', 'xml-sitemap-generator-for-google' ); ?>
		</p>

		<p class="cache-timeout-group grim-mb-20">
			<?php
			$cache_timeout_period = $settings->cache_timeout_period ?? 3600;

			$args = array(
				'label'       => __( 'Cache Expiration Time:', 'xml-sitemap-generator-for-google' ),
				'name'        => 'cache_timeout_period',
				'value'       => (string) $cache_timeout_period,
				'options'     => array(
					'60'    => __( 'minute(s)', 'xml-sitemap-generator-for-google' ),
					'3600'  => __( 'hour(s)', 'xml-sitemap-generator-for-google' ),
					'86400' => __( 'day(s)', 'xml-sitemap-generator-for-google' ),
				),
				'select_size' => 'grim-select-small',
			);
			?>

			<div class="grim-select-label sitemap-cache" data-search-id="<?php echo esc_attr( $args['name'] ); ?>">
				<?php echo esc_html( $args['label'] ); ?>
			</div>
			<div class="grim-cache-time grim-mb-20">
				<input type="number" id="cache_timeout" name="cache_timeout" class="grim-input sitemap-cache" value="<?php echo esc_attr( $settings->cache_timeout ?? 24 ); ?>"/>
				<div class="grim-select <?php echo esc_attr( $args['select_size'] ?? 'grim-select-default' ); ?> sitemap-cache" data-name="<?php echo esc_attr( $args['name'] ); ?>">
					<div class="grim-select__trigger">
						<span>
							<?php echo esc_html( $args['options'][ $args['value'] ] ?? __( 'None', 'xml-sitemap-generator-for-google' ) ); ?>
						</span>
						<i class="grim-icon-chevron-down"></i>
					</div>
					<div class="grim-options">
						<?php foreach ( $args['options'] as $value => $label ) { ?>
							<div class="grim-option <?php echo $args['options'][ $args['value'] ] === $label ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $value ); ?>">
								<?php echo esc_html( $label ); ?>
							</div>
						<?php } ?>
					</div>
				</div>

				<select id="<?php echo esc_attr( $args['name'] ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" class="grim-hidden-select" hidden>
					<?php foreach ( $args['options'] as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $args['value'] ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php } ?>
				</select>
			</div>
		</p>
		<div class="sitemap-cache">
			<table class="grim-table grim-table-small-row wp-list-table widefat fixed striped" role="presentation">
				<thead>
					<tr>
						<th><span data-search-id="last_cached_time"><?php esc_html_e( 'Last Cached Time:', 'xml-sitemap-generator-for-google' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $settings->enable_sitemap ) { ?>
						<tr>
							<td>
								<span><?php esc_html_e( 'XML Sitemap', 'xml-sitemap-generator-for-google' ); ?></span>:
							</td>
							<td class="grim-td-center"><i><?php echo esc_html( Cache::get_time_formatted( 'sitemap' ) ); ?></i></td>
							<td>
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->sitemap_url, 'sitemap_xml' ) ); ?>" target="_blank" class="grim-button white">
									<span><?php esc_html_e( 'View', 'xml-sitemap-generator-for-google' ); ?></span>
								</a>
							</td>
						</tr>
					<?php } ?>
					<?php if ( sgg_pro_enabled() && $settings->enable_html_sitemap ) { ?>
						<tr>
							<td>
								<span><?php esc_html_e( 'HTML Sitemap', 'xml-sitemap-generator-for-google' ); ?></span>:
							</td>
							<td class="grim-td-center"><i><?php echo esc_html( Cache::get_time_formatted( 'sitemap' ) ); ?></i></td>
							<td>
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->html_sitemap_url, 'sitemap_html' ) ); ?>" target="_blank" class="grim-button white">
									<span><?php esc_html_e( 'View', 'xml-sitemap-generator-for-google' ); ?></span>
								</a>
							</td>
						</tr>
					<?php } ?>
					<?php if ( $settings->enable_google_news ) { ?>
						<tr>
							<td>
								<span><?php esc_html_e( 'Google News', 'xml-sitemap-generator-for-google' ); ?></span>:
							</td>
							<td class="grim-td-center"><i><?php echo esc_html( Cache::get_time_formatted( GoogleNews::$template ) ); ?></i></td>
							<td>
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->google_news_url, 'google_news' ) ); ?>" target="_blank" class="grim-button white">
									<span><?php esc_html_e( 'View', 'xml-sitemap-generator-for-google' ); ?></span>
								</a>
							</td>
						</tr>
					<?php } ?>
					<?php if ( $settings->enable_image_sitemap ) { ?>
						<tr>
							<td>
								<span><?php esc_html_e( 'Image Sitemap', 'xml-sitemap-generator-for-google' ); ?></span>:
							</td>
							<td class="grim-td-center"><i><?php echo esc_html( Cache::get_time_formatted( ImageSitemap::$template ) ); ?></i></td>
							<td>
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ) ); ?>" target="_blank" class="grim-button white">
									<span><?php esc_html_e( 'View', 'xml-sitemap-generator-for-google' ); ?></span>
								</a>
							</td>
						</tr>
					<?php } ?>
					<?php if ( $settings->enable_video_sitemap ) { ?>
						<tr>
							<td>
								<span><?php esc_html_e( 'Video Sitemap', 'xml-sitemap-generator-for-google' ); ?></span>:
							</td>
							<td class="grim-td-center"><i><?php echo esc_html( Cache::get_time_formatted( VideoSitemap::$template ) ); ?></i></td>
							<td>
								<a href="<?php echo esc_url( sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ) ); ?>" target="_blank" class="grim-button white">
									<span><?php esc_html_e( 'View', 'xml-sitemap-generator-for-google' ); ?></span>
								</a>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

			<div class="grim-notice grim-mb-20">
				<i class="grim-icon-information"></i>
				<p>
					<?php
					echo sprintf(
						esc_html__( 'Note: %s', 'xml-sitemap-generator-for-google' ),
						'<span>' . esc_html__( 'Sitemap Cache will only be created when someone opens/visits the Sitemap on front-end.', 'xml-sitemap-generator-for-google' ) . '</span>'
					)
					?>
				</p>
			</div>
		</div>

		<p class="sitemap-cache grim-button-section" data-search-id="clear_cache">
			<input type="hidden" name="clear_cache" value="">
			<button type="submit" id="clear-sitemap-cache" class="grim-button white sitemap-cache">
				<span>
					<i class="grim-icon-trash"></i>
					<?php esc_html_e( 'Clear Cache', 'xml-sitemap-generator-for-google' ); ?>
				</span>
			</button>
		</p>
	</div>
</div>

<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-toggle-section pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'clear_cache_on_save_post',
					'class' => 'sitemap-cache',
					'value' => $settings->clear_cache_on_save_post ?? false,
					'label' => esc_html__( 'Smart Caching', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>

		<p class="grim-section-desc inside sitemap-cache"><?php esc_html_e( 'Clear cache when Page/Post created or updated', 'xml-sitemap-generator-for-google' ); ?></p>

	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>

<div class="grim-section">
	<div class="grim-toggle-section pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'disable_media_sitemap_cache',
					'value' => $settings->disable_media_sitemap_cache ?? false,
					'label' => esc_html__( 'Disable Media Sitemap Cache Collection', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>
		<p class="inside grim-section-desc">
			<?php esc_html_e( 'If you are having issues with Image and Video Sitemaps, you can try to disable Media Sitemap Cache Collection.', 'xml-sitemap-generator-for-google' ); ?>
			<br>
		</p>
		<div class="grim-notice">
			<i class="grim-icon-information"></i>
			<p>
				<?php
				echo sprintf(
					esc_html__( 'Note: %s', 'xml-sitemap-generator-for-google' ),
					'<span>' . esc_html__( 'This will affect the performance of Media Sitemap Generation.', 'xml-sitemap-generator-for-google' ) . '</span>'
				)
				?>
			</p>
		</div>
	</div>
</div>