<?php
/**
 * Abstracts Factory.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Abstracts Factory.
 */
abstract class Factory {

	/**
	 * Retrieves the classname for entity type.
	 *
	 * @param Types  $manager      The manager object.
	 * @param string $entity_type  The entity type.
	 * @param string $default_type The entity default type.
	 *
	 * @return string The classname for the given entity type.
	 */
	public function get_classname( $manager, $entity_type, $default_type = 'default' ) {
		// If the manager is called outside `init` hook, we need to initialize it.
		if ( empty( $manager->get_types() ) || ! $manager->has_type( $default_type ) ) {
			$manager->register_types();
		}

		$type = $manager->has_type( $entity_type )
			? $manager->get_type( $entity_type )
			: $manager->get_type( $default_type );

		return $type->get_classname();
	}
}
