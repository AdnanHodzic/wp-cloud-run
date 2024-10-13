<?php

namespace Advanced_Ads;

/**
 * The placement type options are stored in an array, this wraps them so we can operate on them.
 */
class Placement_Type_Options extends \ArrayObject {
	/**
	 * Whether to show the positioning options.
	 *
	 * @var bool
	 */
	private $show_position = false;

	/**
	 * Whether to show lazy loading.
	 *
	 * @var bool
	 */
	private $show_lazy_load = false;

	/**
	 * Whether to show content options.
	 *
	 * @var bool
	 */
	private $uses_the_content = false;

	/**
	 * Whether this placement is available in AMP.
	 *
	 * @var bool
	 */
	private $amp = false;

	/**
	 * Array of allowed ad types for this placement. Default null.
	 *
	 * @var null|string[]
	 */
	private $allowed_ad_types = null;

	/**
	 * Array of excluded ad types for this placement.
	 *
	 * @var string[]
	 */
	private $excluded_ad_types = [];

	/**
	 * Array of allowed ad group types for this placement.
	 *
	 * @var null|string[]
	 */
	private $allowed_group_types = null;

	/**
	 * Array of excluded ad groups for this placement.
	 *
	 * @var string[]
	 */
	private $excluded_group_types = [];

	/**
	 * Overload offsetGet in order to get the default value for the option.
	 *
	 * @param string $key The array offset to search for.
	 *
	 * @return false|mixed
	 */
	#[\ReturnTypeWillChange]
	public function &offsetGet( $key ) {
		if ( ! $this->offsetExists( $key ) && property_exists( $this, $key ) ) {
			return $this->{$key};
		}

		$value = parent::offsetGet( $key );

		return $value;
	}
}
