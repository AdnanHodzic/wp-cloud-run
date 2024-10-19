<?php
/**
 * PGHB Connect metabox template
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Modules\OneClick\Options;

$header_bidding = Options::module( 'header_bidding' );
?>
<div class="pubguru-not-connected space-y-4<?php echo false === $pubguru_config ? '' : ' hidden'; ?>">
	<label for="m2-connect-consent" class="flex items-center w-full mt-4">
		<span class="self-start">
			<input type="checkbox" class="!mt-1 !mr-2" id="m2-connect-consent">
		</span>
		<span class="text-base">
			<?php
			printf(
				wp_kses_post(
					/* translators: %s link to privacy policy */
					__( 'This form is designed exclusively for MonetizeMore customers who wish to integrate Advanced Ads with the PubGuru Dashboard. By selecting "Connect PGHB," you agree to share your domain name to facilitate the connection with your PubGuru account, in alignment with our <a href="%s">Privacy Policy</a>. Rest assured, no additional information is exchanged, and Advanced Ads does not engage in any tracking activities.', 'advanced-ads' )
				),
				'https://wpadvancedads.com/privacy-policy/'
			);
			?>
		</span>
	</label>

	<?php if ( 'domain' === Params::get( 'aa-oci' ) ) : ?>
	<label for="test-domain" class="flex items-center w-full">
		<strong class="mr-4">Test Domain:</strong>
		<span>
			<input type="text" id="m2-oci-test-domain">
		</span>
	</label>
	<?php endif; ?>

	<div class="flex items-center gap-x-4">
		<button class="button button-primary js-pubguru-continue" disabled type="button"><?php esc_html_e( 'Connect PGHB', 'advanced-ads' ); ?></button>
		<div class="lds-ripple aa-spinner"><div></div><div></div></div>
	</div>

	<div id="pubguru-warning" class="hidden">
		<div class="flex items-center bg-[#f2fcff] border border-solid border-advads px-4 rounded mb-4">
			<p><?php esc_html_e( 'When you connect to PubGuru, your existing ads and placements will be drafted, and PubGuru ad units will be imported automatically. If needed, you can easily rollback these changes at any time.', 'advanced-ads' ); ?></p>
		</div>
		<div class="flex items-center gap-x-4">
			<button class="button button-secondary js-pubguru-cancel" type="button"><?php esc_html_e( 'Cancel', 'advanced-ads' ); ?></button>
			<button class="button button-primary js-pubguru-connecting" type="button"><?php esc_html_e( 'Continue', 'advanced-ads' ); ?></button>
			<div class="lds-ripple aa-spinner"><div></div><div></div></div>
		</div>
	</div>
</div>

<div class="pubguru-connected<?php echo false === $pubguru_config ? ' hidden' : ''; ?>">
	<form id="pubguru-modules" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST" data-security="<?php echo esc_attr( wp_create_nonce( 'pubguru_module_changed' ) ); ?>">

		<p class="text-base mb-6">
			<?php
				echo wp_kses_post(
					__( 'To activate any of the settings with your PubGuru account, please select from the options below. Should you have any inquiries, feel free to <a href="https://www.monetizemore.com/contact/">click here<a/> to reach out to us or send an email to <a href="mailto:support@monetizemore.com">support@monetizemore.com</a>', 'advanced-ads' )
				);
				?>
		</p>
		<div class="advads-ui-switch-list">
			<div>
				<label for="pubguru-header-bidding" class="advads-ui-switch">
					<input type="checkbox" name="header_bidding" id="pubguru-header-bidding" class="sr-only peer" <?php checked( $header_bidding ); ?>>
					<div></div>
					<span>
						<?php esc_html_e( 'Install PubGuru Header Bidding', 'advanced-ads' ); ?>
					</span>
				</label>
			</div>

			<div<?php echo $header_bidding ? ' style="margin-left: 20px;"' : ' style="display: none;"'; ?>>
				<label for="pubguru-header-bidding-at-body" class="advads-ui-switch">
					<input type="checkbox" name="header_bidding_at_body" id="pubguru-header-bidding-at-body" class="sr-only peer" <?php checked( Options::module( 'header_bidding_at_body' ) ); ?>>
					<div></div>
					<span>
						<?php esc_html_e( 'Move the PubGuru Header Bidding script to the footer. Keep this option disabled to maximize revenue. Only enable it if PageSpeed is your priority.', 'advanced-ads' ); ?>
					</span>
				</label>
			</div>

			<div>
				<label for="pubguru-adstxt" class="advads-ui-switch">
					<input type="checkbox" name="ads_txt" id="pubguru-adstxt" <?php checked( Options::module( 'ads_txt' ) ); ?> />
					<div></div>
					<span>
						<?php
						echo wp_kses(
							__( 'Enable ads.txt settings from the <a href="https://app.pubguru.com/ads-txt" target="_blank">PubGuru platform</a>', 'advanced-ads' ),
							[
								'a' => [
									'href'   => [],
									'title'  => [],
									'target' => [],
								],
							]
						);
						?>
					</span>
				</label>
			</div>

			<div class="hidden">
				<label for="pubguru-traffic-cop" class="advads-ui-switch">
					<input type="checkbox" name="traffic_cop" id="pubguru-traffic-cop" <?php checked( Options::module( 'traffic_cop' ) ); ?> />
					<div></div>
					<span class="pg-tc-trail <?php echo $has_traffic_cop ? 'hidden' : ''; ?>">
						<?php esc_html_e( 'Install Traffic Cop. 7 days free trial', 'advanced-ads' ); ?>
					</span>
					<span class="pg-tc-install <?php echo $has_traffic_cop ? '' : 'hidden'; ?>">
						<?php esc_html_e( 'Install Traffic Cop.', 'advanced-ads' ); ?>
					</span>
				</label>
			</div>

			<div class="hidden">
				<label for="pubguru-tag-conversion" class="advads-ui-switch">
					<input type="checkbox" name="tag_conversion" id="pubguru-tag-conversion" <?php checked( Options::module( 'tag_conversion' ) ); ?> />
					<div></div>
					<span>
						<?php esc_html_e( 'Activate Tag Conversion', 'advanced-ads' ); ?>
					</span>
				</label>
			</div>

		</div>

	</form>

</div>

<?php
$session_key = get_option( 'advanced-ads-importer-history' );
if ( $session_key ) :
	$delete_link = wp_nonce_url(
		add_query_arg(
			[
				'action'      => 'advads_import_delete',
				'session_key' => $session_key,
			]
		),
		'advads_import_delete'
	);
	?>
<div class="rollback-block">
	<header><?php esc_html_e( 'Rollback', 'advanced-ads' ); ?></header>
	<a href="<?php echo esc_url( $delete_link ); ?>" class="button-link !text-red-500"><?php esc_html_e( 'Rollback the api changes back to previous state.', 'advanced-ads' ); ?></a>
</div>
<?php endif; ?>
