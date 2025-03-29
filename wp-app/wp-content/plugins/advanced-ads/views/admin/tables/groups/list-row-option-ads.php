<?php
/**
 * Render a list of ads included in an ad group
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var array $ad_form_rows  HTML to render ad form.
 * @var array $this->all_ads Array with ads that can be chosen from for the group.
 * @var Group $group         Group instance.
 */

$max_weight = $group->get_max_weight();
$sorted_ads = $group->get_sorted_ads();
?>
<table class="advads-group-ads">
	<?php if ( $this->all_ads ) : ?>
	<thead>
		<tr>
			<th class="group-sort group-ad" data-sortby="ad"><?php esc_html_e( 'Ad', 'advanced-ads' ); ?></th>
			<th class="group-sort group-status" data-sortby="status"><?php esc_html_e( 'Status', 'advanced-ads' ); ?></th>
			<th colspan="2" class="group-sort group-weight" data-sortby="weight"><?php esc_html_e( 'Weight', 'advanced-ads' ); ?></th>
		</tr>
	</thead>
	<?php endif; ?>

	<tbody>
		<?php
		foreach ( $sorted_ads as $ad_id => $ad ) :
			$ad_object = wp_advads_get_ad( $ad_id );
			$ad_url    = add_query_arg(
				[
					'post'   => $ad_id,
					'action' => 'edit',
				],
				admin_url( 'post.php' )
			);
			/* translators: %s is the title for ad. */
			$link_title = sprintf( esc_html__( 'Opens ad %s in a new tab', 'advanced-ads' ), $ad['title'] );
			?>
		<tr data-ad-id="<?php echo esc_attr( $ad_id ); ?>" data-group-id="<?php echo esc_attr( $group->get_id() ); ?>">
			<td>
				<a target="_blank" href="<?php echo esc_url( $ad_url ); ?>" title="<?php echo esc_attr( $link_title ); ?>">
					<?php echo esc_html( $ad['title'] ); ?>
				</a>
			</td>
			<td>
				<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is already escaped.
					echo $ad_object->get_ad_schedule_html();
				?>
			</td>
			<td>
				<select name="advads-groups[<?php echo esc_attr( $group->get_id() ); ?>][ads][<?php echo esc_attr( $ad_id ); ?>]">
				<?php for ( $i = 0; $i <= $max_weight; $i++ ) : ?>
					<option<?php selected( $ad['weight'], $i ); ?>><?php echo $i; // phpcs:ignore ?></option>
				<?php endfor; ?>

				</select>
			</td>
			<td>
				<button type="button" class="advads-remove-ad-from-group button">x</button>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ( $this->all_ads ) : ?>
	<fieldset class="advads-group-add-ad">
		<legend><?php esc_html_e( 'New Ad', 'advanced-ads' ); ?></legend>
		<select class="advads-group-add-ad-list-ads">
			<?php
			foreach ( $this->all_ads as $ad_id => $ad_title ) {
				$ad_status = wp_advads_get_ad( $ad_id )->get_ad_schedule_details();
				printf(
					'<option value="advads-groups[%1$d][ads][%2$d]" data-status="%3$s" data-status-string="%4$s">%5$s</option>',
					absint( $group->get_id() ),
					absint( $ad_id ),
					esc_html( $ad_status['status_type'] ?? '' ),
					esc_html( $ad_status['status_strings'][0] ?? '' ),
					esc_html( $ad_title )
				);
			}
			?>
		</select>

		<select class="advads-group-add-ad-list-weights">
		<?php for ( $i = 0; $i <= $max_weight; $i++ ) : ?>
			<option<?php selected( 10, $i ); ?>><?php echo absint( $i ); ?></option>
		<?php endfor; ?>
		</select>
		<button type="button" class="button"><?php esc_html_e( 'add', 'advanced-ads' ); ?></button>
	</fieldset>
<?php else : ?>
	<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=advanced_ads' ) ); ?>"><?php esc_html_e( 'Create your first ad', 'advanced-ads' ); ?></a>
	<?php
endif;
