<?php
/**
 * Header tab menu on admin pages
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 *
 * @var array  $tabs   Array of screen tabs.
 * @var string $active Active tab id.
 */

?>
<div class="advads-header-tabs">
	<?php foreach ( $tabs as $tab_id => $tab_data ) : ?>
	<a href="<?php echo esc_url( add_query_arg( 'sub_page', $tab_id ) ); ?>"<?php echo $active === $tab_id ? 'class="is-active"' : ''; ?>>
		<?php echo esc_html( $tab_data['label'] ); ?>
	</a>
	<?php endforeach; ?>
</div>
