<?php
/**
 * Page/Post quick edit fields
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 *
 * @var string $post_type current post type.
 */

use AdvancedAds\Admin\Upgrades;

?>
<fieldset class="inline-edit-col-right" disabled>
	<div class="inline-edit-col">
		<div class="inline-edit-group wp-clearfix">
			<label class="alignleft">
				<input type="checkbox" name="advads-disable-ads" value="1"/>
				<span class="checkbox-title">
					<?php echo 'page' === $post_type ? esc_html__( 'Disable ads on this page', 'advanced-ads' ) : esc_html__( 'Disable ads on this post', 'advanced-ads' ); ?>
				</span>
			</label>
		</div>
			<div class="inline-edit-group wp-clearfix">
				<?php $pro_installed = defined( 'AAP_VERSION' ); ?>
				<label class="alignleft">
					<input type="checkbox" name="<?php echo esc_attr( $pro_installed ? 'advads-disable-the-content' : '' ); ?>" value="1" <?php echo $pro_installed ? '' : 'disabled'; ?>>
					<span class="checkbox-title"><?php esc_html_e( 'Disable automatic ad injection into the content', 'advanced-ads' ); ?></span>
					<?php if ( ! $pro_installed ) : ?>
						( <?php Upgrades::upgrade_link( '', 'https://wpadvancedads.com/advanced-ads-pro/', 'upgrade-pro-disable-post-quick-edit' ); ?>)
					<?php endif; ?>
				</label>
			</div>
	</div>
</fieldset>
