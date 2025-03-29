<?php
/**
 * Render the group details column content in the group table.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 *
 * @var Group $group Group instance.
 */

?>
<ul>
	<li>
		<?php
		/*
			* translators: %s is the ID of an ad group
			*/
		printf( esc_attr__( 'ID: %s', 'advanced-ads' ), absint( $group->get_id() ) );
		?>
	</li>
	<li>
		<strong>
		<?php
		/* translators: %s is the name of a group type */
		printf( esc_html__( 'Type: %s', 'advanced-ads' ), esc_html( $group->get_type_object()->get_title() ) );
		?>
		</strong>
	</li>
</ul>
