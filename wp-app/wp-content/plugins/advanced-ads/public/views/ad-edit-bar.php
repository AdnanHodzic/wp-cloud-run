<?php
/**
 * Edit bar for ads.
 *
 * @package AdvanceAds
 *
 * @var Ad $this Ad instance.
 */

?>
<div class="advads-edit-bar advads-edit-appear">
<a href="<?php echo esc_url( $this->get_edit_link() ); ?>" class="advads-edit-button" title="<?php echo esc_attr( wp_strip_all_tags( $this->get_title() ) ); ?>" rel="nofollow"><span class="dashicons dashicons-edit"></span></a>
</div>
