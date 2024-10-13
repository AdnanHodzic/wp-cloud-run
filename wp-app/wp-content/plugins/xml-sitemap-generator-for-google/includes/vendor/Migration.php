<?php

namespace GRIM_SG\vendor;

class Migration extends Controller {
	const DB_VERSION_KEY = 'sgg_version';

	/**
	 * Database updates and callbacks that need to be run per Migration version.
	 */
	private $migrations = array(
		'1.8.4' => array(
			'migrate_settings_dynamic_created_objects',
		),
		'1.9.9' => array(
			'migrate_video_settings',
		),
	);

	public function __construct() {
		add_action( 'admin_init', array( $this, 'run_migrations' ) );
	}

	/**
	 * Runs all the necessary migrations.
	 */
	public function run_migrations() {
		$current_db_version = get_option( self::DB_VERSION_KEY, '' );

		if ( version_compare( $current_db_version, GRIM_SG_VERSION, '>=' ) ) {
			return;
		}

		foreach ( $this->migrations as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$this->{$update_callback}();
				}
			}
		}

		self::update_version();
	}

	/**
	 * Update Version in Database
	 */
	public static function update_version() {
		update_option( self::DB_VERSION_KEY, GRIM_SG_VERSION );
	}

	/**
	 * Migrate Dynamic created CPT and Taxonomies to array
	 */
	public function migrate_settings_dynamic_created_objects() {
		$settings = get_option( self::$slug, array() );

		if ( ! empty( $settings ) ) {
			$settings->cpt        = array();
			$settings->taxonomies = array();

			foreach ( $this->get_cpt() as $cpt ) {
				if ( ! empty( $settings->{$cpt} ) ) {
					$settings->cpt[ $cpt ] = $settings->{$cpt};
				}
			}

			foreach ( $this->get_taxonomy_types() as $taxonomy ) {
				if ( ! empty( $settings->{$taxonomy} ) ) {
					$settings->taxonomies[ $taxonomy ] = $settings->{$taxonomy};
				}
			}

			update_option( Controller::$slug, $settings );
		}
	}

	/**
	 * Migrate Video Sitemap Settings
	 */
	public function migrate_video_settings() {
		$settings = get_option( self::$slug, array() );

		if ( ! empty( $settings ) ) {
			$settings->enable_video_api_cache = $settings->enable_youtube_cache ?? true;

			update_option( Controller::$slug, $settings );
		}

		// Migrate YouTube Data to Video API Data
		$youtube_data = get_option( 'sgg_youtube_data', array() );

		if ( ! empty( $youtube_data ) ) {
			update_option( 'sgg_video_api_data', $youtube_data );
		}
	}
}
