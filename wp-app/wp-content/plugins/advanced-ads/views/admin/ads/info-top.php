<?php
/**
 * Display ad wizard controls.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.7.3
 */

use AdvancedAds\Utilities\WordPress;

?>
<button id="advads-start-wizard" type="button" class="header-action button advads-button-secondary">
	<span class="dashicons dashicons-controls-play"></span><?php esc_html_e( 'Start Wizard', 'advanced-ads' ); ?>
</button>
<button id="advads-stop-wizard" type="button" class="header-action button advads-button-secondary advads-stop-wizard hidden">
	<span class="dashicons dashicons-no"></span><?php esc_html_e( 'Stop Wizard', 'advanced-ads' ); ?>
</button>
<script>
	// Move wizard button to head.
	jQuery('#advads-start-wizard').appendTo('#advads-header-actions');
	jQuery('.advads-stop-wizard').appendTo('#advads-header-actions');
</script>
<?php if ( $this->show_wizard_welcome() || ! WordPress::get_count_ads() ) : ?>
<div class="advads-ad-metabox postbox">
	<?php
	if ( ! WordPress::get_count_ads() ) {
		include ADVADS_ABSPATH . 'admin/views/ad-list-no-ads.php';
	} if ( $this->show_wizard_welcome() ) :
		?>
<div id="advads-wizard-welcome">
	<br/>
	<a class="advads-stop-wizard dashicons-before dashicons-no" style="line-height: 1.6em; cursor: pointer;"><?php esc_html_e( 'Stop Wizard and show all options', 'advanced-ads' ); ?></a>
</div>
<script>
	// Move wizard button to head
	jQuery('#advads-hide-wizard-welcome').click( function(){ jQuery( '#advads-wizard-welcome' ).remove(); });
	jQuery('#advads-end-wizard').insertBefore('h1');
</script>
		<?php
	endif;
	?>
	</div>
	<?php
endif;
