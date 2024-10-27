<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

delete_option('dsxmlrpc-settings');
delete_option('pand-' . md5('wpsg-notice'));
delete_option('pand-' . md5('dsxmlrpc-notice'));