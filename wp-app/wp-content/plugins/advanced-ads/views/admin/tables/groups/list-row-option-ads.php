<?php
/**
 * Render a list of ads included in an ad group
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var array              $ad_form_rows  HTML to render ad form.
 * @var array              $this->all_ads Array with ads that can be chosen from for the group.
 * @var Advanced_Ads_Group $group         Group instance.
 * @var int                $max_weight    Max weight allowed.
 */

?>
<table class="advads-group-ads">
	<?php if ( $this->all_ads ) : ?>
	<thead>
		<tr>
			<th><?php esc_html_e( 'Ad', 'advanced-ads' ); ?></th>
			<th><?php esc_html_e( 'Status', 'advanced-ads' ); ?></th>
			<th colspan="2"><?php esc_html_e( 'Weight', 'advanced-ads' ); ?></th>
		</tr>
	</thead>
	<?php endif; ?>

	<tbody>
		<?php
		if ( ! empty( $ad_form_rows ) ) {
			foreach ( $ad_form_rows as $row ) {
				echo $row; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		?>
	</tbody>
</table>

<?php if ( $this->all_ads ) : ?>
	<fieldset class="advads-group-add-ad">
		<legend><?php esc_html_e( 'New Ad', 'advanced-ads' ); ?></legend>
		<select class="advads-group-add-ad-list-ads">
			<?php
			foreach ( $this->all_ads as $_ad_id => $_ad_title ) {
				printf(
					'<option value="advads-groups[%1$d][ads][%2$d]">%3$s</option>',
					absint( $group->id ),
					absint( $_ad_id ),
					esc_html( $_ad_title )
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
