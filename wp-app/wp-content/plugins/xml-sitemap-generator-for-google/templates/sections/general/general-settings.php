<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section general-settings">
	<h3 class="grim-section-title"><?php esc_html_e( 'General Settings', 'xml-sitemap-generator-for-google' ); ?></h3>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'Basic Settings for your Sitemaps. Enabling all below options is recommended.', 'xml-sitemap-generator-for-google' ); ?></p>
		<ul>
			<li class="grim-mb-20">
				<?php
				Dashboard::render(
					'fields/checkbox.php',
					array(
						'name'  => 'sitemap_to_robots',
						'value' => $settings->sitemap_to_robots,
						'label' => esc_html__( 'Add Sitemap Output URLs to site "robots.txt" file', 'xml-sitemap-generator-for-google' ),
						'class' => 'grim-section-label',

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
						'class' => 'grim-section-label has-dependency',
						'data'  => array( 'target' => 'indexnow' ),
					)
				);
				?>
				<div class="grim-ml-45 general-settings-index indexnow indexnow-api-key">
					<div class="grim-section-desc grim-mb-15 indexnow"><?php esc_html_e( 'IndexNow Protocol informs search engines like Microsoft Bing, Seznam.cz, Naver, and Yandex about all updates of your website, including changes when saving Posts.', 'xml-sitemap-generator-for-google' ); ?></div>
					<?php
					$indexnow = ( new \GRIM_SG\IndexNow() );
					?>
					<span class="general-settings-index-label indexnow"><?php esc_html_e( 'INDEXNOW API KEY:', 'xml-sitemap-generator-for-google' ); ?></span>
					<div class="general-settings-index-info grim-mb-15">
						<span class="general-settings-index-key" id="grim-api-key"><?php echo esc_html( $indexnow->get_api_key() ); ?></span>
						<span class="grim-btn-copied" data-target="grim-api-key">
							<i class="grim-icon-copy"></i>
							<span class="grim-tooltip"><?php esc_html_e( 'Copied!', 'xml-sitemap-generator-for-google' ); ?></span>
						</span>
					</div>
					<div class="general-settings-actions">
						<a href="<?php echo esc_url( $indexnow->get_api_key_location() ); ?>" target="_blank" class="grim-button white button-small indexnow button-icon-left">
							<span>
								<i class="grim-icon-check"></i>
								<?php esc_html_e( 'Check API Key', 'xml-sitemap-generator-for-google' ); ?>
							</span>
						</a>
						<input type="hidden" name="change_indexnow_key" value="">
						<button type="submit" id="change-indexnow-key" class="grim-button white button-small indexnow">
							<span>
								<i class="grim-icon-edit"></i>
								<?php esc_html_e( 'Change API Key', 'xml-sitemap-generator-for-google' ); ?>
							</span>
						</button>
					</div>
				</div>
			</li>
		</ul>
	</div>
</div>
