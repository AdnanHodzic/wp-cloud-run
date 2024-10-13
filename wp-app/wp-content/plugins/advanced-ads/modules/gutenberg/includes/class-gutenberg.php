<?php
// phpcs:ignoreFile
/**
 * Gutenberg block registration
 *
 * @package Advanced_Ads
 */

/**
 * Class Advanced_Ads_Gutenberg
 */
class Advanced_Ads_Gutenberg {

	/**
	 * The singleton
	 *
	 * @var Advanced_Ads_Gutenberg
	 */
	private static $instance;

	/**
	 * CSS classes to use on the frontend
	 *
	 * @var string
	 */
	private static $css_class;

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_scripts' ] );
	}

	/**
	 * Register blocks
	 */
	public function init() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// no Gutenberg, Abort.
			return;
		}

		register_block_type(
			'advads/gblock',
			[
				'editor_script_handles' => [ ADVADS_PLUGIN_BASENAME . '/gutenberg-ad' ],
				'editor_style_handles'  => [ ADVADS_PLUGIN_BASENAME . '/gutenberg-ad' ],
				'render_callback'       => [ $this, 'render_ad_selector' ],
			]
		);

		/**
		 * Removes legacy widget from legacy widget block.
		 *
		 * @param string[] $widget_types An array of excluded widget-type IDs.
		 *
		 * @return array
		 */
		add_filter(
			'widget_types_to_hide_from_legacy_widget_block',
			function( $widget_types ) {
				$widget_types[] = 'advads_ad_widget';

				return $widget_types;
			}
		);
	}

	/**
	 * Register back end scripts
	 *
	 * @return void
	 */
	public function register_scripts() {
		if ( ! function_exists( 'register_block_type' ) ) {
			// no Gutenberg, Abort.
			return;
		}

		wp_register_script(
			ADVADS_PLUGIN_BASENAME . '/gutenberg-ad',
			ADVADS_BASE_URL . 'modules/gutenberg/assets/advanced-ads.block.js',
			[ 'wp-dom-ready', 'wp-blocks', 'wp-element' ],
			ADVADS_VERSION,
			false
		);

		$model = Advanced_Ads::get_instance()->get_model();

		$all_ads    = Advanced_Ads::get_ads(
			[
				'post_status' => [ 'publish' ],
				'orderby'     => 'title',
				'order'       => 'ASC',
			]
		);
		$all_groups = $model->get_ad_groups();

		$ads        = [];
		$groups     = [];
		$placements = [];

		foreach ( $all_ads as $ad ) {
			$ads[] = [
				'id'    => $ad->ID,
				'title' => $ad->post_title,
			];
		}

		foreach ( $all_groups as $gr ) {
			$groups[] = [
				'id'   => $gr->term_id,
				'name' => $gr->name,
			];
		}

		foreach ( Advanced_Ads::get_instance()->get_model()->get_ad_placements_array() as $key => $value ) {
			if ( in_array( $value['type'], [ 'sidebar_widget', 'default' ], true ) ) {
				$placements[] = [
					'id'   => $key,
					'name' => $value['name'],
				];
			}
		}

		ksort( $placements );

		if ( empty( $placements ) ) {
			$placements = false;
		}

		$i18n = [
			'--empty--'  => __( '--empty--', 'advanced-ads' ),
			'advads'     => __( 'Advanced Ads', 'advanced-ads' ),
			'ads'        => __( 'Ads', 'advanced-ads' ),
			'adGroups'   => __( 'Ad Groups', 'advanced-ads' ),
			'placements' => __( 'Placements', 'advanced-ads' ),
			'width'      => __( 'Width', 'advanced-ads' ),
			'height'     => __( 'Height', 'advanced-ads' ),
			'size'       => __( 'Size', 'advanced-ads' ),
			'alignment'  => __( 'Alignment', 'advanced-ads' ),
		];

		$inline_script = wp_json_encode(
			[
				'ads'        => $ads,
				'groups'     => $groups,
				'placements' => $placements,
				'editLinks'  => [
					'group'     => admin_url( 'admin.php?page=advanced-ads-groups' ),
					'placement' => admin_url( 'admin.php?page=advanced-ads-placements' ),
					'ad'        => admin_url( 'post.php?post=%ID%&action=edit' ),
				],
				'imagesUrl'  => ADVADS_BASE_URL . 'modules/gutenberg/assets/img/',
				'i18n'       => $i18n,
				'textFlow'   => [
					'default'     => [
						'label'       => __( "Theme's default", 'advanced-ads' ),
						'description' => __( 'The ad will behave as predefined by the theme.', 'advanced-ads' ),
					],
					'float-left'  => [
						'label'       => __( "Float left", 'advanced-ads' ),
						'description' => __( 'Text will wrap around the ad and its margin.', 'advanced-ads' ),
					],
					'float-right' => [
						'label'       => __( "Float right", 'advanced-ads' ),
						'description' => __( 'Text will wrap around the ad and its margin.', 'advanced-ads' ),
					],
					'block-left'  => [
						'label'       => __( "Block left", 'advanced-ads' ),
						'description' => __( 'Text will continue after the ad and its margin.', 'advanced-ads' ),
					],
					'block-right' => [
						'label'       => __( "Block right", 'advanced-ads' ),
						'description' => __( 'Text will continue after the ad and its margin.', 'advanced-ads' ),
					],
					'center'      => [
						'label'       => __( "Centered", 'advanced-ads' ),
						'description' => __( 'Text will continue after the ad and its margin.', 'advanced-ads' ),
					],
				],
			]
		);

		// put the inline code with the global variable right before the block's JS file.
		wp_add_inline_script( ADVADS_PLUGIN_BASENAME . '/gutenberg-ad', 'var advadsGutenberg = ' . $inline_script, 'before' );
		wp_enqueue_script( ADVADS_PLUGIN_BASENAME . '/gutenberg-ad' );

		wp_enqueue_style(
			ADVADS_PLUGIN_BASENAME . '/gutenberg-ad',
			ADVADS_BASE_URL . 'modules/gutenberg/assets/block.css',
			[],
			ADVADS_VERSION
		);
	}

	/**
	 * Server side rendering for single ad block
	 *
	 * @param array $attr Block's attributes.
	 */
	public static function render_ad_selector( $attr ) {
		ob_start();

		if ( ! isset( $attr['itemID'] ) ) {
			ob_end_clean();

			return '';
		}

		$output = [
			'output' => [
				'class' => ! empty( $attr['className'] ) ? array_filter( explode( ' ', $attr['className'] ) ) : [],
			],
		];

		if ( isset( $attr['fixed_widget'] ) ) {
			$output['wrapper_attrs']['data-fixed_widget'] = esc_attr( $attr['fixed_widget'] );
		}

		if ( ! empty( $attr['width'] ) ) {
			$output['output']['wrapper_attrs']['style']['width'] = absint( $attr['width'] ) . 'px';
		}

		if ( ! empty( $attr['height'] ) ) {
			$output['output']['wrapper_attrs']['style']['height'] = absint( $attr['height'] ) . 'px';
		}

		$align           = esc_attr( $attr['align'] ?? 'default' );
		$after_ad_filter = function( $output, $ad ) {
			return $output . '<br style="clear: both; display: block; float: none;">';
		};

		if ( 0 === strpos( $align, 'block' ) ) {
			add_filter( 'advanced-ads-ad-output', $after_ad_filter, 10, 2 );
		}

		switch ( $align ) {
			case 'float-left':
			case 'block-left':
				$output['output']['wrapper_attrs']['style']['float'] = 'left';
				break;
			case 'float-right':
			case 'block-right':
				$output['output']['wrapper_attrs']['style']['float'] = 'right';
				break;
			case 'center':
				$output['output']['wrapper_attrs']['style']['margin-left']  = 'auto';
				$output['output']['wrapper_attrs']['style']['margin-right'] = 'auto';
				$output['output']['wrapper_attrs']['style']['text-align']   = 'center';
				break;
			default:
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- we can't escape ad output without potentially breaking ads
		if ( 0 === strpos( $attr['itemID'], 'ad_' ) ) {
			echo get_ad( absint( substr( $attr['itemID'], 3 ) ), $output );
		} elseif ( 0 === strpos( $attr['itemID'], 'group_' ) ) {
			echo get_ad_group( substr( $attr['itemID'], 6 ), $output );
		} elseif ( 0 === strpos( $attr['itemID'], 'place_' ) ) {
			echo get_ad_placement( substr( $attr['itemID'], 6 ), $output );
		}

		// phpcs:enable

		return ob_get_clean();
	}

	/**
	 * Return the unique instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
