<?php

use GRIM_SG\Dashboard;
/**
 * @var $args
 */

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-toggle-section">
		<strong>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'enable_cronjob',
					'value' => $settings->enable_cronjob ?? false,
					'label' => esc_html__( 'Cron Job', 'xml-sitemap-generator-for-google' ),
					'class' => 'has-dependency',
					'data'  => array( 'target' => 'cronjob' ),
				)
			);
			?>
		</strong>
	</div>
	<div class="inside">
		<div class="pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
			<p class="grim-section-desc grim-mb-20">
				<?php esc_html_e( 'Cron Job is a feature that allows you to schedule the generation of sitemaps at specific times.', 'xml-sitemap-generator-for-google' ); ?>
			</p>

			<p class="cronjob-runtime-group">
				<?php
				$schedules       = wp_get_schedules();
				$cronjob_options = array();

				foreach ( $schedules as $key => $schedule ) {
					$cronjob_options[ $key ] = $schedule['display'];
				}

				Dashboard::render(
					'fields/select.php',
					array(
						'name'        => 'cronjob_runtime',
						'label'       => esc_html__( 'Cron Job Run Time:', 'xml-sitemap-generator-for-google' ),
						'value'       => $settings->cronjob_runtime ?? 'daily',
						'options'     => $cronjob_options,
						'class'       => 'cronjob',
						'select_size' => 'grim-select-long',
					)
				);
				?>
			</p>

			<p class="cronjob grim-mt-20">
				<?php
				$next_run = wp_next_scheduled( 'xml_sitemap_cronjob' );

				if ( $next_run ) {
					printf(
						'%s <strong>%s</strong>',
						esc_html__( 'Cron Job Next Run:', 'xml-sitemap-generator-for-google' ),
						esc_html( gmdate( 'Y-m-d H:i:s', $next_run ) )
					);
				}
				?>
			</p>

			<div class="grim-notice cronjob">
				<i class="grim-icon-information"></i>
				<p>
					<span>
						<?php esc_html_e( 'Cron Job will run at the time you selected and will be triggered by WordPress Cron. It will clear all caches and re-generate Sitemaps.', 'xml-sitemap-generator-for-google' ); ?>
						<br>
						<?php esc_html_e( 'You can move the cron job to the system task scheduler by following the instructions', 'xml-sitemap-generator-for-google' ); ?> -
						<a href="https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/" target="_blank"><?php esc_html_e( 'Hooking WP Cron into the System Task Scheduler', 'xml-sitemap-generator-for-google' ); ?></a>.
					</span>
				</p>
			</div>


		</div>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>
