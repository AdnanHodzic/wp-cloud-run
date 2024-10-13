<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'General Settings', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'Basic Settings for your Sitemaps. Enabling all below options is recommended.', 'xml-sitemap-generator-for-google' ); ?></p>
		<ul>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'sitemap_to_robots',
						'value' => $settings->sitemap_to_robots,
						'label' => esc_html__( 'Add Sitemap Output URLs to site "robots.txt" file', 'xml-sitemap-generator-for-google' ),
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'enable_indexnow',
						'value' => $settings->enable_indexnow,
						'label' => esc_html__( 'Enable IndexNow Protocol (Microsoft Bing, Seznam.cz, Naver, Yandex)', 'xml-sitemap-generator-for-google' ),
						'class' => 'has-dependency',
						'data'  => array( 'target' => 'indexnow' ),
					)
				);
				?>
				<br>
				<small class="indexnow"><?php esc_html_e( 'IndexNow Protocol informs search engines like Microsoft Bing, Seznam.cz, Naver, and Yandex about all updates of your website, including changes when saving Posts.', 'xml-sitemap-generator-for-google' ); ?></small>

				<br>
				<small class="indexnow indexnow-api-key">
					<?php
					$indexnow = ( new \GRIM_SG\IndexNow() );
					printf(
					/* translators: %s: IndexNow API Key */
						esc_html__( 'INDEXNOW API KEY: %s', 'xml-sitemap-generator-for-google' ),
						wp_kses_post( "<b>{$indexnow->get_api_key()}</b>" )
					);
					?>
				</small>

				<br>
				<a href="<?php echo esc_url( $indexnow->get_api_key_location() ); ?>" target="_blank" class="button button-small indexnow">
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Check API Key', 'xml-sitemap-generator-for-google' ); ?>
				</a>
				<input type="hidden" name="change_indexnow_key" value="">
				<button type="submit" id="change-indexnow-key" class="button button-small indexnow">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Change API Key', 'xml-sitemap-generator-for-google' ); ?>
				</button>
			</li>
		</ul>
		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'type'        => 'number',
					'name'        => 'links_per_page',
					'class'       => 'sitemap-index-depended',
					'value'       => $settings->links_per_page ?? 1000,
					'label'       => esc_html__( 'Links per page:', 'xml-sitemap-generator-for-google' ),
					'description' => esc_html__( 'Number of links per page in Sitemap Index. Note: Setting a low limit per page may affect the speed of generating the Sitemap Index.', 'xml-sitemap-generator-for-google' ),
				)
			);
			?>
		</p>
	</div>
</div>
