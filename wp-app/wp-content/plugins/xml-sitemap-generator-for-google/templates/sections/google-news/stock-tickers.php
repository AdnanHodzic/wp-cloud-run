<?php
/**
 * @var $args
 */

use GRIM_SG\Dashboard;

$settings = $args['settings'] ?? new stdClass();
?>
<div class="postbox">
	<h3 class="hndle"><?php
		esc_html_e( 'Stock Tickers', 'xml-sitemap-generator-for-google' );

		sgg_show_pro_badge();
		?></h3>
	<div class="inside pro-wrapper <?php echo esc_attr( sgg_pro_class() ); ?>">
		<p>
			<?php
			Dashboard::render(
				'fields/checkbox.php',
				array(
					'name'  => 'google_news_stocks',
					'value' => $settings->google_news_stocks ?? false,
					'label' => esc_html__( 'Enable Stock Tickers ', 'xml-sitemap-generator-for-google' ),
					'class' => 'google-news-depended',
				)
			);
			?>
		</p>
		<p class="google-news-depended">
			<?php esc_html_e( 'Stock tickers are most commonly used in articles related to business.', 'xml-sitemap-generator-for-google' ); ?>
			<br>
			<?php esc_html_e( 'Once this option is enabled, you will be able to specify values under the custom Stock Tickers Taxonomy.', 'xml-sitemap-generator-for-google' ); ?>
		</p>

		<?php sgg_show_pro_overlay(); ?>
	</div>
</div>
