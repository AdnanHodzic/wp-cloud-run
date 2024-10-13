<?php

namespace GRIM_SG;

class PostSettings {
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_meta' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_plugin_sidebar_block' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
		add_filter( 'user_has_cap', array( $this, 'allow_edit_post_meta' ), 10, 3 );
	}

	/**
	 * Registers protected Post Meta fields.
	 */
	public function register_post_meta() {
		register_post_meta(
			'',
			'_sitemap_exclude',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'boolean',
			)
		);
		register_post_meta(
			'',
			'_sitemap_priority',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
		register_post_meta(
			'',
			'_sitemap_frequency',
			array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'string',
			)
		);
	}

	/**
	 * Registers Gutenberg Plugin Sidebar Block.
	 */
	public function register_plugin_sidebar_block() {
		$assets_file = GRIM_SG_PATH . '/assets/gutenberg/build/plugin-sidebar.asset.php';

		if ( file_exists( $assets_file ) ) {
			$assets = include $assets_file;

			wp_enqueue_script(
				'sitemap-settings',
				GRIM_SG_URL . 'assets/gutenberg/build/plugin-sidebar.js',
				$assets['dependencies'],
				$assets['version'],
				true
			);
			wp_localize_script(
				'sitemap-settings',
				'sitemapSettings',
				array(
					'isProEnabled' => sgg_pro_enabled(),
				)
			);
		}
	}

	/**
	 * Registers Post Metabox.
	 */
	public function add_meta_box() {
		$public_post_types = get_post_types(
			array(
				'public' => true,
			)
		);

		foreach ( $public_post_types as $post_type ) {
			add_meta_box(
				'sgg_pro_meta_box',
				esc_html__( 'XML Sitemap Options', 'xml-sitemap-generator-for-google' ),
				array( $this, 'meta_box_render' ),
				$post_type,
			);
		}
	}

	/**
	 * Renders Post Metabox.
	 */
	public function meta_box_render( $post ) {
		load_template(
			GRIM_SG_PATH . '/templates/post-meta-box.php',
			false,
			compact( 'post' )
		);
	}

	/**
	 * Saves Post Metabox.
	 */
	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['sgg_pro_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['sgg_pro_meta_box_nonce'], 'sgg_pro_meta_box' ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_posts' ) || ! sgg_pro_enabled() ) {
			return;
		}

		update_post_meta( $post_id, '_sitemap_exclude', $_POST['_sitemap_exclude'] ?? '' );

		if ( isset( $_POST['_sitemap_priority'] ) ) {
			update_post_meta( $post_id, '_sitemap_priority', sanitize_text_field( $_POST['_sitemap_priority'] ) );
		}

		if ( isset( $_POST['_sitemap_frequency'] ) ) {
			update_post_meta( $post_id, '_sitemap_frequency', sanitize_text_field( $_POST['_sitemap_frequency'] ) );
		}
	}

	/**
	 * Allow editing protected Post Meta fields.
	 */
	public function allow_edit_post_meta( $allcaps, $caps, $args ) {
		if ( ! empty( $args[0] ) && ! empty( $args[3] ) && 'edit_post_meta' === $args[0]
			&& in_array( $args[3], array( '_sitemap_exclude', '_sitemap_priority', '_sitemap_frequency' ), true ) ) {
			$allcaps['edit_post_meta'] = current_user_can( 'edit_posts' );
		}

		return $allcaps;
	}
}
