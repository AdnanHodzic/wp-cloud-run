<?php
/**
 * Groups Manager.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Groups;

use AdvancedAds\Abstracts\Types;
use AdvancedAds\Groups\Types\Grid;
use AdvancedAds\Groups\Types\Ordered;
use AdvancedAds\Groups\Types\Slider;
use AdvancedAds\Groups\Types\Standard;
use AdvancedAds\Groups\Types\Unknown;
use AdvancedAds\Interfaces\Group_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Manager.
 */
class Manager extends Types {

	/**
	 * Hook to filter types.
	 *
	 * @var string
	 */
	protected $hook = 'advanced-ads-group-types';

	/**
	 * Class for unknown type.
	 *
	 * @var string
	 */
	protected $type_unknown = Unknown::class;

	/**
	 * Type interface to check.
	 *
	 * @var string
	 */
	protected $type_interface = Group_Type::class;

	/**
	 * Check if has premium types.
	 *
	 * @var bool
	 */
	private $has_premium = null;

	/**
	 * Register default types.
	 *
	 * @return void
	 */
	protected function register_default_types(): void {
		$this->register_type( Standard::class );
		$this->register_type( Ordered::class );
		$this->register_type( Grid::class );
		$this->register_type( Slider::class );
	}

	/**
	 * Check if has premium types.
	 *
	 * @return bool
	 */
	public function has_premium(): bool {
		if ( null !== $this->has_premium ) {
			return $this->has_premium;
		}

		$this->has_premium = false;

		foreach ( $this->get_types() as $type ) {
			if ( $type->is_premium() ) {
				$this->has_premium = true;
				break;
			}
		}

		return $this->has_premium;
	}
}
