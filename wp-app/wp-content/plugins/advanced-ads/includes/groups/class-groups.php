<?php
/**
 * This class is responsible to hold all the Groups functionality.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Groups;

use AdvancedAds\Framework\Interfaces\Initializer_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Groups.
 */
class Groups implements Initializer_Interface {

	/**
	 * Hold factory instance
	 *
	 * @var Group_Factory
	 */
	public $factory = null;

	/**
	 * Hold repository instance
	 *
	 * @var Group_Repository
	 */
	public $repository = null;

	/**
	 * Hold types manager
	 *
	 * @var Group_Types
	 */
	public $types = null;

	/**
	 * Runs this initializer.
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->factory    = new Group_Factory();
		$this->types      = new Group_Types();
		$this->repository = new Group_Repository();

		$this->types->hooks();
	}
}
