<?php
/**
 * Plugin file
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

/**
 * WordPress integration and definitions:
 *
 * @deprecated 2.0 Use Advanced_Ads
 */
class Advanced_Ads_Plugin {

	/**
	 * Instance of Advanced_Ads_Plugin
	 *
	 * @var object Advanced_Ads_Plugin
	 */
	protected static $instance;

	/**
	 * Plugin options
	 *
	 * @var array $options
	 */
	protected $options;

	/**
	 * Get instance of Advanced_Ads_Plugin
	 */
	public static function get_instance() {
		_deprecated_file( __CLASS__, '2.0', 'Advanced_Ads' );

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return plugin options these are the options updated by the user
	 *
	 * @deprecated 2.0 Use AdvancedAds\Options class instead.
	 *
	 * @return array $options
	 */
	public function options() {
		_deprecated_function( __METHOD__, '2.0', "AdvancedAds\Options::get_instance()->get_options('advanced-ads')" );

		return Advanced_Ads::get_instance()->options();
	}

	/**
	 * Get prefix used for frontend elements
	 *
	 * @deprecated 2.0 Use wp_advads()->get_frontend_prefix().
	 *
	 * @return string
	 */
	public function get_frontend_prefix() {
		_deprecated_function( __METHOD__, '2.0', 'wp_advads()->get_frontend_prefix()' );

		return wp_advads()->get_frontend_prefix();
	}
}
