<?php
/**
 * Group Filter
 *
 * @package AdvancedAds
 *
 * @var Groups_List_Table $this
 */

use AdvancedAds\Constants;
use AdvancedAds\Admin\Groups_List_Table;
use AdvancedAds\Framework\Utilities\Params;

$group_type = Params::request( 'group_type' );

$group_types = wp_advads_get_group_types();
usort(
	$group_types,
	function ( $a, $b ) {
		return strcasecmp( $a->get_title(), $b->get_title() );
	}
);
?>

<form id="advads-group-filter-form" method="get">
	<div id="filters">
		<input type="hidden" name="page" value="<?php echo esc_attr( Params::get( 'page' ) ); ?>">

		<select id="advads-group-filter-type" name="group_type">
			<option value="">- <?php esc_html_e( 'all group types', 'advanced-ads' ); ?> -</option>
			<?php foreach ( $group_types as $gtype ) : ?>
			<option <?php selected( $group_type, $gtype->get_id() ); ?> value="<?php echo esc_attr( $gtype->get_id() ); ?>"><?php echo esc_html( $gtype->get_title() ); ?></option>
			<?php endforeach; ?>
		</select>

		<input type="submit" class="button" value="Filter">
	</div>

	<div class="search-form">
		<?php
		$group_taxonomy = get_taxonomy( Constants::TAXONOMY_GROUP );
		$this->search_box( $group_taxonomy->labels->search_items, 'tag' );
		?>
	</div>
</form>
