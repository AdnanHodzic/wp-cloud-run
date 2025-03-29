<?php // phpcs:ignoreFilename

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
	const GROUP     = 'group';
	const AD        = 'id';

	/**
	 * Hold methods
	 *
	 * @var array
	 */
	protected $methods;

	/**
	 * Get instance of this class.
	 *
	 * @return Advanced_Ads_Select
	 */
	public static function get_instance() {
		static $instance;
		if ( ! isset( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Get ad selection methods.
	 *
	 * @return array
	 */
	public function get_methods() {
		if ( ! isset( $this->methods ) ) {
			$methods = [
				self::AD        => [ $this, 'get_ad_by_id' ],
				self::GROUP     => [ $this, 'get_ad_by_group' ],
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
	 * @param string $method Ad selection method.
	 * @param int    $id     Ad ID.
	 * @param array  $args   Ad selection arguments.
	 *
	 * @return array
	 */
	public function get_ad_arguments( $method, $id, $args = [] ) {
		$args = (array) $args;

		$args['previous_id']     = $args['id'] ?? null;
		$args['previous_method'] = $args['method'] ?? null;

		if ( $id || ! isset( $args['id'] ) ) {
			$args['id'] = $id;
		}

		$args['method'] = $method;

		$args = apply_filters( 'advanced-ads-ad-select-args', $args, $method, $id );

		return $args;
	}

	/**
	 * Get ad by method.
	 *
	 * @param int    $id     Ad ID.
	 * @param string $method Ad selection method.
	 * @param array  $args   Ad selection arguments.
	 *
	 * @return string
	 */
	public function get_ad_by_method( $id, $method, $args = [] ) {
		$methods = $this->get_methods();

		if ( ! isset( $methods[ $method ] ) ) {
			return;
		}

		if ( ! advads_can_display_ads() ) {
			return;
		}

		$args = $this->get_ad_arguments( $method, $id, $args );

		return call_user_func( $methods[ $method ], $args );
	}

	/**
	 * Get ad by ID.
	 *
	 * @param array $args Ad selection arguments.
	 *
	 * @return string
	 */
	public function get_ad_by_id( $args ) {
		return get_the_ad( $args['id'], '', $args );
	}

	/**
	 * Get ad by group.
	 *
	 * @param array $args Ad selection arguments.
	 *
	 * @return string
	 */
	public function get_ad_by_group( $args ) {
		return get_the_group( $args['id'], '', $args );
	}

	/**
	 * Get ad by placement.
	 *
	 * @param array $args Ad selection arguments.
	 *
	 * @return string
	 */
 	public function get_ad_by_placement( $args ) {
		return get_the_placement( $args['id'], '', $args );
	}
}
