<?php
/**
 * Render schedule options in the publish meta box on the ad edit screen
 *
 * @var int $curr_month current month index;
 */
?><div id="advanced-ads-expiry-date" class="misc-pub-section curtime misc-pub-curtime">
	<label onclick="advads_toggle_box('#advanced-ads-expiry-date-enable', '#advanced-ads-expiry-date .inner')">
		<input type="checkbox" id="advanced-ads-expiry-date-enable" name="advanced_ad[expiry_date][enabled]" value="1" <?php checked( $enabled, 1 ); ?>/><?php esc_html_e( 'Set expiry date', 'advanced-ads' ); ?>
	</label>
	<br/>
	<div class="inner"<?php echo ( ! $enabled ) ? ' style="display:none;"' : ''; ?>>
		<?php
		$month_field = '<label><span class="screen-reader-text">' . __( 'Month', 'advanced-ads' ) . '</span><select class="advads-mm" name="advanced_ad[expiry_date][month]"' . ">\n";
		for ( $i = 1; $i < 13; $i = ++$i ) {
			$month_num    = zeroise( $i, 2 );
			$month_field .= "\t\t\t" . '<option value="' . $month_num . '" ' . selected( $curr_month, $month_num, false ) . '>';
			$month_field .= sprintf(
				// translators: %1$s is the month number, %2$s is the month shortname.
				_x( '%1$s-%2$s', '1: month number (01, 02, etc.), 2: month abbreviation', 'advanced-ads' ),
				$month_num,
				$wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) )
			) . "</option>\n";
		}
			$month_field .= '</select></label>';

			$day_field    = '<label><span class="screen-reader-text">' . __( 'Day', 'advanced-ads' ) . '</span><input type="text" class="advads-jj" name="advanced_ad[expiry_date][day]" value="' . $curr_day . '" size="2" maxlength="2" autocomplete="off" /></label>';
			$year_field   = '<label><span class="screen-reader-text">' . __( 'Year', 'advanced-ads' ) . '</span><input type="text" class="advads-aa" name="advanced_ad[expiry_date][year]" value="' . $curr_year . '" size="4" maxlength="4" autocomplete="off" /></label>';
			$hour_field   = '<label><span class="screen-reader-text">' . __( 'Hour', 'advanced-ads' ) . '</span><input type="text" class="advads-hh" name="advanced_ad[expiry_date][hour]" value="' . $curr_hour . '" size="2" maxlength="2" autocomplete="off" /></label>';
			$minute_field = '<label><span class="screen-reader-text">' . __( 'Minute', 'advanced-ads' ) . '</span><input type="text" class="advads-mn" name="advanced_ad[expiry_date][minute]" value="' . $curr_minute . '" size="2" maxlength="2" autocomplete="off" /></label>';

		?>
		<fieldset class="advads-timestamp">
				<?php
				printf(
				// translators: %1$s month, %2$s day, %3$s year, %4$s hour, %5$s minute.
					_x( '%1$s %2$s, %3$s @ %4$s %5$s', 'order of expiry date fields 1: month, 2: day, 3: year, 4: hour, 5: minute', 'advanced-ads' ),
					$month_field,
					$day_field,
					$year_field,
					$hour_field,
					$minute_field
				);
				?>
		</fieldset>
		(<?php echo esc_html( Advanced_Ads_Utils::get_timezone_name() ); ?>)
	</div>
</div>
