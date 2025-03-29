<?php
/**
 * This class is responsible to hold all the Ads functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads;

use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Ads Ads.
 */
class Ads implements Initializer_Interface {

	/**
	 * Hold factory instance
	 *
	 * @var Ad_Factory
	 */
	public $factory = null;

	/**
	 * Hold repository instance
	 *
	 * @var Ad_Repository
	 */
	public $repository = null;

	/**
	 * Hold types manager
	 *
	 * @var Ad_Types
	 */
	public $types = null;

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->factory    = new Ad_Factory();
		$this->types      = new Ad_Types();
		$this->repository = new Ad_Repository();

		$this->types->hooks();
	}
}
