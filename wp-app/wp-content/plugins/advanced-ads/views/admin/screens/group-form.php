<?php
/**
 * Render form to create a new ad group.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

$group_types = wp_advads()->group_manager->get_types();

?>
<form method="post" class="advads-group-new-form advads-form" id="advads-group-new-form">
	<h3>1. <?php esc_html_e( 'Choose the type', 'advanced-ads' ); ?></h3>
	<div class="advads-form-types advads-buttonset">
		<?php
		foreach ( $group_types as $group_type ) :
			if ( $group_type->is_premium() ) {
				continue;
			}
			?>
			<div class="advads-form-type">
				<label for="advads-form-type-<?php echo esc_attr( $group_type->get_id() ); ?>">
					<img src="<?php echo esc_attr( $group_type->get_image() ); ?>" alt="<?php echo esc_attr( $group_type->get_title() ); ?>"/>
				</label>
				<input type="radio" id="advads-form-type-<?php echo esc_attr( $group_type->get_id() ); ?>" name="advads-group-type" value="<?php echo esc_attr( $group_type->get_id() ); ?>"/>
				<div class="advads-form-description">
					<h4><?php echo esc_html( $group_type->get_title() ); ?></h4>
					<?php echo esc_html( $group_type->get_description() ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( wp_advads()->group_manager->has_premium() ) : ?>
		<div class="advads-form-types">
			<?php
			foreach ( $group_types as $group_type ) :
				if ( ! $group_type->is_premium() ) {
					continue;
				}
				?>
				<div class="advads-form-type">
					<label class="advads-button advads-pro-link">
						<span class="advads-button-text">
							<img src="<?php echo esc_attr( $group_type->get_image() ); ?>" alt="<?php echo esc_attr( $group_type->get_title() ); ?>"/>
						</span>
					</label>
					<p class="advads-form-description">
						<strong><?php echo esc_html( $group_type->get_title() ); ?></strong>
					</p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="clear"></div>
		<h4>
			<?php Advanced_Ads_Admin_Upgrades::upgrade_link( __( 'Get all group types with All Access', 'advanced-ads' ), 'https://wpadvancedads.com/add-ons/all-access/', 'upgrades-pro-groups' ); ?>
		</h4>
	<?php endif; ?>
	<div class="clear"></div>
	<p class="advads-notice-inline advads-error advads-form-type-error"><?php esc_html_e( 'Please select a type.', 'advanced-ads' ); ?></p>
	<br/>
	<h3>2. <?php esc_html_e( 'Choose a name', 'advanced-ads' ); ?></h3>
	<input type="text" name="advads-group-name" class="advads-form-name" placeholder="<?php esc_attr_e( 'Group title', 'advanced-ads' ); ?>"/>
	<p class="advads-notice-inline advads-error advads-form-name-error"><?php esc_html_e( 'Please enter a name.', 'advanced-ads' ); ?></p>
	<?php wp_nonce_field( 'add-advads-groups', 'advads-group-add-nonce' ); ?>
</form>
