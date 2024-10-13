<?php
/**
 * Render form to create new placements.
 *
 * @var array $placement_types types of placements.
 */
?>
<form method="POST" class="advads-placements-new-form advads-form" id="advads-placements-new-form">
	<h3>1. <?php esc_html_e( 'Choose a placement type', 'advanced-ads' ); ?></h3>
	<p class="description">
		<?php
		printf(
			wp_kses(
			// translators: %s is a URL.
				__( 'Placement types define where the ad is going to be displayed. Learn more about the different types from the <a href="%s">manual</a>', 'advanced-ads' ),
				[
					'a' => [
						'href' => [],
					],
				]
			),
			'https://wpadvancedads.com/manual/placements/#utm_source=advanced-ads&utm_medium=link&utm_campaign=placements'
		);
		?>
	</p>
	<?php require_once 'placement-types.php'; ?>
	<?php

	// show Pro placements if Pro is not activated.
	if ( ! defined( 'AAP_VERSION' ) ) :
		include ADVADS_ABSPATH . 'admin/views/upgrades/pro-placements.php';
	else :
		?>
		<div class="clear"></div>
		<?php
	endif;
	?>
	<p class="advads-notice-inline advads-error advads-form-type-error"><?php esc_html_e( 'Please select a type.', 'advanced-ads' ); ?></p>
	<br/>
	<h3>2. <?php esc_html_e( 'Choose a Name', 'advanced-ads' ); ?></h3>
	<p>
		<input name="advads[placement][name]" class="advads-form-name" type="text" value="" placeholder="<?php esc_html_e( 'Placement Name', 'advanced-ads' ); ?>" />
		<span class="advads-help">
			<span class="advads-tooltip">
				<?php esc_html_e( 'The name of the placement is only visible to you. Tip: choose a descriptive one, e.g. Below Post Headline.', 'advanced-ads' ); ?>
			</span>
		</span>
	</p>
	<p class="advads-notice-inline advads-error advads-form-name-error"><?php esc_html_e( 'Please enter a name.', 'advanced-ads' ); ?></p>
	<h3>
		<label for="advads-placement-item">3. <?php esc_html_e( 'Choose the Ad or Group', 'advanced-ads' ); ?></label>
	</h3>
	<p>
		<select name="advads[placement][item]" id="advads-placement-item" disabled>
			<option value=""><?php esc_html_e( '--not selected--', 'advanced-ads' ); ?></option>
		</select>
	</p>
	<?php wp_nonce_field( 'advads-placement', 'advads_placement', true ); ?>
</form>

<script type="text/html" id="tmpl-advads-placement-ad-select">
	<select name="advads[placement][item]" id="advads-placement-item">
		<option value=""><?php esc_html_e( '--not selected--', 'advanced-ads' ); ?></option>
		<# for ( group of data.items ) { #>
		<optgroup label="{{ group.label }}">
			<# for ( item_id in group.items ) { #>
			<option value="{{ item_id }}">
				{{ group.items[item_id].name }}
			</option>
			<# } #>
		</optgroup>
		<# } #>
	</select>
</script>
