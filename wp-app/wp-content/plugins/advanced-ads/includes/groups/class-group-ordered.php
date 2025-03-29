<?php
/**
 * This class is responsible to model ordered groups.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Groups;

use AdvancedAds\Interfaces\Group_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Ordered group.
 */
class Group_Ordered extends Group_Standard implements Group_Interface {}
