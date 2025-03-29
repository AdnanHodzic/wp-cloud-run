<?php
/**
 * Ad types manager.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Ads;

use AdvancedAds\Ads\Types\AMP;
use AdvancedAds\Ads\Types\GAM;
use AdvancedAds\Abstracts\Types;
use AdvancedAds\Ads\Types\Dummy;
use AdvancedAds\Ads\Types\Group;
use AdvancedAds\Ads\Types\Image;
use AdvancedAds\Ads\Types\Plain;
use AdvancedAds\Ads\Types\Content;
use AdvancedAds\Ads\Types\Unknown;
use AdvancedAds\Interfaces\Ad_Type;

defined( 'ABSPATH' ) || exit;

/**
 * Ad Types.
 */
class Ad_Types extends Types {

	/**
	 * Hook to filter types.
	 *
	 * @var string
	 */
	protected $hook = 'advanced-ads-ad-types';

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
	protected $type_interface = Ad_Type::class;

	/**
	 * Register default types.
	 *
	 * @return void
	 */
	protected function register_default_types(): void {
		$this->register_type( Plain::class );
		$this->register_type( Dummy::class );
		$this->register_type( Content::class );
		$this->register_type( Image::class );
		$this->register_type( Group::class );
		$this->register_type( GAM::class );
		$this->register_type( AMP::class );
	}
}
