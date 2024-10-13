<?php

/**
 * Class handling the ad positioning and migrating values from previous solutions.
 */
class Advanced_Ads_Ad_Positioning {
	/**
	 * The instance of the current ad.
	 *
	 * @var Advanced_Ads_Ad
	 */
	private $ad;

	/**
	 * The structure of these output options.
	 *
	 * @var array
	 */
	private $positioning = [
		'position' => 'none',
		'clearfix' => false,
		'margin'   => [
			'top'    => 0,
			'left'   => 0,
			'bottom' => 0,
			'right'  => 0,
		],
	];

	/**
	 * Class constructor.
	 *
	 * @param Advanced_Ads_Ad $ad The current ad object.
	 */
	public function __construct( Advanced_Ads_Ad $ad ) {
		$this->ad = $ad;
		$this->migrate_values();
		$this->filter_values();
	}

	/**
	 * Migrate option from a previous solution where floating was an additional setting.
	 *
	 * @return void
	 */
	private function migrate_values() {
		$options = $this->get_options();

		$this->positioning['margin'] = array_merge(
			$this->positioning['margin'],
			array_map( function($value) { return (int)$value; }, $options['margin'] )
		);

		$this->positioning['position'] = $options['position'];
		// instead of having an empty value, set an explicit default.
		if ( empty( $this->positioning['position'] ) ) {
			$this->positioning['position'] = 'none';
			$this->positioning['clearfix'] = false;
		}

		// left, center, right are the old values, if it's none of these we've already migrated.
		if ( ! in_array( $this->positioning['position'], [ 'left', 'center', 'right' ], true ) ) {
			// ensure we get an array with min two elements.
			$position = explode( '_', $this->positioning['position'] . '_' );

			// explicitly set clearfix option.
			$this->positioning['clearfix'] = $position[0] !== 'center' && $position[1] === 'nofloat';

			return;
		}

		if ( $this->positioning['position'] === 'center' ) {
			$this->positioning['position'] = 'center_nofloat';

			return;
		}

		$this->positioning['clearfix'] = ! empty( $options['clearfix'] );
		$this->positioning['position'] .= $this->positioning['clearfix'] ? '_nofloat' : '_float';
	}

	/**
	 * Filter the option value for Advanced_Ads_Ad.
	 * This ensures we don't have to update the whole positioning process but can change only the wp-admin side of things.
	 *
	 * @return void
	 */
	private function filter_values() {
		foreach ( $this->positioning as $key => $value ) {
			add_filter( "advanced-ads-ad-option-output.{$key}", function() use ( $value ) {
				return $value;
			} );

			if ( is_array( $value ) ) {
				foreach ( $value as $sub_key => $sub_value ) {
					add_filter( "advanced-ads-ad-option-output.{$sub_key}", function() use ( $sub_value ) {
						return $sub_value;
					} );
				}
			}
		}
	}

	/**
	 * Set up the positioning options with title, description and icon.
	 *
	 * @return array
	 */
	private function setup_positioning_options() {
		return [
			'default' => [
				'title'       => __( "Theme’s Default", 'advanced-ads' ),
				'description' => __( 'The ad will behave as predefined by the theme.', 'advanced-ads' ),
				'options'     => [
					'none' => [],
				],
			],
			'float'   => [
				'title'       => _x( 'Float', 'Layout options "Text Flow" heading', 'advanced-ads' ),
				'description' => __( 'Text will wrap around the ad and its margin.', 'advanced-ads' ),
				'options'     => [
					'left_float'  => [],
					'right_float' => [],
				],
			],
			'block'   => [
				'title'       => _x( 'Block', 'Layout options "Text Flow" heading', 'advanced-ads' ),
				'description' => __( 'Text will continue after the ad and its margin.', 'advanced-ads' ),
				'options'     => [
					'left_nofloat'   => [
						'img' => 'block-lr',
					],
					'center_nofloat' => [
						'img' => 'block-cntr',
					],
					'right_nofloat'  => [
						'img' => 'block-lr',
					],
				],
			],
		];
	}

	/**
	 * Concatenate the templates and prepare inline styles and scripts.
	 *
	 * @return string
	 */
	public function return_admin_view() {
		return $this->positioning_admin_view() . $this->spacing_admin_view();
	}

	/**
	 * Include the positioning view.
	 *
	 * @return string
	 */
	private function positioning_admin_view() {
		$positioning         = $this->positioning['position'];
		$positioning_options = $this->setup_positioning_options();

		ob_start();
		include_once __DIR__ . '/../views/ad-positioning.php';

		return ob_get_clean();
	}

	/**
	 * Include the spacing/margin view.
	 *
	 * @return string
	 */
	private function spacing_admin_view() {
		$is_centered = explode( '_', $this->positioning['position'] )[0] === 'center';
		$spacings    = [
			'top'    => [
				'label' => _x( 'Top', 'Ad positioning spacing label', 'advanced-ads' ),
			],
			'right'  => [
				'label' => _x( 'Right', 'Ad positioning spacing label', 'advanced-ads' ),
			],
			'bottom' => [
				'label' => _x( 'Bottom', 'Ad positioning spacing label', 'advanced-ads' ),
			],
			'left'   => [
				'label' => _x( 'Left', 'Ad positioning spacing label', 'advanced-ads' ),
			],
		];
		foreach ( $spacings as $direction => $item ) {
			$spacings[ $direction ]['value'] = (int) $this->positioning['margin'][ $direction ];
		}

		ob_start();
		include_once __DIR__ . '/../views/ad-spacing.php';

		return ob_get_clean();
	}

	/**
	 * Get a well-formed array to work with.
	 *
	 * @return array
	 */
	private function get_options() {
		$options = $this->ad->options();
		if ( empty( $options['output'] ) ) {
			return $this->positioning;
		}

		return wp_parse_args( $options['output'], $this->positioning );
	}
}
