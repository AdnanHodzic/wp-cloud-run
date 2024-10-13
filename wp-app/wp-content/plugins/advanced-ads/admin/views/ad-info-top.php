<?php // display ad wizard controls.
?><button id="advads-start-wizard" type="button" class="header-action button advads-button-secondary">
	<span class="dashicons dashicons-controls-play"></span><?php esc_html_e( 'Start Wizard', 'advanced-ads' ); ?>
</button>
<button id="advads-stop-wizard" type="button" class="header-action button advads-button-secondary advads-stop-wizard hidden">
	<span class="dashicons dashicons-no"></span><?php esc_html_e( 'Stop Wizard', 'advanced-ads' ); ?>
</button>
<script>
	// move wizard button to head.
	jQuery('#advads-start-wizard').appendTo('#advads-header-actions');
	jQuery('.advads-stop-wizard').appendTo('#advads-header-actions');
</script>
<?php
// show wizard welcome message.
if ( $this->show_wizard_welcome() || ! Advanced_Ads::get_number_of_ads() ) :
	?>
<div  class="advads-ad-metabox postbox">
	<?php
	if ( ! Advanced_Ads::get_number_of_ads() ) {
		include ADVADS_ABSPATH . 'admin/views/ad-list-no-ads.php';
	} if ( $this->show_wizard_welcome() ) :
		?>
<div id="advads-wizard-welcome">
	<br/>
		<?php
		/*
		<h2><?php _e( 'Welcome to the Wizard', 'advanced-ads' ); ?></h2>
		<p><?php _e( 'The Wizard helps you to quickly create and publish an ad. Therefore, only the most common options are visible.', 'advanced-ads' ); ?></p>
		*/
		?>
	<a class="advads-stop-wizard dashicons-before dashicons-no" style="line-height: 1.6em; cursor: pointer;"><?php esc_html_e( 'Stop Wizard and show all options', 'advanced-ads' ); ?></a>
</div>
<script>
	// move wizard button to head
	jQuery('#advads-hide-wizard-welcome').click( function(){ jQuery( '#advads-wizard-welcome' ).remove(); });
	jQuery('#advads-end-wizard').insertBefore('h1');
</script>
		<?php
	endif;
	?>
	</div>
	<?php
endif;
