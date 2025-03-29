<?php
/**
 * The class is responsible for adding widget in the WordPress admin area.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Modules\OneClick\Admin;

use AdvancedAds\Utilities\WordPress;
use AdvancedAds\Modules\OneClick\Helpers;
use AdvancedAds\Modules\OneClick\Options;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Admin.
 */
class Admin implements Integration_Interface {

	/**
	 * Hook into WordPress
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'advanced-ads-overview-widgets-after', [ $this, 'add_metabox' ] );
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook current page hook.
	 *
	 * @return void
	 */
	public function enqueue( $hook ): void {
		if ( 'toplevel_page_advanced-ads' !== $hook ) {
			return;
		}

		$config = Options::pubguru_config();

		wp_advads()->registry->enqueue_script( 'oneclick-onboarding' );
		wp_advads()->json->add(
			'oneclick',
			[
				'security'    => wp_create_nonce( 'pubguru_oneclick_security' ),
				'isConnected' => false !== Options::pubguru_config(),
				'btnCancel'   => __( 'Cancel', 'advanced-ads' ),
				'btnClose'    => __( 'Close', 'advanced-ads' ),
				'btnContinue' => __( 'Continue', 'advanced-ads' ),
				'btnRetry'    => __( 'Retry', 'advanced-ads' ),
				'siteDomain'  => WordPress::get_site_domain(),
				'spinner'     => ADVADS_BASE_URL . 'admin/assets/img/loader.gif',
				'addonRow'    => [
					'icon'       => ADVADS_BASE_URL . 'assets/img/add-ons/aa-addons-icons-m2pg.svg',
					'title'      => __( 'MonetizeMore & PubGuru Integration', 'advanced-ads' ),
					'content'    => __( 'Enables MonetizeMore users to link their settings with the PubGuru insights & analytics dashboard.', 'advanced-ads' ),
					'connect'    => __( 'Connect now', 'advanced-ads' ),
					'disconnect' => __( 'Disconnect now', 'advanced-ads' ),
				],
				'metabox'     => [
					'title'     => __( 'MonetizeMore & PubGuru Integration', 'advanced-ads' ),
					'visitText' => __( 'Visit the MonetizeMore website to learn about PubGuru', 'advanced-ads' ),
					'visitLink' => 'https://www.monetizemore.com/solutions/pubguru',
				],
				'step1'       => [
					'title'     => __( 'Onboarding Step 1 of 3, Consent and Privacy Policy', 'advanced-ads' ),
					'content'   => sprintf(
						wp_kses_post(
							/* translators: %s link to privacy policy */
							__( 'This form is designed exclusively for MonetizeMore customers who wish to integrate Advanced Ads with their PubGuru Dashboard. In alignment with our <a href="%s">Privacy Policy</a>, no information other than your domain name is exchanged, and Advanced Ads does not engage in any tracking activities.', 'advanced-ads' )
						),
						'https://wpadvancedads.com/privacy-policy/'
					),
					'agreeText' => __( 'I agree to share my domain name to facilitate the connection with my PubGuru account.', 'advanced-ads' ),
					'btnAgree'  => __( 'Connect with PubGuru', 'advanced-ads' ),
				],
				'step2'       => [
					'title'         => __( 'Onboarding Step 2 of 3, Connecting to PubGuru', 'advanced-ads' ),
					'loading'       => __( 'Fetching your domain information from PubGuru, please wait...', 'advanced-ads' ),
					'notRegistered' => __( 'The domain &ldquo;{0}&rdquo; is not registered with PubGuru', 'advanced-ads' ),
					'content'       => sprintf(
						/* translators: %1$s is contact link, %2$s is email link */
						__( 'If you are on a domain &ldquo;unknow&rdquo; to PubGuru, e.g., a staging site, please indicate the domain that you registered with PubGuru. If you need assistance, please <a href="%1$s">click here to contact PubGuru support</a> or <a href="%2$s">send an email to support@monetizemore.com</a>.', 'advanced-ads' ),
						'https://www.monetizemore.com/contact/',
						'mailto:support@monetizemore.com'
					),
					'inputLabel'    => __( 'Registered domain', 'advanced-ads' ),
					'serverError'   => __( 'The onboarding process has encountered an error: &ldquo;{0}&rdquo;', 'advanced-ads' ),
					'serverContent' => sprintf(
						/* translators: %1$s is contact link, %2$s is email link */
						__( 'Please wait a few minutes and try again. If you need assistance, please <a href="%1$s">click here to contact PubGuru support</a> or <a href="%2$s">send an email to support@monetizemore.com</a>.', 'advanced-ads' ),
						'https://www.monetizemore.com/contact/',
						'mailto:support@monetizemore.com'
					),
				],
				'step3'       => [
					'title'          => __( 'Onboarding Step 3 of 3, Test and Finalize Ad Unit Import', 'advanced-ads' ),
					'yourDomain'     => __( 'Your domain &ldquo;{0}&rdquo; is connected to PubGuru.', 'advanced-ads' ),
					'btnImport'      => __( 'Import PubGuru Ad Units', 'advanced-ads' ),
					'importContent'  => wp_kses_post(
						join(
							'',
							[
								'<p>' . __( 'This step is entirely optional. Your PubGuru configuration shows the following available ad units', 'advanced-ads' ) . '</p>',
								'<ul class="list-disc ml-4">',
								'<li>' . __( '3 In-Content Ads', 'advanced-ads' ) . '</li>',
								'<li>' . __( '1 Leaderboard Ad', 'advanced-ads' ) . '</li>',
								'</ul>',
								'<p>' . __( 'You will be able to preview the ad units injections on a test page before and there is a rollback option after the import.', 'advanced-ads' ) . '</p>',
							]
						)
					),
					'previewContent' => __( 'You may preview or change the test page for the ad units&rsquo; injections, or finalize the PubGuru Ad Unit import.', 'advanced-ads' ),
					'finalContent'   => sprintf(
						/* translators: %s rollback page link */
						__( 'You have successfully imported your PubGuru Ad Units. If necessary, use the <a href="%s">Rollback Tool</a> to revert your ad setup to a previous state.', 'advanced-ads' ),
						esc_url( admin_url( 'admin.php?page=advanced-ads-tools#import-history' ) )
					),
				],
				'settings'    => [
					'title'           => __( 'General Settings', 'advanced-ads' ),
					'help'            => sprintf(
						/* translators: %1$s is contact link, %2$s is email link */
						__( 'If you need assistance, please <a href="%1$s">click here to contact PubGuru support</a> or <a href="%2$s">send an email to support@monetizemore.com</a>.', 'advanced-ads' ),
						'https://www.monetizemore.com/contact/',
						'mailto:support@monetizemore.com'
					),
					'headerBidding'   => __( 'Activate PubGuru Header Bidding', 'advanced-ads' ),
					'activateTags'    => __( 'Activate Tag Conversion', 'advanced-ads' ),
					'trafficCop'      => __( 'Activate Traffic Cop Invalid Traffic Protection', 'advanced-ads' ),
					'trafficCopTrial' => __( '7 Days Trial', 'advanced-ads' ),
					'adsTxt'          => sprintf(
						/* translators: %s is link to PubGuru */
						__( 'Redirect ads.txt calls to the <a href="%s" target="_blank" rel="noreferrer">PubGuru platform</a>', 'advanced-ads' ),
						'https://app.pubguru.com/ads-txt'
					),
					'scriptLocation'  => __( 'Move the PubGuru Header Bidding script to the footer. <span class="muted">Keep this option disabled to maximize revenue. Only enable it if PageSpeed is your priority.</span>', 'advanced-ads' ),
					'onlyPreview'     => __( '(Only enabled on Preview Page)', 'advanced-ads' ),
				],
				'options'     => [
					'headerBidding'       => Options::module( 'header_bidding' ),
					'headerBiddingAtBody' => Options::module( 'header_bidding_at_body' ),
					'adsTxt'              => Options::module( 'ads_txt' ),
					'trafficCop'          => Options::module( 'traffic_cop' ),
					'tagConversion'       => Options::module( 'tag_conversion' ),
					'connectedDomain'     => $config['domain'] ?? '',
					'selectedMethod'      => $config['method'] ?? 'page',
					'selectedPage'        => $config['page'] ?? 0,
					'selectedPageTitle'   => isset( $config['page'] ) ? get_the_title( $config['page'] ) : '',
					'hasTrafficCop'       => Helpers::has_traffic_cop( $config ),
				],
				'modal'       => [
					'title'             => __( 'Import PubGuru Ad Units', 'advanced-ads' ),
					'btnSave'           => __( 'Close and update preview', 'advanced-ads' ),
					'btnFinal'          => __( 'Finalize ad unit import', 'advanced-ads' ),
					'btnUpdate'         => __( 'Update preview', 'advanced-ads' ),
					'btnGoto'           => __( 'Go to the preview', 'advanced-ads' ),
					'labelImport'       => __( 'Import method', 'advanced-ads' ),
					'labelSpecificPage' => __( 'Preview ad units on specific page', 'advanced-ads' ),
					'labelFinalImport'  => __( 'Finalize ad units import', 'advanced-ads' ),
					'descFinalImport'   => join(
						'',
						[
							'<ul class="list-disc ml-2">',
							'<li>' . __( 'Your existing ads and placements will be set to &lsquo;Draft&rsquo; mode', 'advanced-ads' ) . '</li>',
							'<li>' . __( 'Your PubGuru ad units will be imported as suitable ads and placements, and published right away', 'advanced-ads' ) . '</li>',
							'</ul>',
							'<p>' . __( 'You can manually republish specific ads and placements or fully rollback at any time.', 'advanced-ads' ) . '</p>',
						]
					),
				],
			]
		);
	}

	/**
	 * Add metabox
	 *
	 * @return void
	 */
	public function add_metabox(): void {
		echo '<div id="advads-oneclick-app"></div>';
	}
}
