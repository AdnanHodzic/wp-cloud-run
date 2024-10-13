<fieldset>
	<input type="checkbox" <?php checked( $enabled, true ); ?> value="1"
		   name="<?php echo esc_attr( ADVADS_SLUG ); ?>[custom-label][enabled]"/>
	<input id="advads-custom-label" type="text" value="<?php echo esc_html( $label ); ?>"
		   name="<?php echo esc_attr( ADVADS_SLUG ); ?>[custom-label][text]"/>
</fieldset>
<p class="description"><?php esc_html_e( 'Displayed above ads.', 'advanced-ads' ); ?>&nbsp;
	<a target="_blank" href="https://wpadvancedads.com/manual/advertisement-label/?utm_source=advanced-ads&utm_medium=link&utm_campaign=settings-advertisement-label" class="advads-manual-link">
										<?php
										esc_html_e( 'Manual', 'advanced-ads' );
										?>
	</a>
</p>
