<?php
/**
 * @var array $args
 */

if ( sgg_pro_enabled() ) {
	return;
}

$segment     = sanitize_key( $args['segment'] ?? 'default' );
$cta         = sgg_get_segment_cta( $segment, $args['utm'] ?? 'locked-feature' );
$cta_title   = $args['title'] ?? __( 'Premium feature', 'xml-sitemap-generator-for-google' );
$description = $args['description'] ?? __( 'Upgrade to unlock this workflow in Pro.', 'xml-sitemap-generator-for-google' );
?>
<div class="grim-upgrade-cta grim-upgrade-cta-<?php echo esc_attr( $segment ); ?>">
	<div class="grim-upgrade-cta-copy">
		<strong><?php echo esc_html( $cta_title ); ?></strong>
		<p><?php echo esc_html( $description ); ?></p>
	</div>
</div>
