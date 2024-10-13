<input id="advanced-ads-front-prefix" type="text" value="<?php echo esc_attr( $prefix ); ?>" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[front-prefix]" />
<?php // deprecated. ?>
<input type="hidden" value="<?php echo esc_attr( $old_prefix ); ?>" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[id-prefix]" />
<p class="description"><?php esc_html_e( 'Prefix of class and id attributes for elements created in the frontend.', 'advanced-ads' ); ?></p>
