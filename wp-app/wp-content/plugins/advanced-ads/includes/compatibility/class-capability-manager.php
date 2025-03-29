<?php
/**
 * Integrates Advanced Ads capabilities with third party role manager plugins.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Compatibility;

use AdvancedAds\Constants;
use AdvancedAds\Installation\Capabilities;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Capability Manager.
 *
 * Integrates with: Members
 * Integrates with: User Role Editor
 * Integrates with: PublishPress Capabilities
 */
class Capability_Manager implements Integration_Interface {

	/**
	 * Group name.
	 *
	 * @var string
	 */
	const GROUP = 'advanced-ads';

	/**
	 * Capability manager.
	 *
	 * @var Capabilities
	 */
	private $capability_manager = null;

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		$this->capability_manager = new Capabilities();

		// Integrates with: Members.
		add_action( 'members_register_caps', [ $this, 'register_members_caps' ] );
		add_action( 'members_register_cap_groups', [ $this, 'register_members_groups' ] );

		// Integrates with: User Role Editor.
		add_filter( 'ure_capabilities_groups_tree', [ $this, 'register_ure_groups' ] );
		add_filter( 'ure_custom_capability_groups', [ $this, 'register_ure_caps' ], 10, 2 );

		// Integrates with: PublishPress Capabilities.
		add_filter( 'cme_plugin_capabilities', [ $this, 'register_publishpress_caps' ] );
	}

	/**
	 * Get the name of the integration.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return __( 'Advanced Ads', 'advanced-ads' );
	}

	/**
	 * Register Members groups.
	 *
	 * @return void
	 */
	public function register_members_groups(): void {
		members_register_cap_group(
			self::GROUP,
			[
				'label'    => $this->get_name(),
				'priority' => 10,
				'icon'     => 'dashicons-editor-textcolor',
				'caps'     => array_keys( $this->capability_manager->get_capabilities() ),
			]
		);

		// Remove post types groups.
		members_unregister_cap_group( 'type-' . Constants::POST_TYPE_AD );
		members_unregister_cap_group( 'type-' . Constants::POST_TYPE_PLACEMENT );
	}

	/**
	 * Register members capabilities.
	 *
	 * @return void
	 */
	public function register_members_caps(): void {
		$capabilities = $this->capability_manager->get_capabilities();
		foreach ( $capabilities as $cap => $label ) {
			members_register_cap(
				$cap,
				[
					'label' => $label,
					'group' => self::GROUP,
				]
			);
		}
	}

	/**
	 * Register URE groups.
	 *
	 * @param array $groups Groups.
	 *
	 * @return array
	 */
	public function register_ure_groups( array $groups ): array {
		$groups = (array) $groups;

		$groups[ self::GROUP ] = [
			'caption' => $this->get_name(),
			'parent'  => 'custom',
			'level'   => 2,
		];

		return $groups;
	}

	/**
	 * Register URE capabilities.
	 *
	 * @param array  $groups Current capability groups.
	 * @param string $cap_id Capability identifier.
	 *
	 * @return array
	 */
	public function register_ure_caps( $groups, $cap_id ): array {
		if ( array_key_exists( $cap_id, $this->capability_manager->get_capabilities() ) ) {
			$groups   = (array) $groups;
			$groups[] = self::GROUP;
		}

		return $groups;
	}

	/**
	 * Register PublishPress capabilities.
	 *
	 * @param array $caps Capabilities.
	 *
	 * @return array
	 */
	public function register_publishpress_caps( array $caps ): array {
		$caps[ $this->get_name() ] = array_keys( $this->capability_manager->get_capabilities() );

		return $caps;
	}
}
