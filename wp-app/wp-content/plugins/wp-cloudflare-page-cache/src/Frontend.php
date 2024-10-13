<?php

namespace SPC;

/**
 * Frontend module.
 */
class Frontend implements Module_Interface {
	private const BG_LAZYLOADED_CLASS = 'spc-bg-lazyloaded';

	/**
	 * Initialize the module.
	 *
	 * @return void
	 */
	public function init() {
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
		wp_add_inline_style( 'spc_bg_lazy', $this->get_background_lazy_css() );
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

		$selectors = array_map(
			function ( $selector ) {
				return sprintf( 'html %s:not(.%s)', $selector, self::BG_LAZYLOADED_CLASS );
			},
			$selectors 
		);

		return strip_tags( implode( ",\n", $selectors ) . ' { background-image: none !important; }' );
	}

	/**
	 * Get CSS selectors for background lazy loading.
	 *
	 * @return string[]
	 */
	private function get_lazyload_background_selectors() {
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
	private function is_background_lazyload_enabled() {
		global $sw_cloudflare_pagecache;

		return (int) $sw_cloudflare_pagecache->get_single_config( Constants::SETTING_LAZY_LOAD_BG ) === 1;
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

		// Merge all selectors into a single array & ensure unique selectors.
		return array_unique( array_merge( ...array_values( $active_compatibilities ) ) );
	}
}
