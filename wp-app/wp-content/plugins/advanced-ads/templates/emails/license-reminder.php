<?php
/**
 * License reminder email template.
 *
 * @package AdvancedAds
 */

?>

<p><?php esc_html_e( 'Hi there,', 'advanced-ads' ); ?></p>

<p>
	<?php
	printf(
		/* translators: %s: Website URL */
		esc_html__( 'This is a weekly reminder that one or more of your Advanced Ads add-ons on %s doesn’t have a license key enabled.', 'advanced-ads' ),
		esc_html( get_bloginfo( 'url' ) )
	);
	?>
</p>

<p>
	<?php
	printf(
		/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag */
		esc_html__( '%1$sPlease visit the license page%2$s to make sure that all add-ons have valid licenses and that the keys are activated.', 'advanced-ads' ),
		'<a href="' . esc_url( 'https://wpadvancedads.com/account/?utm_source=admin-email&utm_medium=link&utm_campaign=a2-20-license-reminder#h-licenses' ) . '">',
		'</a>'
	);
	?>
</p>

<p>
	<?php
	printf(
		/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag */
		esc_html__( 'This email is sent in preparation for the upcoming Advanced Ads 2.0 update, to ensure compatibility and avoid potential issues caused by outdated plugin versions. %1$sRead more on the Advanced Ads website.%2$s', 'advanced-ads' ),
		'<a href="' . esc_url( 'https://wpadvancedads.com/advanced-ads-2-0/?utm_source=admin-email&utm_medium=link&utm_campaign=a2-20-license-reminder' ) . '">',
		'</a>'
	);
	?>
</p>

<p>
	<?php
	printf(
		/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag */
		esc_html__( '%1$sYou can disable this reminder here.%2$s', 'advanced-ads' ),
		'<a href="' . esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#licenses' ) ) . '">',
		'</a>'
	);
	?>
</p>

<p><?php esc_html_e( 'Kind regards', 'advanced-ads' ); ?><br><?php esc_html_e( 'The Advanced Ads team', 'advanced-ads' ); ?></p>
