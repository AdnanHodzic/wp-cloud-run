<input id="advanced-ads-content-injection-priority" type="number" value="<?php echo esc_attr( $priority ); ?>" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[content-injection-priority]" size="3"/>
<p class="description">
<?php
if ( $priority < 11 ) :
	?>
	<span class="advads-notice-inline advads-error"><?php esc_html_e( 'Please check your post content. A priority of 10 and below might cause issues (wpautop function might run twice).', 'advanced-ads' ); ?></span><br />
	<?php
	endif;
	esc_html_e( 'Play with this value in order to change the priority of the injected ads compared to other auto injected elements in the post content.', 'advanced-ads' );
?>
	</p>
