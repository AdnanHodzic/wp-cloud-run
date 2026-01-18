<?php

namespace SPC\Modules;

use SPC\Constants;
use SPC\Models\Asset_Rules;
use SPC\Services\Settings_Store;
use SPC\Services\Asset_Exclusion_Handler;
use SPC\Utils\Assets_Handler;

class Assets_Manager implements Module_Interface {

	public const ASSETS_MANAGER_QUERY_VAR = 'spc_assets';

	public function init() {
		// Create database table for asset rules
		add_action( 'spc_after_settings_update', array( $this, 'register_database_table' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 11 );

		// Apply rules early in WordPress lifecycle
		add_action( 'wp', array( $this, 'apply_asset_rules' ), 1 );

		// Hook into WordPress asset processing
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_disabled_external_assets' ), 999 );
		add_action( 'wp_print_styles', array( $this, 'remove_disabled_inline_assets' ), 1 );
		add_action( 'wp_print_scripts', array( $this, 'remove_disabled_inline_assets' ), 1 );
	}

	/**
	 * Register the database table for storing asset rules.
	 */
	public function register_database_table() {
		if ( Settings_Store::get_instance()->get( Constants::SETTING_ENABLE_ASSETS_MANAGER ) ) {
			Asset_Rules::register_database_table();
		}
	}

	/**
	 * Enqueue assets manager styles and scripts.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_load_assets_manager() ) {
			return;
		}

		Assets_Handler::enqueue_style( 'spc-assets-manager', 'assets-manager' );
		Assets_Handler::enqueue_script( 'spc-assets-manager', 'assets-manager', [], $this->get_localization(), 'SPCAssetManager' );
	}

	/**
	 * Check need to load the assets manager.
	 */
	private function should_load_assets_manager() {
		if ( ! isset( $_GET[ self::ASSETS_MANAGER_QUERY_VAR ] ) || 'yes' !== $_GET[ self::ASSETS_MANAGER_QUERY_VAR ] ) {
			return false;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( ! Settings_Store::get_instance()->get( Constants::SETTING_ENABLE_ASSETS_MANAGER ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Localize the data for the assets manager.
	 *
	 * @return array
	 */
	private function get_localization() {
		$current_context = $this->get_current_page_context();
		return apply_filters(
			'spc_asset_manager_localization',
			array(
				'api'               => rest_url( Rest_Server::REST_NAMESPACE ),
				'nonce'             => wp_create_nonce( 'wp_rest' ),

				'assets'            => $this->get_current_page_assets(),
				'currentContext'    => $current_context,
				'availableContexts' => $this->get_available_contexts( $current_context ),
				'existingRules'     => $this->get_existing_asset_rules(),
				'otherExclusions'   => ( new Asset_Exclusion_Handler() )->get_other_context_exclusions(),
				'cssURL'            => Assets_Handler::get_style_url( 'assets-manager' ),
			)
		);
	}


	/**
	 * Get existing assets rules from database.
	 *
	 * @return array
	 */
	private function get_existing_asset_rules() {
		$results = Asset_Rules::get_asset_rules();
		$rules   = [];

		foreach ( $results as $asset_data ) {
			$asset_rules = json_decode( $asset_data->rules, true );

			if ( ! empty( $asset_rules ) && is_array( $asset_rules ) ) {
				$rules[ $asset_data->asset_hash ] = $asset_rules;
			}
		}

		return $rules;
	}

	/**
	 * Detect all page assets (enqueued files + inline content)
	 *
	 * @return array {
	 *  'handle' => 'handle',
	 *  'asset_hash' => 'asset_hash',
	 *  'name' => 'name',
	 *  'asset_type' => 'asset_type',
	 *  'origin_type' => 'origin_type',
	 *  'asset_url' => 'asset_url',
	 * }
	 */
	private function get_current_page_assets() {
		global $wp_styles, $wp_scripts;

		$assets = [];

		// Process CSS assets
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( $this->is_spc_asset( $handle ) ) {
					continue;
				}

				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					$style = $wp_styles->registered[ $handle ];

					// Enqueued file
					if ( ! empty( $style->src ) ) {
						$assets[] = $this->format_asset_data( $handle, $style->src, 'css', 'file', $style );
					}

					// Inline CSS (after)
					if ( ! empty( $style->extra['after'] ) ) {
						$inline_css = implode( "\n", $style->extra['after'] );
						if ( trim( $inline_css ) ) {
							$assets[] = $this->format_inline_asset_data( $handle . '-inline-after', $inline_css, 'css', $handle );
						}
					}

					// Inline CSS (before)
					if ( ! empty( $style->extra['before'] ) ) {
						$inline_css = implode( "\n", $style->extra['before'] );
						if ( trim( $inline_css ) ) {
							$assets[] = $this->format_inline_asset_data( $handle . '-inline-before', $inline_css, 'css', $handle );
						}
					}
				}
			}
		}

		// Process JS assets
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( $this->is_spc_asset( $handle ) ) {
					continue;
				}

				if ( isset( $wp_scripts->registered[ $handle ] ) ) {
					$script = $wp_scripts->registered[ $handle ];

					// Enqueued file
					if ( ! empty( $script->src ) ) {
						$assets[] = $this->format_asset_data( $handle, $script->src, 'js', 'file', $script );
					}

					// Localized data
					if ( ! empty( $script->extra['data'] ) ) {
						$localized_js = $script->extra['data'];
						if ( trim( $localized_js ) ) {
							$assets[] = $this->format_inline_asset_data( $handle . '-localized', $localized_js, 'js', $handle );
						}
					}

					// Inline JS (after)
					if ( ! empty( $script->extra['after'] ) ) {
						$inline_js = implode( "\n", $script->extra['after'] );
						if ( trim( $inline_js ) ) {
							$assets[] = $this->format_inline_asset_data( $handle . '-inline-after', $inline_js, 'js', $handle );
						}
					}

					// Inline JS (before)
					if ( ! empty( $script->extra['before'] ) ) {
						$inline_js = implode( "\n", $script->extra['before'] );
						if ( trim( $inline_js ) ) {
							$assets[] = $this->format_inline_asset_data( $handle . '-inline-before', $inline_js, 'js', $handle );
						}
					}
				}
			}
		}

		return $assets;
	}

	private function is_spc_asset( $handle ) {
		$prefixes = array( 'spc', 'swcfpc' );

		foreach ( $prefixes as $prefix ) {
			if ( strpos( $handle, $prefix ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Format enqueued asset data
	 *
	 * @param string $handle The handle of the asset.
	 * @param string $src The source URL of the asset.
	 * @param string $type The type of the asset (css/js).
	 * @param string $asset_type The type of asset (file/inline).
	 * @param object $wp_object The WordPress object containing asset data.
	 *
	 * @return array Structured asset data
	 */
	private function format_asset_data( $handle, $src, $type, $asset_type, $wp_object ) {

		$src = str_replace( home_url(), '', $src );

		return [
			'handle'        => $handle,
			'asset_hash'    => $this->generate_asset_hash( $handle, $src, $asset_type ),
			'name'          => basename( $src ),
			'asset_type'    => $type,
			'origin_type'   => $this->determine_origin_type( $src ),
			'asset_url'     => $src,
			'size'          => $this->get_asset_size( $src ),
			'is_inline'     => false,
			'parent_handle' => null,
			'content'       => null,
			'dependencies'  => $wp_object->deps ?? [],
			'version'       => $wp_object->ver ?? 'none',
			'category'      => $this->get_asset_category( $src ),
		];
	}

	/**
	 * Format inline asset data
	 *
	 * @param string $handle The handle of the inline asset.
	 * @param string $content The content of the inline asset.
	 * @param string $type The type of the asset (css/js).
	 * @param string $parent_handle The handle of the parent asset.
	 *
	 * @return array Structured inline asset data
	 */
	private function format_inline_asset_data( $handle, $content, $type, $parent_handle ) {
		return [
			'handle'          => $handle,
			'asset_hash'      => $this->generate_asset_hash( $handle, $content, 'inline' ),
			'name'            => 'Inline ' . strtoupper( $type ) . ' (' . $parent_handle . ')',
			'asset_type'      => $type,
			'origin_type'     => 'inline',
			'asset_url'       => 'inline:' . $parent_handle . ':' . str_replace( $parent_handle . '-', '', $handle ),
			'size'            => $this->format_bytes( strlen( $content ) ),
			'is_inline'       => true,
			'parent_handle'   => $parent_handle,
			'content'         => $content,
			'content_hash'    => md5( $content ),
			'content_preview' => $this->get_content_preview( $content, 100 ),
			'category'        => 'custom',
		];
	}

	/**
	 * Get asset category based on source URL.
	 *
	 * @param string $src The source URL of the asset.
	 * @return string The category of the asset.
	 */
	private function get_asset_category( $src ) {
		if ( strpos( $src, 'wp-content/themes/' ) !== false ) {
			return 'theme';
		} elseif ( strpos( $src, 'wp-content/plugins/' ) !== false ) {
			return 'plugin';
		} elseif ( strpos( $src, 'wp-includes/' ) !== false || strpos( $src, 'wp-admin/' ) !== false ) {
			return 'core';
		} elseif ( strpos( $src, 'http' ) === 0 && strpos( $src, home_url() ) === false ) {
			return 'external';
		}
		return 'custom';
	}

	/**
	 * Generate asset hash
	 *
	 * @param string $handle The handle of the asset.
	 * @param string $source The source URL or content of the asset.
	 * @param string $type The type of the asset (file/inline).
	 *
	 * @return string The generated hash for the asset.
	 */
	private function generate_asset_hash( $handle, $source, $type ) {
		if ( $type === 'file' ) {
			$identifier = 'file:' . $handle . ':' . $source;
		} else {
			$identifier = 'inline:' . $handle . ':' . md5( $source );
		}
		return md5( $identifier );
	}

	/**
	 * Get current page context for rule matching
	 */
	private function get_current_page_context() {
		$context = [
			'url'          => $_SERVER['REQUEST_URI'],
			'title'        => wp_title( '', false ),
			'pageType'     => null,
			'subType'      => null,
			'postType'     => null,
			'taxonomyType' => null,
			'taxonomyId'   => null,
			'taxonomySlug' => null,
			'authorId'     => null,
			'postId'       => null,
		];

		if ( is_singular() ) {
			$context['pageType'] = 'is_singular';
			$context['postType'] = get_post_type();
			$post_type_object    = get_post_type_object( $context['postType'] );

			if ( $post_type_object ) {
				if ( isset( $post_type_object->labels->singular_name ) ) {
					$context['postTypeLabel'] = $post_type_object->labels->singular_name;
				} else {
					$context['postTypeLabel'] = ucfirst( $context['postType'] );
				}
			}

			$context['postTypeSlug'] = get_post_field( 'post_name' );
			$context['postId']       = get_the_ID();
			$context['entity']       = is_page() ? __( 'page', 'wp-cloudflare-page-cache' ) : __( 'post', 'wp-cloudflare-page-cache' );
		} elseif ( is_archive() ) {
			$context['pageType'] = 'is_archive';

			if ( is_tax() || is_category() || is_tag() ) {
				$context['subType']      = 'is_tax';
				$term                    = get_queried_object();
				$context['taxonomyType'] = $term->taxonomy;
				$taxonomy_object         = get_taxonomy( $context['taxonomyType'] );
				if ( $taxonomy_object && isset( $taxonomy_object->labels->singular_name ) ) {
					$context['taxonomyLabel'] = $taxonomy_object->labels->singular_name;
				} else {
					$context['taxonomyLabel'] = ucfirst( $$context['taxonomyType'] );
				}

				$context['taxonomyId']   = $term->term_id;
				$context['taxonomySlug'] = $term->slug;
			} elseif ( is_author() ) {
				$context['subType']    = 'is_author';
				$context['authorId']   = get_query_var( 'author' );
				$context['authorName'] = get_queried_object()->display_name;
			} elseif ( is_date() ) {
				$context['subType']     = 'is_date';
				$context['currentDate'] = get_the_date( 'Y-m-d' );
			}
		} elseif ( is_search() ) {
			$context['pageType'] = 'is_search';
		} elseif ( is_404() ) {
			$context['pageType'] = 'is_404';
		} elseif ( is_front_page() ) {
			$context['pageType'] = 'is_front_page';
			$context['title']    = __( 'Front Page', 'wp-cloudflare-page-cache' );
		} elseif ( is_home() ) {
			$context['pageType'] = 'is_home';
			$context['title']    = __( 'Home Page', 'wp-cloudflare-page-cache' );
		}

		return $context;
	}

	/**
	 * Generate available context options based on current page
	 *
	 * @param array $current_context The current page context.
	 * @return array available contexts
	 */
	private function get_available_contexts( $current_context ) {
		$location_contexts = [
			[
				'key'         => 'global',
				'label'       => __( 'Entire Website', 'wp-cloudflare-page-cache' ),
				'description' => __( 'Disable on all pages across the site', 'wp-cloudflare-page-cache' ),
				'category'    => 'global',
				'saveAs'      => 'global',
			],
		];

		// Add context-specific options based on page type
		if ( $current_context['pageType'] === 'is_singular' ) {
			$location_contexts[] = [
				'key'         => 'current_singular',
				'label'       => sprintf(
					// translators: %s is the post type.
					__( 'This %s', 'wp-cloudflare-page-cache' ),
					$current_context['postTypeLabel']
				),
				'description' => sprintf(
					/* translators: 1: Post type slug, 2: Entity name */
					__( 'Disable only on "%1$s" %2$s', 'wp-cloudflare-page-cache' ),
					$current_context['postTypeSlug'],
					$current_context['entity']
				),
				'category'    => 'singular',
				'saveAs'      => 'is_singular:id:' . $current_context['postId'],
			];

			$location_contexts[] = [
				'key'         => 'all_post_type',
				'label'       => sprintf(
					'page' === $current_context['postType']
						? __( 'All Pages', 'wp-cloudflare-page-cache' )
						// translators: %s is post type lable.
						: __( 'All %s Pages', 'wp-cloudflare-page-cache' ),
					$current_context['postTypeLabel']
				),
				'description' => sprintf(
					'page' === $current_context['postType']
						? __( 'Disable on all single pages', 'wp-cloudflare-page-cache' )
						// translators: %s is post type lable.
						: __( 'Disable on all single %s pages', 'wp-cloudflare-page-cache' ),
					lcfirst( $current_context['postTypeLabel'] )
				),
				'category'    => 'singular',
				'saveAs'      => 'is_singular:post_type:' . $current_context['postType'],
			];
		}

		if ( $current_context['pageType'] === 'is_archive' && $current_context['subType'] === 'is_tax' ) {
			$location_contexts[] = [
				'key'         => 'current_taxonomy_term',
				'label'       => sprintf(
					// translators: %1$s is the taxonomy type, %2$s is the taxonomy slug.
					__( 'This %1$s (%2$s)', 'wp-cloudflare-page-cache' ),
					$current_context['taxonomyLabel'],
					$current_context['taxonomySlug']
				),
				'description' => sprintf(
					// translators: %1$s is the taxonomy slug, %2$s is the taxonomy type.
					__( 'Disable only on "%1$s" %2$s archive', 'wp-cloudflare-page-cache' ),
					$current_context['taxonomySlug'],
					lcfirst( $current_context['taxonomyLabel'] )
				),
				'category'    => 'archive',
				'saveAs'      => 'is_tax:id:' . $current_context['taxonomyId'],
			];

			$location_contexts[] = [
				'key'         => 'all_taxonomy_type',
				'label'       => sprintf(
					// translators: %s is the taxonomy type.
					__( 'All %s Archives', 'wp-cloudflare-page-cache' ),
					$current_context['taxonomyLabel']
				),
				'description' => sprintf(
					// translators: %s is the taxonomy type.
					__( 'Disable on all %s archive pages', 'wp-cloudflare-page-cache' ),
					lcfirst( $current_context['taxonomyLabel'] )
				),
				'category'    => 'archive',
				'saveAs'      => 'is_tax:taxonomy:' . $current_context['taxonomyType'],
			];
		}

		if ( $current_context['subType'] === 'is_author' ) {
			$location_contexts[] = [
				'key'         => 'current_author',
				'label'       => __( 'This Author Archive', 'wp-cloudflare-page-cache' ),
				'description' => sprintf(
					// translators: %s is the author name.
					__( 'Disable only on "%s" author archive', 'wp-cloudflare-page-cache' ),
					$current_context['authorName'],
				),
				'category'    => 'archive',
				'saveAs'      => "is_author:id:{$current_context['authorId']}",
			];

			$location_contexts[] = [
				'key'         => 'all_author_archives',
				'label'       => __( 'All Author Archives', 'wp-cloudflare-page-cache' ),
				'description' => __( 'Disable on all author archive pages', 'wp-cloudflare-page-cache' ),
				'category'    => 'archive',
				'saveAs'      => 'is_author:all',
			];
		}

		if ( $current_context['subType'] === 'is_date' ) {
			$location_contexts[] = [
				'key'         => 'current_date_archive',
				'label'       => sprintf(
					// translators: %s is date.
					__( 'This %s Archive', 'wp-cloudflare-page-cache' ),
					$current_context['currentDate']
				),
				'description' => sprintf(
					// translators: %s is current date.
					__( 'Disable only on "%s" date archive', 'wp-cloudflare-page-cache' ),
					$current_context['currentDate']
				),
				'category'    => 'archive',
				'saveAs'      => "is_date:{$current_context['currentDate']}",
			];

			$location_contexts[] = [
				'key'         => 'all_date_archives',
				'label'       => __( 'All Date Archives', 'wp-cloudflare-page-cache' ),
				'description' => __( 'Disable on all date-based archive pages', 'wp-cloudflare-page-cache' ),
				'category'    => 'archive',
				'saveAs'      => 'is_date:all',
			];
		}

		if ( $current_context['pageType'] === 'is_search' ) {
			$location_contexts[] = [
				'key'         => 'all_search_pages',
				'label'       => __( 'All Search Pages', 'wp-cloudflare-page-cache' ),
				'description' => __( 'Disable on all search result pages', 'wp-cloudflare-page-cache' ),
				'category'    => 'special',
				'saveAs'      => 'is_search:all',
			];
		}

		if ( $current_context['pageType'] === 'is_404' ) {
			$location_contexts[] = [
				'key'         => 'all_404_pages',
				'label'       => __( 'All 404 Pages', 'wp-cloudflare-page-cache' ),
				'description' => __( 'Disable on all page not found errors', 'wp-cloudflare-page-cache' ),
				'category'    => 'special',
				'saveAs'      => 'is_404:all',
			];
		}

		if ( $current_context['pageType'] === 'is_front_page' ) {
			$location_contexts[] = [
				'key'         => 'front_page',
				'label'       => __( 'Front Page', 'wp-cloudflare-page-cache' ),
				'description' => __( 'Disable on the main homepage', 'wp-cloudflare-page-cache' ),
				'category'    => 'special',
				'saveAs'      => 'is_front_page:true',
			];
		}

		if ( $current_context['pageType'] === 'is_home' ) {
			$location_contexts[] = [
				'key'         => 'blog_homepage',
				'label'       => __( 'Blog Home page', 'wp-cloudflare-page-cache' ),
				'description' => __( 'Disable on the main blog page', 'wp-cloudflare-page-cache' ),
				'category'    => 'special',
				'saveAs'      => 'is_home:true',
			];
		}

		// Add user state context
		$user_state_contexts[] = [
			'key'         => 'non_logged_in',
			'label'       => __( 'Non-Logged In Users', 'wp-cloudflare-page-cache' ),
			'description' => __( 'Apply the above location rules only to visitors who are not logged in', 'wp-cloudflare-page-cache' ),
			'category'    => 'user_state',
			'saveAs'      => 'is_logged_in:false',
		];

		$contexts['locationContexts']  = $location_contexts;
		$contexts['userStateContexts'] = $user_state_contexts;

		return $contexts;
	}

	/**
	 * Determine asset origin type
	 *
	 * @param string $src The source URL of the asset.
	 * @return string The origin type of the asset.
	 */
	private function determine_origin_type( $src ) {
		if ( empty( $src ) ) {
			return 'inline';
		}

		if ( strpos( $src, 'wp-includes/' ) !== false ) {
			return 'WordPress Core';
		}
		if ( strpos( $src, 'wp-admin/' ) !== false ) {
			return 'WordPress Core';
		}

		if ( strpos( $src, 'wp-content/themes/' ) !== false ) {
			$theme = wp_get_theme();
			return $theme->get( 'Name' ) ? $theme->get( 'Name' ) : 'Theme';
		}
		if ( strpos( $src, 'wp-content/plugins/' ) !== false ) {

			$plugins = get_plugins();
			foreach ( $plugins as $path => $details ) {
				$needle = explode( '/', $path );
				$needle = reset( $needle );

				if ( false !== strpos( $src, $needle ) ) {
					return $details['Name'];
				}
			}
		}

		if ( strpos( $src, 'http' ) === 0 && strpos( $src, home_url() ) === false ) {
			return 'external';
		}

		return 'core';
	}

	/**
	 * Get asset file size
	 *
	 * @param string $src The source URL of the asset.
	 * @return string file size.
	 */
	private function get_asset_size( $src ) {
		if ( empty( $src ) || 0 === strpos( $src, 'http://' ) || 0 === strpos( $src, 'https://' ) ) {
			return __( 'Unknown', 'wp-cloudflare-page-cache' );
		}

		$file_path = str_replace( home_url(), ABSPATH, $src );
		if ( false === strpos( $src, ABSPATH ) ) {
			$file_path = ABSPATH . str_replace( home_url(), '', $src );
		}
		if ( file_exists( $file_path ) ) {
			return $this->format_bytes( filesize( $file_path ) );
		}

		return __( 'Unknown', 'wp-cloudflare-page-cache' );
	}

	/**
	 * Format bytes to human readable
	 *
	 * @param int $bytes The number of bytes to format.
	 * @return string Human readable file size
	 */
	private function format_bytes( $bytes ) {
		if ( $bytes == 0 ) {
			return '0B';
		}

		$units = [ 'B', 'KB', 'MB', 'GB' ];
		$pow   = floor( log( $bytes ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );

		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 1 ) . $units[ $pow ];
	}

	/**
	 * Get content preview for inline assets
	 *
	 * @param  string $content    The content to preview.
	 * @param  int    $max_length The maximum length of the preview.
	 * @return string The content preview.
	 */
	private function get_content_preview( $content, $max_length = 100 ) {
		$content = trim( $content );
		if ( strlen( $content ) <= $max_length ) {
			return $content;
		}

		return substr( $content, 0, $max_length ) . '...';
	}

	/**
	 * Apply asset disable rules on page load
	 */
	public function apply_asset_rules() {
		if ( ! Settings_Store::get_instance()->get( Constants::SETTING_ENABLE_ASSETS_MANAGER ) ) {
			return;
		}

		// Get current page contexts
		$current_contexts = $this->get_current_page_contexts_for_matching();
		$is_logged_in     = is_user_logged_in();

		// Get all assets with rules
		$rules = Asset_Rules::get_assets_with_applicable_rules();

		$disabled_assets = [];

		foreach ( $rules as $row ) {
			$asset_rules = json_decode( $row->rules, true );

			if ( ! is_array( $asset_rules ) ) {
				continue;
			}

			foreach ( $asset_rules as $rule ) {
				if ( $this->should_disable_asset( $rule, $current_contexts, $is_logged_in ) ) {
					$disabled_assets[] = [
						'hash'         => $row->asset_hash,
						'name'         => $row->asset_name,
						'type'         => $row->asset_type,
						'url'          => $row->asset_url,
						'rule_matched' => $rule,
					];
					break;
				}
			}
		}

		// Store for use in dequeue functions
		global $spc_disabled_assets;
		$spc_disabled_assets = $disabled_assets;
	}

	/**
	 * Check if asset should be disabled based on rule
	 *
	 * @param string $rule The rule to check.
	 * @param array  $current_contexts The current page contexts.
	 * @param bool   $is_logged_in Whether the user is logged in.
	 * @return bool True if the asset should be disabled, false otherwise.
	 */
	private function should_disable_asset( $rule, $current_contexts, $is_logged_in ) {
		// Handle user state rules
		if ( $rule === 'is_logged_in:false' && ! $is_logged_in ) {
			return true;
		}
		if ( $rule === 'is_logged_in:true' && $is_logged_in ) {
			return true;
		}

		// Handle location rules
		return in_array( $rule, $current_contexts, true );
	}

	/**
	 * Get current page contexts for rule matching
	 *
	 * @return array Contexts for the current page
	 */
	private function get_current_page_contexts_for_matching() {
		$contexts = [ 'global' ];

		if ( is_singular() ) {
			$contexts[] = 'is_singular:id:' . get_the_ID();
			$contexts[] = 'is_singular:post_type:' . get_post_type();
		} elseif ( is_archive() ) {
			if ( is_tax() || is_category() || is_tag() ) {
				$term       = get_queried_object();
				$contexts[] = 'is_tax:id:' . $term->term_id;
				$contexts[] = 'is_tax:taxonomy:' . $term->taxonomy;
			} elseif ( is_author() ) {
				$author_id  = get_query_var( 'author' );
				$contexts[] = 'is_author:id:' . $author_id;
				$contexts[] = 'is_author:all';
			} elseif ( is_date() ) {
				$contexts[] = 'is_date:' . current_time( 'Y-m-d' );
				$contexts[] = 'is_date:all';
			}
		} elseif ( is_search() ) {
			$contexts[] = 'is_search:all';
		} elseif ( is_404() ) {
			$contexts[] = 'is_404:all';
		} elseif ( is_front_page() ) {
			$contexts[] = 'is_front_page:true';
		} elseif ( is_home() ) {
			$contexts[] = 'is_home:true';
		}

		return $contexts;
	}

	/**
	 * Dequeue disabled external assets
	 */
	public function dequeue_disabled_external_assets() {
		global $spc_disabled_assets;

		if ( empty( $spc_disabled_assets ) ) {
			return;
		}

		foreach ( $spc_disabled_assets as $asset ) {
			if ( strpos( $asset['url'], 'inline:' ) !== 0 ) {

				$handle = $this->extract_handle_from_url( $asset['url'] );

				if ( empty( $handle ) ) {
					continue; // Skip if handle not found
				}

				if ( $asset['type'] === 'css' ) {
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
				} else {
					wp_dequeue_script( $handle );
					wp_deregister_script( $handle );
				}
			}
		}
	}

	/**
	 * Extract handle from URL
	 *
	 * @param string $url Source url.
	 * @return string The handle if found, empty string otherwise.
	 */
	private function extract_handle_from_url( $url ) {
		global $wp_styles, $wp_scripts;

		if ( false !== strpos( $url, 'css' ) ) {
			if ( $wp_styles->registered ) {
				foreach ( $wp_styles->registered as $registered_handle => $style ) {
					if ( $style->src && false !== strpos( $style->src, $url ) ) {
						return $registered_handle;
					}
				}
			}
		}

		if ( false !== strpos( $url, 'js' ) ) {
			if ( $wp_scripts->registered ) {
				foreach ( $wp_scripts->registered as $registered_handle => $script ) {
					if ( $script->src && false !== strpos( $script->src, $url ) ) {
						return $registered_handle;
					}
				}
			}
		}
		return '';
	}

	/**
	 * Remove disabled inline assets
	 */
	public function remove_disabled_inline_assets() {
		global $spc_disabled_assets, $wp_styles, $wp_scripts;

		if ( empty( $spc_disabled_assets ) ) {
			return;
		}

		foreach ( $spc_disabled_assets as $asset ) {
			if ( strpos( $asset['url'], 'inline:' ) === 0 ) {
				// Parse inline asset URL: inline:handle:type
				$parts = explode( ':', $asset['url'] );
				if ( count( $parts ) >= 3 ) {
					$parent_handle = $parts[1];
					$inline_type   = $parts[2];

					if ( $asset['type'] === 'css' && isset( $wp_styles->registered[ $parent_handle ] ) ) {
						if ( $inline_type === 'inline-after' ) {
							$wp_styles->registered[ $parent_handle ]->extra['after'] = [];
						} elseif ( $inline_type === 'inline-before' ) {
							$wp_styles->registered[ $parent_handle ]->extra['before'] = [];
						}
					} elseif ( $asset['type'] === 'js' && isset( $wp_scripts->registered[ $parent_handle ] ) ) {
						if ( $inline_type === 'inline-after' ) {
							$wp_scripts->registered[ $parent_handle ]->extra['after'] = [];
						} elseif ( $inline_type === 'inline-before' ) {
							$wp_scripts->registered[ $parent_handle ]->extra['before'] = [];
						} elseif ( $inline_type === 'localized' ) {
							$wp_scripts->registered[ $parent_handle ]->extra['data'] = '';
						}
					}
				}
			}
		}
	}
}
