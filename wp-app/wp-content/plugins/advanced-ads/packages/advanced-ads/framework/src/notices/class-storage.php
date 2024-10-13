<?php
/**
 * The notices storage
 *
 * @package AdvancedAds\Framework\Notices
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Notices;

use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Storage class
 */
class Storage implements Integration_Interface {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = null;

	/**
	 * Notices.
	 *
	 * @var Notice[]
	 */
	private $notices = null;

	/**
	 * The constructor
	 *
	 * @param string $option_name Option name to store notice in.
	 */
	public function __construct( $option_name ) {
		$this->option_name = $option_name;
	}

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'init', [ $this, 'get_from_storage' ] );
		add_action( 'shutdown', [ $this, 'update_storage' ] );
	}

	/**
	 * Get notices.
	 *
	 * @return Notice[] Registered notices.
	 */
	public function get_notices() {
		return $this->notices;
	}

	/**
	 * Get the notice by ID
	 *
	 * @param string $notice_id The ID of the notice to search for.
	 *
	 * @return null|Notice
	 */
	public function get_by_id( $notice_id ) {
		return $this->notices[ $notice_id ] ?? null;
	}

	/**
	 * Add notice
	 *
	 * @param string $id      Notice unique id.
	 * @param string $message Message string.
	 * @param array  $options Set of options.
	 *
	 * @return Notice
	 */
	public function add( $id, $message, $options = [] ) {
		$notice = $this->get_by_id( $id );
		if ( ! is_null( $notice ) ) {
			return $notice;
		}

		$this->notices[ $id ] = new Notice( $id, $message, $options );

		return $this->notices[ $id ];
	}

	/**
	 * Remove the notice by ID
	 *
	 * @param string $notice_id The ID of the notice to search for.
	 *
	 * @return null|Notice
	 */
	public function remove( $notice_id ) {
		$notice = $this->get_by_id( $notice_id );
		if ( ! is_null( $notice ) ) {
			$notice->dismiss();
		}

		return $notice;
	}

	/**
	 * Retrieve the notices from storage
	 *
	 * @return array Notice[] Notices
	 */
	public function get_from_storage() {
		if ( null !== $this->notices ) {
			return;
		}

		$this->notices = [];
		$notices       = get_option( $this->option_name );

		// Check if there are any notices.
		if ( empty( $notices ) ) {
			return;
		}

		if ( is_array( $notices ) ) {
			foreach ( $notices as $notice ) {
				$this->notices[ $notice['id'] ] = new Notice(
					$notice['id'],
					$notice['message'],
					$notice['options']
				);
			}
		}
	}

	/**
	 * Save persistent or transactional notices to storage.
	 *
	 * We need to be able to retrieve these so they can be dismissed at any time during the execution.
	 *
	 * @return void
	 */
	public function update_storage(): void {
		$notices = array_filter( $this->notices, [ $this, 'remove_notice' ] );

		// No notices to store, clear storage.
		if ( empty( $notices ) ) {
			delete_option( $this->option_name );
			return;
		}

		// Save the notices to the storage.
		update_option( $this->option_name, $notices );
	}

	/**
	 * Remove notice after it has been displayed.
	 *
	 * @param Notice $notice Notice to remove.
	 *
	 * @return bool
	 */
	public function remove_notice( Notice $notice ) {
		if ( ! $notice->is_displayed() ) {
			return true;
		}

		if ( $notice->is_persistent() ) {
			return true;
		}

		return false;
	}
}
