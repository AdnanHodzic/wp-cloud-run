<?php

require_once SWCFPC_PLUGIN_PATH . 'src/views/template_functions.php';

$switch_counter = 0;
$tab_active     = isset( $_REQUEST['swcfpc_tab'] ) ? $_REQUEST['swcfpc_tab'] : false;

?>

<div class="wrap">

	<div id="swcfpc_main_content" class="<?php echo defined( 'SPC_PRO_PATH' ) ? '' : 'width_sidebar'; ?>"
		 data-cache_enabled="<?php echo $this->main_instance->get_single_config( 'cf_cache_enabled', 0 ); ?>">

	<h1><?php _e( 'Super Page Cache', 'wp-cloudflare-page-cache' ); ?></h1>
		<?php settings_errors(); ?>

		<?php if ( ! file_exists( $this->main_instance->get_plugin_wp_content_directory() ) ) : ?>

			<div class="notice is-dismissible notice-error">
				<p><?php echo sprintf( __( 'Unable to create the directory %s', 'wp-cloudflare-page-cache' ), $this->main_instance->get_plugin_wp_content_directory() ); ?></p>
				<button type="button" class="notice-dismiss"><span
							class="screen-reader-text"><?php _e( 'Hide this notice', 'wp-cloudflare-page-cache' ); ?></span>
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
				<button type="button" class="notice-dismiss"><span
							class="screen-reader-text"><?php _e( 'Hide this notice', 'wp-cloudflare-page-cache' ); ?></span>
				</button>
			</div>

		<?php endif; ?>

		<?php if ( ! $this->modules['cache_controller']->is_cache_enabled() ) : ?>

			<!-- WIZARD Enable Page Caching -->
			<div class="step">
				<h2><?php _e( 'Enable Page Caching', 'wp-cloudflare-page-cache' ); ?></h2>
				<p style="text-align: center;"><?php _e( 'A WordPress performance plugin that lets you get Edge Caching enabled on a Cloudflare free plan.', 'wp-cloudflare-page-cache' ); ?></p>

				<form action="" method="post" id="swcfpc_form_enable_cache">
					<p class="submit"><input type="submit" name="swcfpc_submit_enable_page_cache"
											 class="button button-primary green_button"
											 value="<?php _e( 'Enable Page Caching Now', 'wp-cloudflare-page-cache' ); ?>">
					</p>
					<div class="swcfpc-highlight"><?php _e( 'We strongly recommend disabling all page caching functions of other plugins.', 'wp-cloudflare-page-cache' ); ?></div>
				</form>
			</div>

		<?php else : ?>

			<!-- Quick Actions -->
			<div id="swcfpc_actions">

				<h2><?php echo __( 'Quick Actions', 'wp-cloudflare-page-cache' ); ?></h2>

				<form action="" method="post" id="swcfpc_form_disable_cache">
					<p class="submit"><input type="submit" name="swcfpc_submit_disable_page_cache"
											 class="button button-primary"
											 value="<?php _e( 'Disable All Caching', 'wp-cloudflare-page-cache' ); ?>">
					</p>
				</form>

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

				<?php if ( empty( $this->main_instance->get_cloudflare_api_zone_id() ) ) : ?>
					<div class="swcfpc-highlight"><?php _e( 'We recommend you to enable the edge caching by connecting your Cloudflare account. It\'s free.', 'wp-cloudflare-page-cache' ); ?></div>
				<?php endif; ?>

			</div>

			<!-- Dashboard Tabs -->


			<?php
			$tabs = [
				[
					'id'    => 'cache',
					'label' => __( 'Cache', 'wp-cloudflare-page-cache' ),
				],
				[
					'id'    => 'general',
					'label' => __( 'Cloudflare (CDN & Edge Caching)', 'wp-cloudflare-page-cache' ),
				],
				[
					'id'    => 'advanced',
					'label' => __( 'Advanced', 'wp-cloudflare-page-cache' ),
				],
				[
					'id'     => 'javascript',
					'label'  => __( 'Javascript', 'wp-cloudflare-page-cache' ),
					'locked' => ! defined( 'SPC_PRO_PATH' ),
				],
				[
					'id'    => 'media',
					'label' => __( 'Media', 'wp-cloudflare-page-cache' ),
				],
				[
					'id'    => 'thirdparty',
					'label' => __( 'Third Party', 'wp-cloudflare-page-cache' ),
				],
				[
					'id'    => 'faq',
					'label' => __( 'FAQ', 'wp-cloudflare-page-cache' ),
				],
			];

			if ( ! defined( 'OPTML_VERSION' ) ) {
				$tabs[] = [
					'id'    => 'image_optimization',
					'label' => __( 'Image Optimization', 'wp-cloudflare-page-cache' ),
				];
			}

			$tabs = apply_filters( 'swcfpc_admin_tabs', $tabs );

			?>

			<h2 id="swcfpc_tab_links" class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab_data ) {
					$tab_classes  = 'nav-tab';
					$tab_classes .= $tab_active === $tab_data['id'] || ( ! $tab_active && $tab_data['id'] === 'cache' ) ? ' nav-tab-active' : '';
					?>
					<a data-tab="<?php echo esc_attr( $tab_data['id'] ); ?>" class="<?php echo esc_attr( $tab_classes ); ?>">
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
				<?php wp_nonce_field( 'swcfpc_index_nonce', 'swcfpc_index_nonce' ); ?>

				<!-- GENERAL/CLOUDFLARE TAB -->
				<div class="swcfpc_tab
				<?php
				if ( $tab_active == 'general' ) {
					echo 'active';
				}
				?>
				" id="general">

					<div class="main_section_header first_section">
						<h3><?php echo __( 'Cloudflare General Settings', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<?php if ( count( $zone_id_list ) === 0 ) : ?>

						<!-- Cloudflare Account Signup -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'You don\'t have a Cloudflare account?', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( 'Cloudflare significantly speeds up your website by leveraging a global network of servers to deliver content faster to your visitors.', 'wp-cloudflare-page-cache' ); ?></div>

								<br>
								<label><?php _e( 'Here’s how it works', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( 'Cloudflare stores copies of your site’s content (HTML, images, CSS, and JavaScript files) on multiple servers around the world.', 'wp-cloudflare-page-cache' ); ?></div>
								<br>
								<div class="description"><?php _e( 'When a visitor accesses your site, Cloudflare serves this content from the server nearest to their location.', 'wp-cloudflare-page-cache' ); ?></div>
								<br>
								<div class="description"><?php _e( 'This approach, known as edge caching or content delivery network (CDN) service, reduces the distance data needs to travel.', 'wp-cloudflare-page-cache' ); ?></div>
								<br>
							</div>
							<div class="right_column">
								<a href="https://dash.cloudflare.com/sign-up?pt=f" target="_blank"
								   class="button button-secondary"><?php _e( 'Sign up for free', 'wp-cloudflare-page-cache' ); ?></a>
								<br><br>
								<div>
									<p><?php _e( 'After creating your account, get your API Keys:', 'wp-cloudflare-page-cache' ); ?></p>
									<ol>
										<li><a href="https://dash.cloudflare.com/login"
											   target="_blank"><?php _e( 'Log in to your Cloudflare account', 'wp-cloudflare-page-cache' ); ?></a> <?php _e( 'and click on My Profile', 'wp-cloudflare-page-cache' ); ?>
										</li>
										<li><?php _e( 'Click on API tokens, scroll to API Keys and click on View beside Global API Key', 'wp-cloudflare-page-cache' ); ?></li>
										<li><?php _e( 'Enter your Cloudflare login password and click on View', 'wp-cloudflare-page-cache' ); ?></li>
										<li><?php _e( 'Enter both API key and e-mail address into the form below and click on Update settings', 'wp-cloudflare-page-cache' ); ?></li>
									</ol>
								</div>
							</div>
							<div class="clear"></div>
						</div>
					<?php endif; ?>

					<?php
					// TODO: Simplify this.
					if ( ( $this->main_instance->get_cloudflare_api_zone_id() == '' && count( $zone_id_list ) > 0 ) || $this->main_instance->get_cloudflare_api_zone_id() != '' ) :
						?>
						<!-- Enable/Disable Cloudflare Cache -->
						<?php if ( $this->main_instance->get_cloudflare_api_zone_id() != '' ) : ?>
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Enable Cloudflare CDN & Caching', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( 'Serve cached files from Cloudflare using Cache Rule.', 'wp-cloudflare-page-cache' ); ?></div>
							</div>
							<div class="right_column">
								<div class="switch-field">
									<?php
									// Old page rule ID.
									$has_page_rule = ! empty( $this->main_instance->get_single_config( 'cf_page_rule_id', '' ) );
									// Had worker enabled.
									$worker_enabled = (int) $this->main_instance->get_single_config( 'cf_woker_enabled', 0 ) > 0;
									// Has cache rule ID.
									$has_rule_id                 = ! empty( $this->main_instance->get_single_config( 'cf_cache_settings_ruleset_rule_id', '' ) );
									$is_cloudflare_cache_enabled = $has_rule_id ? 1 : (int) ( $has_page_rule || $worker_enabled );
									?>
									<input type="radio" class="conditional_item"
										   id="switch_<?php echo ++$switch_counter; ?>_left"
										   name="swcfpc_enable_cache_rule" value="1"
										<?php
										if ( $is_cloudflare_cache_enabled ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Enabled', 'wp-cloudflare-page-cache' ); ?></label>
									<input type="radio" class="conditional_item"
										   id="switch_<?php echo $switch_counter; ?>_right"
										   name="swcfpc_enable_cache_rule" value="0"
										<?php
										if ( ! $is_cloudflare_cache_enabled ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'Disabled', 'wp-cloudflare-page-cache' ); ?></label>
								</div>
							</div>
							<div class="clear"></div>
						</div>
					<?php endif; ?>

						<?php if ( $this->main_instance->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_KEY ) : ?>

						<!-- Cloudflare Domain Name -->
						<div class="main_section">
							<div class="left_column">
								<label>
									<?php _e( 'Cloudflare Domain Name', 'wp-cloudflare-page-cache' ); ?>
									<span class="swcfpc-required">*</span>
								</label>
								<div class="description"><?php _e( 'Select the domain for which you want to enable the cache and click on Update settings.', 'wp-cloudflare-page-cache' ); ?></div>
							</div>
							<div class="right_column">

								<select name="swcfpc_cf_zoneid">

									<option value=""><?php _e( 'Select a Domain Name', 'wp-cloudflare-page-cache' ); ?></option>

									<?php

									$selected_zone_id = $this->main_instance->get_cloudflare_api_zone_id();
									if ( empty( $selected_zone_id ) ) {
										$host_domain_name = $this->main_instance->get_second_level_domain();

										foreach ( $zone_id_list as $zone_id_name => $zone_id ) {
											if ( strpos( $zone_id_name, $host_domain_name ) !== false ) {
												$selected_zone_id = $zone_id;
												break;
											}
										}
									}

									foreach ( $zone_id_list as $zone_id_name => $zone_id ) :
										?>

										<option value="<?php echo $zone_id; ?>"
											<?php
											if ( $zone_id === $selected_zone_id ) {
												echo 'selected';
											}
											?>
										><?php echo $zone_id_name; ?></option>

									<?php endforeach; ?>

								</select>

							</div>
							<?php if ( ! $this->main_instance->get_cloudflare_api_zone_id() != '' ) : ?>
								<div style="display: flex; justify-content: flex-end;">
									<button type="submit" name="swcfpc_submit_general"
											class="button button-primary green_button"><?php _e( 'Continue', 'wp-cloudflare-page-cache' ); ?></button>
								</div>
							<?php endif; ?>
							<div class="clear"></div>
						</div>

					<?php else : ?>

						<input type="hidden" name="swcfpc_cf_zoneid"
							   value="<?php echo $this->main_instance->get_cloudflare_api_zone_id(); ?>"/>

					<?php endif; ?>
					<?php endif; ?>

					<!-- Authentication Mode -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Authentication mode', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Authentication mode to use to connect to your Cloudflare account.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<select name="swcfpc_cf_auth_mode">
								<option value="<?php echo SWCFPC_AUTH_MODE_API_TOKEN; ?>"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_TOKEN ) {
										echo 'selected';
									}
									?>
								><?php _e( 'API Token', 'wp-cloudflare-page-cache' ); ?></option>
								<option value="<?php echo SWCFPC_AUTH_MODE_API_KEY; ?>"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_KEY ) {
										echo 'selected';
									}
									?>
								><?php _e( 'API Key', 'wp-cloudflare-page-cache' ); ?></option>
							</select>
						</div>
						<div class="clear"></div>
					</div>
					<!--<div class="main_section api_key_method <?php // if( $this->main_instance->get_single_config('cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY) != SWCFPC_AUTH_MODE_API_KEY ) echo 'swcfpc_hide'; ?>">-->

					<!-- Cloudflare e-mail -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Cloudflare e-mail', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'The email address you use to log in to Cloudflare.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="text" name="swcfpc_cf_email"
								   value="<?php echo $this->main_instance->get_cloudflare_api_email(); ?>"
								   autocomplete="off"/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Cloudflare API Key -->
					<div class="main_section api_key_method
					<?php
					if ( $this->main_instance->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) != SWCFPC_AUTH_MODE_API_KEY ) {
						echo 'swcfpc_hide';
					}
					?>
					">
						<div class="left_column">
							<label><?php _e( 'Cloudflare API Key', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'The Global API Key extrapolated from your Cloudflare account.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="password" name="swcfpc_cf_apikey"
								   value="<?php echo $this->main_instance->get_cloudflare_api_key(); ?>"
								   autocomplete="new-password"/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Cloudflare API Token -->
					<div class="main_section api_token_method
					<?php
					if ( $this->main_instance->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) != SWCFPC_AUTH_MODE_API_TOKEN ) {
						echo 'swcfpc_hide';
					}
					?>
					">
						<div class="left_column">
							<label><?php _e( 'Cloudflare API Token', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'The API Token extrapolated from your Cloudflare account.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="password" name="swcfpc_cf_apitoken"
								   value="<?php echo $this->main_instance->get_cloudflare_api_token(); ?>"
								   autocomplete="new-password"/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Cloudflare Domain Name (API TOKEN) -->
					<div class="main_section api_token_method
					<?php
					if ( $this->main_instance->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) != SWCFPC_AUTH_MODE_API_TOKEN ) {
						echo 'swcfpc_hide';
					}
					?>
					">
						<div class="left_column">
							<label><?php _e( 'Cloudflare Domain Name', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Select/add the domain name for which you want to enable the cache exactly as reported on Cloudflare, then click on Update settings.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="text" name="swcfpc_cf_apitoken_domain"
								   value="<?php echo $this->main_instance->get_single_config( 'cf_apitoken_domain', $this->main_instance->get_second_level_domain() ); ?>"
								   autocomplete="off"/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Log Mode -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Log mode', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Enable this option if you want log all communications between Cloudflare and this plugin.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" data-mainoption="logs" class="conditional_item"
									   id="switch_<?php echo ++$switch_counter; ?>_left" name="swcfpc_log_enabled"
									   value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'log_enabled', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Enabled', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" data-mainoption="logs" class="conditional_item"
									   id="switch_<?php echo $switch_counter; ?>_right" name="swcfpc_log_enabled"
									   value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'log_enabled', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'Disabled', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<?php
					// TODO: Simplify this.
					if ( ( $this->main_instance->get_cloudflare_api_zone_id() == '' && count( $zone_id_list ) > 0 ) || $this->main_instance->get_cloudflare_api_zone_id() != '' ) :
						?>
						<?php if ( $this->main_instance->get_cloudflare_api_zone_id() != '' ) { ?>
						<!-- Cache TTL -->
						<div class="main_section_header first_section">
							<h3><?php echo __( 'Cache lifetime settings', 'wp-cloudflare-page-cache' ); ?></h3>
						</div>

						<!-- Cloudflare Cache-Control max-age -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Cloudflare Cache-Control max-age', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( 'Don\'t touch if you don\'t know what is it. Must be greater than zero. Recommended 31536000 (1 year)', 'wp-cloudflare-page-cache' ); ?></div>
							</div>
							<div class="right_column">
								<input type="text" name="swcfpc_maxage"
									   value="<?php echo $this->main_instance->get_single_config( 'cf_maxage', '' ); ?>"/>
							</div>
							<div class="clear"></div>
						</div>

						<!-- Browser Cache-Control max-age -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Browser Cache-Control max-age', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( 'Don\'t touch if you don\'t know what is it. Must be greater than zero. Recommended a value between 60 and 600', 'wp-cloudflare-page-cache' ); ?></div>
							</div>
							<div class="right_column">
								<input type="text" name="swcfpc_browser_maxage"
									   value="<?php echo $this->main_instance->get_single_config( 'cf_browser_maxage', '' ); ?>"/>
							</div>
							<div class="clear"></div>
						</div>

						<div class="main_section_header">
							<h3><?php echo __( 'Cache behavior settings', 'wp-cloudflare-page-cache' ); ?></h3>
						</div>

						<!-- Automatically purge the Cloudflare's cache -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Automatically purge the Cloudflare\'s cache when something changes on the website', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description">
									<strong><?php _e( 'Example: update/publish a post/page', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'it is recommended to add the browser caching rules that you find', 'wp-cloudflare-page-cache' ); ?>
									<a href="<?php echo $nginx_instructions_page_url; ?>"
									   target="_blank"><?php _e( 'on this page', 'wp-cloudflare-page-cache' ); ?></a> <?php _e( 'after saving these settings', 'wp-cloudflare-page-cache' ); ?>
									.
								</div>
							</div>
							<div class="right_column">
								<div><input type="checkbox" name="swcfpc_cf_auto_purge"
											value="1" <?php echo $this->main_instance->get_single_config( 'cf_auto_purge', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Purge cache for related pages only', 'wp-cloudflare-page-cache' ); ?>
									- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
								<div><input type="checkbox" name="swcfpc_cf_auto_purge_all"
											value="1" <?php echo $this->main_instance->get_single_config( 'cf_auto_purge_all', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Purge whole cache', 'wp-cloudflare-page-cache' ); ?></strong>
								</div>
							</div>
							<div class="clear"></div>
						</div>

						<!-- Automatically purge the Page cache when Cloudflare cache is purged -->
						<div class="main_section fallbackcache">
							<div class="left_column">
								<label><?php _e( 'Automatically purge the Page cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
							<div class="right_column">
								<div class="switch-field">
									<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
										   name="swcfpc_cf_fallback_cache_auto_purge" value="1"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_fallback_cache_auto_purge', 0 ) > 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
									<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
										   name="swcfpc_cf_fallback_cache_auto_purge" value="0"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_fallback_cache_auto_purge', 0 ) <= 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
								</div>
							</div>
							<div class="clear"></div>
						</div>

						<!-- Force cache bypassing for backend with an additional Cloudflare page rule -->
						<div class="main_section cfworker_not">
							<div class="left_column">
								<label><?php _e( 'Force cache bypassing for backend with an additional Cloudflare page rule', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( '<strong>Read here:</strong> by default, all back-end URLs are not cached thanks to some response headers, but if for some circumstances your backend pages are still cached, you can enable this option which will add an <strong>additional page rule on Cloudflare</strong> to force cache bypassing for the whole WordPress backend directly from Cloudflare. This option will be ignored if worker mode is enabled.', 'wp-cloudflare-page-cache' ); ?></div>
							</div>
							<div class="right_column">
								<div class="switch-field">
									<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
										   name="swcfpc_cf_bypass_backend_page_rule" value="1"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule', 0 ) > 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Enabled', 'wp-cloudflare-page-cache' ); ?></label>
									<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
										   name="swcfpc_cf_bypass_backend_page_rule" value="0"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_bypass_backend_page_rule', 0 ) <= 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'Disabled', 'wp-cloudflare-page-cache' ); ?></label>
								</div>
							</div>
							<div class="clear"></div>
						</div>

						<!-- CF Worker -->
						<div class="main_section_header first_section">
							<h3><?php echo __( 'Cloudflare Workers', 'wp-cloudflare-page-cache' ); ?></h3>
						</div>

						<div class="description_section">
							<?php _e( 'This is a different way of using Cloudflare as a page caching system. Instead of page rules, you can use Cloudflare workers. This mode is only recommended if there are conflicts with the current web server or other plugins, as it is not 100% free.', 'wp-cloudflare-page-cache' ); ?>
						</div>

						<!-- Enable Cloudflare Worker -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Worker mode', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( 'Use Cloudflare Worker instead of page rule.', 'wp-cloudflare-page-cache' ); ?></div>
							</div>
							<div class="right_column">
								<div class="switch-field">
									<input type="radio" data-mainoption="cfworker" class="conditional_item"
										   id="switch_<?php echo ++$switch_counter; ?>_left"
										   name="swcfpc_cf_woker_enabled" value="1"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_woker_enabled', 0 ) > 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Enabled', 'wp-cloudflare-page-cache' ); ?></label>
									<input type="radio" data-mainoption="cfworker" class="conditional_item"
										   id="switch_<?php echo $switch_counter; ?>_right"
										   name="swcfpc_cf_woker_enabled" value="0"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_woker_enabled', 0 ) <= 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'Disabled', 'wp-cloudflare-page-cache' ); ?></label>
								</div>

								<?php if ( $this->main_instance->get_single_config( 'cf_auth_mode', SWCFPC_AUTH_MODE_API_KEY ) == SWCFPC_AUTH_MODE_API_TOKEN ) : ?>

									<br/>
									<div class="description highlighted"><?php echo sprintf( __( 'If you are using an API Token, make sure you have enabled the permissions %1$s and %2$s', 'wp-cloudflare-page-cache' ), '<strong>' . __( 'Zone - Worker Routes - Edit', 'wp-cloudflare-page-cache' ) . '</strong>', '<strong>' . __( 'Account - Worker Scripts - Edit', 'wp-cloudflare-page-cache' ) . '</strong>' ); ?></div>
									<br/>
									<div class="description highlighted"><?php _e( 'After enabled this option, enter to <strong>Workers</strong> section of your domain on Cloudflare, click on Edit near to Worker <strong>swcfpc_worker</strong> than select <strong>Fail open</strong> as <em>Request limit failure mode</em> and click on Save', 'wp-cloudflare-page-cache' ); ?></div>
									<br/>

								<?php endif; ?>
							</div>
							<div class="clear"></div>
						</div>

						<!-- CF Worker Bypass Cookies -->
						<div class="main_section cfworker">
							<div class="left_column">
								<label><?php _e( 'Bypass cache for the following cookies', 'wp-cloudflare-page-cache' ); ?></label>
								<div class="description"><?php _e( 'One cookie per line.', 'wp-cloudflare-page-cache' ); ?></div>
								<br/>
								<div class="description">
									<strong><?php _e( 'Read here', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'to apply the changes you will need to purge the cache after saving.', 'wp-cloudflare-page-cache' ); ?>
								</div>
							</div>
							<div class="right_column">
								<textarea
										name="swcfpc_cf_worker_bypass_cookies"><?php echo ( is_array( $this->main_instance->get_single_config( 'cf_worker_bypass_cookies', [] ) ) && count( $this->main_instance->get_single_config( 'cf_worker_bypass_cookies', [] ) ) > 0 ) ? implode( "\n", $this->main_instance->get_single_config( 'cf_worker_bypass_cookies', '' ) ) : ''; ?></textarea>
							</div>
							<div class="clear"></div>
						</div>

						<!-- Other -->
						<div class="main_section_header">
							<h3>
								<?php echo __( 'Other', 'wp-cloudflare-page-cache' ); ?>
							</h3>
						</div>

						<!-- Automatically purge the OPcache when Cloudflare cache is purged -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Automatically purge the OPcache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
							<div class="right_column">
								<div class="switch-field">
									<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
										   name="swcfpc_cf_opcache_purge_on_flush" value="1"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_opcache_purge_on_flush', 0 ) > 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
									<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
										   name="swcfpc_cf_opcache_purge_on_flush" value="0"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_opcache_purge_on_flush', 0 ) <= 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
								</div>
							</div>
							<div class="clear"></div>
						</div>

						<!-- Automatically purge the object cache when Cloudflare cache is purged -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Automatically purge the object cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
							<div class="right_column">
								<div class="switch-field">
									<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
										   name="swcfpc_cf_object_cache_purge_on_flush" value="1"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_object_cache_purge_on_flush', 0 ) > 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
									<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
										   name="swcfpc_cf_object_cache_purge_on_flush" value="0"
										<?php
										if ( $this->main_instance->get_single_config( 'cf_object_cache_purge_on_flush', 0 ) <= 0 ) {
											echo 'checked';
										}
										?>
									/>
									<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
								</div>
							</div>
							<div class="clear"></div>
						</div>

						<!-- Purge the whole Cloudflare cache via Cronjob -->
						<div class="main_section">
							<div class="left_column">
								<label><?php _e( 'Purge the whole Cloudflare cache via Cronjob', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
							<div class="right_column">
								<p><?php _e( 'If you want purge the whole Cloudflare cache at specific intervals decided by you, you can create a cronjob that hits the following URL', 'wp-cloudflare-page-cache' ); ?>
									:</p>
								<p><strong><?php echo $cronjob_url; ?></strong></p>
							</div>
							<div class="clear"></div>
						</div>

					<?php } ?>

					<?php endif; ?>
				</div>

				<!-- CACHE TAB -->
				<div class="swcfpc_tab
				<?php
				if ( ! $tab_active || $tab_active == 'cache' ) {
					echo 'active';
				}
				?>
				" id="cache">

					<!-- Fallback page caching -->
					<div class="main_section_header">
						<h3><?php echo __( 'Cache Settings', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<!-- <div class="description_section">
						<?php
						// _e('This is a traditional page cache on disk but which follows the same rules of the cache on Cloudflare set with this plugin. It is very useful when Cloudflare on its own initiative decides to invalidate a few pages from its cache. Thanks to this function you will no longer need to use other page caching functions of other plugins.', 'wp-cloudflare-page-cache');
						?>
					</div> -->

					<?php if ( ! $this->modules['fallback_cache']->fallback_cache_is_wp_config_writable() ) : ?>
						<div class="description_section highlighted"><?php _e( 'The file wp-config.php is not writable. Please add write permission to activate the fallback cache.', 'wp-cloudflare-page-cache' ); ?></div>
					<?php endif; ?>

					<?php if ( ! $this->modules['fallback_cache']->fallback_cache_is_wp_content_writable() ) : ?>
						<div class="description_section highlighted"><?php _e( 'The directory wp-content is not writable. Please add write permission or you have to use the fallback cache with cURL.', 'wp-cloudflare-page-cache' ); ?></div>
					<?php endif; ?>

					<!-- Enable Disk Page cache -->
					<div class="main_section fallbackcache">
						<div class="left_column">
							<label><?php _e( 'Enable Disk Page cache', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Disable this option if you want to use only Cloudflare Cache.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">

							<div class="switch-field">
								<input type="radio" data-mainoption="cloudflarecache" class="conditional_item"
									   id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_fallback_cache" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" data-mainoption="cloudflarecache" class="conditional_item"
									   id="switch_<?php echo $switch_counter; ?>_right" name="swcfpc_cf_fallback_cache"
									   value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

							<br/>
							<div class="description highlighted"><?php _e( 'If you enable the DIsk Page cache is strongly recommended disable all page caching functions of other plugins.', 'wp-cloudflare-page-cache' ); ?></div>


						</div>
						<div class="clear"></div>
					</div>


					<!-- Cache TTL/Lifespan -->
					<div class="main_section fallbackcache cloudflarecache">
						<div class="left_column">
							<label><?php _e( 'Cache Lifespan', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Enter 0 for no expiration.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="text" name="swcfpc_cf_fallback_cache_ttl"
								   value="<?php echo $this->main_instance->get_single_config( 'cf_fallback_cache_ttl', 0 ); ?>"/>
							<div class="description"><?php _e( 'Enter a value in seconds.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- ByPass Cache when cookies are present -->
					<div class="main_section fallbackcache cloudflarecache">
						<div class="left_column">
							<label><?php _e( 'Bypass Page cache when these cookies are present in the request packet', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'One cookie name per line. These strings will be used by preg_grep.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<textarea
									name="swcfpc_cf_fallback_cache_excluded_cookies"><?php echo ( is_array( $this->main_instance->get_single_config( 'cf_fallback_cache_excluded_cookies', [] ) ) && count( $this->main_instance->get_single_config( 'cf_fallback_cache_excluded_cookies', [] ) ) > 0 ) ? implode( "\n", $this->main_instance->get_single_config( 'cf_fallback_cache_excluded_cookies', '' ) ) : ''; ?></textarea>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Don't cache the following dynamic contents -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Don\'t cache the following dynamic contents:', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_bypass_404"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_404', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Page 404 (is_404)', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_single_post"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_single_post', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Single posts (is_single)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_pages"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_pages', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Pages (is_page)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_front_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_front_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Front Page (is_front_page)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_home"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_home', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Home (is_home)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_archives"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_archives', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Archives (is_archive)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_tags"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_tags', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Tags (is_tag)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_category"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_category', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Categories (is_category)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_feeds"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_feeds', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Feeds (is_feed)', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_search_pages"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_search_pages', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Search Pages (is_search)', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_author_pages"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_author_pages', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Author Pages (is_author)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_amp"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_amp', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'AMP pages', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_ajax"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_ajax', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Ajax requests', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_query_var"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_query_var', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Pages with query args', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_wp_json_rest"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_wp_json_rest', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WP JSON endpoints', 'wp-cloudflare-page-cache' ); ?>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Don't cache the following static contents -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Don\'t cache the following static contents:', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description">
								<div class="orange_color"><?php _e( 'Writes into .htaccess', 'wp-cloudflare-page-cache' ); ?></div>
								<br/>
								<strong><?php _e( 'If you only use Nginx', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'it is recommended to add the browser caching rules that you find', 'wp-cloudflare-page-cache' ); ?>
								<a href="<?php echo $nginx_instructions_page_url; ?>"
								   target="_blank"><?php _e( 'on this page', 'wp-cloudflare-page-cache' ); ?></a> <?php _e( 'after saving these settings', 'wp-cloudflare-page-cache' ); ?>
								.
							</div>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_bypass_sitemap"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_sitemap', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'XML sitemaps', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_file_robots"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_file_robots', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Robots.txt', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Prevent the following URIs to be cached -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Prevent the following URIs to be cached', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'One URI per line. You can use the * for wildcard URLs.', 'wp-cloudflare-page-cache' ); ?></div>
							<div class="description"><?php _e( 'Example', 'wp-cloudflare-page-cache' ); ?>:
								/my-page<br/>/my-main-page/my-sub-page<br/>/my-main-page*
							</div>
						</div>
						<div class="right_column">
							<textarea
									name="swcfpc_cf_excluded_urls"><?php echo ( is_array( $this->main_instance->get_single_config( 'cf_excluded_urls', [] ) ) && count( $this->main_instance->get_single_config( 'cf_excluded_urls', [] ) ) > 0 ) ? implode( "\n", $this->main_instance->get_single_config( 'cf_excluded_urls', '' ) ) : ''; ?></textarea>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Posts per page -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Posts per page', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Enter how many posts per page (or category) the theme shows to your users. It will be use to clean up the pagination on cache purge.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="text" name="swcfpc_post_per_page"
								   value="<?php echo $this->main_instance->get_single_config( 'cf_post_per_page', '' ); ?>"/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Browser caching -->
					<div class="main_section_header">
						<h3><?php echo __( 'Browser caching', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<div class="description_section">
						<?php _e( 'This option is useful if you want to use Super Page Cache to enable browser caching rules for assets such like images, CSS, scripts, etc. It works automatically if you use Apache as web server or as backend web server.', 'wp-cloudflare-page-cache' ); ?>
					</div>

					<!-- Add browser caching rules for static assets -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Add browser caching rules for static assets', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description">
								<div class="orange_color"><?php _e( 'Writes into .htaccess', 'wp-cloudflare-page-cache' ); ?></div>
								<br/>
							</div>
							<div class="description">
								<strong><?php _e( 'Read here if you only use Nginx', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'it is not possible for Super Page Cache to automatically change the settings to allow this option to work immediately. For it to work, update these settings and then follow the instructions', 'wp-cloudflare-page-cache' ); ?>
								<a href="<?php echo $nginx_instructions_page_url; ?>"
								   target="_blank"><?php _e( 'on this page', 'wp-cloudflare-page-cache' ); ?>.</a></div>
						</div>
						<div class="right_column">

							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_browser_caching_htaccess" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_browser_caching_htaccess', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_browser_caching_htaccess" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_browser_caching_htaccess', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

							<br/>
							<div class="description highlighted"><?php _e( 'If you are using Plesk, make sure you have disabled the options "Smart static files processing" and "Serve static files directly by Nginx" on "Apache & Nginx Settings" page of your Plesk panel or ask your hosting provider to update browser caching rules for you.', 'wp-cloudflare-page-cache' ); ?></div>

						</div>
						<div class="clear"></div>
					</div>

				</div>


				<!-- ADVANCED TAB -->
				<div class="swcfpc_tab
				<?php
				if ( $tab_active == 'advanced' ) {
					echo 'active';
				}
				?>
				" id="advanced">

					<!-- Cache Advanced Settings -->
					<div class="main_section_header first_section">
						<h3><?php echo __( 'Cache', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<!-- Use cURL -->
					<div class="main_section fallbackcache">
						<div class="left_column">
							<label><?php _e( 'Use cURL', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Use cURL instead of WordPress advanced-cache.php to generate the Page page. It can increase the time it takes to generate the Page cache but improves compatibility with other performance plugins.', 'wp-cloudflare-page-cache' ); ?></div>

						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_fallback_cache_curl" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache_curl', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_fallback_cache_curl" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache_curl', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Save response headers -->
					<div class="main_section fallbackcache">
						<div class="left_column">
							<label><?php _e( 'Save response headers', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Save response headers together with HTML code.', 'wp-cloudflare-page-cache' ); ?></div>
							<div class="description"><?php _e( 'The following response header will never be saved:', 'wp-cloudflare-page-cache' ); ?>
								cache-control, set-cookie, X-WP-CF-Super-Cache*
							</div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_fallback_cache_save_headers" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache_save_headers', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_fallback_cache_save_headers" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache_save_headers', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Prevent cache URLs without trailing slash -->
					<div class="main_section fallbackcache">
						<div class="left_column">
							<label><?php _e( 'Prevent to cache URLs without trailing slash', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_fallback_cache_prevent_cache_urls_without_trailing_slash"
									   value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache_prevent_cache_urls_without_trailing_slash', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_fallback_cache_prevent_cache_urls_without_trailing_slash"
									   value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_fallback_cache_prevent_cache_urls_without_trailing_slash', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Automatically purge single post cache when a new comment -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge single post cache when a new comment is inserted into the database or when a comment is approved or deleted', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_auto_purge_on_comments" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_comments', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_auto_purge_on_comments" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_comments', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Automatically purge the cache when the upgrader process is complete -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when the upgrader process is complete', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_auto_purge_on_upgrader_process_complete" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_upgrader_process_complete', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_auto_purge_on_upgrader_process_complete" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_on_upgrader_process_complete', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Strip response cookies -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Strip response cookies on pages that should be cached', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Cloudflare will not cache when there are cookies in responses unless you strip out them to overwrite the behavior.', 'wp-cloudflare-page-cache' ); ?></div>
							<div class="description"><?php _e( 'If the cache does not work due to response cookies and you are sure that these cookies are not essential for the website to works, enable this option.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">

							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_strip_cookies" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_strip_cookies', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_strip_cookies" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_strip_cookies', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

						</div>
						<div class="clear"></div>
					</div>

					<!-- Overwrite the cache-control header for WordPress's pages using web server rules -->
					<div class="main_section cfworker_not">
						<div class="left_column">
							<label><?php _e( 'Overwrite the cache-control header for WordPress\'s pages using web server rules', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description">
								<div class="orange_color"><?php _e( 'Writes into .htaccess', 'wp-cloudflare-page-cache' ); ?></div>
								<br/>
								<?php _e( 'This option is useful if you use Super Page Cache together with other performance plugins that could affect the Cloudflare cache with their cache-control headers. It works automatically if you are using Apache as web server or as backend web server.', 'wp-cloudflare-page-cache' ); ?>
							</div>
						</div>
						<div class="right_column">

							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_cache_control_htaccess" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_cache_control_htaccess', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_cache_control_htaccess" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_cache_control_htaccess', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

							<br/>
							<div class="description highlighted"><?php _e( 'This option is not essential and must be disabled if enabled the Workers mode option. In most cases this plugin works out of the box. If the page cache does not work after a considerable number of attempts or you see that max-age and s-maxage values of <strong>X-WP-CF-Super-Cache-Cache-Control</strong> response header are not the same of the ones in <strong>Cache-Control</strong> response header, activate this option.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>

							<div class="description">
								<strong><?php _e( 'Read here if you use Apache (htaccess)', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'for overwriting to work, make sure that the rules added by Super Page Cache are placed at the bottom of the htaccess file. If they are present BEFORE other caching rules of other plugins, move them to the bottom manually.', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<br/>
							<div class="description">
								<strong><?php _e( 'Read here if you only use Nginx', 'wp-cloudflare-page-cache' ); ?></strong>: <?php _e( 'it is not possible for Super Page Cache to automatically change the settings to allow this option to work immediately. For it to work, update these settings and then follow the instructions', 'wp-cloudflare-page-cache' ); ?>
								<a href="<?php echo $nginx_instructions_page_url; ?>"
								   target="_blank"><?php _e( 'on this page', 'wp-cloudflare-page-cache' ); ?>.</a></div>

						</div>
						<div class="clear"></div>
					</div>

					<!-- Purge HTML pages only -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Purge HTML pages only', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description">

								<?php _e( 'Purge only the cached HTML pages instead of the whole Cloudflare cache (assets + pages).', 'wp-cloudflare-page-cache' ); ?>

								<?php if ( $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) > 0 ) : ?>
									<br/><br/>
									<a href="<?php echo $cached_html_pages_list_url; ?>"
									   target="_blank"><?php _e( 'Show cached HTML pages list', 'wp-cloudflare-page-cache' ); ?></a>
								<?php endif; ?>

							</div>
						</div>
						<div class="right_column">

							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_purge_only_html" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_purge_only_html" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_purge_only_html', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

						</div>
						<div class="clear"></div>
					</div>

					<!-- Disable cache purging using queue -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Disable cache purging using queue', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'By default this plugin purge the cache after 10 seconds from the purging action, to avoid a high number of purge requests in case of multiple events triggered by third party plugins. This is done using a classic WordPress scheduled event. If you notice any errors regarding the scheduled intervals, you can deactivate this mode by enabling this option.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">

							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_disable_cache_purging_queue" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_disable_cache_purging_queue', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_disable_cache_purging_queue" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_disable_cache_purging_queue', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

						</div>
						<div class="clear"></div>
					</div>

					<div class="main_section_header first_section">
						<h3><?php echo __( 'Preloader', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<!-- Enable Preloader -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Enable preloader', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input data-mainoption="preloader" class="conditional_item" type="radio"
									   id="switch_<?php echo ++$switch_counter; ?>_left" name="swcfpc_cf_preloader"
									   value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_preloader', 1 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input data-mainoption="preloader" class="conditional_item" type="radio"
									   id="switch_<?php echo $switch_counter; ?>_right" name="swcfpc_cf_preloader"
									   value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_preloader', 1 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Automatically preload the pages you have purged from cache -->
					<div class="main_section preloader">
						<div class="left_column">
							<label><?php _e( 'Automatically preload the pages you have purged from cache.', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_cache_preloader_start_on_purge" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_preloader_start_on_purge', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_cache_preloader_start_on_purge" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_preloader_start_on_purge', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Preloader operation -->
					<div class="main_section preloader">
						<div class="left_column">
							<label><?php _e( 'Preloader operation', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Choose the URLs preloading logic that the preloader must use. If no option is chosen, the most recently published URLs and the home page will be preloaded.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">

							<?php
							if ( is_array( $wordpress_menus ) && count( $wordpress_menus ) > 0 ) :
								foreach ( $wordpress_menus as $single_nav_menu ) :
									?>

									<div><input type="checkbox" name="swcfpc_cf_preloader_nav_menus[]"
												value="<?php echo $single_nav_menu->term_id; ?>" <?php echo in_array( $single_nav_menu->term_id, $this->main_instance->get_single_config( 'cf_preloader_nav_menus', [] ) ) ? 'checked' : ''; ?> /> <?php echo sprintf( __( 'Preload all internal links in <strong>%s</strong> WP menu', 'wp-cloudflare-page-cache' ), $single_nav_menu->name ); ?>
									</div>

									<?php
								endforeach;
							endif;
							?>

							<div><input type="checkbox" name="swcfpc_cf_preload_last_urls"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_preload_last_urls', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Preload last 20 published/updated posts, pages & CPTs combined', 'wp-cloudflare-page-cache' ); ?>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Preload all URLs into the following sitemaps -->
					<div class="main_section preloader">
						<div class="left_column">
							<label><?php _e( 'Preload all URLs into the following sitemaps', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'One sitemap per line.', 'wp-cloudflare-page-cache' ); ?></div>
							<div class="description"><?php _e( 'Example', 'wp-cloudflare-page-cache' ); ?>:
								/post-sitemap.xml<br/>/page-sitemap.xml
							</div>
						</div>
						<div class="right_column">
							<textarea
									name="swcfpc_cf_preload_sitemap_urls"><?php echo ( is_array( $this->main_instance->get_single_config( 'cf_preload_sitemap_urls', [] ) ) && count( $this->main_instance->get_single_config( 'cf_preload_sitemap_urls', [] ) ) > 0 ) ? implode( "\n", $this->main_instance->get_single_config( 'cf_preload_sitemap_urls', '' ) ) : ''; ?></textarea>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Start the preloader manually -->
					<div class="main_section preloader">
						<div class="left_column">
							<label><?php _e( 'Start the preloader manually', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Start preloading the pages of your website to speed up their inclusion in the cache. Make sure the cache is working first.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<button type="button" id="swcfpc_start_preloader"
									class="button button-primary"><?php _e( 'Start preloader', 'wp-cloudflare-page-cache' ); ?></button>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Start the preloader via Cronjob -->
					<div class="main_section preloader">
						<div class="left_column">
							<label><?php _e( 'Start the preloader via Cronjob', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<p><?php _e( 'If you want start the preloader at specific intervals decided by you, you can create a cronjob that hits the following URL', 'wp-cloudflare-page-cache' ); ?>
								:</p>
							<p><strong><?php echo $preloader_cronjob_url; ?></strong></p>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Cronjob secret key -->
					<div class="main_section preloader">
						<div class="left_column">
							<label><?php _e( 'Cronjob secret key', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Secret key to use to start the preloader via URL. Don\'t touch if you don\'t know how to use it.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="text" name="swcfpc_cf_preloader_url_secret_key"
								   value="<?php echo $this->main_instance->get_single_config( 'cf_preloader_url_secret_key', wp_generate_password( 20, false, false ) ); ?>"/>
						</div>
						<div class="clear"></div>
					</div>

					<?php if ( ! $this->modules['cache_controller']->can_i_start_preloader() ) : ?>

						<!-- Manually unlock preloader -->
						<div class="main_section preloader">
							<div class="left_column">
								<label><?php _e( 'Manually unlock preloader', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
							<div class="right_column">
								<button type="button" id="swcfpc_unlock_preloader"
										class="button button-primary"><?php _e( 'Unlock preloader', 'wp-cloudflare-page-cache' ); ?></button>
							</div>
							<div class="clear"></div>
						</div>

					<?php endif; ?>


					<!-- Varnish Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Varnish settings', 'wp-cloudflare-page-cache' ); ?>
						</h3>
					</div>

					<!-- Enable Varnish Support -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Varnish Support', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input data-mainoption="varnish" class="conditional_item" type="radio"
									   id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_varnish_support" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_varnish_support', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input data-mainoption="varnish" class="conditional_item" type="radio"
									   id="switch_<?php echo $switch_counter; ?>_right" name="swcfpc_cf_varnish_support"
									   value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_varnish_support', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Varnish Hostname -->
					<div class="main_section varnish">
						<div class="left_column">
							<label><?php _e( 'Hostname', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="right_column">
								<input type="text" name="swcfpc_cf_varnish_hostname"
									   value="<?php echo $this->main_instance->get_single_config( 'cf_varnish_hostname', 'localhost' ); ?>"/>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Varnish Port -->
					<div class="main_section varnish">
						<div class="left_column">
							<label><?php _e( 'Port', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="right_column">
								<input type="text" name="swcfpc_cf_varnish_port"
									   value="<?php echo $this->main_instance->get_single_config( 'cf_varnish_port', 6081 ); ?>"/>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Varnish HTTP method for single page cache purge -->
					<div class="main_section varnish">
						<div class="left_column">
							<label><?php _e( 'HTTP method for single page cache purge', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="right_column">
								<input type="text" name="swcfpc_cf_varnish_purge_method"
									   value="<?php echo $this->main_instance->get_single_config( 'cf_varnish_purge_method', 'PURGE' ); ?>"/>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Varnish HTTP method for whole page cache purge -->
					<div class="main_section varnish">
						<div class="left_column">
							<label><?php _e( 'HTTP method for whole page cache purge', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="right_column">
								<input type="text" name="swcfpc_cf_varnish_purge_all_method"
									   value="<?php echo $this->main_instance->get_single_config( 'cf_varnish_purge_all_method', 'PURGE' ); ?>"/>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Varnish Cloudways -->
					<div class="main_section varnish">
						<div class="left_column">
							<label><?php _e( 'Cloudways Varnish', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Enable this option if you are using Varnish on Cloudways.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_varnish_cw" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_varnish_cw', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_varnish_cw" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_varnish_cw', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Automatically purge Varnish cache when the cache is purged -->
					<div class="main_section varnish">
						<div class="left_column">
							<label><?php _e( 'Automatically purge Varnish cache when the cache is purged.', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_varnish_auto_purge" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_varnish_auto_purge', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_varnish_auto_purge" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_varnish_auto_purge', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Purge Varnish cache -->
					<div class="main_section varnish">
						<div class="left_column">
							<label><?php _e( 'Purge Varnish cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<button type="button" id="swcfpc_varnish_cache_purge"
									class="button button-primary"><?php _e( 'Purge cache', 'wp-cloudflare-page-cache' ); ?></button>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Logs -->
					<div class="main_section_header first_section logs">
						<h3><?php echo __( 'Logs', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<!-- Clear logs -->
					<div class="main_section logs">
						<div class="left_column">
							<label><?php _e( 'Clear logs manually', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Delete all the logs currently stored and optimize the log table.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<button type="button" id="swcfpc_clear_logs"
									class="button button-primary"><?php _e( 'Clear logs now', 'wp-cloudflare-page-cache' ); ?></button>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Download logs -->
					<div class="main_section logs">
						<div class="left_column">
							<label><?php _e( 'Download logs', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<a href="<?php echo add_query_arg( [ 'swcfpc_download_log' => 1 ], admin_url() ); ?>"
							   target="_blank">
								<button type="button"
										class="button button-primary"><?php _e( 'Download log file', 'wp-cloudflare-page-cache' ); ?></button>
							</a>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Max log file size -->
					<div class="main_section logs">
						<div class="left_column">
							<label><?php _e( 'Max log file size in MB', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Automatically reset the log file when it exceeded the max file size. Set 0 to never reset it.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="text" name="swcfpc_log_max_file_size"
								   value="<?php echo $this->main_instance->get_single_config( 'log_max_file_size', 2 ); ?>"/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Log verbosity -->
					<div class="main_section logs">
						<div class="left_column">
							<label><?php _e( 'Log verbosity', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<select name="swcfpc_log_verbosity">
								<option value="<?php echo SWCFPC_LOGS_STANDARD_VERBOSITY; ?>"
									<?php
									if ( $this->main_instance->get_single_config( 'log_verbosity', SWCFPC_LOGS_STANDARD_VERBOSITY ) == SWCFPC_LOGS_STANDARD_VERBOSITY ) {
										echo 'selected';
									}
									?>
								><?php _e( 'Standard', 'wp-cloudflare-page-cache' ); ?></option>
								<option value="<?php echo SWCFPC_LOGS_HIGH_VERBOSITY; ?>"
									<?php
									if ( $this->main_instance->get_single_config( 'log_verbosity', SWCFPC_LOGS_STANDARD_VERBOSITY ) == SWCFPC_LOGS_HIGH_VERBOSITY ) {
										echo 'selected';
									}
									?>
								><?php _e( 'High', 'wp-cloudflare-page-cache' ); ?></option>
							</select>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Import/Export settings -->
					<div class="main_section_header">
						<h3><?php echo __( 'Import/Export', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<!-- Export config file -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Export config file', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<a href="<?php echo add_query_arg( [ 'swcfpc_export_config' => 1 ], admin_url() ); ?>"
							   target="_blank">
								<button type="button"
										class="button button-primary"><?php _e( 'Export', 'wp-cloudflare-page-cache' ); ?></button>
							</a>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Import config file -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Import config file', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Import the options of the previously exported configuration file.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>
							<div class="description"><?php _e( '<strong>Read here:</strong> after the import you will be forced to re-enter the authentication data to Cloudflare and to manually enable the cache.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<textarea name="swcfpc_import_config" id="swcfpc_import_config_content"
									  placeholder="<?php _e( 'Copy and paste here the content of the swcfpc_config.json file', 'wp-cloudflare-page-cache' ); ?>"></textarea>
							<button type="button" id="swcfpc_import_config_start"
									class="button button-primary"><?php _e( 'Import', 'wp-cloudflare-page-cache' ); ?></button>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Other settings -->
					<div class="main_section_header">
						<h3><?php echo __( 'Other settings', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<!-- Purge cache URL secret key -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Purge cache URL secret key', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Secret key to use to purge the whole Cloudflare cache via URL. Don\'t touch if you don\'t know how to use it.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<input type="text" name="swcfpc_cf_purge_url_secret_key"
								   value="<?php echo $this->main_instance->get_single_config( 'cf_purge_url_secret_key', wp_generate_password( 20, false, false ) ); ?>"/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Remove purge option from toolbar -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Remove purge option from toolbar', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_remove_purge_option_toolbar" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_remove_purge_option_toolbar', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_remove_purge_option_toolbar" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_remove_purge_option_toolbar', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Disable metaboxes on single pages and posts -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Disable metaboxes on single pages and posts', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'If enabled, a metabox is displayed for each post type by allowing you to exclude specific pages/posts from the cache.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_disable_single_metabox" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_disable_single_metabox', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_disable_single_metabox" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_disable_single_metabox', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- SEO redirect -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'SEO redirect', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Redirect 301 for all URLs that for any reason have been indexed together with the cache buster. Works for logged out users only.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_seo_redirect" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_seo_redirect', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Enabled', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_seo_redirect" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_seo_redirect', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'Disabled', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Select user roles allowed to purge the cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Select user roles allowed to purge the cache', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Admins are always allowed.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<?php
							if ( is_array( $wordpress_roles ) && count( $wordpress_roles ) > 0 ) :
								foreach ( $wordpress_roles as $single_role_name ) :
									if ( $single_role_name == 'administrator' ) {
										continue;
									}
									?>
									<div><input type="checkbox" name="swcfpc_purge_roles[]"
												value="<?php echo $single_role_name; ?>" <?php echo in_array( $single_role_name, $this->main_instance->get_single_config( 'cf_purge_roles', [] ) ) ? 'checked' : ''; ?> /> <?php echo $single_role_name; ?>
									</div>
									<?php
								endforeach;
							endif;
							?>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Auto prefetch URLs in viewport -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Auto prefetch URLs in viewport', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'If enabled, the browser prefetches in background all the internal URLs found in the viewport.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_prefetch_urls_viewport" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_prefetch_urls_viewport', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_prefetch_urls_viewport" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_prefetch_urls_viewport', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

							<br/>
							<div class="description highlighted"><?php _e( 'Purge the cache and wait about 30 seconds after enabling/disabling this option.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>
							<div class="description highlighted"><?php _e( 'URIs in <em>Prevent the following URIs to be cached</em> will not be prefetched.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Auto prefetch URLs on mouse hover -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Auto prefetch URLs on mouse hover', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'If enabled, it preloads a page right before a user clicks on it. It uses instant.page just-in-time preloading.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_prefetch_urls_on_hover" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_prefetch_urls_on_hover', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_prefetch_urls_on_hover" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_prefetch_urls_on_hover', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

							<br/>
							<div class="description highlighted"><?php _e( 'Purge the cache and wait about 30 seconds after enabling/disabling this option.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>
							<div class="description highlighted"><?php _e( 'URIs in <em>Prevent the following URIs to be cached</em> will not be prefetched.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Remove Cache Buster Query Parameter -->
					<div class="main_section cfworker_not">
						<div class="left_column">
							<label><?php _e( 'Remove Cache Buster Query Parameter', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Stop adding cache buster query parameter when using the default page rule mode.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_remove_cache_buster" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_remove_cache_buster', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_remove_cache_buster" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_remove_cache_buster', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

							<br/>
							<div class="description highlighted"><?php _e( '<strong>DO NOT ENABLE this option</strong> unless you are an advanced user confortable with creating advanced Cloudflare rules. Otherwise caching system will break on your website.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>
							<div class="description highlighted"><?php _e( 'Check <strong><a href="https://gist.github.com/isaumya/af10e4855ac83156cc210b7148135fa2" target="_blank" rel="external noopener noreferrer">this implementation guide</a></strong> first before enabling this option.', 'wp-cloudflare-page-cache' ); ?></div>
							<br/>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Keep settings on deactivation -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Keep settings on deactivation', 'wp-cloudflare-page-cache' ); ?></label>
							<div class="description"><?php _e( 'Keep settings on plugin deactivation.', 'wp-cloudflare-page-cache' ); ?></div>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_keep_settings_on_deactivation" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'keep_settings_on_deactivation', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_keep_settings_on_deactivation" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'keep_settings_on_deactivation', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>
				</div>


				<!-- THIRD PARTY TAB -->
				<div class="swcfpc_tab
				<?php
				if ( $tab_active == 'thirdparty' ) {
					echo 'active';
				}
				?>
				" id="thirdparty">

					<!-- WooCommerce Options -->
					<div class="main_section_header first_section">
						<h3>
							<?php echo __( 'WooCommerce settings', 'wp-cloudflare-page-cache' ); ?>

							<?php if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<!-- Don't cache the following WooCommerce page types -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Don\'t cache the following WooCommerce page types', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_cart_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_cart_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Cart (is_cart)', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_checkout_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_checkout_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Checkout (is_checkout)', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_checkout_pay_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_checkout_pay_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Checkout\'s pay page (is_checkout_pay_page)', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_product_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_product_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Product (is_product)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_shop_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_shop_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Shop (is_shop)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_product_tax_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_product_tax_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Product taxonomy (is_product_taxonomy)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_product_tag_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_product_tag_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Product tag (is_product_tag)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_product_cat_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_product_cat_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Product category (is_product_category)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_pages"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_pages', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WooCommerce page (is_woocommerce)', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_woo_account_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_woo_account_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'My Account page (is_account)', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Automatically purge cache for product page and related categories when stock quantity changes -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge cache for product page and related categories when stock quantity changes', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_auto_purge_woo_product_page" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_woo_product_page', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_auto_purge_woo_product_page" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_woo_product_page', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Automatically purge cache for product page and related categories when product is updated -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge cache for scheduled sales', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_auto_purge_woo_scheduled_sales" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_woo_scheduled_sales', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_auto_purge_woo_scheduled_sales" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_woo_scheduled_sales', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- EDD Options -->
					<div class="main_section_header first_section">
						<h3>
							<?php echo __( 'Easy Digital Downloads settings', 'wp-cloudflare-page-cache' ); ?>

							<?php if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) || is_plugin_active( 'easy-digital-downloads-pro/easy-digital-downloads.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<!-- Don't cache the following EDD page types -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Don\'t cache the following EDD page types', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_bypass_edd_checkout_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_edd_checkout_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Primary checkout page', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_edd_purchase_history_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_edd_purchase_history_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Purchase history page', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_edd_login_redirect_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_edd_login_redirect_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Login redirect page', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_edd_success_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_edd_success_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Success page', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_bypass_edd_failure_page"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_bypass_edd_failure_page', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Failure page', 'wp-cloudflare-page-cache' ); ?>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Automatically purge cache when a payment is inserted into the database -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge cache when a payment is inserted into the database', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_auto_purge_edd_payment_add" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_edd_payment_add', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_auto_purge_edd_payment_add" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_auto_purge_edd_payment_add', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Autoptimize Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Autoptimize settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<!-- Automatically purge the cache when Autoptimize flushs its cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when Autoptimize flushs its cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_autoptimize_purge_on_cache_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_autoptimize_purge_on_cache_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_autoptimize_purge_on_cache_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_autoptimize_purge_on_cache_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- W3TC Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'W3 Total Cache settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<?php if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) : ?>
						<div class="description_section highlighted">
							<?php _e( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?>
						</div>
					<?php endif; ?>

					<!-- Automatically purge the cache when W3TC flushs all caches -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_w3tc_purge_on_flush_all"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_all', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'W3TC flushs all caches', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_w3tc_purge_on_flush_dbcache"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_dbcache', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'W3TC flushs database cache', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_w3tc_purge_on_flush_fragmentcache"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_fragmentcache', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'W3TC flushs fragment cache', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_w3tc_purge_on_flush_objectcache"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_objectcache', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'W3TC flushs object cache', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_w3tc_purge_on_flush_posts"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_posts', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'W3TC flushs posts cache', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_w3tc_purge_on_flush_minfy"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_w3tc_purge_on_flush_minfy', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'W3TC flushs minify cache', 'wp-cloudflare-page-cache' ); ?>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- LiteSpeed Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'LiteSpeed Cache settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<?php if ( is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) : ?>
						<div class="description_section highlighted">
							<?php _e( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?>
						</div>
					<?php endif; ?>

					<!-- Automatically purge the cache when LiteSpeed Cache flushs all caches -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_litespeed_purge_on_cache_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_litespeed_purge_on_cache_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'LiteSpeed Cache flushs all caches', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_litespeed_purge_on_ccss_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_litespeed_purge_on_ccss_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'LiteSpeed Cache flushs Critical CSS', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_litespeed_purge_on_cssjs_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_litespeed_purge_on_cssjs_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'LiteSpeed Cache flushs CSS and JS cache', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_litespeed_purge_on_object_cache_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_litespeed_purge_on_object_cache_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'LiteSpeed Cache flushs object cache', 'wp-cloudflare-page-cache' ); ?>
							</div>
							<div><input type="checkbox" name="swcfpc_cf_litespeed_purge_on_single_post_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_litespeed_purge_on_single_post_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'LiteSpeed Cache flushs single post cache via API', 'wp-cloudflare-page-cache' ); ?>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Hummingbird Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Hummingbird settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'hummingbird-performance/wp-hummingbird.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<?php if ( is_plugin_active( 'hummingbird-performance/wp-hummingbird.php' ) ) : ?>
						<div class="description_section highlighted">
							<?php _e( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?>
						</div>
					<?php endif; ?>

					<!-- Automatically purge the cache when Hummingbird flushs page cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when Hummingbird flushs page cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_hummingbird_purge_on_cache_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_hummingbird_purge_on_cache_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_hummingbird_purge_on_cache_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_hummingbird_purge_on_cache_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- WP-Optimize Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'WP-Optimize settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'wp-optimize/wp-optimize.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<?php if ( is_plugin_active( 'wp-optimize/wp-optimize.php' ) ) : ?>
						<div class="description_section highlighted">
							<?php _e( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?>
						</div>
					<?php endif; ?>

					<!-- Automatically purge the cache when WP-Optimize flushs page cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when WP-Optimize flushs page cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_wp_optimize_purge_on_cache_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wp_optimize_purge_on_cache_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_wp_optimize_purge_on_cache_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wp_optimize_purge_on_cache_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Flying Press Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Flying Press settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'flying-press/flying-press.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<?php if ( is_plugin_active( 'flying-press/flying-press.php' ) ) : ?>
						<div class="description_section highlighted">
							<?php _e( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?>
						</div>
					<?php endif; ?>

					<!-- Automatically purge the cache when Flying Press flushs its own cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when Flying Press flushs its own cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_flypress_purge_on_cache_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_flypress_purge_on_cache_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_flypress_purge_on_cache_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_flypress_purge_on_cache_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- WP Rocket Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'WP Rocket settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<?php if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) : ?>
						<div class="description_section highlighted">
							<?php _e( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?>
						</div>
					<?php endif; ?>

					<!-- Automatically purge the cache when WP Rocket flushs its cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_domain_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_domain_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WP Rocket flushs all caches', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_post_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_post_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WP Rocket flushs single post cache', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_cache_dir_flush"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_cache_dir_flush', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WP Rocket flushs cache directories', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_clean_files"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_clean_files', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WP Rocket flushs files', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_clean_cache_busting"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_clean_cache_busting', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WP Rocket flushs cache busting', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_clean_minify"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_clean_minify', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'WP Rocket flushs minified files', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_ccss_generation_complete"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_ccss_generation_complete', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'CCSS generation process ends', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_wp_rocket_purge_on_rucss_job_complete"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_wp_rocket_purge_on_rucss_job_complete', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'RUCSS generation process ends', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
						</div>
						<div class="clear"></div>
					</div>

					<!-- Disable WP Rocket page cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Disable WP Rocket page cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">

							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_wp_rocket_disable_cache" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wp_rocket_disable_cache', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_wp_rocket_disable_cache" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wp_rocket_disable_cache', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>

						</div>
						<div class="clear"></div>
					</div>


					<!-- WP Asset Clean Up Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'WP Asset Clean Up settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'wp-asset-clean-up/wpacu.php' ) || is_plugin_active( 'wp-asset-clean-up-pro/wpacu.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<!-- Automatically purge the cache when WP Asset Clean Up flushs its own cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when WP Asset Clean Up flushs its own cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_wpacu_purge_on_cache_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wpacu_purge_on_cache_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_wpacu_purge_on_cache_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wpacu_purge_on_cache_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Nginx Helper Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Nginx Helper settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'nginx-helper/nginx-helper.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<!-- Automatically purge the cache when Nginx Helper flushs the cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when Nginx Helper flushs the cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_nginx_helper_purge_on_cache_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_nginx_helper_purge_on_cache_flush', 1 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_nginx_helper_purge_on_cache_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_nginx_helper_purge_on_cache_flush', 1 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- WP Performance Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'WP Performance settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'wp-performance/wp-performance.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<!-- Automatically purge the cache when WP Performance flushs its own cache -->
					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when WP Performance flushs its own cache', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_wp_performance_purge_on_cache_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wp_performance_purge_on_cache_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_wp_performance_purge_on_cache_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wp_performance_purge_on_cache_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- YASR Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Yet Another Stars Rating settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'yet-another-stars-rating/yet-another-stars-rating.php' ) || is_plugin_active( 'yet-another-stars-rating-premium/yet-another-stars-rating.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the page cache when a visitor votes', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_yasr_purge_on_rating" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_yasr_purge_on_rating', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_yasr_purge_on_rating" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_yasr_purge_on_rating', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Swift Performance (Lite/Pro) Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Swift Performance (Lite/Pro) settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( is_plugin_active( 'swift-performance-lite/performance.php' ) || is_plugin_active( 'swift-performance/performance.php' ) ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<?php if ( is_plugin_active( 'swift-performance-lite/performance.php' ) || is_plugin_active( 'swift-performance/performance.php' ) ) : ?>
						<div class="description_section highlighted">
							<?php _e( 'It is strongly recommended to disable the page caching functions of other plugins. If you want to add a page cache as fallback to Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?>
						</div>
					<?php endif; ?>

					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the cache when', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div><input type="checkbox" name="swcfpc_cf_spl_purge_on_flush_all"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_spl_purge_on_flush_all', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Swift Performance (Lite/Pro) flushs all caches', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
							<div><input type="checkbox" name="swcfpc_cf_spl_purge_on_flush_single_post"
										value="1" <?php echo $this->main_instance->get_single_config( 'cf_spl_purge_on_flush_single_post', 0 ) > 0 ? 'checked' : ''; ?> /> <?php _e( 'Swift Performance (Lite/Pro) flushs single post cache', 'wp-cloudflare-page-cache' ); ?>
								- <strong><?php _e( '(recommended)', 'wp-cloudflare-page-cache' ); ?></strong></div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Siteground Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Siteground SuperCacher settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( $this->modules['cache_controller']->is_siteground_supercacher_enabled() ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Active plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Inactive plugin', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the Siteground cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_siteground_purge_on_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_siteground_purge_on_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_siteground_purge_on_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_siteground_purge_on_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- WP Engine Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'WP Engine settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( $this->modules['cache_controller']->can_wpengine_cache_be_purged() ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Provider detected', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Provider not detected', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the WP Engine cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_wpengine_purge_on_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wpengine_purge_on_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_wpengine_purge_on_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_wpengine_purge_on_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- SpinupWP Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'SpinupWP settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( $this->modules['cache_controller']->can_spinupwp_cache_be_purged() ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Provider detected', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Provider not detected', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the SpinupWP cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_spinupwp_purge_on_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_spinupwp_purge_on_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_spinupwp_purge_on_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_spinupwp_purge_on_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>


					<!-- Kinsta Options -->
					<div class="main_section_header">
						<h3>
							<?php echo __( 'Kinsta settings', 'wp-cloudflare-page-cache' ); ?>
							<?php if ( $this->modules['cache_controller']->can_kinsta_cache_be_purged() ) : ?>
								<span class="swcfpc_plugin_active"><?php _e( 'Provider detected', 'wp-cloudflare-page-cache' ); ?></span>
							<?php else : ?>
								<span class="swcfpc_plugin_inactive"><?php _e( 'Provider not detected', 'wp-cloudflare-page-cache' ); ?></span>
							<?php endif; ?>
						</h3>
					</div>

					<div class="main_section">
						<div class="left_column">
							<label><?php _e( 'Automatically purge the Kinsta cache when Cloudflare cache is purged', 'wp-cloudflare-page-cache' ); ?></label>
						</div>
						<div class="right_column">
							<div class="switch-field">
								<input type="radio" id="switch_<?php echo ++$switch_counter; ?>_left"
									   name="swcfpc_cf_kinsta_purge_on_flush" value="1"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_kinsta_purge_on_flush', 0 ) > 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_left"><?php _e( 'Yes', 'wp-cloudflare-page-cache' ); ?></label>
								<input type="radio" id="switch_<?php echo $switch_counter; ?>_right"
									   name="swcfpc_cf_kinsta_purge_on_flush" value="0"
									<?php
									if ( $this->main_instance->get_single_config( 'cf_kinsta_purge_on_flush', 0 ) <= 0 ) {
										echo 'checked';
									}
									?>
								/>
								<label for="switch_<?php echo $switch_counter; ?>_right"><?php _e( 'No', 'wp-cloudflare-page-cache' ); ?></label>
							</div>
						</div>
						<div class="clear"></div>
					</div>

				</div>

				<!-- FAQ TAB -->
				<div class="swcfpc_tab
				<?php
				if ( $tab_active == 'faq' ) {
					echo 'active';
				}
				?>
				" id="faq">

					<!-- COMMON FAQs -->
					<div class="main_section_header first_section">
						<h3><?php _e( 'Common questions', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<div class="swcfpc_faq_accordion">

						<h3 class="swcfpc_faq_question"><?php _e( 'How it works?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Super Page Cache generate static HTML version of the webpages inside your site. This is a great option and can increase your site speed dramatically.', 'wp-cloudflare-page-cache' ); ?></p>


							<p><?php _e( 'Our page cache system works way better than any other disk cache system and plugins out there in the market. In short now you don\'t need to install any other caching plugin in conjunction with Super Page Cache, as the plugin can now handle Cloudflare caching and disk caching.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin to take advantage of Cloudflare\'s Cache Everything rule, bypassing the cache for logged in users even if I\'m using the free plan?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes. This is the main purpose of this plugin.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'What is the <strong>swcfpc=1</strong> parameter I see to every internal links when I\'m logged in?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'It is a cache buster. Allows you, while logged in, to bypass the Cloudflare cache for pages that could be cached.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'It is added to all internal links for logged in users only. It is disabled in Worker mode.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Can I restore all Cloudflare settings as before the plugin activation?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes you can by click on <strong>Reset all</strong> button.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'Why all the pages have "BYPASS" as the cf-cache-status?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Cloudflare does not add in cache pages with cookies because they can have dynamic contents. If you want to force this behavior, strip out cookies by enabling the option <strong>Strip response cookies on pages that should be cached.</strong>', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'What is Preloader and how it works?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'The preloader is a simple crawler that is started in the background and aims to preload pages so that they are cached immediately.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'Once the preloader is enabled, you need to specify which preloading logic among those available to use. You can choose a combination of options (sitemaps, WordPress menus, recent published posts, etc ..).', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'It is also possible to automatically start the preloader when the cache is cleared.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'In order to avoid overloading the server, only one preloader will be started at once. It is therefore not possible to start more than one preloader at the same time. Furthermore, between one preload and the other there will be a waiting time of 2 seconds.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If you want to run the preloader at middle of the night when you have low users, you can run the preloader over CRON job as well.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'What is the difference between Purge Cache and Force purge everything actions?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'If the <strong>Purge HTML pages only</strong> option is enabled, clicking on the <strong>PURGE CACHE</strong> button only HTML pages already in cache will be deleted .', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If for some reason you need to delete static files (such as CSS, images and scripts) from Cloudflare\'s cache, you can do so by clicking on <strong>Force purge everything</strong>', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


					</div>


					<!-- COMMON ISSUES FAQs -->
					<div class="main_section_header">
						<h3><?php _e( 'Common issues', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<div class="swcfpc_faq_accordion">

						<h3 class="swcfpc_faq_question"><?php _e( 'Error: Invalid request headers (err code: 6003 )', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'This is a Cloudflare authentication error. <strong>If you chose the API Key as the authentication mode</strong>, make sure you have entered the correct email address associated with your Cloudflare account and the correct Global API key (not your Cloudflare password!).', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( '<strong>If you are chose the API Token as the authentication mode</strong>, make sure you have entered the correct token, with all the required permissions, and the domain name exactly as it appears in your Cloudflare account.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'Also make sure you haven\'t entered the API Token instead of the API key or vice versa', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Error: Page Rule validation failed: See messages for details. (err code: 1004 )', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Login to Cloudflare, click on your domain and go to Page Rules section. Check if a <strong>Cache Everything</strong> page rule already exists for your domain. If yes, delete it. Now from the settings page of Super Page Cache, disable and re-enable the cache.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Error: Actor \'com.cloudflare.api.token.\' requires permission \'com.cloudflare.api.account.zone.list\' to list zones (err code: 0 )', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'If you are using an <strong>API Token</strong>, check that you entered the domain name <strong>exactly</strong> as on Cloudflare', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'PHP Warning: Cannot modify header information - headers already sent in /wp-content/advanced-cache.php', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Maybe you have some extra newline or space in other PHP files executed before advanced-cache.php such like must-use plugins or wp-config.php.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'Check those files. Try the following:', 'wp-cloudflare-page-cache' ); ?></p>

							<ol>
								<li><?php _e( 'If you have any code inside mu-plugin take them out of that folder. Check your server error log and test to see if you are getting the header errors still.', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'If not then check the codes inside the PHP files and see if any of them has an extra newline or space at the end of the script. If they have delete those new lines and spaces and test.', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'If still doesn\'t work check if any of the scripts inside mu-plugins have print, echo, printf, vprintf etc. in the code. For more details check:', 'wp-cloudflare-page-cache' ); ?>
									<a href="https://stackoverflow.com/a/8028987/2308992" target="_blank"
									   rel="external nofollow noopener noreferrer">https://stackoverflow.com/a/8028987/2308992</a>
								</li>
							</ol>

							<p><?php _e( 'In short, the problem is not coming from this plugin but some <strong>mu-plugin</strong> is sending the header before advanced-cache.php can. That\'s causing the issue. We have thoroughly tested this.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Custom login page does not redirect when you login', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Exclude the custom login URL by adding it into the textarea near the option <strong>Prevent the following URIs to be cached</strong>, then save, purge the cache, wait 30 seconds and retry.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

					</div>


					<!-- THIRD PARTY INTEGRATIONS FAQs -->
					<div class="main_section_header">
						<h3><?php _e( 'Third-party integrations', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>


					<div class="swcfpc_faq_accordion">

						<h3 class="swcfpc_faq_question"><?php _e( 'Does it work with Litespeed Server?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes but if you are using a LiteSpeed Server version lower than 6.0RC1, disable the option <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong>. You can keep this option enabled for Litespeed Server versions equal or higher then 6.0RC1', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'How does this plugin work with Kinsta Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'If you are using a Kinsta hosting, you can integrate this plugin to work with Kinsta Server Level Caching and to ensure when you Purge Cache via this plugin, it not only purges the cache on Cloudflare but also on Kinsta Cache. You can enable this feature by going to the "Third Party" tab of the plugin settings and enabling the "Automatically purge the Kinsta cache when Cloudflare cache is purged" option. It is also recommended that if you are taking advantage of the Kinsta Server Caching (Recommended), please ensure that the <string>Fallback Cache</strong> system provided by this plugin is turned OFF.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'After purging the cache from our plugin, it gets deleted from both Kinsta & Cloudflare Cache. Now when you visit the webpage for the first time after purging the cache, you will get cache response headers as MISS/EXPIRED etc. for both Cloudflare and Kinsta cache.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'At this point after receiving the first request both Cloudflare & Kinsta caches the page on their own platforms respectively. But do note that when Cloudflare is caching the first request at this point the <code>x-kinsta-cache</code> header is of status MISS. For the second request when Cloudflare serves the page from it\'s own CDN Edge servers without sending the request to the origin server, it keeps showing the <code>x-kinsta-cache</code> cache header as MISS. Because when Cloudflare cached the page the response header was MISS but Kinsta caching system has already cached it upon receiving the first request.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'Now if you purge the Cloudflare cache only (on Cloudflare dashboard) or enable development mode in the Cloudflare dashboard so that the request doesn\'t get served from Cloudflare CDN, you will see the <code>x-kinsta-cache</code> showing as HIT, because this time the request is going to your origin server and you are seeing the updated response headers that is being served by the server.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin together with Litespeed Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes you can! You have only to disable its page caching functionality. To do this:', 'wp-cloudflare-page-cache' ); ?></p>
							<ol>
								<li><?php _e( 'Go to <strong>Litespeed Cache > Cache</strong>', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Click on <strong>OFF</strong> near <strong>Enable Cache</strong>', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Click on <strong>Save Changes</strong> button', 'wp-cloudflare-page-cache' ); ?></li>
							</ol>

							<p>Then:</p>

							<ol>
								<li><?php _e( 'Enter to the settings page of this plugin', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Click on <strong>Third Party</strong> tab', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Scroll to <strong>Litespeed Cache Settings</strong> section', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Enable the option <strong>Automatically purge the cache when LiteSpeed Cache flushs all caches</strong>', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Purge the cache', 'wp-cloudflare-page-cache' ); ?></li>
							</ol>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin together with other page caching plugins such like Cache Enabler, WP Super Cache and WP Fastest Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'No. The support for these plugin was removed because you can use the fallback cache option of this plugin if you want to use a standard page cache behind Cloudflare.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'In order to avoid conflicts, it is strongly recommended to use only this plugin as page caching system.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin together with Varnish Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes but you don\'t need it. If you want to use a standard page cache behind Cloudflare, enable the fallback cache option of this plugin.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'In order to avoid conflicts, it is strongly recommended to use only this plugin as page caching system.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

					</div>


					<!-- CACHE FAQs -->
					<div class="main_section_header">
						<h3><?php _e( 'Cache questions and issues', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<div class="swcfpc_faq_accordion">

						<h3 class="swcfpc_faq_question"><?php _e( 'WP Admin or WP Admin Bar is being cached', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'This should never happen. If it happens, it is because the value of the <strong>Cache-Control</strong> response header is different from that of the <strong>X-WP-CF-Super-Cache-Cache-Control</strong> response header (make sure it is the same).', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If you are using <strong>LiteSpeed Server version lower than 6.0RC1</strong>, make sure the <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong> option is <strong>disabled</strong>. If not, disable it, clear your cache and try again. You can keep this option enabled for Litespeed Server versions equal or higher then 6.0RC1', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If you are not using LiteSpeed Server and you are using this plugin together with other performance plugins, enable the <strong>Overwrite the cache-control header for WordPress\'s pages using web server rules</strong> option, clear the cache and try again.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If this doesn\'t work, you can always choose to activate the <strong>Force cache bypassing for backend with an additional Cloudflare page rule</strong> option or to change the caching mode by activating the <strong>Worker mode</strong> option.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Why changes are never showed when visiting the website?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'First of all enable the log mode and check if in the log file, after clicking on the update button on the edit page, you see the information about the cache purging.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If so, good news: the plugin is working correctly. If not, open a ticket on the support forum.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If you have enabled the <strong>Page cache</strong>, make sure you have also enabled the option <strong>Automatically purge the Page cache when Cloudflare cache is purged</strong>.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If you are using <strong>Varnish cache</strong>, make sure you have also enabled the option <strong>Automatically purge Varnish cache when the cache is purged</strong>.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'Disable any other page caching plugins or services. Only use this plugin to cache HTML pages.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If you still don\'t see the changes despite everything, the problem is to be found elsewhere. For example wrong configuration of wp-config.php.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'URLs with swcfpc=1 parameter getting indexed', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'In very rare cases, it may happen that some third-party plugin stores the cache buster parameter in the database, and therefore this is then also displayed to users who are not logged in and therefore to search engine bots.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If this happened on your site, enable the <strong>SEO redirect</strong> inside the plugin settings page under the <strong>Advanced</strong> tab. This will auto redirect any URLs which has <em>swcfpc=1</em> in it to it\'s normal URL when any non-logged in user clicks on that link, avoiding duplicate content problems.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'I am seeing ?swcfpc=1 at the front end URLs even when I\'m not logged in', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Some page builders might copy the admin side links and show that on the front end for all users. This happens because these page builders do not follow the standard WordPress coding guidelines to fetch URLs and instead copy hard code URLs. If you are facing this issue, you can easily fix this by enabling the <strong>SEO redirect</strong> option inside the plugin settings page under the <strong>Advanced</strong> tab. This will auto redirect any URLs which has <em>swcfpc=1</em> in it to it\'s normal URL when any non-logged in user clicks on that link.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Even after enabling the plugin I\'m seeing CF Cache Status DYNAMIC for all the pages', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'There are a couple of things that can cause this issue and tell Cloudflare not to cache everything. If you are facing this issue, please check the following this:', 'wp-cloudflare-page-cache' ); ?></p>

							<ul>
								<li><?php _e( 'Make sure that <strong>Development Mode</strong> is NOT enabled for your domain inside Cloudflare.', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Make sure you have the orange cloud icon enabled inside the Cloudflare DNS settings for your main domain A record and WWW record.', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Make sure you do not have any other page rules that might conflict with the Cache Everything rule', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Make sure you do not have any Cloudflare Worker code deployed that might overwrite the normal cache policies', 'wp-cloudflare-page-cache' ); ?></li>
								<li><?php _e( 'Make sure you don\'t have any plugins which might be adding a lot of unnecessary Cookies in your request header for no reason. If you have any cookie consent plugin or any similar things, try disabling those plugins and check again. You can also enable the <strong>Strip response cookies on pages that should be cached</strong> option under the <strong>Cache</strong> tab to see if this resolves your issue. If it does, that means there are plugin which is injecting cookies into your site header and when Cloudflare sees these Cookies, it think that the page has dynamic content, so it doesn\'t cache everything.', 'wp-cloudflare-page-cache' ); ?></li>
							</ul>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Should I enable the cURL mode for Disk Cache?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'In most cases you don\'t need to enable the cURL mode for fallback cache. If you don\'t enable the cURL mode, the plugin will use the standard WordPress <code>advanced-cache.php</code> method to generate the Page cache. This system works well in almost all the cases, also this cache generation mechanism is very fast and don\'t eat much server resource. On the other hand the cURL mode is useful in some edge cases where the <code>advanced-cache.php</code> mode of fallback cache is unable to generate proper HTML for the page. This is rare, but the cURL option is given just for these edge cases.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'One of the benefit of the cURL mode is that as it uses server level cRUL instead of <code>advanced-cache.php</code> to generate the page cache, the cache files comes out very stable and without any issues. But then again if your enable the cURL mode, that means cURL will fetch every page of your website (which are not excluded from fallback cache) to generate the Page cache and each cURL request is going to increase some server load. So, if you have a small to medium site with not many pages, you can definitely use the cURL mode of fallback cache. But for large high traffic website, unless you have more than enough server resource to handle so many  cURL requests, we will recommend stick to using the default <code>advanced-cache.php</code> option which works flawlessly anyway.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'What\'s the benefit of using Cloudflare worker over using page rules?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Cloudflare Workers is an amazing technology which allows us to run complicated logical operations inside the Cloudflare edges. So, basically before Cloudflare picks up the request, it passes through the Cloudflare worker and then we can programmatically tell Cloudflare what to do and what not to do. This gives us great control over each and every request and how Cloudflare should handle them.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'The Page Rule option of <strong>Cache Everything</strong> works perfectly in almost every cases but in some situations due to some server config or other reasons, the headers that this plugin sets for each requests, does not get respected by the server and gets stripped out. In those edge case scenarios Cloudflare Worker mode can be really helpful over the page rules mode.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'So, in terms of speed, you might not see a difference but the Worker mode is there just for the cases when the page rule mode is not working correctly across the whole website (frontend & backend).', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Isn\'t Cloudflare Workers are chargeable?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes & No. It depends on how many visitors your site have.  Cloudflare Workers have a free plan which allows <strong>100,000 requests/day</strong> for FREE. But if your site has more requests than that per day, then you need to opt for the paid plan of <strong>$5/month</strong> which will give you <strong>10 Million Requests/month</strong> and after that <strong>$0.50 per additional 1 Million requests</strong>.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'Please note that, all requests first get intercepted by Cloudflare Worker before Cloudflare decide what to do with that request, so whether your requests gets served from Cloudflare CDN Cache or from origin, it will be counted towards your Worker usage limit.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'If you have a small to medium site, you can easily use Cloudflare Worker without hesitating about payment as you will not get pass the free quota, but as you grow, and if you still want to use the Cloudflare Workers, you might have to pay for it. Cloudflare Workers are so much more and has so much power that if you are truly taking advantage of the Cloudflare Workers, your can do a lot of cool things. So, in short if you have a big high traffic site and you don\'t want to pay extra for the Cloudflare Workers, you should just stick with the Page Rules option.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'How is the Worker Code is deployed to my Cloudflare Account?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'After you enter the Cloudflare API details, we push our worker code using Cloudflare API to your Cloudflare account. You can find our Cloudflare Worker code inside the plugin\'s <code>/assets/js/worker_template.js</code> path.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Can I use this plugin with WP CLI?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes. Commands list: <strong>wp cfcache</strong>', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

					</div>


					<!-- ADVANCED FAQs -->
					<div class="main_section_header">
						<h3><?php _e( 'Advanced questions', 'wp-cloudflare-page-cache' ); ?></h3>
					</div>

					<div class="swcfpc_faq_accordion">

						<h3 class="swcfpc_faq_question"><?php _e( 'How to use the <strong>Remove Cache Buster Query Parameter</strong> option? Is there any implementation guides?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'That is a super advanced option to use Cache Everything Cloudflare page rules without the swcfpc cache buster query parameter. This option is only for super advanced users who are confortable adding custom rules in their Cloudflare dashbord. If you are that person, this option probably will not be a good fit for you.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'Also when using this option please keep in mind that some rules can only be implemented in Cloudflare Business and Enterprise account users. So, if you are a CLoudflare Free or Pro plan users, you might not be able to implement some rules.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'Please check <strong><a href="https://gist.github.com/isaumya/af10e4855ac83156cc210b7148135fa2" target="_blank" rel="external noopener noreferrer">this implementation guide</a></strong> which comes all types of Cloudflare accounts before enabling this option.', 'wp-cloudflare-page-cache' ); ?></p>
							<p><?php _e( 'Without implementioned these rules properly if you enable this option, it will break the cache functionality of your website.', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'Can I change <strong>swcfpc</strong> with another one?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes you can by adding the PHP constant <strong>SWCFPC_CACHE_BUSTER</strong> to your wp-config.php', 'wp-cloudflare-page-cache' ); ?></p>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'Can I configure this plugin using PHP constants?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes you can use the following PHP constants:', 'wp-cloudflare-page-cache' ); ?></p>

							<ul>
								<li>
									<strong>SWCFPC_CACHE_BUSTER</strong>, <?php _e( 'cache buster name. Default: swcfpc', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_API_ZONE_ID</strong>, <?php _e( 'Cloudflare zone ID', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_API_KEY</strong>, <?php _e( 'API Key to use', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_API_EMAIL</strong>, <?php _e( 'Cloudflare email to use (API Key authentication mode)', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_API_TOKEN</strong>, <?php _e( 'API Token to use', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_PRELOADER_MAX_POST_NUMBER</strong>, <?php _e( 'max pages to preload. Default: 50', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_WOKER_ENABLED</strong>, <?php _e( 'true or false', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_WOKER_ID</strong>, <?php _e( 'CF Worker ID', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_WOKER_ROUTE_ID</strong>, <?php _e( 'route ID', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CF_WOKER_FULL_PATH</strong>, <?php _e( 'full path to worker template to use', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_CURL_TIMEOUT</strong>, <?php _e( 'timeout in seconds for cURL calls. Default: 10', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_PURGE_CACHE_LOCK_SECONDS</strong>, <?php _e( 'time in seconds for cache purge lock. Default: 10', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_PURGE_CACHE_CRON_INTERVAL</strong>, <?php _e( 'time interval in seconds for the purge cache cronjob. Default: 10', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>SWCFPC_HOME_PAGE_SHOWS_POSTS</strong>, <?php _e( 'if the front page a.k.a. the home page of the website showing latest posts. Default: true (bool)', 'wp-cloudflare-page-cache' ); ?>
								</li>
							</ul>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'What hooks can I use?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p>Actions:</p>

							<ul>
								<li><strong>swcfpc_purge_all</strong>, no
									arguments. <?php _e( 'Fired when whole caches are purged.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li><strong>swcfpc_purge_urls</strong>, 1 argument:
									$urls. <?php _e( 'Fired when caches for specific URLs are purged.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>swcfpc_cf_purge_whole_cache_before</strong>, <?php _e( 'no arguments. Fired before purge the Cloudflare whole cache.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>swcfpc_cf_purge_whole_cache_after</strong>, <?php _e( 'no arguments. Fired after the Cloudflare whole cache is purged.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li><strong>swcfpc_cf_purge_cache_by_urls_before</strong>, 1 argument:
									$urls. <?php _e( 'no arguments. Fired before purge the Cloudflare cache for specific URLs only.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li><strong>swcfpc_cf_purge_cache_by_urls_after</strong>, 1 argument:
									$urls. <?php _e( 'no arguments. Fired after the Cloudflare cache for specific URLs only is purged.', 'wp-cloudflare-page-cache' ); ?>
								</li>
							</ul>

							<p>Filters:</p>

							<ul>
								<li>
									<strong>swcfpc_bypass_cache_metabox_post_types</strong>, <?php _e( '$allowed_post_types (Array). You can use this filter to ensure that the bypass cache metabox is also shown for your own custom post types. Example code link: https://wordpress.org/support/topic/disable-page-caching-for-specific-pages-with-cpt/#post-16824221', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>swcfpc_fc_modify_current_url</strong>, <?php _e( '$current_uri (string). You can use this filter to modify the url that will be used by the fallback cache. For example you can remove many query strings from the url. Please note that this filter will return the URL without the trailing slash.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>swcfpc_cache_bypass</strong>, <?php _e( 'one arguments. Return true to bypass the cache.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>swcfpc_post_related_url_init</strong>, <?php _e( '$listofurls (array), $postId. Fired when creating the initial array that holds the list of related urls to be purged when a post is updated. Show return array of full URLs (e.g. https://example.com/some-example/) that you want to include in the related purge list.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>swcfpc_normal_fallback_cache_html</strong>, <?php _e( '[One Arguments] : $html. This filter is fired before storing the page HTML to fallback cache. So, this gives you the ability to make changes to the HTML that gets saved within the fallback cache. This filter is fired when the fallback cache is generated normally via the advanced-cache.php file.', 'wp-cloudflare-page-cache' ); ?>
								</li>
								<li>
									<strong>swcfpc_curl_fallback_cache_html</strong>, <?php _e( '[One Arguments] : $html. This filter is fired before storing the page HTML to fallback cache. So, this gives you the ability to make changes to the HTML that gets saved within the fallback cache. This filter is fired when the fallback cache is generated normally via cURL method.', 'wp-cloudflare-page-cache' ); ?>
								</li>
							</ul>

						</div>


						<h3 class="swcfpc_faq_question"><?php _e( 'Can I use my own worker code along with the default worker code this plugin uses?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Unfortunately, Cloudflare allows one worker per route. So, as long as our worker is setup in the main route, you cannot use your own worker code in the same route. But you can take advantage of <code>SWCFPC_CF_WOKER_FULL_PATH </code> PHP constant to provide the full path of your own custom JavaScript file.', 'wp-cloudflare-page-cache' ); ?></p>

							<p><?php _e( 'In this way you can take a look at inside the plugin\'s <code>/assets/js/worker_template.js</code> path and see the Worker code we are using by default. Then you can copy that worker template file in your computer and extend it to add more features and conditionality that you might need in your project. Once you are done with your Worker code, you can simply point your custom Worker template JavaScript file inside <code>wp-config.php</code> using the <code>SWCFPC_CF_WOKER_FULL_PATH </code> PHP constant and the plugin will use your Worker file to create the worker in your website route instead of using the default Worker code. Here is an example of how to use the PHP constant inside your <code>wp-config.php</code>. Please make sure you provide the absolute path of your custom Worker file.', 'wp-cloudflare-page-cache' ); ?></p>

							<pre>define('SWCFPC_CF_WOKER_FULL_PATH', '/home/some-site/public/wp-content/themes/your-theme/assets/js/my-custom-cf-worker.js');</pre>

							<p><strong style="color:#c0392b">Please
									note</strong> <?php _e( 'that for 99.999% of users the default Worker code will work perfectly if they choose to use the Worker mode over the Page Rule mode. This option will be provided <strong>only for Super Advanced Knowledgeable Users</strong> who know exactly what they are doing and which will lead to what. General users should <strong>avoid</strong> tinkering with the Worker Code as this might break your website if you don\'t know what you are doing.', 'wp-cloudflare-page-cache' ); ?>
							</p>

						</div>

						<h3 class="swcfpc_faq_question"><?php _e( 'Can I purge the cache programmatically?', 'wp-cloudflare-page-cache' ); ?></h3>
						<div class="swcfpc_faq_answer">

							<p><?php _e( 'Yes. To purge the whole cache use the following PHP command:', 'wp-cloudflare-page-cache' ); ?></p>

							<pre>do_action("swcfpc_purge_cache");</pre>

							<p><?php _e( 'To purge the cache for a subset of URLs use the following PHP command:', 'wp-cloudflare-page-cache' ); ?></p>

							<pre>do_action("swcfpc_purge_cache", array("https://example.com/some-page/", "https://example.com/other-page/"));</pre>

						</div>

					</div>

				</div>


				<!-- Recommendations TAB -->
				<div class="swcfpc_tab
				<?php
				if ( $tab_active == 'recommendations' ) {
					echo 'active';
				}
				?>
				" id="recommendations">


					<?php

					foreach ( $partners as $partner_section ) :

						if ( count( $partner_section['list'] ) == 0 ) {
							continue;
						}

						?>

						<div class="main_section_header">
							<h3><?php echo $partner_section['title']; ?></h3>
						</div>

						<div class="description_section"><?php echo $partner_section['description']; ?></div>

						<?php foreach ( $partner_section['list'] as $single_partner ) : ?>

						<div class="itemDetail">
							<h3 class="itemTitle">
								<a href="<?php echo $single_partner['link']; ?>"
								   target="_blank"><?php echo $single_partner['title']; ?></a>
							</h3>
							<div class="itemImage">
								<a href="<?php echo $single_partner['link']; ?>" target="_blank">
									<img src="<?php echo $single_partner['img']; ?>">
								</a>
							</div>
							<div class="itemDescription"><?php echo $single_partner['description']; ?></div>
							<div class="itemButtonRow">
								<div class="itemButton button-secondary">
									<a href="<?php echo $single_partner['link']; ?>"
									   target="_blank"><?php _e( 'More info', 'wp-cloudflare-page-cache' ); ?></a>
								</div>
							</div>
						</div>

					<?php endforeach; ?>

					<?php endforeach; ?>

					<div style="clear: both;"></div>

				</div>

				<?php

				$additional_tabs = [
					'javascript' => 'admin_js_tab.php',
					'media'      => 'admin_media_tab.php',
				];

				foreach ( $additional_tabs as $id => $template ) {
					?>
					<div class="swcfpc_tab <?php echo $tab_active === $id ? 'active' : ''; ?>"
						 id="<?php echo esc_attr( $id ); ?>">
						<?php
						$default_template = SWCFPC_PLUGIN_PATH . 'src/views/' . $template;
						$template         = apply_filters( 'swcfpc_admin_tab_view_path', $default_template, $id );

						include_once is_file( $template ) ? $template : $default_template;
						?>
					</div>
				<?php } ?>

				<!-- Image Optimization TAB -->
				<?php if ( ! defined( 'OPTML_VERSION' ) ) { ?>
					<div class="swcfpc_tab
					<?php
					if ( $tab_active == 'image_optimization' ) {
						echo 'active';
					}
					?>
					" id="image_optimization">
						<?php require_once SWCFPC_PLUGIN_PATH . 'libs/views/optimole.php'; ?>
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
		require_once SWCFPC_PLUGIN_PATH . 'libs/views/sidebar.php';
	}
	?>

</div>
