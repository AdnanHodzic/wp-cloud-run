<?php
/**
 * Render item option for placements.
 *
 * @var array  $items               Array of available items.
 * @var string $slug                slug of the current placement.
 * @var array  $placement           information of the current placement.
 * @var string $placement_item_type type of the item currently selected for the placement
 * @var int    $placement_item_id   ID of the item currently selected for the placement
 * @package Advanced_Ads_Admin
 */

?>
	<select id="advads-placements-item-<?php echo esc_attr( $slug ); ?>" name="advads[placements][<?php echo esc_attr( $slug ); ?>][item]">
		<option value=""><?php esc_html_e( '--not selected--', 'advanced-ads' ); ?></option>

		<?php foreach ( $items as $item_group ) : ?>
			<optgroup label="<?php echo esc_attr( $item_group['label'] ); ?>">
				<?php foreach ( $item_group['items'] as $item_id => $item ) : ?>
					<option value="<?php echo esc_attr( $item_id ); ?>" <?php selected( $item['selected'] ); ?> <?php disabled( $item['disabled'] ); ?>>
						<?php echo esc_html( $item['name'] ); ?>
					</option>
				<?php endforeach; ?>
			</optgroup>
		<?php endforeach; ?>
	</select>

<?php
// link to item.
if ( $placement_item_type ) :
	$link_to_item = false;
	switch ( $placement_item_type ) :
		case 'ad':
			/**
			 * Deliver the translated version of an ad if set up with WPML.
			 *
			 * @source https://wpml.org/wpml-hook/wpml_object_id/
			 */
			$placement_item_id = apply_filters( 'wpml_object_id', $placement_item_id, 'advanced_ads' );
			$link_to_item      = get_edit_post_link( $placement_item_id );
			break;
		case 'group':
			$link_to_item = admin_url( 'admin.php?page=advanced-ads-groups#modal-group-edit-' . $placement_item_id );
			break;
	endswitch;
	if ( $link_to_item ) {
		?>
		<a href="<?php echo esc_url( $link_to_item ); ?>"><span class="dashicons dashicons-external"></span></span></a>
		<?php
	} elseif ( 'ad' === $placement_item_type && defined( 'ICL_LANGUAGE_NAME' ) ) {
		// translation missing notice.
		?>
		<p>
		<?php
		printf(
				// translators: %s is the name of a language in English.
			esc_html__( 'The ad is not translated into %s', 'advanced-ads' ),
			esc_html( ICL_LANGUAGE_NAME )
		);
		?>
		</p>
		<?php
	}
endif;
// show a button when no ads exist, yet.
if ( empty( $items ) ) :
	?>
	<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=advanced_ads' ) ); ?>"><?php esc_html_e( 'Create your first ad', 'advanced-ads' ); ?></a>
	<?php
endif;
