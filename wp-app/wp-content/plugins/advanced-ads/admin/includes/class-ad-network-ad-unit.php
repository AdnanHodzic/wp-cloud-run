<?php

/**
 * Class Advanced_Ads_Ad_Network_Ad_Unit
 * This represents an external ad unit. Will be used for importing external ads from various ad networks.
 */
class Advanced_Ads_Ad_Network_Ad_Unit {
	/**
	 * Contains the raw data (typically from a JSON response) for this ad unit
	 *
	 * @var string
	 */
	public $raw;

	/**
	 * The (external) id of this ad unit (e.g. pub-ca... for adsense)
	 *
	 * @var string
	 */
	public $id;

	/**
	 * The display name of the ad
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The type of this ad unit (displayed in list)
	 *
	 * @var string
	 */
	public $display_type;

	/**
	 * The size of this ad unit (displayed in list)
	 *
	 * @var string
	 */
	public $display_size;

	/**
	 * In case of an AdSense ad, this is the id of the ad without the publisher id
	 * the value will be displayed in the ads list
	 *
	 * @var string
	 */
	public $slot_id;

	/**
	 * A bool that indicates whether an ad is active (inactives will be hidden by default)
	 *
	 * @var bool
	 */
	public $active;

	/**
	 * Advanced_Ads_Ad_Network_Ad_Unit constructor.
	 *
	 * @param string $raw raw ad data.
	 */
	public function __construct( $raw ) {
		$this->raw = $raw;
	}

	/**
	 * Sort multiple ad units.
	 *
	 * @param array  $ad_units array of ad units.
	 * @param string $selected_id ID of the selected ad. Can be taken from the ad network and therefore also a string.
	 *
	 * @return array
	 */
	public static function sort_ad_units( array &$ad_units, $selected_id ) {
		usort(
			$ad_units,
			function ( $a, $b ) use ( $selected_id ) {
				if ( $a->id == $selected_id ) {
					return - 1;
				}
				if ( $b->id == $selected_id ) {
					return 1;
				}
				if ( $a->is_supported ) {
					if ( ! $b->is_supported ) {
						return - 1;
					}
				} elseif ( $b->is_supported ) {
					return 1;
				}

				return strcasecmp( $a->name, $b->name );
			}
		);

		return $ad_units;
	}
}
