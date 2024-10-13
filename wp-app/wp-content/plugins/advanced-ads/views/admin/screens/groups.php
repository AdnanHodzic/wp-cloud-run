<?php
/**
 * Groups page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var WP_List_Table|false $wp_list_table The groups list table
 * @var WP_Taxonomy         $taxonomy      Ad group taxonomy
 * @var bool                $is_search     True if a group is searched.
 */

use AdvancedAds\Entities;
use AdvancedAds\Framework\Utilities\Params;

?>
<div class="wrap nosubsub">
	<h2 style="display: none;"><!-- There needs to be an empty H2 headline at the top of the page so that WordPress can properly position admin notifications --></h2>
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
	$modal_slug = 'group-new';
	Advanced_Ads_Modal::create(
		[
			'modal_slug'       => $modal_slug,
			'modal_content'    => ob_get_clean(),
			'modal_title'      => __( 'New Ad Group', 'advanced-ads' ),
			'close_action'     => __( 'Save New Group', 'advanced-ads' ),
			'close_form'       => 'advads-group-new-form',
			'close_validation' => 'advads_validate_new_form',
		]
	);
	?>
	<div id="ajax-response"></div>

	<div id="col-container">
		<div class="col-wrap">
			<div class="tablenav top <?php echo $is_search ? '' : 'hidden advads-toggle-with-filters-button'; ?>" style="padding-bottom: 20px;">
				<?php
				if ( $is_search ) {
					printf(
						/* translators: %s search query */
						'<span class="subtitle" style="float:left;">' . esc_html__( 'Search results for: %s', 'advanced-ads' ) . '</span>',
						'<strong>' . esc_html( wp_unslash( Params::request( 's' ) ) ) . '</strong>'
					);
				}
				?>
				<form class="search-form" action="" method="get">
					<input type="hidden" name="page" value="advanced-ads-groups"/>
					<?php
					$wp_list_table->search_box( $taxonomy->labels->search_items, 'tag' );
					?>
				</form>
			</div>
			<div id="advads-ad-group-list">
				<form action="" method="post" id="advads-form-groups" class="advads-form-groups">
					<?php wp_nonce_field( 'update-advads-groups', 'advads-group-update-nonce' ); ?>
					<?php $wp_list_table->display(); ?>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
// trigger the group form when no groups exist and we are not currently searching.
if ( empty( $wp_list_table->items ) && ! $is_search ) :
	?>
	<script>
		window.location.hash = '#modal-<?php echo esc_html( $modal_slug ); ?>';
	</script>
	<?php
endif;
