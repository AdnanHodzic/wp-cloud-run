<?php
$MAPI = Advanced_Ads_AdSense_MAPI::get_instance();
$options = $this->data->get_options();
$adsense_id = $this->data->get_adsense_id();
$mapi_options = Advanced_Ads_AdSense_MAPI::get_option();

$mapi_account_details = false;

$CID = Advanced_Ads_AdSense_MAPI::CID;

$use_user_app = Advanced_Ads_AdSense_MAPI::use_user_app();
if ( $use_user_app ) {
	$CID = ADVANCED_ADS_MAPI_CID;
}

$can_connect = true;

if ( $use_user_app && !( ( defined( 'ADVANCED_ADS_MAPI_CID' ) && '' != ADVANCED_ADS_MAPI_CID ) && ( defined( 'ADVANCED_ADS_MAPI_CIS' ) && '' != ADVANCED_ADS_MAPI_CIS ) ) ) {
	$can_connect = false;
}

$has_token = Advanced_Ads_AdSense_MAPI::has_token( $adsense_id );

if ( $has_token && isset( $mapi_options['accounts'][ $adsense_id ]['details'] ) ) {
    $mapi_account_details = $mapi_options['accounts'][ $adsense_id ]['details'];
}

$alerts = Advanced_Ads_AdSense_MAPI::get_stored_account_alerts( $adsense_id );

/* translators: 1: opening anchor tag for link to adsense account  2: closing anchor tag for link to adsense account */
$alerts_heading            = $adsense_id ? sprintf( esc_html__( 'Warning from your %1$sAdSense account%2$s', 'advanced-ads' ), '<a target="_blank" href="https://www.google.com/adsense/new/u/1/' . esc_html( $adsense_id ) . '/">', '</a>' ) : esc_html__( 'AdSense warnings', 'advanced-ads' );

$alerts_heading = $adsense_id
	? wp_kses(
		sprintf(
			/* translators: 1: opening anchor tag for link to adsense account  2: closing anchor tag for link to adsense account */
			__( 'Warning from your %1$sAdSense account%2$s', 'advanced-ads' ),
			'<a target="_blank" href="https://www.google.com/adsense/new/u/1/' . $adsense_id . '/">',
			'</a>'
		),
		[
			'a' => [
				'target' => true,
				'href'   => true,
			],
		]
	)
	: __( 'AdSense warnings', 'advanced-ads' );

$alerts_dismiss            = __( 'dismiss', 'advanced-ads' );
$connection_error_messages = Advanced_Ads_AdSense_MAPI::get_connect_error_messages();
$alerts_advads_messages    = Advanced_Ads_Adsense_MAPI::get_adsense_alert_messages();

?>
<div id="mapi-account-alerts">
	<?php if ( is_array( $alerts ) && isset( $alerts['items'] ) && is_array( $alerts['items'] ) && $alerts['items'] ) : ?>
		<h3>
			<?php
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped
			echo $alerts_heading;
			?>
		</h3>
		<?php foreach ( $alerts['items'] as $alert_id => $alert ) : ?>
			<div class="card advads-notice-block advads-error">
				<button type="button" class="mapi-dismiss-alert notice-dismiss" data-id="<?php echo esc_attr( $alert_id ); ?>">
					<span class="screen-reader-text"><?php echo esc_html( $alerts_dismiss ); ?></span>
				</button>
				<?php
				$internal_id = $alert['id'] ?? str_replace( '-', '_', strtoupper( $alert['type'] ) );
				echo wp_kses(
					$alerts_advads_messages[ $internal_id ] ?? $alert['message'],
					[
						'a' => [
							'href'   => true,
							'target' => true,
							'class'  => true,
						],
					]
				);
				?>
			</div>
		<?php endforeach; ?>
		<?php /* translators: %s: date and time of last check in the format set in wp_options */ ?>
		<p class="description alignright"><?php printf( __( 'last checked: %s', 'advanced-ads' ), $alerts['lastCheck'] ? esc_html( ( new DateTime( '@' . $alerts['lastCheck'], Advanced_Ads_Utils::get_wp_timezone() ) )->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) : '-' ); ?></p>
	<?php endif; ?>
	<?php
	if ( ! empty( $mapi_options['connect_error'] ) ) {
		$message = isset( $mapi_options['connect_error']['message'] ) ? $mapi_options['connect_error']['message'] : '';
		if ( isset( $connection_error_messages[ $mapi_options['connect_error']['reason'] ] ) ) {
			$message = $connection_error_messages[ $mapi_options['connect_error']['reason'] ];
		}
		if ( ! empty( $message ) ) {
			echo '<div id="mapi-connect-errors" class="notice error inline"><p class="advads-notice-inline advads-error">';
			echo wp_kses( $message, [
				'a' => [
					'id'    => [],
					'class' => [],
					'href'  => [],
					'style' => [],
				],
				'i' => [
					'id'    => [],
					'class' => [],
					'style' => [],
				],
			] );
			echo '</p></div>';
		}
	}
	?>
</div>
<div id="full-adsense-settings-div" <?php if ( empty( $adsense_id ) ) echo 'style="display:none"' ?>>
	<input type="text" <?php echo $has_token ? 'readonly' : ''; ?> name="<?php echo esc_attr( GADSENSE_OPT_NAME ); ?>[adsense-id]" placeholder="pub-1234567891234567" style="margin-right:.8em" id="adsense-id" size="32" value="<?php echo esc_attr( $adsense_id ); ?>"/>
	<?php if ( !empty( $adsense_id ) && !$has_token ) : ?>
	<a id="connect-adsense" class="button-primary  <?php echo ! Advanced_Ads_Checks::php_version_minimum() ? 'disabled ' : ''; ?>preventDefault" <?php if ( ! $can_connect || ! Advanced_Ads_Checks::php_version_minimum() ) echo 'disabled'; ?>><?php esc_attr_e( 'Connect to AdSense', 'advanced-ads' ) ?></a>
	<?php endif; ?>
	<?php if ( $has_token ) : ?>
	<a id="revoke-token" class="button-secondary preventDefault"><?php esc_attr_e( 'Revoke API acccess', 'advanced-ads' ) ?></a>
	<div id="gadsense-freeze-all" style="position:fixed;top:0;bottom:0;right:0;left:0;background-color:rgba(255,255,255,.5);text-align:center;display:none;">
		<img alt="..." src="<?php echo ADVADS_BASE_URL . 'admin/assets/img/loader.gif'; ?>" style="margin-top:40vh" />
	</div>
	<?php endif; ?>
    <?php if ( $mapi_account_details ) : ?>
        <p class="description"><?php esc_html_e( 'Account holder name', 'advanced-ads' ); echo ': <strong>' . esc_html( $mapi_account_details['name'] ) . '</strong>'; ?></p>
    <?php else : ?>
		<?php if ( 0 !== strpos( $adsense_id, 'pub-' ) ) : ?>
			<p class="advads-notice-inline advads-error"><?php esc_html_e( 'The Publisher ID has an incorrect format. (must start with "pub-")', 'advanced-ads' ); ?></p>
		<?php endif; ?>
    <?php endif; ?>
</div>
<?php if ( empty( $adsense_id ) ) : ?>
<div id="auto-adsense-settings-div" <?php if ( !empty( $adsense_id ) ) echo 'style="display:none;"' ?>>
	<div class="widget-col">
		<h3><?php _e( 'Yes, I have an AdSense account', 'advanced-ads' ) ?></h3>
		<a id="connect-adsense" class="button-primary <?php echo ! Advanced_Ads_Checks::php_version_minimum() ? 'disabled ' : ''; ?>preventDefault" <?php echo ! Advanced_Ads_Checks::php_version_minimum() ? 'disabled' : ''; ?>><?php _e( 'Connect to AdSense', 'advanced-ads' ) ?></a>
		<a id="adsense-manual-config" class="button-secondary preventDefault"><?php _e( 'Configure everything manually', 'advanced-ads' ) ?></a>
	</div>
	<div class="widget-col">
		<h3><?php _e( "No, I still don't have an AdSense account", 'advanced-ads' ) ?></h3>
		<a class="button button-secondary" target="_blank" href="<?php echo Advanced_Ads_AdSense_Admin::ADSENSE_NEW_ACCOUNT_LINK; ?>"><?php _e( 'Get a free AdSense account', 'advanced-ads' ); ?></a>
		<p>
			<?php
			printf(
				wp_kses(
					// translators: %1$s is an opening a tag, %2$s is the closing one
					__( 'See all %1$srecommended ad networks%2$s.', 'advanced-ads' ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				),
				'<a href="https://wpadvancedads.com/recommended-ad-networks/?utm_source=advanced-ads&utm_medium=link&utm_campaign=recommendations" target="_blank">',
				'</a>'
			);
			?>
		</p>
	</div>
</div>
<style type="text/css">
	#adsense table h3 {
		margin-top: 0;
		margin-bottom: .2em;
	}
	#adsense table button {
		margin-bottom: .8em;
	}
	#adsense .form-table tr {
		display: none;
	}
	#adsense .form-table tr:first-of-type {
		display: table-row;
	}
	#auto-adsense-settings-div .widget-col {
		float: left;
		margin: 0px 5px 5px 0px;
	}
	#auto-adsense-settings-div:after {
		display: block;
		content: "";
		clear: left;
	}
	#auto-adsense-settings-div .widget-col:first-child {
		margin-right: 20px;
		border-right: 1px solid #cccccc;
		padding: 0px 20px 0px 0px;
		position: relative;
	}
	#auto-adsense-settings-div .widget-col:first-child:after {
		position: absolute;
		content: "or";
		display: block;
		top: 20px;
		right: -10px;
		background: #ffffff;
		color: #cccccc;
		font-size: 20px;
	}
	@media screen and (max-width: 1199px) {
		#auto-adsense-settings-div .widget-col { float: none; margin-right: 0;  }
		#auto-adsense-settings-div .widget-col:first-child { margin: 0px 0px 20px 0px; padding: 0px 0px 20px 0px; border-bottom: 1px solid #cccccc; border-right: 0; }
		#auto-adsense-settings-div .widget-col:first-child:after { top: auto; right: auto; bottom: -10px; left: 20px; display: inline-block; padding: 0px 5px 0px 5px; }
	}
</style>
<?php
	echo "<br/><br/><br/><hr>";
	include ADVADS_ABSPATH . 'modules/gadsense/admin/views/auto-ads-video.php';
	?><p>
	<a href="https://wpadvancedads.com/place-adsense-ad-unit-manually/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-manually" style="text-decoration: none;" target="_blank"><span class="dashicons dashicons-welcome-learn-more"></span>
		<?php
		esc_attr_e( 'How to choose specific positions for AdSense ad units', 'advanced-ads' ); ?></a>
	</p><?php
else : ?>
<p>
	<?php
	printf(
		wp_kses(
			// translators: %1$s is the opening link tag to our manual; %2$s is the appropriate closing link tag; %3$s is the opening link tag to our help forum; %4$s is the appropriate closing link tag
			__( 'Problems with AdSense? Check out the %1$smanual%2$s or %3$sask here%4$s.', 'advanced-ads' ),
			[
				'a' => [
					'href'   => [],
					'target' => [],
				],
			]
		),
		'<a href="https://wpadvancedads.com/adsense-ads/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adsense-manual-check" target="_blank">',
		'</a>',
		'<a href="https://wordpress.org/support/plugin/advanced-ads/#new-post" target="_blank">',
		'</a>'
	); ?></p>
<p>
	<?php
	printf(
		wp_kses(
		// translators: %1$s is an opening a tag, %2$s is the closing one
			__( 'See all %1$srecommended ad networks%2$s.', 'advanced-ads' ),
			[
				'a' => [
					'href'   => [],
					'target' => [],
				],
			]
		),
		'<a href="https://wpadvancedads.com/recommended-ad-networks/?utm_source=advanced-ads&utm_medium=link&utm_campaign=recommendations" target="_blank">',
		'</a>'
	);
	?>
</p><?php endif; ?>
<?php if ( ! Advanced_Ads_Checks::php_version_minimum() ) : ?>
<p class="advads-notice-inline advads-error"><?php esc_html_e( 'Can not connect AdSense account. PHP version is too low.', 'advanced-ads' ); ?></p>
<?php endif; ?>
<div id="mapi-alerts-overlay">
    <div style="position:relative;text-align:center;display:table;width:100%;height:100%;">
        <div style="display:table-cell;vertical-align:middle;">
            <img alt="loading" src="<?php echo esc_url( ADVADS_BASE_URL . 'admin/assets/img/loader.gif' ); ?>" />
        </div>
    </div>
</div>
<script type="text/javascript">
	if ( 'undefined' == typeof window.AdsenseMAPI ) {
		AdsenseMAPI = {};
	}
	AdsenseMAPI = Object.assign(
		AdsenseMAPI,
		<?php
		echo wp_json_encode(
			[
				'alertsMsg'        => $alerts_advads_messages,
				'alertsHeadingMsg' => $alerts_heading,
				'alertsDismissMsg' => wp_kses( $alerts_dismiss, [] ),
			]
		)
		?>
	);
</script>
<style type="text/css">
    #adsense {
        position: relative;
    }
    #mapi-alerts-overlay {
        position:absolute;
        top:0;
        right:0;
        bottom:0;
        left:0;
        background-color: rgb(255, 255, 255, .90);
        display: none;
    }
    #mapi-account-alerts, #mapi-connect-errors {
        margin-bottom: .5em;
    }
    #dissmiss-connect-error {
        cursor: pointer;
    }
    #gadsense-overlay {
        display:none;
        background-color:rgba(255,255,255,.5);
        position:absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        text-align:center;
    }
</style>
