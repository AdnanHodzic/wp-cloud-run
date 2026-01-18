<?php

namespace SPC\Models;

class Asset_Rules {
	public const DB_TABLE_NAME = 'spc_assets_rules';

	/**
	 * Register the database table for storing asset rules.
	 * @return void
	 */
	public static function register_database_table() {
		global $wpdb;

		$table_name       = $wpdb->prefix . self::DB_TABLE_NAME;
		$is_table_present = $wpdb->get_results( 'SHOW TABLES LIKE "' . esc_sql( $table_name ) . '"' );

		// check table is alreay not preset in the database.
		if ( ! $is_table_present ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id INT NOT NULL AUTO_INCREMENT,
				asset_hash VARCHAR(32) NOT NULL,
				asset_name VARCHAR(255) NOT NULL,
				asset_type ENUM('css', 'js') NOT NULL,
				origin_type ENUM('plugin', 'theme', 'core', 'external', 'inline') NOT NULL,
				asset_url TEXT NOT NULL,
				rules TEXT NOT NULL,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY unique_asset_hash (asset_hash),
				KEY idx_asset_hash (asset_hash),
				KEY idx_asset_type (asset_type),
				KEY idx_origin_type (origin_type)
			) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Remove the database table for storing asset rules.
	 * @return void
	 */
	public static function remove_database_table( $keep_settings = false ) {

		if ( ! $keep_settings ) {
			return;
		}

		global $wpdb;

		$table_name       = $wpdb->prefix . self::DB_TABLE_NAME;
		$is_table_present = $wpdb->get_results( 'SHOW TABLES LIKE "' . esc_sql( $table_name ) . '"' );

		// check table is preset in the database.
		if ( $is_table_present ) {
			$wpdb->query( 'DROP TABLE IF EXISTS ' . esc_sql( $table_name ) );
		}
	}

	/**
	 * Get asset rules from database.
	 *
	 * @return array
	 */
	public static function get_asset_rules() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::DB_TABLE_NAME;
		$results    = $wpdb->get_results( 'SELECT asset_hash, asset_name, rules FROM  ' . esc_sql( $table_name ) );

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		return $results;
	}

	/**
	 * Get all applicable asset rules from database.
	 *
	 * Applicable rules are the rules that are not empty.
	 *
	 * @return array
	 */
	public static function get_assets_with_applicable_rules() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::DB_TABLE_NAME;
		$results    = $wpdb->get_results( 'SELECT asset_hash, asset_name, asset_type, asset_url, rules FROM ' . esc_sql( $table_name ) . " WHERE rules != '[]'" );

		if ( empty( $results ) || ! is_array( $results ) ) {
			return [];
		}

		return $results;
	}

	/**
	 * Upsert asset rule into database.
	 *
	 * @param string $hash Asset hash.
	 * @param array $asset_data {
	 *  'asset_name' => 'asset_name',
	 *  'asset_type' => 'asset_type',
	 *  'origin_type' => 'origin_type',
	 *  'asset_url' => 'asset_url',
	 *  'rules' => 'rules',
	 * }
	 * @return int | false
	 */
	public static function upsert_asset_rules( string $hash, array $asset_data ) {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;

		$table_name     = $wpdb->prefix . self::DB_TABLE_NAME;
		$upcoming_rules = $asset_data['rules'] ?? [];

		// old rules
		$old_rules = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT rules FROM ' . esc_sql( $table_name ) . ' WHERE asset_hash=%s',
				$hash
			)
		);

		if ( empty( $old_rules ) ) {
			$old_rules = '[]';
		}

		$old_rules = json_decode( $old_rules, true );
		$new_rules = $old_rules;

		// Removables are [key] => true - double-negation because we save rules for disabling.
		$rules_to_remove = array_keys(
			array_filter(
				$upcoming_rules,
				function ( $rule ) {
					return $rule === true;
				}
			)
		);
		$rules_to_add    = array_keys(
			array_filter(
				$upcoming_rules,
				function ( $rule ) {
					return $rule !== true;
				}
			)
		);

		if ( ! empty( $rules_to_remove ) ) {
			$new_rules = array_diff( $new_rules, $rules_to_remove );
			// Ensure array type integrity.
			$new_rules = array_values( $new_rules );
		}

		if ( ! empty( $rules_to_add ) ) {
			$new_rules = array_merge( $new_rules, $rules_to_add );
			// Ensure array type integrity.
			$new_rules = array_values( $new_rules );
		}

		// Remove duplicates, ensure array type integrity.
		$new_rules = array_values( array_unique( $new_rules ) );

		if ( empty( $new_rules ) ) {
			$deletion = $wpdb->delete(
				$table_name,
				[
					'asset_hash' => $hash,
				]
			);

			return $deletion;
		}

		$new_rules = wp_json_encode( $new_rules );

		$result = $wpdb->replace(
			$table_name,
			[
				'asset_hash'  => $hash,
				'asset_name'  => $asset_data['asset_name'] ?? '',
				'asset_type'  => $asset_data['asset_type'] ?? '',
				'origin_type' => $asset_data['origin_type'] ?? '',
				'asset_url'   => $asset_data['asset_url'] ?? '',
				'rules'       => $new_rules,
			]
		);

		return $result;
	}

	/**
	 * Get existing assets rules from database.
	 *
	 * @return array[]
	 */
	public static function get_existing_asset_rules() {
		global $wpdb;

		$table_name = $wpdb->prefix . self::DB_TABLE_NAME;

		$results = $wpdb->get_results( 'SELECT asset_hash, asset_name, rules FROM  ' . esc_sql( $table_name ) );
		$rules   = [];

		foreach ( $results as $row ) {
			$rule = json_decode( $row->rules );

			if ( ! empty( $rule ) && is_array( $rule ) ) {
				$rules[ $row->asset_hash ] = $rule;
			}
		}

		return $rules;
	}
}
