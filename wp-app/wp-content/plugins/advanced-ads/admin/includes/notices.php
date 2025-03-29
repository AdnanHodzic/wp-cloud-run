<?php
/**
 * Array with admin notices
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

use AdvancedAds\Utilities\WordPress;

if ( ! defined( 'NOTICE_TYPES' ) ) {
	define(
		'NOTICE_TYPES',
		[
			'info'      => 'info',
			'subscribe' => 'subscribe',
			'error'     => 'plugin_error',
			'promo'     => 'promo',
		]
	);
}

// These add-on names correspond to the names in the class of constants from ADDONS_NON_COMPATIBLE_VERSIONS.
$manual_addons = [
	'advanced-ads-page-peel'        => [
		'title' => 'Advanced Ads Page Peel',
		'zip'   => esc_url( 'https://wpadvancedads.com/wp-content/uploads/advanced-ads-page-peel.zip' ),
		'link'  => esc_url( 'https://wpadvancedads.com/manual/how-to-install-an-add-on/?utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-update-pagepeel-a220' ),
	],
	'advanced-ads-browser-language' => [
		'title' => 'Advanced Ads Browser Language Visitor Condition',
		'zip'   => esc_url( 'https://wpadvancedads.com/wp-content/uploads/advanced-ads-browser-language.zip' ),
		'link'  => esc_url( 'https://wpadvancedads.com/manual/how-to-install-an-add-on/?utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-update-browserlang-a220' ),
	],
	'slider-ads'                    => [
		'title' => 'Advanced Ads Ad Slider',
		'zip'   => esc_url( 'https://wpadvancedads.com/wp-content/uploads/advanced-ads-slider.zip' ),
		'link'  => esc_url( 'https://wpadvancedads.com/manual/how-to-install-an-add-on/?utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-update-adslider-a220' ),
	],
];

$advanced_ads_admin_notices = [
	// email tutorial.
	'nl_first_steps'  => [
		'type'         => NOTICE_TYPES['subscribe'],
		'text'         => __( 'Thank you for activating <strong>Advanced Ads</strong>. Would you like to receive the first steps via email?', 'advanced-ads' ),
		'confirm_text' => __( 'Yes, send it', 'advanced-ads' ),
		'global'       => true,
	],
	// free add-ons.
	'nl_free_addons'  => [
		'type'         => NOTICE_TYPES['subscribe'],
		'text'         => __( 'Hey, welcome to Advanced Ads! Join our newsletter and snag <strong>2 free add-ons</strong> plus our email intro course. It’s the perfect way to get started smoothly!', 'advanced-ads' ),
		'confirm_text' => __( 'Subscribe me now', 'advanced-ads' ),
		'global'       => false,
	],
	// adsense newsletter group.
	'nl_adsense'      => [
		'type'   => NOTICE_TYPES['subscribe'],
		'text'   => __( 'Learn more about how and <strong>how much you can earn with AdSense</strong> and Advanced Ads from my dedicated newsletter.', 'advanced-ads' ),
		'global' => true,
	],
	// missing license codes.
	'license_invalid' => [
		'type' => NOTICE_TYPES['error'],
		'text' => __( 'One or more license keys for <strong>Advanced Ads add-ons are invalid or missing</strong>.', 'advanced-ads' ) . ' '
				  /* translators: %s is a target URL. */
				  . sprintf( __( 'Please add valid license keys <a href="%s">here</a>.', 'advanced-ads' ), get_admin_url( null, 'admin.php?page=advanced-ads-settings#top#licenses' ) ),
	],
	// please review.
	'review'          => [
		'type'   => NOTICE_TYPES['info'],
		// 'text' => '<img src="' . ADVADS_BASE_URL . 'admin/assets/img/thomas.png" alt="Thomas" width="80" height="115" class="advads-review-image"/>'
		'text'   => '<div style="float: left; font-size: 4em; line-height: 1em; margin-right: 0.5em;">' . WordPress::get_count_ads() . '</div>'
					. '<div style="float:left;">'
					. '<p>' . __( '… ads created using <strong>Advanced Ads</strong>.', 'advanced-ads' ) . '</p>'
					. '<p>' . __( 'Do you find the plugin useful and would like to thank us for updates, fixing bugs and improving your ad setup?', 'advanced-ads' ) . '</p>'
					. '<p>' .
					/* translators: this belongs to our message asking the user for a review. You can find a nice equivalent in your own language. */
					__( 'When you give 5-stars, an actual person does a little happy dance!', 'advanced-ads' ) . '</p>'
					. '<p>'
					. '<span class="dashicons dashicons-external"></span>&nbsp;<strong><a href="https://wordpress.org/support/plugin/advanced-ads/reviews/?rate=5#new-post" target=_"blank">' . __( 'Sure, I appreciate your work', 'advanced-ads' ) . '</a></strong>'
					. ' &nbsp;&nbsp;<span class="dashicons dashicons-sos"></span>&nbsp;<a href="https://wpadvancedads.com/support/?utm_source=advanced-ads&utm_medium=link&utm_campaign=notice-review" target=_"blank">' . __( 'Yes, but help me first to solve a problem, please', 'advanced-ads' ) . '</a>'
					. '</p></div>',
		'global' => false,
	],
	// Black Friday 2023 promotion.
	'bfcm23'          => [
		'type'   => NOTICE_TYPES['promo'],
		'text'   => sprintf(
		/* translators: %1$s is the markup for the discount value, %2$s starts a button link, %3$s closes the button link. */
			__( 'Save %1$s on all products with our Black Friday / Cyber Monday offer! %2$sGet this deal%3$s', 'advanced-ads' ),
			'<span style="font-weight: bold; font-size: 1.6em; vertical-align: sub;">30%</span>',
			'<a class="button button-primary" target="_blank" href="https://wpadvancedads.com/pricing/?utm_source=advanced-ads&utm_medium=link&utm_campaign=bfcm-2023">',
			'</a>'
		),
		'global' => true,
	],
	'monetize_wizard' => [
		'type' => NOTICE_TYPES['info'],
		'text' => sprintf(
			wp_kses_post(
			/* translators: %s: URL to the Advanced Ads onboarding wizard. */
				__( 'Quickly set up Advanced Ads and monetize your website with just a few clicks. <a class="button button-primary" href="%s">Launch the wizard</a>', 'advanced-ads' )
			),
			admin_url( 'admin.php?page=advanced-ads-onboarding' )
		),
	],
];

// Add specific notifications for plugins that are incompatible with Advanced Ads 2.0.
foreach ( \AdvancedAds\Constants::ADDONS_NON_COMPATIBLE_VERSIONS as $version => $addon ) {
	if ( isset( $manual_addons[ $addon ] ) ) {
		$advanced_ads_admin_notices[ $addon . '_upgrade' ] = [
			'type'   => NOTICE_TYPES['info'],
			'text'   => sprintf(
				wp_kses(
				/* translators: %1$s: URL to the plugin file, %2$s: URL to the guide */
					__( 'Your automatically deactivated version of <strong>%1$s needs to be updated manually</strong>. Please <a href="%2$s" target="_blank">download the newest plugin file</a> and follow our guide on <a href="%3$s" target="_blank">How to install an add-on</a>.', 'advanced-ads' ),
					[
						'strong' => [],
						'a'      => [
							'href'   => [],
							'target' => [],
						],
					]
				),
				$manual_addons[ $addon ]['title'],
				$manual_addons[ $addon ]['zip'],
				$manual_addons[ $addon ]['link'],
			),
			'global' => true,
		];
	} else {
		$advanced_ads_admin_notices[ $addon . '_upgrade' ] = [
			'type'   => NOTICE_TYPES['info'],
			'text'   => sprintf(
				wp_kses(
				/* translators: %1$s: URL to the plugin file, %2$s: URL to the guide */
					__( 'Your version of <strong>%1$s</strong> is incompatible with <strong>Advanced Ads %2$s</strong> and has been deactivated. Please update the plugin to the latest version.', 'advanced-ads' ),
					[
						'strong' => [],
					]
				),
				ucwords( str_replace( '-', ' ', $addon ) ),
				ADVADS_VERSION,
			),
			'global' => true,
		];
	}
}

$advanced_ads_admin_notices = apply_filters(
	'advanced-ads-notices',
	$advanced_ads_admin_notices
);
