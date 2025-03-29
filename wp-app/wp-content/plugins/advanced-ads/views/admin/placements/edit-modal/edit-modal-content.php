<?php
/**
 * Render the edit modal for the placement.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 *
 * @var Placement $placement Placement instance.
 *
 * @var int       $user_id
 * @var int       $author_id
 * @var string    $options   Marked-up options.
 */

use AdvancedAds\Abstracts\Placement;

?>

<form name="post" method="post" id="<?php echo esc_attr( $this->get_form_id() ); ?>">
	<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'advads-update-placement' ) ); ?>"/>
	<input type="hidden" name="post_ID" value="<?php echo absint( $placement->get_id() ); ?>">

	<?php

	$slug = $placement->get_slug();

	/**
	 * Hook before placement options
	 *
	 * @param string    $slug      the placement slug.
	 * @param Placement $placement the placement.
	 */
	do_action( 'advanced-ads-placement-options-before', $slug, $placement );

	$this->render_settings();

	/**
	 * Hook after placement options
	 *
	 * @param string    $slug      the placement slug.
	 * @param Placement $placement the placement.
	 */
	do_action( 'advanced-ads-placement-options-after', $slug, $placement );
	?>
</form>
