<?php

use GRIM_SG\Dashboard;
$settings = $args['settings'] ?? new stdClass();
$previews = array();
$tools    = array();

// XML Sitemap
if ( $settings->enable_sitemap ) {
	$previews[] = array(
		'label' => __( 'XML Sitemap', 'xml-sitemap-generator-for-google' ),
		'url'   => sgg_get_sitemap_url( $settings->sitemap_url, 'sitemap_xml' ),
	);
}
// HTML Sitemap (Pro)
if ( sgg_pro_enabled() && $settings->enable_html_sitemap ) {
	$previews[] = array(
		'label' => __( 'HTML Sitemap', 'xml-sitemap-generator-for-google' ),
		'url'   => sgg_get_sitemap_url( $settings->html_sitemap_url, 'sitemap_html' ),
	);
}
// Google News
if ( $settings->enable_google_news ) {
	$previews[] = array(
		'label' => __( 'Google News', 'xml-sitemap-generator-for-google' ),
		'url'   => sgg_get_sitemap_url( $settings->google_news_url, 'google_news' ),
	);
}
// Image Sitemap
if ( $settings->enable_image_sitemap ) {
	$previews[] = array(
		'label' => __( 'Image Sitemap', 'xml-sitemap-generator-for-google' ),
		'url'   => sgg_get_sitemap_url( $settings->image_sitemap_url, 'image_sitemap' ),
	);
}
// Video Sitemap
if ( $settings->enable_video_sitemap ) {
	$previews[] = array(
		'label' => __( 'Video Sitemap', 'xml-sitemap-generator-for-google' ),
		'url'   => sgg_get_sitemap_url( $settings->video_sitemap_url, 'video_sitemap' ),
	);
}

// IndexNow
if ( $settings->enable_indexnow ) {
	$tools[] = array(
		'name'  => 'sgg-indexnow',
		'label' => __( 'Ping IndexNow Protocol', 'xml-sitemap-generator-for-google' ),
		'class' => 'white',
	);
}
// Flush Rewrite Rules
$tools[] = array(
	'name'  => 'sgg-flush-rewrite-rules',
	'label' => __( 'Flush Rewrite Rules', 'xml-sitemap-generator-for-google' ),
	'class' => 'white',
);
// Clear Cache
if ( $settings->enable_cache ) {
	$tools[] = array(
		'name'  => 'sgg-clear-cache',
		'label' => __( 'Clear Sitemaps Cache', 'xml-sitemap-generator-for-google' ),
		'class' => 'delete',
	);
}

$plugin_links = array(
	array(
		'title' => esc_html__( 'Documentation', 'xml-sitemap-generator-for-google' ),
		'icon'  => 'grim-icon-file-text',
		'link'  => 'https://wpgrim.com/docs/google-xml-sitemaps-generator/?utm_source=sgg-plugin&utm_medium=documentation&utm_campaign=xml_sitemap',
	),
	array(
		'title' => esc_html__( 'Support Forum', 'xml-sitemap-generator-for-google' ),
		'icon'  => 'grim-icon-help',
		'link'  => esc_url( sgg_get_support_url() ),
	),
	array(
		'title' => sprintf(
			/* translators: %s: Rating stars */
			esc_html__( 'Rate %s', 'xml-sitemap-generator-for-google' ),
			'<strong>' . esc_html__( '★★★★★', 'xml-sitemap-generator-for-google' ) . '</strong>'
		),
		'icon'  => 'grim-icon-star',
		'link'  => esc_url( sgg_get_review_url() ),
		'class' => 'grim-rate-button',
	),
);

if ( sgg_pro_enabled() ) {
	array_unshift(
		$plugin_links,
		array(
			'title' => esc_html__( 'Account & Support', 'xml-sitemap-generator-for-google' ),
			'icon'  => 'grim-icon-user',
			'link'  => 'https://wpgrim.com/account?utm_source=sgg-plugin&utm_medium=account&utm_campaign=xml_sitemap',
		)
	);
}

$footer_links = array(
	array(
		'title' => esc_html__( 'Google Search Console', 'xml-sitemap-generator-for-google' ),
		'link'  => 'https://search.google.com/search-console',
	),
	array(
		'title' => esc_html__( 'Google News Help Center', 'xml-sitemap-generator-for-google' ),
		'link'  => 'https://support.google.com/googlenews/',
	),
	array(
		'title' => esc_html__( 'Show up in Google News', 'xml-sitemap-generator-for-google' ),
		'link'  => 'https://support.google.com/news/publisher-center/answer/9607025',
	),
	array(
		'title' => esc_html__( 'IndexNow Protocol', 'xml-sitemap-generator-for-google' ),
		'link'  => 'https://www.indexnow.org/',
	),
	array(
		'title' => esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-generator-for-google' ),
		'link'  => 'https://www.bing.com/webmasters',
	),
	array(
		'title' => esc_html__( 'Yandex Webmaster', 'xml-sitemap-generator-for-google' ),
		'link'  => 'https://webmaster.yandex.com/sites/',
	),
	array(
		'title' => esc_html__( 'XML Sitemap Validator', 'xml-sitemap-generator-for-google' ),
		'link'  => 'https://www.xml-sitemaps.com/validate-xml-sitemap.html',
	),
);
?>

<div class="grim-section">
	<?php if ( ! empty( $tools ) ) : ?>
		<div class="grim-sidebar-section grim-sidebar-actions">
			<h3 class="grim-section-title"><?php esc_html_e( 'Tools', 'xml-sitemap-generator-for-google' ); ?></h3>
			<div class="grim-sidebar-list">
				<?php foreach ( $tools as $tool ) : ?>
					<div class="grim-sidebar-list-item">
						<div class="grim-button white <?php echo esc_attr( $tool['class'] ); ?>">
							<input type="hidden" name="<?php echo esc_attr( $tool['name'] ); ?>" value="">
							<input
								type="submit"
								id="<?php echo esc_attr( $tool['name'] ); ?>"
								name="<?php echo esc_attr( $tool['name'] ); ?>-button"
								value="<?php echo esc_attr( $tool['label'] ); ?>"
								class="<?php echo esc_attr( $tool['class'] ); ?>"
							>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $previews ) ) : ?>
		<div class="grim-sidebar-section">
			<h3 class="grim-section-title"><?php esc_html_e( 'Preview', 'xml-sitemap-generator-for-google' ); ?></h3>
			<div class="grim-sidebar-list">
				<?php foreach ( $previews as $item ) : ?>
					<div class="grim-sidebar-list-item">
						<a href="<?php echo esc_url( $item['url'] ); ?>" class="grim-button white button-icon-right" target="_blank">
							<span>
								<?php echo wp_kses_post( $item['label'] ); ?>
								<i class="grim-icon-external-link"></i>
							</span>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	if ( ! sgg_pro_enabled() ) {
		Dashboard::render(
			'partials/rate-banner.php',
			array(
				'description'  => sprintf(
					/* translators: %s: Pro version */
					esc_html__( 'If you want to unlock more features, please check out our %s.', 'xml-sitemap-generator-for-google' ),
					'<a href="' . esc_url( sgg_get_pro_url( 'notice' ) ) . '" target="_blank">' . esc_html__( 'Pro version', 'xml-sitemap-generator-for-google' ) . '</a>'
				),
				'button_text'  => esc_html__( 'Read More', 'xml-sitemap-generator-for-google' ),
				'button_url'   => esc_url( sgg_get_pro_url( 'notice' ) ),
				'data_notice'  => 'sgg_buy_pro',
				'notice_class' => 'grim-pro-notice grim-sidebar-notice',
			)
		);
	}
	?>

	<div class="grim-sidebar-section grim-sidebar-actions">
		<h3 class="grim-section-title"><?php esc_html_e( 'Useful', 'xml-sitemap-generator-for-google' ); ?></h3>
		<?php foreach ( $plugin_links as $plugin_link ) : ?>
			<div class="grim-sidebar-list-item grim-sidebar-actions-item">
				<a class="grim-button white <?php echo ! empty( $plugin_link['class'] ) ? esc_attr( $plugin_link['class'] ) : ''; ?>" href="<?php echo esc_url( $plugin_link['link'] ); ?>" target="_blank">
					<span>
						<i class="<?php echo esc_attr( $plugin_link['icon'] ); ?>"></i><?php echo wp_kses_post( $plugin_link['title'] ); ?>
					</span>
				</a>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="grim-sidebar-section">
		<h3 class="grim-section-title"><?php esc_html_e( 'Links', 'xml-sitemap-generator-for-google' ); ?></h3>
		<div class="grim-sidebar-links">
			<?php foreach ( $footer_links as $footer_link ) : ?>
				<a class="grim-sidebar-links-item" target="_blank" href="<?php echo esc_url( $footer_link['link'] ); ?>"><?php echo esc_html( $footer_link['title'] ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>

	<hr>

	<div class="grim-sidebar-section grim-sidebar-wrapper-info">
		<div class="grim-sidebar-wrapper-info-logo">
			<img src="<?php echo esc_url( plugins_url( 'assets/images/sgg-logo.svg', GRIM_SG_FILE ) ); ?>" alt="logo" width="60" height="60"/>
		</div>
		<div class="grim-sidebar-wrapper-info-title">
			<a href="https://wpgrim.com/dynamic-xml-sitemaps-generator-for-google/?utm_source=sgg-plugin&utm_medium=footer&utm_campaign=xml_sitemap" target="_blank">Dynamic XML Sitemaps Generator For Google Pro</a>
		</div>
	</div>
</div>
