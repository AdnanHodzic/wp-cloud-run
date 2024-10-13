<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php
		esc_html_e( 'Posts Priority', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
	?></h3>

	<div class="inside">
		<p><?php esc_html_e( 'Please choose a priority for calculating each of posts:', 'xml-sitemap-generator-for-google' ); ?></p>
		<ul>
			<li>
				<?php
				Dashboard::render(
					'fields/radio.php',
					array(
						'label'         => esc_html__( 'Do not use Priority Calculation', 'xml-sitemap-generator-for-google' ),
						'description'   => esc_html__( 'Posts will have the same priority which is defined in "Sitemap Options"', 'xml-sitemap-generator-for-google' ),
						'name'          => 'posts_priority',
						'id'            => 'posts_priority_1',
						'value'         => '',
						'current_value' => $settings->posts_priority ?? '',
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/radio.php',
					array(
						'label'         => esc_html__( 'Comments Count', 'xml-sitemap-generator-for-google' ),
						'description'   => esc_html__( 'Number of Post Comments will be used for Priority Calculation', 'xml-sitemap-generator-for-google' ),
						'name'          => 'posts_priority',
						'id'            => 'posts_priority_2',
						'value'         => 'SGG_PRO/Classes/Priority_Count',
						'current_value' => $settings->posts_priority ?? '',
					)
				);
				?>
			</li>
			<li>
				<?php
				Dashboard::render(
					'fields/radio.php',
					array(
						'label'         => esc_html__( 'Comments Average', 'xml-sitemap-generator-for-google' ),
						'description'   => esc_html__( 'Average Comments Count will be used for Priority Calculation', 'xml-sitemap-generator-for-google' ),
						'name'          => 'posts_priority',
						'id'            => 'posts_priority_3',
						'value'         => 'SGG_PRO/Classes/Priority_Average',
						'current_value' => $settings->posts_priority ?? '',
					)
				);
				?>
			</li>
		</ul>

		<?php sgg_show_pro_overlay(); ?>
	</div>
</div>
