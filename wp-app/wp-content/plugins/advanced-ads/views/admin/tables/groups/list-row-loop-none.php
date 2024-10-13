<?php
/**
 * Ads loop in a group.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var Advanced_Ads_Group $group  Group object.
 */

esc_html_e( 'No ads assigned', 'advanced-ads' );
?>
<br/>
<?php if ( ! empty( $this->all_ads ) ) : ?>
	<a href="#modal-group-edit-<?php echo esc_attr( $group->id ); ?>">+ <?php esc_html_e( 'Add some', 'advanced-ads' ); ?></a>
<?php else : ?>
	<a class="button create-first-ad" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=advanced_ads' ) ); ?>"><?php esc_html_e( 'Create your first ad', 'advanced-ads' ); ?></a>
	<?php
endif;
