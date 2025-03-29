<?php
/**
 * Render Importers
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
$size       = size_format( $bytes );
$upload_dir = wp_upload_dir();
?>
<div class="advads-importers max-w-screen-lg">
	<?php wp_advads()->importers->display_message(); ?>
	<div class="wp-header-end hidden"></div>
	<?php require 'plugin-importer.php'; ?>
	<?php require 'other-plugin-importer.php'; ?>
	<?php require 'import-history.php'; ?>
</div>
