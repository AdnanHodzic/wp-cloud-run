<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section">
	<p class="grim-toggle-section">
		<strong>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'enable_google_news',
					'value' => $settings->enable_google_news ?? false,
					'label' => esc_html__( 'Google News', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'google-news-depended' ),
				)
			);
			?>
		</strong>
	</p>
	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php echo wp_kses_post( 'All options will be available after enabling Google News. Note that only posts from the last 48 hours will be processed by <a href="https://news.google.com" target="_blank">Google News</a>.' ); ?></p>
		<p>
			<?php
			Dashboard::render(
				'fields/input.php',
				array(
					'name'        => 'google_news_name',
					'value'       => $settings->google_news_name ?? '',
					'label'       => esc_html__( 'Publication Name:', 'xml-sitemap-generator-for-google' ),
					'description' => sprintf(
						/* translators: %s General Settings URL */
						wp_kses_post( 'Default value is General Settings > <a href="%s" target="_blank">Site Title</a>.' ),
						esc_url( admin_url( 'options-general.php' ) )
					),
					'class'       => 'grim-input google-news-depended',
					'placeholder' => 'Enter publication name',
				)
			);
			?>
		</p>
		<div class="google-news-depended">
			<div class="grim-notice notice inline sitemap-detector grim-mb-20">
				<i class="grim-icon-information"></i>
				<p>
					<?php
					echo sprintf(
					/* translators: %s: URL to Google News Publisher Center */
						esc_html__( 'Source Labels: %s', 'xml-sitemap-generator-for-google' ),
						sprintf(
							wp_kses(
							/* translators: %s: URL */
								'<span>' . __( 'To manage your Site Source Labels, please go to the <a href="%s" target="_blank">Google News Publisher Center</a>.', 'xml-sitemap-generator-for-google' ) . '</span>',
								array(
									'a'    => array(
										'href'   => array(),
										'target' => array(),
									),
									'span' => array(),
								)
							),
							esc_url( 'https://publishercenter.google.com/' )
						)
					);
					?>
				</p>
			</div>
		</div>
		<p>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'google_news_old_posts',
					'value' => $settings->google_news_old_posts ?? false,
					'label' => esc_html__( 'Include Older Posts', 'xml-sitemap-generator-for-google' ),
					'class' => 'grim-toggle-label google-news-depended',
				)
			);
			?>
			<br>
			<small class="google-news-depended grim-section-desc grim-ml-45"><?php esc_html_e( 'Include posts older than 48 hours for informational purposes only. Note that they will NOT be indexed by Google News.', 'xml-sitemap-generator-for-google' ); ?></small>
	</div>
</div>
