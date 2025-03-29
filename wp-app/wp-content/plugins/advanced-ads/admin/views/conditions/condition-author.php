<?php
/**
 * Template for the Author display condition
 *
 * @package AdvancedAds
 *
 * @var string $name        Form name attribute.
 * @var int    $max_authors Number of maximum author entries to show.
 */

?>
<div class="advads-conditions-single advads-buttonset">
	<?php
	if ( count( $authors ) >= $max_authors ) :
		// show active authors.
		?>
			<div class="advads-conditions-authors-buttons dynamic-search">
				<?php
				foreach ( $authors as $_author ) :
					// don’t use strict comparision because $values contains strings.
					if ( in_array( $_author->ID, $values ) ) : // phpcs:ignore
						$author_name = $_author->display_name;
						$field_id    = 'advads-conditions-' . absint( $_author->ID ) . $rand;
						?>
						<label class="button advads-button advads-ui-state-active">
							<span class="advads-button-text">
								<?php echo esc_attr( $author_name ); ?>
								<input type="hidden"
								name="<?php echo esc_attr( $name ); ?>[value][]"
								value="<?php echo absint( $_author->ID ); ?>">
							</span>
						</label>
						<?php
					endif;
				endforeach;
				?>
			</div>
			<span class="advads-conditions-authors-show-search button" title="<?php echo esc_html_x( 'add more authors', 'display the authors search field on ad edit page', 'advanced-ads' ); ?>">
			+
			</span>
			<br/>
			<input type="text" class="advads-conditions-authors-search"
			data-input-name="<?php echo esc_attr( $name ); ?>[value][]"
			placeholder="<?php esc_html_e( 'author name or id', 'advanced-ads' ); ?>"/>
		<?php
	else :
		$max_counter = $max_authors;
		foreach ( $authors as $_author ) {
			if ( $max_counter <= 0 ) {
				return false;
			}
			--$max_counter;
			// don’t use strict comparision because $values contains strings.
			if ( in_array( $_author->ID, $values ) ) { // phpcs:ignore
				$_val = 1;
			} else {
				$_val = 0;
			}
			$author_name = $_author->display_name;
			$field_id    = 'advads-conditions-' . absint( $_author->ID ) . $rand;
			?>
			<label class="button advads-button"
				for="<?php echo esc_attr( $field_id ); ?>">
				<?php echo esc_attr( $author_name ); ?>
			</label><input type="checkbox"
						id="<?php echo esc_attr( $field_id ); ?>"
						name="<?php echo esc_attr( $name ); ?>[value][]" <?php checked( $_val, 1 ); ?>
						value="<?php echo absint( $_author->ID ); ?>">
			<?php
		}
		include ADVADS_ABSPATH . 'admin/views/conditions/not-selected.php';
	endif;
	?>
</div>
