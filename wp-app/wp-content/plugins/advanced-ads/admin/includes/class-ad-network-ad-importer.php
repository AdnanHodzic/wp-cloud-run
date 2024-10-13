<?php

/**
 * Class Advanced_Ads_Ad_Network_Ad_Importer
 */
abstract class Advanced_Ads_Ad_Network_Ad_Importer {

	/**
	 * Ad network
	 *
	 * @var object
	 */
	protected $ad_network;

	/**
	 * Advanced_Ads_Ad_Network_Ad_Importer constructor.
	 *
	 * @param object $ad_network ad network object.
	 */
	public function __construct( $ad_network ) {
		$this->ad_network = $ad_network;
	}

	/**
	 * Render network
	 */
	public function render() {

	}
}
