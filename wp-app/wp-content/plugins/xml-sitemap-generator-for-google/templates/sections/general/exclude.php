<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php
		esc_html_e( 'Exclude', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
	?></h3>
	<div class="inside">
		<p><?php esc_html_e( 'Please search and choose here Pages, Posts, Custom Posts, Categories and Tags that should be excluded from Sitemap:', 'xml-sitemap-generator-for-google' ); ?></p>

		<?php
		Dashboard::render(
			'fields/autocomplete.php',
			array(
				'label' => esc_html__( 'Exclude Pages/Posts:', 'xml-sitemap-generator-for-google' ),
				'name'  => 'exclude_posts',
				'value' => $settings->exclude_posts ?? '',
			)
		);
		?>
		<br>
		<?php
		Dashboard::render(
			'fields/autocomplete.php',
			array(
				'type'  => 'taxonomy',
				'label' => esc_html__( 'Exclude Categories and Tags:', 'xml-sitemap-generator-for-google' ),
				'name'  => 'exclude_terms',
				'value' => $settings->exclude_terms ?? '',
			)
		);

		sgg_show_pro_overlay();
		?>
	</div>
</div>
