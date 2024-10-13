<?php
/**
 * Assets registry handles the registration of stylesheets and scripts required for plugin functionality.
 *
 * @package AdvancedAds\Framework
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Assets Registry.
 *
 * Script functions:
 *
 * @method void enqueue_script(string $handle)
 * @method void dequeue_script(string $handle)
 * @method void deregister_script(string $handle)
 * @method bool register_script(string $handle, string|false $src, string[] $deps = [], string|bool|null $ver = false, array|bool $args = [])
 * @method bool inline_script(string $handle, string $data, string $position = 'after')
 * @method bool is_script(string $handle, string $status = 'enqueued')
 *
 * Style functions:
 *
 * @method void enqueue_style(string $handle)
 * @method void dequeue_style(string $handle)
 * @method void deregister_style(string $handle)
 * @method bool register_style(string $handle, string|false $src, string[] $deps = [], string|bool|null $ver = false, string $media = 'all')
 * @method bool inline_style(string $handle, string $data, )
 * @method bool is_style(string $handle, string $status = 'enqueued')
 */
abstract class Assets_Registry implements Integration_Interface {

	/**
	 * Base URL for plugin local assets.
	 *
	 * @return string
	 */
	abstract public function get_base_url(): string;

	/**
	 * Prefix to use in handle to make it unique.
	 *
	 * @return string
	 */
	abstract public function get_prefix(): string;

	/**
	 * Version for plugin local assets.
	 *
	 * @return string
	 */
	abstract public function get_version(): string;

	/**
	 * Magic method to catch all calls to.
	 *
	 * @param string $name      The name of the method.
	 * @param array  $arguments The arguments passed to the method.
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		if ( preg_match( '/^(enqueue|dequeue|register|deregister|is|inline)_(script|style)$/', $name, $matches ) ) {
			$action    = $matches[1];
			$type      = $matches[2];
			$handle    = $this->prefix_it( $arguments[0] );
			$func      = $this->resolve_function( $action . '_' . $type );
			$func_args = [ $handle ];

			switch ( $action ) {
				case 'register':
					$func_args[] = $this->resolve_url( $arguments[1] );
					$func_args[] = $arguments[2] ?? [];
					$func_args[] = isset( $arguments[3] ) && ! empty( $arguments[3] ) ? $arguments[3] : $this->get_version();
					$func_args[] = $arguments[4] ?? ( 'script' === $type ? true : 'all' );
					break;
				case 'is':
					$func_args[] = $arguments[1] ?? 'enqueued';
					break;
				case 'inline':
					$func_args[] = $arguments[1] ?? '';
					if ( 'script' === $type ) {
						$func_args[] = $arguments[2] ?? 'after';
					}
					break;
				default:
					break;
			}

			return call_user_func_array( $func, $func_args );
		}
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ], 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 0 );
	}

	/**
	 * Register assets
	 *
	 * @return void
	 */
	public function register_assets(): void {
		$this->register_styles();
		$this->register_scripts();
	}

	/**
	 * Prefix the handle
	 *
	 * @param string $handle Name of the asset.
	 *
	 * @return string
	 */
	public function prefix_it( $handle ): string {
		return $this->get_prefix() . '-' . $handle;
	}

	/**
	 * Register styles
	 *
	 * @return void
	 */
	public function register_styles(): void {}

	/**
	 * Register scripts
	 *
	 * @return void
	 */
	public function register_scripts(): void {}

	/**
	 * Resolves the URL.
	 *
	 * If the provided URL is an absolute URL or protocol-relative URL, it is returned as is.
	 * Otherwise, the base URL is prepended to the relative path.
	 *
	 * @param string $src The source URL.
	 *
	 * @return string The resolved URL.
	 */
	private function resolve_url( $src ): string {
		if ( preg_match( '/^(https?:)?\/\//', $src ) ) {
			return $src;
		}

		return $this->get_base_url() . $src;
	}

	/**
	 * Resolves the function name.
	 *
	 * @param string $name The name of the function.
	 *
	 * @return string
	 */
	private function resolve_function( $name ): string {
		$method_map = [
			'is_script'     => 'script_is',
			'is_style'      => 'style_is',
			'inline_script' => 'add_inline_script',
			'inline_style'  => 'add_inline_style',
		];

		$name = $method_map[ $name ] ?? $name;

		return 'wp_' . $name;
	}
}
