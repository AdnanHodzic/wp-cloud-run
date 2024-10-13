<div id="advads-first-ad-links">
	<button type="button" id="advads-first-ad-video-link" class="button-primary">
		<span class="dashicons dashicons-format-video"></span>
		&nbsp;<?php esc_attr_e( 'Watch the “First Ad” Tutorial (Video)', 'advanced-ads' ); ?>
	</button>
</div>
<script>
	( function ( $ ) {
		var $videoLink = $( '#advads-first-ad-video-link' );
		var wpLang        = '<?php echo esc_html( get_locale() ); ?>';
		var buttonClicked = false;
		$videoLink.click( function () {
			if ( ! buttonClicked ) {
				if ( wpLang === 'de_DE' ) {
					$( '<br class="clear"/><br/><iframe width="420" height="315" src="https://www.youtube-nocookie.com/embed/Gd09nf1dNwY?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>' ).appendTo( '#advads-first-ad-links' );
				} else {
					$( '<br class="clear"/><br/><iframe width="420" height="315" src="https://www.youtube-nocookie.com/embed/nfybYz8ayXQ?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>' ).appendTo( '#advads-first-ad-links' );
				}
				buttonClicked = ! buttonClicked;
			}
		} )
				  .children( '.dashicons' ).css( 'line-height', $videoLink.css( 'line-height' ) );
	} )( jQuery );
</script>

