<?php
/**
 * Render System information
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

use AdvancedAds\Admin\System_Info;

$system_info = new System_Info();
?>
<div class="advads-system-information">
	<h2><?php esc_html_e( 'System Information', 'advanced-ads' ); ?></h2>
	<textarea id="advads-system-information" readonly><?php echo esc_textarea( $system_info->get_info() ); ?></textarea>
	<button type="button" id="advads-system-information-copy" class="button button-primary">
		<?php esc_html_e( 'Copy System Information', 'advanced-ads' ); ?>
	</button>
</div>
