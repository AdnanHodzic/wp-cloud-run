<?php
/**
 * HTML markup for AdSense connection modal frame.
 *
 * @package   Advanced_Ads_Admin
 */

use AdvancedAds\Utilities\WordPress;

$data_obj = Advanced_Ads_AdSense_Data::get_instance();
$options  = $data_obj->get_options();

$nonce = wp_create_nonce( 'advads-mapi' );

$CID = Advanced_Ads_AdSense_MAPI::CID;

$use_user_app = Advanced_Ads_AdSense_MAPI::use_user_app();
if ( $use_user_app ) {
	$CID = ADVANCED_ADS_MAPI_CID;
}

$state = [
	'api' => 'adsense',
	'nonce' => $nonce,
	'return_url' => admin_url( 'admin.php?page=advanced-ads-settings&oauth=1#top#adsense' ),
];

$connection_error_messages = Advanced_Ads_AdSense_MAPI::get_connect_error_messages();

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?scope=' .
			urlencode( 'https://www.googleapis.com/auth/adsense.readonly' ) .
			'&client_id=' . $CID .
			'&redirect_uri=' . urlencode( Advanced_Ads_AdSense_MAPI::REDIRECT_URI ) .
			'&state=' . urlencode( base64_encode( wp_json_encode( $state ) ) ) .
			'&access_type=offline&include_granted_scopes=true&prompt=consent&response_type=code';

$_get = wp_unslash( $_GET );

if ( isset( $_get['oauth'] ) && '1' == $_get['oauth'] && isset( $_get['api'] ) && 'adsense' == $_get['api'] ) : ?>
	<?php if ( isset( $_get['nonce'] ) && false !== wp_verify_nonce( $_get['nonce'], 'advads-mapi' ) ) : ?>
		<?php if ( isset( $_get['code'] ) && WordPress::user_can( 'advanced_ads_manage_options' ) ) : ?>
		<input type="hidden" id="advads-adsense-oauth-code" value="<?php echo esc_attr( urldecode( $_get['code'] ) ); ?>" />
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
<div id="gadsense-modal" data-return="<?php echo ESC_ATTR( admin_url( 'admin.php?page=advanced-ads-settings&oauth=1#top#adsense' ) ); ?>">
	<div id="gadsense-modal-outer">
		<div id="gadsense-modal-inner">
			<div id="gadsense-modal-content">
				<div class="gadsense-modal-content-inner" data-content="confirm-code">
					<i class="dashicons dashicons-dismiss"></i>
					<h2><?php esc_html_e( 'Processing authorization', 'advanced-ads-gam' );?></h2>
					<div class="gadsense-overlay">
						<img alt="..." src="<?php echo ADVADS_BASE_URL . 'admin/assets/img/loader.gif'; ?>" style="margin-top:3em" />
					</div>
				</div>
				<div class="gadsense-modal-content-inner" data-content="error" style="display:none;">
					<i class="dashicons dashicons-dismiss"></i>
					<h3><?php esc_html_e( 'Cannot access your account information.', 'advanced-ads' ); ?></h3>
					<p class="error-message advads-notice-inline advads-error"></p>
					<p class="error-description" style="font-size:1.1em;"></p>
				</div>
				<div class="gadsense-modal-content-inner" data-content="account-selector" style="display:none;">
					<i class="dashicons dashicons-dismiss"></i>
					<h3><?php esc_html_e( 'Please select an account', 'advanced-ads' ); ?></h3>
					<p>
						<select id="mapi-select-account">
						</select>
					</p>
					<p><button class="button-primary"><?php esc_html_e( 'Use this account', 'advanced-ads' ); ?></button></p>
					<input type="hidden" class="token-data" value="" />
					<input type="hidden" class="accounts-details" value="" />
					<div class="gadsense-overlay">
						<img alt="..." src="<?php echo ADVADS_BASE_URL . 'admin/assets/img/loader.gif'; ?>" style="margin-top:3em" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	if ( 'undefined' == typeof window.AdsenseMAPI ) {
		AdsenseMAPI = {};
	}
	AdsenseMAPI.nonce = '<?php echo wp_strip_all_tags( $nonce ); ?>';
	AdsenseMAPI.oAuth2 = '<?php echo wp_strip_all_tags( $auth_url ); ?>';
    AdsenseMAPI.connectErrorMsg = <?php echo wp_json_encode( $connection_error_messages ); ?>;
</script>
<style type="text/css">
.gadsense-overlay {
	display:none;
	background-color:rgba(255,255,255,.5);
	position:absolute;
	width: 100%;
	height: 100%;
	top: 0;
	left: 0;
	text-align:center;
}
#gadsense-modal {
	display: none;
	background-color: rgba(0,0,0,.5);
	position:fixed;
	top:0;
	left:0;
	right:0;
	bottom:0;
}
#gadsense-modal-outer {
	position: relative;
	width: 60%;
	height: 100%;
    <?php if ( is_rtl() ) : ?>
	margin-right: 20%;
    <?php else : ?>
	margin-left: 20%;
    <?php endif; ?>
}
#gadsense-modal-inner {
	display: table;
	width: 100%;
	height: 100%;
}
#gadsense-modal-content {
	display: table-cell;
	vertical-align: middle;
}
.gadsense-modal-content-inner {
	padding: 1em;
	background-color: #f0f0f0;
	position: relative;
	border: 3px solid #808b94;
}
.gadsense-modal-content-inner .dashicons-dismiss {
	background-color: #fff;
	border-radius: 100%;
	cursor: pointer;
	top: -.5em;
    <?php if ( is_rtl() ) : ?>
    left: -.5em;
    <?php else : ?>
	right: -.5em;
    <?php endif; ?>
	position: absolute;
	z-index: 2;
}
</style>
