<?php

/**
 * Abstracts ad selection.
 *
 * The class allows to modify 'methods' (named callbacks) to provide ads
 * through `advanced-ads-ad-select-methods` filter.
 * This can be used to replace default methods, wrap them or add new ones.
 *
 * Further allows to provide ad selection attributes
 * through `advanced-ads-ad-select-args` filter to influence behaviour of the
 * selection method.
 * Default methods have a `override` attribute that allows to replace the
 * content. This may be used to defer or skip ad codes dynamically.
 *
 * @since 1.5.0
 */
class Advanced_Ads_Select {

	const PLACEMENT = 'placement';
	const GROUP = 'group';
	const AD = 'id'; // alias of self::ID
	const ID = 'id';

	protected $methods;

	private function __construct() {}

	/**
	 *
	 * @var Advanced_Ads_Select
	 */
	private static $instance;

	/**
	 *
	 * @return Advanced_Ads_Select
	 */
	public static function get_instance()
	{
		if ( ! isset(self::$instance) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 *
	 * @return array
	 */
	public function get_methods()
	{
		if ( ! isset($this->methods) ) {
			$methods = [
				self::AD => [ $this, 'get_ad_by_id' ],
				self::GROUP => [ $this, 'get_ad_by_group' ],
				self::PLACEMENT => [ $this, 'get_ad_by_placement' ],
			];

			$this->methods = apply_filters( 'advanced-ads-ad-select-methods', $methods );
		}

		return $this->methods;
	}

	/**
	 * Advanced ad selection methods should not directly rely on
	 * current environment factors.
	 * Prior to actual ad selection the meta is provided to allow for
	 * serialised, proxied or otherwise defered selection workflows.
	 *
	 * @return array
	 */
	public function get_ad_arguments( $method, $id, $args = [] )
	{
		$args = (array) $args;

		$args['previous_method'] = isset( $args['method'] ) ? $args['method']  : null;
		$args['previous_id'] = isset( $args['id'] ) ? $args['id'] : null;

		if ( $id || ! isset( $args['id'] ) ) $args['id'] = $id;
		$args['method'] = $method;

		$args = apply_filters( 'advanced-ads-ad-select-args', $args, $method, $id );

		return $args;
	}

	public function get_ad_by_method( $id, $method, $args = [] ) {

		$methods = $this->get_methods();
		if ( ! isset($methods[ $method ]) ) {
			return ;
		}
		if ( ! advads_can_display_ads() ) {
			return ;
		}
		$args = $this->get_ad_arguments( $method, $id, $args );

		return call_user_func( $methods[ $method ], $args );
	}

	// internal
	public function get_ad_by_id($args) {
		if ( isset($args['override']) ) {
			return $args['override'];
		}
		if ( ! isset($args['id']) || $args['id'] == 0 ) {
			return ;
		}

		// We can't get the ad from the repository, this is the only instance where the arguments are passed in the constructor.
		$ad = new Advanced_Ads_Ad( (int) $args['id'], $args );

		if ( false !== ( $override = apply_filters( 'advanced-ads-ad-select-override-by-ad', false, $ad, $args ) ) ) {
			return $override;
		}

		// check conditions
		if ( $ad->can_display() ) {
			return $ad->output();
		}
	}

	// internal
	public function get_ad_by_group($args) {
		if ( isset($args['override']) ) {
			return $args['override'];
		}
		if ( ! isset($args['id']) || $args['id'] == 0 ) {
			return;
		}

		// get ad
		$id = (int) $args['id'];
		$adgroup = new Advanced_Ads_Group( $id, $args );
		$ordered_ad_ids = $adgroup->get_ordered_ad_ids();

		if ( false !== ( $override = apply_filters( 'advanced-ads-ad-select-override-by-group', false, $adgroup, $ordered_ad_ids, $args ) ) ) {
			return $override;
		}

		return $adgroup->output( $ordered_ad_ids );
	}

	// internal
	public function get_ad_by_placement($args) {
		if ( isset($args['override']) ) {
			return $args['override'];
		}
		if ( ! isset($args['id']) || $args['id'] == '' ) {
			return ;
		}

		// check conditions
		if ( ! Advanced_Ads_Placements::can_display( $args['id'] ) ) {
			return;
		}

		// get placement content
		$id = $args['id'];
		return Advanced_Ads_Placements::output( $id, $args );
	}
}
