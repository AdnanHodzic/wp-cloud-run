<?php

/**
 * A couple of checks to see if there is any critical issue
 * listed on support and settings page
 */

$messages = [];

if ( Advanced_Ads_Ad_Health_Notices::has_visible_problems() ) {
	$messages[] = sprintf(
		// translators: %1$s is a starting link tag, %2$s is closing the link tag.
		esc_attr__( 'Advanced Ads detected potential problems with your ad setup. %1$sShow me these errors%2$s', 'advanced-ads' ),
		'<a href="' . admin_url( 'admin.php?page=advanced-ads' ) . '">',
		'</a>'
	);
}

$messages = apply_filters( 'advanced-ads-support-messages', $messages );

if ( count( $messages ) ) :
	?><div class="message error">
	<?php
	foreach ( $messages as $_message ) :
		?>
	<p>
		<?php
		// phpcs:ignore
		echo $_message;
		?>
		</p>
		<?php
endforeach;
	?>
	</div>
	<?php
	endif;
