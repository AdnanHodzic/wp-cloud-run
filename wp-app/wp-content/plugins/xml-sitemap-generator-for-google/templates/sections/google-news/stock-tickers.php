<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="grim-section <?php echo esc_attr( sgg_pro_class() ); ?>">
	<?php sgg_show_pro_badge(); ?>
	<p class="grim-toggle-section pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<?php
		Dashboard::render(
			'fields/checkbox.php',
			array(
				'name'  => 'google_news_stocks',
				'value' => $settings->google_news_stocks ?? false,
				'label' => esc_html__( 'Stock Tickers', 'xml-sitemap-generator-for-google' ),
				'class' => 'google-news-depended',
			)
		);
		?>
	</p>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p class="google-news-depended">
			<?php esc_html_e( 'Stock tickers are most commonly used in articles related to business.', 'xml-sitemap-generator-for-google' ); ?>
			<br>
			<?php esc_html_e( 'Once this option is enabled, you will be able to specify values under the custom Stock Tickers Taxonomy.', 'xml-sitemap-generator-for-google' ); ?>
		</p>
	</div>
	<?php sgg_show_pro_overlay(); ?>
</div>
