<?php

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

class SWCFPC_Installer {


	function __construct() {}


	function start() {

		$this->create_mysql_tables();

	}

	function create_mysql_tables() {

		/*
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql   = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}swcfpc_logs (";
		$sql  .= 'id bigint NOT NULL AUTO_INCREMENT,';
		$sql  .= 'date datetime NOT NULL,';
		$sql  .= 'log_identifier varchar(150) NOT NULL,';
		$sql  .= 'log_msg longtext NOT NULL,';
		$sql  .= 'PRIMARY KEY  (id)';
		$sql  .= ") {$charset_collate}";

		dbDelta( $sql );
		*/

	}

}
