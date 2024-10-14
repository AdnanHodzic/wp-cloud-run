<div class="wizard-header">
	<div class="wizard-title">
		<?php echo esc_html__( 'Google XML Sitemaps Generator Wizard', 'xml-sitemap-generator-for-google' ) ?>
	</div>
	<div class="wizard-top-info">
		<div class="pro-badge">
			<a href="<?php echo esc_url( sgg_get_pro_url( 'wizard-header' ) ); ?>" target="_blank"><?php esc_html_e( 'Upgrade', 'xml-sitemap-generator-for-google' ); ?></a>
		</div>
		<span>|</span>
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=' . \GRIM_SG\Dashboard::$slug ) ); ?>" class="wizard-close-btn">&#x2715;</a>
	</div>
</div>
