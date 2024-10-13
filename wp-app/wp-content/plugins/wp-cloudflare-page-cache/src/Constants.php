<?php

namespace SPC;

class Constants {
	public const SETTING_NATIVE_LAZY_LOADING    = 'cf_native_lazy_loading';
	public const SETTING_LAZY_LOADING           = 'cf_lazy_loading';
	public const SETTING_LAZY_LOAD_VIDEO_IFRAME = 'cf_lazy_load_video_iframe';
	public const SETTING_LAZY_LOAD_SKIP_IMAGES  = 'cf_lazy_load_skip_images';
	public const SETTING_LAZY_EXCLUDED          = 'cf_lazy_load_excluded';
	public const SETTING_LAZY_LOAD_BG           = 'cf_lazy_load_bg';
	public const SETTING_LAZY_LOAD_BG_SELECTORS = 'cf_lazy_load_bg_selectors';
	public const DEFAULT_LAZY_LOAD_EXCLUSIONS   = [
		'skip-lazy',
	];
	public const DEFAULT_BG_LAZYLOAD_SELECTORS  = [
		'[style*="background-image:"]',
		'[class*="elementor"][data-settings*="background_background"]',
		'.elementor-section > .elementor-background-overlay',
		'[class*="wp-block-cover"][style*="background-image"]',
		'[class*="wp-block-group"][style*="background-image"]',
	];
	public const COMPAT_BG_LAZYLOAD_SELECTORS   = [
		'otter-blocks/otter-blocks.php'                 => [
			'.o-flip-front',
			'.o-flip-back',
			'.wp-block-themeisle-blocks-advanced-columns',
			'.wp-block-themeisle-blocks-advanced-columns-overlay',
			'.wp-block-themeisle-blocks-advanced-column',
			'.wp-block-themeisle-blocks-advanced-column-overlay',
		],
		'bb-plugin/fl-builder.php'                      => [
			'.fl-col-content',
			'.fl-row-bg-photo > .fl-row-content-wrap',
		],
		'beaver-builder-lite-version/fl-builder.php'    => [
			'.fl-col-content',
			'.fl-row-bg-photo > .fl-row-content-wrap',
		],
		'divi-builder/divi-builder.php'                 => [
			'.et_pb_slides > .et_pb_slide',
			'.et_parallax_bg',
			'.et_pb_video_overlay',
			'.et_pb_module:not([class*="et_pb_blog"])',
			'.et_pb_row',
			'.et_pb_section.et_pb_section_1',
			'.et_pb_with_background',
		],
		'elementor/elementor.php'                       => [
			'.elementor-widget-container',
			'.elementor-background-slideshow__slide__image',
		],
		'essential-grid/essential-grid.php'             => [
			'.esg-media-poster',
		],
		'master-slider/master-slider.php'               => [
			'.master-slider',
		],
		'ml-slider/ml-slider.php'                       => [
			'.coin-slider > .coin-slider > a',
			'.coin-slider > .coin-slider',
		],
		'ml-slider-pro/ml-slider-pro.php'               => [
			'.coin-slider > .coin-slider > a',
			'.coin-slider > .coin-slider',
		],
		'revslider/revslider.php'                       => [
			'.tp-bgimg',
		],
		'thrive-visual-editor/thrive-visual-editor.php' => [
			'.tve-content-box-background',
			'.tve-page-section-out',
			'.thrv_text_element',
		],
	];
}
