<?php
/**
 * Disable post types upgrade notice.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

use AdvancedAds\Admin\Upgrades;

foreach ( $post_types as $_type_id => $_type ) :
	$_label = sprintf( '%s (%s)', $_type->label, $_type_id );
	if ( $type_label_counts[ $_type->label ] < 2 ) {
		$_label = $_type->label;
	}
	?>
	<label style="margin-right: 1em;"><input type="checkbox" disabled="disabled"><?php echo esc_html( $_label ); ?></label>
	<?php endforeach; ?>
<p>
	<?php esc_html_e( 'The free version provides the post type display condition on the ad edit page.', 'advanced-ads' ); ?>
</p>
<p>
	<?php Upgrades::pro_feature_link( 'upgrade-pro-disable-post-type' ); ?>
</p>
