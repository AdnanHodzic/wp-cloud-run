<?php

namespace SPC;

class HTML_Modifier implements Module_Interface {
	private const DEFAULT_CONFIG = [
		'disable_native_lazyload' => false,
		'lazy_load'               => false,
	];

	private const TAGS_TO_MODIFY = [ 'IMG', 'IFRAME', 'VIDEO' ];

	private static $skipped_images_lazyload = 0;

	/**
	 * Initialize the module.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! \SPC\Loader::can_process_html() ) {
			return;
		}

		add_filter( 'swcfpc_normal_fallback_cache_html', [ $this, 'alter_cached_html' ] );
	}

	/**
	 * Alter HTML before saving into cache.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function alter_cached_html( $html ) {
		global $sw_cloudflare_pagecache;

		$lazy_load       = (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOADING ) === 1;
		$native_lazyload = (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_NATIVE_LAZY_LOADING ) === 1;

		return ! $lazy_load && $native_lazyload ? $html : $this->parse_html(
			$html,
			wp_parse_args(
				[
					'lazy_load'               => $lazy_load,
					'disable_native_lazyload' => ! $native_lazyload,
				],
				self::DEFAULT_CONFIG 
			) 
		);
	}

	/**
	 * Parse the HTML and add required attributes.
	 *
	 * @param string $html The HTML content.
	 * @param array  $config The config to use when parsing.
	 *
	 * @return string
	 */
	private function parse_html( $html, $config ) {
		$parser = new \WP_HTML_Tag_Processor( $html );

		while ( $parser->next_tag() ) {
			$current_tag = $parser->get_tag();

			if ( ! in_array( $current_tag, self::TAGS_TO_MODIFY, true ) ) {
				continue;
			}

			if ( $config['lazy_load'] && in_array( $current_tag, $this->get_lazyloadable_tags() ) ) {
				$this->handle_lazy_load( $parser );
			}

			if ( ( $config['disable_native_lazyload'] ) && in_array(
				$current_tag,
				[
					'IMG',
					'IFRAME',
				] 
			) ) {
				$parser->remove_attribute( 'loading' );
			}
		}

		return $parser->get_updated_html();
	}

	/**
	 * Handle JS delay.
	 *
	 * @param \WP_HTML_Tag_Processor $parser The parser.
	 *
	 * @return void
	 */
	public function handle_lazy_load( $parser ) {
		global $sw_cloudflare_pagecache;

		$tag     = $parser->get_tag();
		$to_skip = (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOAD_SKIP_IMAGES, 2 );

		if ( $tag === 'IMG' && $to_skip > self::$skipped_images_lazyload ) {
			self::$skipped_images_lazyload++;

			return;
		}

		$exclusions = array_merge(
			$sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_EXCLUDED, [] ),
			Constants::DEFAULT_LAZY_LOAD_EXCLUSIONS
		);

		$data_attributes = $parser->get_attribute_names_with_prefix( 'data-' );

		$attribute_values_to_check = array_filter(
			array_map(
				function ( $attribute ) use ( $parser ) {
					return $parser->get_attribute( $attribute );
				},
				array_merge(
					$data_attributes,
					[
						'src',
						'srcset',
						'class',
					] 
				) 
			),
			'is_string' 
		);

		foreach ( $attribute_values_to_check as $attribute_value ) {
			if ( array_filter(
				$exclusions,
				function ( $exclusion ) use ( $attribute_value ) {
					return strpos( $attribute_value, $exclusion ) !== false;
				} 
			) ) {
				return;
			}
		}

		if ( $tag === 'IMG' && $parser->get_attribute( 'fetchpriority' ) === 'high' ) {
			return;
		}

		$parser->add_class( 'lazyload' );

		$attributes_to_replace = array_filter(
			[ 'src', 'srcset' ],
			function ( $attribute ) use ( $parser ) {
				return is_string( $parser->get_attribute( $attribute ) );
			} 
		);

		foreach ( $attributes_to_replace as $attribute ) {
			$parser->set_attribute( 'data-' . $attribute, $parser->get_attribute( $attribute ) );
			$parser->remove_attribute( $attribute );
		}
	}

	/**
	 * Return the tags that can be lazy loaded.
	 *
	 * @return string[]
	 */
	private function get_lazyloadable_tags() {
		global $sw_cloudflare_pagecache;

		return (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOAD_VIDEO_IFRAME ) === 1 ? [
			'IMG',
			'VIDEO',
			'IFRAME',
		] : [ 'IMG' ];
	}
}
