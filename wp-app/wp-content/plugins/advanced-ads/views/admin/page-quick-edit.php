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
		<?php if ( defined( 'AAP_VERSION' ) ) : ?>
			<div class="inline-edit-group wp-clearfix">
				<label class="alignleft">
					<input type="checkbox" name="advads-disable-the-content" value="1"/>
					<span class="checkbox-title"><?php esc_html_e( 'Disable automatic ad injection into the content', 'advanced-ads' ); ?></span>
				</label>
			</div>
		<?php endif; ?>
	</div>
</fieldset>
