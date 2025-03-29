<?php
/**
 * Groups page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var WP_List_Table|false $wp_list_table The groups list table
 */

use AdvancedAds\Modal;
use AdvancedAds\Entities;
use AdvancedAds\Utilities\Conditional;

?>
<span class="wp-header-end"></span>
<div class="wrap">
	<?php
	ob_start();
	if ( empty( $wp_list_table->items ) ) :
		?>
		<p>
			<?php
			echo esc_html( Entities::get_group_description() );
			?>
			<a href="https://wpadvancedads.com/manual/ad-groups/?utm_source=advanced-ads&utm_medium=link&utm_campaign=groups" target="_blank" class="advads-manual-link"><?php esc_html_e( 'Manual', 'advanced-ads' ); ?></a>
		</p>
		<?php
	endif;

	require ADVADS_ABSPATH . 'views/admin/screens/group-form.php';

	Modal::create(
		[
			'modal_slug'       => 'group-new',
			'modal_content'    => ob_get_clean(),
			'modal_title'      => __( 'New Ad Group', 'advanced-ads' ),
			'close_validation' => 'advads_validate_new_form',
		]
	);
	?>
	<div id="ajax-response"></div>

	<div id="advads-group-filter">
		<?php $wp_list_table->render_filters(); ?>
	</div>

	<div id="advads-ad-group-list">
		<?php $wp_list_table->display(); ?>
	</div>
</div>
<?php
// no groups and no filters then open the new group modal.
if ( empty( $wp_list_table->items ) && ! Conditional::has_filter_or_search() ) :
	?>
	<script>
		window.location.hash = '#modal-group-new';
	</script>
	<?php
endif;
