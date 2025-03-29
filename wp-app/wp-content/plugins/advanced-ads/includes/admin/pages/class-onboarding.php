<?php
/**
 * Onboarding wizard screen.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin\Pages;

use AdvancedAds\Constants;
use Advanced_Ads_AdSense_Data;
use Advanced_Ads_AdSense_MAPI;
use Advanced_Ads_AdSense_Admin;
use AdvancedAds\Abstracts\Screen;
use AdvancedAds\Utilities\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Onboarding Wizard.
 */
class Onboarding extends Screen {

	/**
	 * Screen unique id.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return 'onboarding';
	}

	/**
	 * Register screen into WordPress admin area.
	 *
	 * @return void
	 */
	public function register_screen(): void {
		$hook = add_submenu_page(
			Constants::HIDDEN_PAGE_SLUG,
			__( 'Onboarding Wizard', 'advanced-ads' ),
			__( 'Onboarding Wizard', 'advanced-ads' ),
			Conditional::user_cap( 'advanced_ads_manage_options' ),
			ADVADS_SLUG . '-onboarding',
			[ $this, 'display' ]
		);

		$this->set_hook( $hook );
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		$this->i18n();
		$this->adsense_data();
		wp_enqueue_media();

		wp_advads()->registry->enqueue_style( 'screen-onboarding' );
		wp_advads()->registry->enqueue_script( 'screen-onboarding' );
	}

	/**
	 * Display screen content.
	 *
	 * @return void
	 */
	public function display(): void {
		include ADVADS_ABSPATH . 'views/admin/screens/onboarding.php';
	}

	/**
	 * Add Adsense data
	 *
	 * @return void
	 */
	private function adsense_data(): void {
		if ( current_user_can( Conditional::user_cap( 'advanced_ads_manage_options' ) ) ) {
			$nonce = wp_create_nonce( 'advanced_ads_wizard' );
			wp_advads()->json->add(
				'wizard',
				[
					'nonce'          => $nonce,
					'authUrl'        => 'https://accounts.google.com/o/oauth2/v2/auth?scope=' . rawurlencode( 'https://www.googleapis.com/auth/adsense.readonly' ),
					'clientId'       => Advanced_Ads_AdSense_MAPI::CID,
					'state'          => base64_encode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						wp_json_encode(
							[
								'api'        => 'adsense',
								'nonce'      => $nonce,
								'return_url' => admin_url( 'admin.php?page=advanced-ads-onboarding&route=adsense#wizard#adsense' ),
							]
						)
					),
					'redirectUri'    => Advanced_Ads_AdSense_MAPI::REDIRECT_URI,
					'adsenseData'    => array_merge(
						Advanced_Ads_AdSense_Data::get_instance()->get_options(),
						[ 'accounts' => Advanced_Ads_AdSense_MAPI::get_option()['accounts'] ]
					),
					'newAccountLink' => Advanced_Ads_AdSense_Admin::ADSENSE_NEW_ACCOUNT_LINK,
				]
			);
		}
	}

	/**
	 * Add wizard internationalization
	 *
	 * @return void
	 */
	private function i18n(): void {
		wp_advads_json_add(
			'i18n',
			[
				'wizard' => [
					'loading'       => __( 'Loading...', 'advanced-ads' ),
					'processing'    => __( 'Processing authorization...', 'advanced-ads' ),
					'selectAccount' => [
						'optionZero' => __( 'Select an account', 'advanced-ads' ),
						'title'      => __( 'Please select an account to use', 'advanced-ads' ),
					],
					'exitLabel'     => __( 'Exit the wizard without saving', 'advanced-ads' ),
					'btnGoBack'     => __( 'Go back', 'advanced-ads' ),
					'newsletter'    => [
						'title'            => __( 'Subscribe to our newsletter and get 2 add-ons for free', 'advanced-ads' ),
						'btnLabel'         => __( 'Subscribe now', 'advanced-ads' ),
						'inputPlaceholder' => __( 'Enter your email address', 'advanced-ads' ),
					],
					'stepTitles'    => [
						'adImage'  => __( 'Please select your image', 'advanced-ads' ),
						'adCode'   => __( 'Please paste your ad code', 'advanced-ads' ),
						'congrats' => [
							'default'       => __( 'Congratulations, your ad is now published!', 'advanced-ads' ),
							'adsenseManual' => __( 'Your ad is almost ready!', 'advanced-ads' ),
							'adsenseAuto'   => __( 'Congratulations, AdSense Auto Ads are now set up!', 'advanced-ads' ),
						],
					],
					'firstStep'     => [
						'taskAdSense'   => __( 'I want to use mostly Google AdSense or Google Auto Ads', 'advanced-ads' ),
						'taskImage'     => __( 'I want to add a banner ad with an image', 'advanced-ads' ),
						'taskCode'      => __( 'I want to insert an ad code from an ad network', 'advanced-ads' ),
						'stepHeading'   => __( 'Welcome! To kick things off, and to simplify your journey, answer a few questions and let Advanced Ads tailor the perfect ad for your site\'s needs.', 'advanced-ads' ),
						'agreementText' => __( 'I agree to share usage data to <strong>help the developers</strong> improve the plugin. Read more in our <a href="https://wpadvancedads.com/privacy-policy/" target="_blank">privacy policy</a>.', 'advanced-ads' ),
						'inputTitle'    => __( 'What\'s your task?', 'advanced-ads' ),
					],
					'bannerAd'      => [
						'mediaFrameTitle'   => __( 'Select an image to upload', 'advanced-ads' ),
						'mediaFrameButton'  => __( 'Use this image', 'advanced-ads' ),
						'mediaBtnUpload'    => __( 'Upload', 'advanced-ads' ),
						'mediaBtnReplace'   => __( 'Replace', 'advanced-ads' ),
						'stepHeading'       => __( 'Would you like to set a target URL for your image ad?', 'advanced-ads' ),
						'inputPlaceholder'  => __( 'Enter an optional target URL for your image ad', 'advanced-ads' ),
						'footerEnableText'  => __( 'Create placement and ad', 'advanced-ads' ),
						'footerDisableText' => __( 'Please select an image', 'advanced-ads' ),
					],
					'codeAd'        => [
						'inputPlaceholder'  => __( 'Paste the ad code that your advertising network has provided to you', 'advanced-ads' ),
						'footerEnableText'  => __( 'Insert the ad code into my site', 'advanced-ads' ),
						'footerDisableText' => __( 'Please paste your ad code', 'advanced-ads' ),
					],
					'googleAd'      => [
						'adsPlacement'      => [
							[
								'label' => __( 'I will place ad units manually', 'advanced-ads' ),
								'value' => 'manual',
							],
							[
								'label' => __( 'I will use Auto Ads and let Google place the ads automatically', 'advanced-ads' ),
								'value' => 'auto_ads',
							],
						],
						'autoAdsOptions'    => [
							[
								'label' => __( 'Enable Auto Ads on my site', 'advanced-ads' ),
								'value' => 'enable',
							],
							[
								'label' => __( 'Enable Accelerated Mobile Pages (AMP) Auto Ads', 'advanced-ads' ),
								'value' => 'enableAmp',
							],
						],
						'errors'            => [
							'notSaved'      => __( 'Unknown error while saving account information.', 'advanced-ads' ),
							'notFetched'    => __( 'Unknown error while fetching AdSense account information.', 'advanced-ads' ),
							'notAuthorized' => __( 'Unknown error while submitting the authorization code.', 'advanced-ads' ),
						],
						'stepHeading'       => __( 'Do you have a Google AdSense account?', 'advanced-ads' ),
						'btnSignup'         => __( 'No, I’d like to sign up for free now', 'advanced-ads' ),
						'btnConnect'        => __( 'Yes, connect to AdSense now', 'advanced-ads' ),
						'labelAccount'      => __( 'Account holder name:', 'advanced-ads' ),
						'labelConnected'    => __( 'You are connected to Google AdSense. Publisher ID:', 'advanced-ads' ),
						'labelAdsPlacement' => __( 'Will you place ad units manually or use Google Auto Ads?', 'advanced-ads' ),
						'labelAutoAds'      => __( 'Please confirm these Auto Ads options', 'advanced-ads' ),
						'footerProcessText' => __( 'Process', 'advanced-ads' ),
						'footerEnableText'  => [
							'manual'  => __( 'Create placement and ad', 'advanced-ads' ),
							'autoAds' => __( 'Confirm Auto Ads options', 'advanced-ads' ),
						],
						'footerDisableText' => __( 'Please select an option', 'advanced-ads' ),
					],
					'congrats'      => [
						'adsenseManual'  => [
							'stepHeading' => __( "For the last step, import the desired ad unit from AdSense. Visit the ad's edit screen to make your selection.", 'advanced-ads' ),
							'btnEditItem' => __( 'Select ad unit', 'advanced-ads' ),
							'liveHeading' => sprintf(
								/* translators: 1: opening strong tag, 2: closing strong tag, 3: opening italic tag, 4: closing italic tag. */
								esc_html__( 'We have created a placement for your ad that will display %1$safter the 3rd paragraph on every post%2$s. Go to %3$sAdvanced Ads > Placements%4$s and edit the placement to change this.', 'advanced-ads' ),
								'<strong>',
								'</strong>',
								'<i>',
								'</i>'
							),
						],
						'adsenseAuto'    => [
							'stepHeading' => __( "Everything's ready for AdSense to populate your site with Auto Ads. Make sure your site is verified, <strong>enable Auto Ads in your AdSense account</strong>, and, optionally, fine-tune their settings further.", 'advanced-ads' ),
							'btnAccount'  => __( 'Go to AdSense account', 'advanced-ads' ),
						],
						'stepHeading'    => __( 'We have created a placement for your ad that will display <strong>after the 3rd paragraph on every post</strong>. You may edit the placement to change this.', 'advanced-ads' ),
						'liveHeading'    => __( 'See the live ad in your website\'s frontend.', 'advanced-ads' ),
						'btnEditItem'    => __( 'Edit the placement', 'advanced-ads' ),
						'btnLiveAd'      => __( 'See the live ad', 'advanced-ads' ),
						'upgradeHeading' => __( 'Upgrade to all features and full support today', 'advanced-ads' ),
						'upgradeText'    => __( 'Our All Access deal offers every drop of ad expertise that we\'ve acquired in more than ten years, distilled into one jam-packed plugin bundle, supported by a dedicated team of real persons eager to help you.', 'advanced-ads' ),
						'btnUpgrade'     => __( 'Upgrade now', 'advanced-ads' ),
						'btnDashboard'   => __( 'Go to the Dashboard', 'advanced-ads' ),
						'upgradePoints'  => [
							[
								'title' => __( 'More placements', 'advanced-ads' ),
								'text'  => __( 'to embed in high-converting spots', 'advanced-ads' ),
								'icon'  => true,
							],
							[
								'title' => __( 'More conditions', 'advanced-ads' ),
								'text'  => __( 'for advanced targeting', 'advanced-ads' ),
								'icon'  => true,
							],
							[
								'title' => __( 'Ad Tracking', 'advanced-ads' ),
								'text'  => __( 'to optimize performance', 'advanced-ads' ),
								'icon'  => true,
							],
							[
								'title' => __( 'Click Fraud Protection', 'advanced-ads' ),
								'text'  => __( 'to safeguard your accounts', 'advanced-ads' ),
								'icon'  => true,
							],
							[
								'title' => __( 'Lazy Loading', 'advanced-ads' ),
								'text'  => __( 'to speed up your website', 'advanced-ads' ),
								'icon'  => true,
							],
							[
								'text' => '…' . __( 'and much more!', 'advanced-ads' ),
								'icon' => false,
							],
						],
					],
				],
			]
		);
	}
}
