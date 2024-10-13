<?php // phpcs:ignoreFile
/**
 * Render box with problems and notifications on the Advanced Ads overview page
 *
 * @var int $has_problems number of problems.
 * @var int $has_notices number of notices.
 * @var int $ignored_count number of ignored notices.
 */
?>
<h3<?php echo ! $has_problems ? ' style="display:none;"' : ''; ?>>
			  <?php
				esc_attr_e( 'Problems', 'advanceda-ads' );
				?>
	</h3>
<?php
Advanced_Ads_Ad_Health_Notices::get_instance()->display_problems();

?>
<h3<?php echo ! $has_notices ? ' style="display:none;"' : ''; ?>>
				<?php
				esc_attr_e( 'Notifications', 'advanceda-ads' );
				?>
</h3>
<?php
Advanced_Ads_Ad_Health_Notices::get_instance()->display_notices();

?>
<p class="adsvads-ad-health-notices-show-hidden" <?php echo ! $ignored_count ? 'style="display: none;"' : ''; ?>>
															<?php
															printf(
																wp_kses(
																	// translators: %s is the number of hidden notices.
																	esc_html__( 'Show %s hidden notices', 'advanced-ads' ),
																	[
																		'span' => [
																			'class',
																		],
																	]
																),
																'<span class="count">' . absint( $ignored_count ) . '</span>'
															);
															?>
	&nbsp;
	<button type="button"><span class="dashicons dashicons-visibility"></span></button>
</p>
<?php

if ( Advanced_Ads_Ad_Health_Notices::has_visible_problems() ) {
	include ADVADS_ABSPATH . 'admin/views/support-callout.php';
}

?>
<div class="advads-loader" style="display: none;"></div>
