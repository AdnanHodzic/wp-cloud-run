<label><input id="advanced-ads-disable-ads-all" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[disabled-ads][all]"
																						   <?php
																							checked( $disable_all, 1 );
																							?>
	><?php esc_html_e( 'Disable all ads in frontend', 'advanced-ads' ); ?></label>
<span class="advads-help"><span class="advads-tooltip"><?php esc_html_e( 'Use this option to disable all ads in the frontend, but still be able to use the plugin.', 'advanced-ads' ); ?></span></span>
<br/>
<label><input id="advanced-ads-disable-ads-404" type="checkbox" value="1" name="<?php	echo esc_attr( ADVADS_SLUG ); ?>[disabled-ads][404]"
	<?php
	checked( $disable_404, 1 );
	?>
	><?php esc_html_e( 'Disable ads on 404 error pages', 'advanced-ads' ); ?></label>

<br/><label><input id="advanced-ads-disable-ads-archives" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[disabled-ads][archives]"
																									 <?php
																										checked( $disable_archives, 1 );
																										?>
	><?php esc_html_e( 'Disable ads on non-singular pages', 'advanced-ads' ); ?></label>
	<span class="advads-help"><span class="advads-tooltip"><?php esc_html_e( 'e.g. archive pages like categories, tags, authors, front page (if a list)', 'advanced-ads' ); ?></span></span>
<br/>
<label><input id="advanced-ads-disable-ads-secondary" type="checkbox" value="1" name="<?php	echo esc_attr( ADVADS_SLUG ); ?>[disabled-ads][secondary]"
	<?php
	checked( $disable_secondary, 1 );
	?>
	><?php esc_html_e( 'Disable ads on secondary queries', 'advanced-ads' ); ?></label>
	<span class="advads-help"><span class="advads-tooltip"><?php esc_html_e( 'Secondary queries are custom queries of posts outside the main query of a page. Try this option if you see ads injected on places where they shouldn’t appear.', 'advanced-ads' ); ?></span></span>
<br/>
<label><input id="advanced-ads-disable-ads-feed" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[disabled-ads][feed]"
	<?php
	checked( $disable_feed, 1 );
	?>
	><?php esc_html_e( 'Disable ads in RSS Feed', 'advanced-ads' ); ?></label>

<br/><label><input id="advanced-ads-disable-ads-rest-api" type="checkbox" value="1" name="<?php echo esc_attr( ADVADS_SLUG ); ?>[disabled-ads][rest-api]"
	<?php
	checked( $disable_rest_api, 1 );
	?>
	><?php esc_html_e( 'Disable ads in REST API', 'advanced-ads' ); ?></label>
