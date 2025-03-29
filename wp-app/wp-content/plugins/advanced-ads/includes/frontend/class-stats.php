<?php
/**
 * Frontend Stats.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Stats.
 */
class Stats {

	/**
	 * Array with ads currently delivered in the frontend
	 *
	 * @var array Ads already loaded in the frontend
	 */
	public $entities = [];

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Stats
	 */
	public static function get() {
		static $instance;

		if ( null === $instance ) {
			$instance = new Stats();
		}

		return $instance;
	}

	/**
	 * Add an entity to the stats.
	 *
	 * @param string $type      Entity type.
	 * @param string $id        Entity id.
	 * @param string $title     Entity title.
	 * @param string $parent_id Parent entity id.
	 *
	 * @return void
	 */
	public function add_entity( $type, $id, $title, $parent_id = false ): void {
		if ( ! isset( $this->entities[ $id ] ) ) {
			$this->entities[ $id ] = [
				'type'   => $type,
				'id'     => $id,
				'title'  => $title,
				'count'  => 0,
				'childs' => [],
			];
		}

		if ( ! $parent_id ) {
			++$this->entities[ $id ]['count'];
		} else {
			$this->entities[ $parent_id ]['childs'][ $id ] = [
				'type'  => $type,
				'id'    => $id,
				'title' => $title,
			];
		}
	}
}
