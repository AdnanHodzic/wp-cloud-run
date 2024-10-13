<?php
/**
 * Settings page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

// array with setting tabs for frontend.
$setting_tabs = apply_filters(
	'advanced-ads-setting-tabs',
	[
		'general' => [
			'page'  => Advanced_Ads_Admin::get_instance()->plugin_screen_hook_suffix,
			'group' => ADVADS_SLUG,
			'tabid' => 'general',
			'title' => __( 'General', 'advanced-ads' ),
		],
	]
);
?><div class="wrap">
	<h2 style="display: none;"><!-- There needs to be an empty H2 headline at the top of the page so that WordPress can properly position admin notifications --></h2>
	<?php Advanced_Ads_Checks::show_issues(); ?>

	<?php settings_errors(); ?>
	<div class="nav-tab-wrapper" id="advads-tabs">
		<?php foreach ( $setting_tabs as $_setting_tab_id => $_setting_tab ) : ?>
			<a class="nav-tab" id="<?php echo esc_attr( $_setting_tab_id ); ?>-tab"
				href="#top#<?php echo esc_attr( $_setting_tab_id ); ?>"><?php echo esc_html( $_setting_tab['title'] ); ?></a>
		<?php endforeach; ?>
		<a class="nav-tab" id="support-tab"
				href="#top#support"><?php esc_html_e( 'Support', 'advanced-ads' ); ?></a>
	</div>
		<?php foreach ( $setting_tabs as $_setting_tab_id => $_setting_tab ) : ?>
			<div id="<?php echo esc_attr( $_setting_tab_id ); ?>" class="advads-tab">
				<div id="advads-sub-menu-<?php echo esc_attr( $_setting_tab_id ); ?>" class="advads-tab-sub-menu"></div>
				<form class="advads-settings-tab-main-form" method="post" action="options.php">
					<?php
					if ( isset( $_setting_tab['group'] ) ) {
						settings_fields( $_setting_tab['group'] );
					}
					do_settings_sections( $_setting_tab['page'] );

					do_action( 'advanced-ads-settings-form', $_setting_tab_id, $_setting_tab );
					if ( isset( $_setting_tab['group'] ) && 'advanced-ads-licenses' !== $_setting_tab['group'] ) {
						submit_button( __( 'Save settings on this page', 'advanced-ads' ) );
					}
					?>
				</form>
				<?php do_action( 'advanced-ads-settings-tab-after-form', $_setting_tab_id, $_setting_tab ); ?>
			<?php if ( 'general' === $_setting_tab_id ) : ?>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=advanced-ads-import-export' ) ); ?>"><?php esc_html_e( 'Import &amp; Export', 'advanced-ads' ); ?></a></li>
			</ul>
			<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<div id="support" class="advads-tab">
		<?php require_once ADVADS_ABSPATH . 'admin/views/support.php'; ?>
	</div>
		<?php
			do_action( 'advanced-ads-additional-settings-form' );
			// print the filesystem credentials modal if needed.
			Advanced_Ads_Filesystem::get_instance()->print_request_filesystem_credentials_modal();
		?>

</div>
