<?php
/**
 * This class is responsible to hold all the Placements functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Placements;

use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Placements.
 */
class Placements implements Initializer_Interface {

	/**
	 * Hold factory instance
	 *
	 * @var Placement_Factory
	 */
	public $factory = null;

	/**
	 * Hold repository instance
	 *
	 * @var Placement_Repository
	 */
	public $repository = null;

	/**
	 * Hold types manager
	 *
	 * @var Placement_Types
	 */
	public $types = null;

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->factory    = new Placement_Factory();
		$this->types      = new Placement_Types();
		$this->repository = new Placement_Repository();

		$this->types->hooks();
	}
}
