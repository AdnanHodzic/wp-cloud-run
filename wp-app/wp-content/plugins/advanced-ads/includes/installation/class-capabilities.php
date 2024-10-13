<?php
/**
 * The class is responsible for managing and registering capabilities in WordPress.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Installation;

defined( 'ABSPATH' ) || exit;

/**
 * Capabilities manager.
 */
class Capabilities {

	/**
	 * Instance
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Registered capabilities.
	 *
	 * @var array
	 */
	private $capabilities = [];

	/**
	 * Registered capabilities by role.
	 *
	 * @var array
	 */
	private $role_capabilities = [];

	/**
	 * The constructor
	 */
	public function __construct() {
		$this->register_defaults();
	}

	/**
	 * Get instance
	 *
	 * @return Capabilities
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Set default capabilities.
	 *
	 * @return void
	 */
	public function register_defaults(): void {
		$this->register(
			'advanced_ads_manage_options',
			esc_html__( 'Allows changing plugin options', 'advanced-ads' ),
			'administrator'
		);

		$this->register(
			'advanced_ads_see_interface',
			esc_html__( 'Allows access to the Advanced Ads backend', 'advanced-ads' ),
			'administrator'
		);

		$this->register(
			'advanced_ads_edit_ads',
			esc_html__( 'Allows editing ads', 'advanced-ads' ),
			'administrator'
		);

		$this->register(
			'advanced_ads_manage_placements',
			esc_html__( 'Allows changing the placements page', 'advanced-ads' ),
			'administrator'
		);

		$this->register(
			'advanced_ads_place_ads',
			esc_html__( 'Enables shortcode button', 'advanced-ads' ),
			'administrator'
		);
	}

	/**
	 * Register a capability.
	 *
	 * @param string       $capability Capability slug.
	 * @param string       $title      Capability title.
	 * @param array|string $roles      Roles to assigned.
	 *
	 * @return void
	 */
	public function register( $capability, $title, $roles ): void {
		$this->capabilities[ $capability ] = $title;

		foreach ( (array) $roles as $role ) {
			$this->role_capabilities[ $role ]   = $this->role_capabilities[ $role ] ?? [];
			$this->role_capabilities[ $role ][] = $capability;
		}
	}

	/**
	 * Create capabilities.
	 *
	 * @return void
	 */
	public function create_capabilities(): void {
		foreach ( $this->get_roles() as $role ) {
			$this->loop_capabilities( $role, 'add_cap' );
		}
	}

	/**
	 * Remove capabilities.
	 *
	 * @return void
	 */
	public function remove_capabilities(): void {
		foreach ( $this->get_roles() as $role ) {
			$this->loop_capabilities( $role, 'remove_cap' );
		}
	}

	/**
	 * Reset capabilities.
	 *
	 * @return void
	 */
	public function reset_capabilities(): void {
		$this->remove_capabilities();
		$this->create_capabilities();
	}

	/**
	 * Get roles names
	 *
	 * @return array
	 */
	private function get_roles(): array {
		$roles = wp_roles();
		return array_keys( $roles->get_names() );
	}

	/**
	 * Loop capabilities and perform action.
	 *
	 * @param string $role_slug Role slug.
	 * @param string $perform   Action to perform.
	 *
	 * @return void
	 */
	private function loop_capabilities( $role_slug, $perform ): void {
		// Early bail!!
		if ( ! isset( $this->role_capabilities[ $role_slug ] ) ) {
			return;
		}

		$role = get_role( $role_slug );
		if ( ! $role ) {
			return;
		}

		foreach ( $this->role_capabilities[ $role_slug ] as $cap ) {
			$role->$perform( $cap );
		}
	}
}
