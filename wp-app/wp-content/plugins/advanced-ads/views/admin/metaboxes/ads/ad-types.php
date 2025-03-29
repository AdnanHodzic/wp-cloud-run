<?php
/**
 * Render the types meta box on the ad edit screen
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 *
 * @var Ad $ad Ad instance.
 */

use AdvancedAds\Admin\Upgrades;
use AdvancedAds\Utilities\Conditional;

$types = wp_advads_get_ad_types();

if ( empty( $types ) ) : ?>
	<p>
		<?php esc_html_e( 'No ad types defined', 'advanced-ads' ); ?>
	</p>
	<?php
	return;
endif;
?>

<ul id="advanced-ad-type">
	<?php foreach ( $types as $ad_type ) : ?>
	<li class="advanced-ads-type-list-<?php echo esc_attr( $ad_type->get_id() ); ?>">
		<input
			type="radio"
			name="advanced_ad[type]"
			id="advanced-ad-type-<?php echo esc_attr( $ad_type->get_id() ); ?>"
			value="<?php echo esc_attr( $ad_type->get_id() ); ?>"
			<?php checked( $ad->get_type(), $ad_type->get_id() ); ?>
			<?php disabled( $ad_type->is_premium() ); ?>
		/>
		<label for="advanced-ad-type-<?php echo esc_attr( $ad_type->get_id() ); ?>"><?php echo esc_html( $ad_type->get_title() ); ?></label>
		<?php if ( ! empty( $ad_type->get_description() ) ) : ?>
		<span class="advads-help">
			<span class="advads-tooltip"><?php echo esc_html( $ad_type->get_description() ); ?></span>
		</span>
		<?php endif; ?>
		<?php
		if ( $ad_type->get_upgrade_url() ) {
			echo ' ';
			Upgrades::upgrade_link(
				__( 'Manual', 'advanced-ads' ),
				$ad_type->get_upgrade_url(),
				'upgrade-ad-type-' . $ad_type->get_id()
			);
		}
		?>
	</li>
	<?php endforeach; ?>
</ul>

<?php
$_notice     = 'nl_adsense';
$box_classes = 'mt-1 advads-notice-adsense' . ( 'adsense' !== $ad->get_type() ? ' !hidden' : '' );

if ( Advanced_Ads_Admin_Notices::get_instance()->can_display( $_notice ) && Conditional::user_can_subscribe( 'nl_first_steps' ) ) {
	$text = sprintf(
		/* translators: %s: number of add-ons. */
		__( 'Subscribe to our free email course for Google AdSense, receive our newsletter for periodic tutorials, and get %s for Advanced Ads.', 'advanced-ads' ),
		'<strong>' . __( '2 free add-ons', 'advanced-ads' ) . '</strong>'
	);
	include ADVADS_ABSPATH . '/admin/views/notices/inline.php';
}
