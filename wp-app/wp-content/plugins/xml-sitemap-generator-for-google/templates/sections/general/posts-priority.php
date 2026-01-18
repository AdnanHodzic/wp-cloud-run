<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section grim-post-priority <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<div class="grim-section-flex">
		<h3 class="grim-section-title" data-search-id="posts_priority"><?php esc_html_e( 'Posts Priority', 'xml-sitemap-generator-for-google' ); ?></h3>
	</div>

	<div class="inside">
		<p class="grim-section-desc grim-mb-20"><?php esc_html_e( 'Please choose a priority for calculating each of posts:', 'xml-sitemap-generator-for-google' ); ?></p>
		<ul class="grim-post-priority-box">
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
