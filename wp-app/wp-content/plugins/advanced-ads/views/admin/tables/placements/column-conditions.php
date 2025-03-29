<?php
/**
 * Render output of the placement conditions.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var Placement $placement Placement instance.
 */

$display_conditions = Advanced_Ads_Display_Conditions::get_instance();
$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance();
?>
<?php if ( $placement->get_display_conditions() ) : ?>
	<h4><?php echo esc_html__( 'Display Conditions', 'advanced-ads' ); ?></h4>
	<ul>
		<?php foreach ( $placement->get_display_conditions() as $condition ) : ?>
			<?php if ( array_key_exists( $condition['type'], (array) $display_conditions->conditions ) ) : ?>
				<li>
					<?php echo esc_html( $display_conditions->conditions[ $condition['type'] ]['label'] ); ?>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php if ( $placement->get_visitor_conditions() ) : ?>
	<h4><?php echo esc_html__( 'Visitor Conditions', 'advanced-ads' ); ?></h4>
	<ul>
		<?php foreach ( $placement->get_visitor_conditions() as $condition ) : ?>
			<?php if ( array_key_exists( $condition['type'], $visitor_conditions->conditions ) ) : ?>
				<li>
					<?php echo esc_html( $visitor_conditions->conditions[ $condition['type'] ]['label'] ); ?>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<a href="#modal-placement-edit-<?php echo esc_attr( $placement->get_id() ); ?>" data-placement="<?php echo esc_attr( $placement->get_id() ); ?>" class="advads-mobile-hidden"><?php esc_html_e( 'edit conditions', 'advanced-ads' ); ?></a>
