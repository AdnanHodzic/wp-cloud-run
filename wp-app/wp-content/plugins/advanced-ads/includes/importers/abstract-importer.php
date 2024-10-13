<?php
/**
 * Importers Base.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use AdvancedAds\Constants;

defined( 'ABSPATH' ) || exit;

/**
 * Importers Base.
 */
abstract class Importer {

	/**
	 * Is importer detected.
	 *
	 * @var bool
	 */
	private $is_detected = null;

	/**
	 * Is importer detected
	 *
	 * @return bool
	 */
	public function is_detected(): bool {
		if ( null === $this->is_detected ) {
			$this->is_detected = $this->detect();
		}

		return $this->is_detected;
	}

	/**
	 * Add session key.
	 *
	 * @param Ad        $ad        Ad instance.
	 * @param Placement $placement Placement instance.
	 * @param string    $key       Session unique key.
	 *
	 * @return void
	 */
	protected function add_session_key( $ad, $placement, $key ): void {
		update_post_meta( $ad->get_id(), '_importer_session_key', $key );
		update_post_meta( $placement->get_id(), '_importer_session_key', $key );
	}

	/**
	 * Rollback import
	 *
	 * @param string $key Session key.
	 *
	 * @return void
	 */
	public function rollback( $key ): void {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE p, pm
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm
				ON pm.post_id = p.ID
				WHERE pm.meta_key = '_importer_session_key'
				AND pm.meta_value = %s
				AND p.post_type in ( %s, %s )",
				$key,
				Constants::POST_TYPE_AD,
				Constants::POST_TYPE_PLACEMENT
			)
		);

		$wpdb->query(
			"DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL"
		);
	}

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	protected function generate_history_key(): string {
		return 'advads_' . wp_rand() . '_' . $this->get_id();
	}
}
