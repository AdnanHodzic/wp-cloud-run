<?php
/**
 * Templace for the Author display condition
 *
 * @package Advanced_Ads_Admin
 * @var string $name form name attribute.
 * @var int $max_authors number of maximum author entries to show.
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
						if ( in_array( $_author->ID, $values ) ) :
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
				<span class="advads-conditions-authors-show-search button" title="<?php _ex( 'add more authors', 'display the authors search field on ad edit page', 'advanced-ads' ); ?>">
				+
				</span>
				<br/>
				<input type="text" class="advads-conditions-authors-search"
				data-input-name="<?php echo esc_attr( $name ); ?>[value][]"
				placeholder="<?php _e( 'author name or id', 'advanced-ads' ); ?>"/>
			<?php
		else :
			$max_counter = $max_authors;
			foreach ( $authors as $_author ) {
				if ( $max_counter <= 0 ) {
					return false;
				}
				--$max_counter;
				// don’t use strict comparision because $values contains strings.
				if ( in_array( $_author->ID, $values ) ) {
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
<?php
/*
if ( count( $authors ) >= $max_authors ) :
	?>
	<p class="advads-notice-inline advads-error">
		<?php
		printf(
			wp_kses(
			// translators: %1$d is the number of elements in the list and %2$s a URL.
				__( 'Only %1$d elements are displayed above. Use the <code>advanced-ads-admin-max-terms</code> filter to change this limit according to <a href="%2$s" target="_blank">this page</a>.', 'advanced-ads' ),
				[
					'code' => [],
					'a'    => [
						'href'   => [],
						'target' => [],
					],
				]
			),
			absint( $max_authors ),
			'https://wpadvancedads.com/codex/filter-hooks/?utm_source=advanced-ads&utm_medium=link&utm_campaign=author-term-limit'
		);
		?>
	</p>
<?php
endif;
*/
