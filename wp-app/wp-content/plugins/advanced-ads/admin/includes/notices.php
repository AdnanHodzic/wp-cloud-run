<?php // phpcs:ignoreFile
/**
 * Array with admin notices
 */
$advanced_ads_admin_notices = apply_filters(
	'advanced-ads-notices',
	[
		// email tutorial.
		'nl_intro'        => [
			'type'   => 'info',
			'text'   => Advanced_Ads_Admin_Notices::get_instance()->get_welcome_panel(),
			'global' => true,
		],
		// email tutorial.
		'nl_first_steps'  => [
			'type'         => 'subscribe',
			'text'         => __( 'Thank you for activating <strong>Advanced Ads</strong>. Would you like to receive the first steps via email?', 'advanced-ads' ),
			'confirm_text' => __( 'Yes, send it', 'advanced-ads' ),
			'global'       => true,
		],
		// free add-ons.
		'nl_free_addons'  => [
			'type'         => 'subscribe',
			'text'         => __( 'Thank you for using <strong>Advanced Ads</strong>. Stay informed and receive <strong>2 free add-ons</strong> for joining the newsletter.', 'advanced-ads' ),
			'confirm_text' => __( 'Add me now', 'advanced-ads' ),
			'global'       => true,
		],
		// adsense newsletter group.
		'nl_adsense'      => [
			'type'         => 'subscribe',
			'text'         => __( 'Learn more about how and <strong>how much you can earn with AdSense</strong> and Advanced Ads from my dedicated newsletter.', 'advanced-ads' ),
			'confirm_text' => __( 'Subscribe me now', 'advanced-ads' ),
			'global'       => true,
		],
		// missing license codes.
		'license_invalid' => [
			'type' => 'plugin_error',
			'text' => __( 'One or more license keys for <strong>Advanced Ads add-ons are invalid or missing</strong>.', 'advanced-ads' ) . ' '
				// translators: %s is a target URL.
				. sprintf( __( 'Please add valid license keys <a href="%s">here</a>.', 'advanced-ads' ), get_admin_url( null, 'admin.php?page=advanced-ads-settings#top#licenses' ) ),
		],
		// please review.
		'review'          => [
			'type'   => 'info',
			// 'text' => '<img src="' . ADVADS_BASE_URL . 'admin/assets/img/thomas.png" alt="Thomas" width="80" height="115" class="advads-review-image"/>'
			'text'   => '<div style="float: left; font-size: 4em; line-height: 1em; margin-right: 0.5em;">' . Advanced_Ads::get_number_of_ads() . '</div>'
						. '<div style="float:left;">'
						. '<p>' . __( '… ads created using <strong>Advanced Ads</strong>.', 'advanced-ads' ) . '</p>'
						. '<p>' . __( 'Do you find the plugin useful and would like to thank us for updates, fixing bugs and improving your ad setup?', 'advanced-ads' ) . '</p>'
						. '<p>' .
						// translators: this belongs to our message asking the user for a review. You can find a nice equivalent in your own language.
						__( 'When you give 5-stars, an actual person does a little happy dance!', 'advanced-ads' ) . '</p>'
						. '<p>'
						. '<span class="dashicons dashicons-external"></span>&nbsp;<strong><a href="https://wordpress.org/support/plugin/advanced-ads/reviews/?rate=5#new-post" target=_"blank">' . __( 'Sure, I appreciate your work', 'advanced-ads' ) . '</a></strong>'
						. ' &nbsp;&nbsp;<span class="dashicons dashicons-sos"></span>&nbsp;<a href="https://wpadvancedads.com/support/?utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-review" target=_"blank">' . __( 'Yes, but help me first to solve a problem, please', 'advanced-ads' ) . '</a>'
						. '</p></div>',
			'global' => false,
		],
		// Black Friday 2023 promotion.
		'bfcm23'          => [
			'type'   => 'promo',
			'text'   => sprintf(
				/* translators: %1$s is the markup for the discount value, %2$s starts a button link, %3$s closes the button link. */
				__( 'Save %1$s on all products with our Black Friday / Cyber Monday offer! %2$sGet this deal%3$s', 'advanced-ads' ),
				'<span style="font-weight: bold; font-size: 1.6em; vertical-align: sub;">30%</span>',
				'<a class="button button-primary" target="_blank" href="https://wpadvancedads.com/pricing/?utm_source=advanced-ads&utm_medium=link&utm_campaign=bfcm-2024">',
				'</a>'
			),
			'global' => true,
		],
		// Black Friday 2023 promotion.
		'promo-10ya'          => [
			'type'   => 'promo',
			'text'   => sprintf(
			/* translators: %1$s is the markup for the discount value, %2$s starts a button link, %3$s closes the button link. */
				__( 'Advanced Ads is turning 10! Come celebrate with us and enjoy a %1$s discount. %2$sSave now%3$s', 'advanced-ads' ),
				'<span style="font-weight: bold; font-size: 1.6em; vertical-align: sub;">30%</span>',
				'<a class="button button-primary" target="_blank" href="https://wpadvancedads.com/10-year-anniversary/?utm_source=advanced-ads&utm_medium=link&utm_campaign=10ya">',
				'</a>'
			),
			'global' => true,
		],
	]
);
