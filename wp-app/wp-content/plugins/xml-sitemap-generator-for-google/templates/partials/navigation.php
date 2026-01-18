<?php
/**
 * @var $args
 */

$settings = $args['settings'] ?? new stdClass();
?>

<div class="grim-header">
	<div class="grim-section grim-nav">
		<div class="grim-nav-wrapper">
			<div class="grim-nav-tab-wrapper">
				<nav class="grim-nav-tab nav-tab-wrapper">
					<a href="#" class="grim-nav-tab-item nav-tab nav-tab-active" data-id="general"><i class="grim-icon-home"></i><?php esc_html_e( 'General', 'xml-sitemap-generator-for-google' ); ?></a>
					<a href="#" class="grim-nav-tab-item nav-tab" data-id="google-news"><i class="grim-icon-google"></i><?php esc_html_e( 'Google News', 'xml-sitemap-generator-for-google' ); ?></a>
					<a href="#" class="grim-nav-tab-item nav-tab" data-id="image-sitemap"><i class="grim-icon-image"></i><?php esc_html_e( 'Image Sitemap', 'xml-sitemap-generator-for-google' ); ?></a>
					<a href="#" class="grim-nav-tab-item nav-tab" data-id="video-sitemap"><i class="grim-icon-play-circle"></i><?php esc_html_e( 'Video Sitemap', 'xml-sitemap-generator-for-google' ); ?></a>
					<a href="#" class="grim-nav-tab-item nav-tab" data-id="advanced"><i class="grim-icon-sliders-horizontal"></i><?php esc_html_e( 'Advanced', 'xml-sitemap-generator-for-google' ); ?></a>
				</nav>
			</div>
			<div class="grim-settings-search">
				<div class="grim-settings-search-input-wrapper">
					<input type="search" class="grim-input" id="grim-settings-search-input" placeholder="<?php esc_attr_e( 'Search settings...', 'xml-sitemap-generator-for-google' ); ?>">
					<div class="grim-settings-search-cancel grim-button white">
						<span><?php esc_html_e( 'Cancel', 'xml-sitemap-generator-for-google' ); ?></span>
					</div>
				</div>
				<div class="grim-search-results" id="grim-search-results">
					<div class="grim-search-results-list"></div>
				</div>
			</div>
			<div class="grim-nav-wrapper-actions">
				<div class="grim-search-action">
					<div class="grim-search-action-btn">
						<i class="grim-icon-search"></i>
					</div>
				</div>
				<button type="submit" name="submit" class="grim-button secondary">
					<span><?php esc_html_e( 'Save changes', 'xml-sitemap-generator-for-google' ); ?></span>
				</button>
			</div>
		</div>
	</div>
</div>
