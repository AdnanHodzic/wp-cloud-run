<?php

namespace SPC\Modules;

use SPC\Constants;
use SPC\Loader;
use SPC\Services\Settings_Store;

/**
 * Frontend module.
 */
class Frontend implements Module_Interface {
	private const BG_LAZYLOADED_CLASS = 'spc-bg-lazyloaded';

	const LAZY_LOAD_BEHAVIOUR_ALL   = 'all';
	const LAZY_LOAD_BEHAVIOUR_FIXED = 'fixed';

	/**
	 * Initialize the module.
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_uncached' ] );
		add_filter( 'script_loader_tag', [ $this, 'modify_script_attributes' ], 10, 2 );

		if ( ! Loader::is_cached_page() ) {
			return;
		}

		add_action( 'wp_print_scripts', [ $this, 'add_bg_lazyload_script' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend' ] );
	}

	/**
	 * Add background lazy loading.
	 *
	 * @return void
	 */
	public function add_bg_lazyload_script() {
		if ( is_admin() || ! $this->is_background_lazyload_enabled() ) {
			return;
		}

		?>

		<script type="text/javascript" id="spc-lazy-bg">
			(function () {
			const loadedClass = '<?php echo esc_js( self::BG_LAZYLOADED_CLASS ); ?>';
			const bgSelectors = '<?php echo wp_strip_all_tags( join( ', ', $this->get_lazyload_background_selectors() ) ); ?>';

			function observerCallback(entries, observer) {
				entries.forEach(function (entry) {
				if (!entry.isIntersecting) return;

				if (entry.target.classList.contains(loadedClass)) return;

				entry.target.classList.add(loadedClass);
				observer.unobserve(entry.target);
				});
			}

			const intersectionObserver = new IntersectionObserver(observerCallback, {
				root: null,
				rootMargin: "150px 0px 500px",
				threshold: [0.1, 0.3, 0.5, 0.6, 0.8, 1],
			});

			function start() {
				document.querySelectorAll(bgSelectors).forEach(function (el) {
					intersectionObserver.observe(el);
					}
				)
			}

			document.addEventListener('DOMContentLoaded', start);
			}());
		</script>
		<?php
	}

	/**
	 * Enqueue scripts & styles.
	 *
	 * @return void
	 */
	public function enqueue_frontend() {
		global $sw_cloudflare_pagecache;

		if ( (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOADING ) !== 1 ) {
			return;
		}

		wp_enqueue_script( 'spc-lazysizes', SWCFPC_PLUGIN_URL . 'assets/js/lazysizes.min.js', [], '5.3.2' );

		if ( ! $this->is_background_lazyload_enabled() ) {
			return;
		}

		wp_register_style( 'spc_bg_lazy', false );
		wp_enqueue_style( 'spc_bg_lazy' );
		$lazyload_css = $this->get_background_lazy_css();
		if ( empty( $lazyload_css ) ) {
			remove_action( 'wp_print_scripts', [ $this, 'add_bg_lazyload_script' ] );
		}
		wp_add_inline_style( 'spc_bg_lazy', $lazyload_css );
	}

	/**
	 * Enqueue scripts that don't depend on caching.
	 *
	 * @return void
	 */
	public function enqueue_uncached() {
		if ( $this->is_amp_or_customizer() ) {
			return;
		}

		if ( Settings_Store::get_instance()->get( Constants::SETTING_PREFETCH_ON_HOVER ) ) {
			wp_enqueue_script( 'swcfpc_instantpage', SWCFPC_PLUGIN_URL . 'assets/js/instantpage.min.js', [], '5.2.0', true );
		}

		if (
			Settings_Store::get_instance()->get( Constants::SETTING_PREFETCH_ON_HOVER ) ||
			Settings_Store::get_instance()->get( Constants::SETTING_PREFETCH_URLS_VIEWPORT )
		) {
			$this->enqueue_auto_prefetch_viewport();
		}
	}

	/**
	 * Enqueue auto-prefetch viewport script.
	 *
	 * @return void
	 */
	private function enqueue_auto_prefetch_viewport() {
		global $sw_cloudflare_pagecache;

		wp_register_script( 'swcfpc_auto_prefetch_url', '', [], '', true );
		wp_enqueue_script( 'swcfpc_auto_prefetch_url' );

			ob_start();
		?>

			function swcfpc_wildcard_check(str, rule) {
			let escapeRegex = (str) => str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
			return new RegExp("^" + rule.split("*").map(escapeRegex).join(".*") + "$").test(str);
			}

			function swcfpc_can_url_be_prefetched(href) {

			if( href.length == 0 )
			return false;

			if( href.startsWith("mailto:") )
			return false;

			if( href.startsWith("https://") )
			href = href.split("https://"+location.host)[1];
			else if( href.startsWith("http://") )
			href = href.split("http://"+location.host)[1];

			for( let i=0; i < swcfpc_prefetch_urls_to_exclude.length; i++) {

			if( swcfpc_wildcard_check(href, swcfpc_prefetch_urls_to_exclude[i]) )
			return false;

			}

			return true;

			}

			let swcfpc_prefetch_urls_to_exclude = '<?php echo json_encode( $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_EXCLUDED_URLS, [] ) ); ?>';
			swcfpc_prefetch_urls_to_exclude = (swcfpc_prefetch_urls_to_exclude) ? JSON.parse(swcfpc_prefetch_urls_to_exclude) : [];

			<?php

			$inline_js = ob_get_contents();
			ob_end_clean();

			wp_add_inline_script( 'swcfpc_auto_prefetch_url', $inline_js, 'before' );
	}


	/**
	 * If the script is instantpage.js then we also need to make sure we load it as module and not text/javascript
	 *
	 * @param string $tag The script tag.
	 * @param string $handle The script handle.
	 *
	 * @return string
	 */
	public function modify_script_attributes( $tag, $handle ) {
		if ( empty( $tag ) || $handle !== 'swcfpc_instantpage' ) {
			return $tag;
		}

		// Make sure the tag has type="text/javascript" in it and no other theme or plugin has removed it before us handling it
		if ( ( strpos( $tag, 'text/javascript' ) !== false ) ) {
			$tag = str_replace( 'text/javascript', 'module', $tag );
		} else {
			$tag = str_replace( ' src', ' type="module" src', $tag );
		}

		return $tag;
	}

	/**
	 * Add style classes for lazy loading background images.
	 *
	 * @return string
	 */
	private function get_background_lazy_css() {
		$selectors = $this->get_lazyload_background_selectors();

		if ( empty( $selectors ) ) {
			return '';
		}

		$formatted_selectors = array_map(
			function ( $selector ) {
				return sprintf( 'html %s:not(.%s)', $selector, self::BG_LAZYLOADED_CLASS );
			},
			$selectors
		);

		return apply_filters( 'spc_lazyload_bg_lazyload_css', strip_tags( implode( ",\n", $formatted_selectors ) . ' { background-image: none !important; }' ), $selectors );
	}

	/**
	 * Get CSS selectors for background lazy loading.
	 *
	 * @return string[]
	 */
	public function get_lazyload_background_selectors() {
		global $sw_cloudflare_pagecache;

		return array_merge(
			$sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOAD_BG_SELECTORS, [] ),
			Constants::DEFAULT_BG_LAZYLOAD_SELECTORS,
			$this->get_compatibilities_lazyload_background_selectors()
		);
	}

	/**
	 * Check if background lazy loading is enabled.
	 *
	 * @return bool
	 */
	public function is_background_lazyload_enabled() {
		global $sw_cloudflare_pagecache;

		return (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOAD_BG ) === 1;
	}

	public static function get_lazyload_behaviours() {
		return apply_filters(
			'spc_lazyload_behaviours',
			[
				self::LAZY_LOAD_BEHAVIOUR_ALL   => true,
				self::LAZY_LOAD_BEHAVIOUR_FIXED => true,
			]
		);
	}
	/**
	 * Get compatibility selectors for other plugins.
	 *
	 * @return string[]
	 */
	private function get_compatibilities_lazyload_background_selectors() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Keep only the selectors for active plugins.
		$active_compatibilities = array_filter( Constants::COMPAT_BG_LAZYLOAD_SELECTORS, 'is_plugin_active', ARRAY_FILTER_USE_KEY );

		if ( empty( $active_compatibilities ) ) {
			return [];
		}

		// Merge all selectors into a single array & ensure unique selectors.
		// @phpstan-ignore-next-line - false positive, as array is checked above
		return array_unique( array_merge( ...array_values( $active_compatibilities ) ) );
	}

	/**
	 * Check if we're on an AMP page or in the customizer.
	 *
	 * @return bool
	 */
	private function is_amp_or_customizer() {
		return (
			( function_exists( 'amp_is_request' ) && amp_is_request() ) ||
			( function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint() ) ||
			is_customize_preview()
		);
	}
}
