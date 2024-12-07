<?php
/**
 * @var SWCFPC_Backend $this Current backend class instance.
 * @var string $error_msg Any error message rendered.
 * @var string $success_msg Any success message rendered.
 * @var bool $wizard_active Is wizard active.
 */


use SPC\Modules\Admin;
use function SPC\Views\Functions\load_view;

require_once SWCFPC_PLUGIN_PATH . 'src/views/template_functions.php';

$switch_counter              = 0;
$tab_active                  = isset( $_REQUEST['active_tab'] ) ? $_REQUEST['active_tab'] : false;
$nginx_instructions_page_url = add_query_arg( [ 'page' => 'wp-cloudflare-super-page-cache-nginx-settings' ], admin_url( 'options-general.php' ) );
$tabs                        = Admin::get_admin_tabs();
?>

<div class="wrap">

	<div id="tsdk_banner" class="swcfpc-banner"></div>
	<div id="swcfpc_main_content" class="<?php echo defined( 'SPC_PRO_PATH' ) ? '' : 'width_sidebar'; ?>"
		 data-cache_enabled="<?php echo $this->main_instance->get_single_config( 'cf_cache_enabled', 0 ); ?>">

		<h1><?php _e( 'Super Page Cache', 'wp-cloudflare-page-cache' ); ?></h1>
		<?php settings_errors(); ?>

		<?php if ( ! file_exists( $this->main_instance->get_plugin_wp_content_directory() ) ) : ?>
			<div class="notice is-dismissible notice-error">
				<p><?php echo sprintf( __( 'Unable to create the directory %s', 'wp-cloudflare-page-cache' ), $this->main_instance->get_plugin_wp_content_directory() ); ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">
						<?php _e( 'Hide this notice', 'wp-cloudflare-page-cache' ); ?>
					</span>
				</button>
			</div>

		<?php endif; ?>

		<?php if ( strlen( $error_msg ) > 0 ) : ?>
			<div class="notice is-dismissible notice-error">
				<p><?php echo sprintf( __( 'Error: %s', 'wp-cloudflare-page-cache' ), $error_msg ); ?></p>
				<button type="button" class="notice-dismiss"><span
							class="screen-reader-text"><?php _e( 'Hide this notice', 'wp-cloudflare-page-cache' ); ?></span>
				</button>
			</div>
		<?php endif; ?>

		<?php if ( ! $wizard_active && strlen( $success_msg ) > 0 ) : ?>
			<div class="notice is-dismissible notice-success"><p><?php echo $success_msg; ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">
						<?php _e( 'Hide this notice', 'wp-cloudflare-page-cache' ); ?>
					</span>
				</button>
			</div>
		<?php endif; ?>

		<?php if ( ! $this->main_instance->get_cache_controller()->is_cache_enabled() ) : ?>
			<?php load_view( 'admin_enable_cache_wizard' ); ?>
		<?php else : ?>

			<!-- Quick Actions -->
			<div id="swcfpc_actions">

				<h2><?php echo __( 'Quick Actions', 'wp-cloudflare-page-cache' ); ?></h2>

				<form action="" method="post" id="swcfpc_form_purge_cache">
					<p class="submit"><input type="submit" name="swcfpc_submit_purge_cache"
											 class="button button-secondary"
											 value="<?php _e( 'Purge Cache', 'wp-cloudflare-page-cache' ); ?>"></p>
				</form>

				<form action="" method="post" id="swcfpc_form_test_cache">
					<p class="submit"><input type="submit" name="swcfpc_submit_test_cache"
											 class="button button-secondary"
											 value="<?php _e( 'Test Cache', 'wp-cloudflare-page-cache' ); ?>"></p>
				</form>

				<?php if ( $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) > 0 ) : ?>
					<a id="swcfpc_purge_cache_everything" href="#"
					   title="<?php _e( 'Purge both HTML pages and static assets', 'wp-cloudflare-page-cache' ); ?>"><?php _e( 'Force purge everything', 'wp-cloudflare-page-cache' ); ?></a>
				<?php endif; ?>

				<?php if ( ! $this->main_instance->has_cloudflare_api_zone_id() ) : ?>
					<div class="swcfpc-highlight"><?php _e( 'We recommend you to enable the edge caching by connecting your Cloudflare account. It\'s free.', 'wp-cloudflare-page-cache' ); ?></div>
				<?php endif; ?>

			</div>

			<h2 id="swcfpc_tab_links" class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab_data ) {
					$tab_classes  = 'nav-tab';
					$tab_classes .= $tab_active === $tab_data['id'] || ( ! $tab_active && $tab_data['id'] === 'cache' ) ? ' nav-tab-active' : '';
					if ( isset( $tab_data['tab_classes'] ) ) {
						$tab_classes .= ' ' . $tab_data['tab_classes'];
					}
					?>
					<a data-tab="<?php echo esc_attr( $tab_data['id'] ); ?>"
					   class="<?php echo esc_attr( $tab_classes ); ?>">
						<?php
						if ( isset( $tab_data['locked'] ) && $tab_data['locked'] ) {
							echo '<span style="font-size:15px; display:flex; align-items:center; pointer-events:none" class="dashicons dashicons-lock"></span>';
						}
						echo esc_html( $tab_data['label'] );
						?>
					</a>
				<?php } ?>
			</h2>


			<!-- Dashboard Settings -->
			<form method="post" action="" id="swcfpc_options">
				<?php
				wp_nonce_field( 'swcfpc_index_nonce', 'swcfpc_index_nonce' );

				foreach ( $tabs as $tab_data ) {
					$tab_classes  = 'swcfpc_tab';
					$tab_classes .= $tab_active === $tab_data['id'] || ( ! $tab_active && $tab_data['id'] === 'cache' ) ? ' active' : '';
					if ( isset( $tab_data['tab_classes'] ) ) {
						$tab_classes .= ' ' . $tab_data['tab_classes'];
					}
					?>
					<div class="<?php echo esc_attr( $tab_classes ); ?>"
						 id="<?php echo esc_attr( $tab_data['id'] ); ?>">
						<?php load_view( $tab_data['template'], $tab_data['id'] ); ?>
					</div>
				<?php } ?>

				<?php do_action( 'swcfpc_after_tabs_content' ); ?>

				<input type="hidden" name="swcfpc_tab" value=""/>

				<div class="swcfpc_row">
					<p class="submit"><input type="submit" name="swcfpc_submit_general" class="button button-primary"
											 value="<?php _e( 'Update settings', 'wp-cloudflare-page-cache' ); ?>"></p>

					<p class="submit"><input type="submit" id="swcfpc_form_reset_all" name="swcfpc_submit_reset_all"
											 class="button button-secondary"
											 value="<?php _e( 'Reset All', 'wp-cloudflare-page-cache' ); ?>"></p>
				</div>
			</form>

		<?php endif; ?>
	</div>

	<?php
	if ( ! defined( 'SPC_PRO_PATH' ) ) {
		load_view( 'sidebar' );
	}
	?>

</div>
