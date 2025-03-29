<?php
/**
 * Ui Toolkit page.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 */

?>
<div class="wrap advads-wrap">
	<?php
	$this->get_header(
		[ 'title' => __( 'Ui Toolkit', 'advanced-ads' ) ]
	);

	$this->get_tabs_menu();
	$this->get_tab_content();
	?>
</div>
