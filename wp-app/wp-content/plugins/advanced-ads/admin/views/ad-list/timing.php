<?php // phpcs:ignoreFile
/**
 * Render content of the Ad Schedule column in the ad overview list
 *
 * @var string $html_classes additonal values for class attribute.
 * @var string $post_future timestamp of the schedule date.
 * @var string $expiry_date_format date format.
 * @var string $expiry expiry date.
 * @var string $content_after HTML to load after the schedule content.
 */
?>
<fieldset class="inline-edit-col-left">
	<div class="inline-edit-col <?php echo esc_attr( $html_classes ); ?>">
		<?php
		if ( $post_future ) :
			?>
			<p>
			<?php
			printf(
				// translators: %s is a date.
				esc_html__( 'starts %s', 'advanced-ads' ),
				esc_html(
					get_date_from_gmt(
						// phpcs:ignore
						date( 'Y-m-d H:i:s', $post_future ),
						esc_html( $expiry_date_format )
					)
				)
			);
			?>
				</p>
		<?php endif; ?>
		<?php if ( $expiry ) : ?>
			<?php
			$tz_option   = get_option( 'timezone_string' );
			$expiry_date = date_create( '@' . $expiry, new DateTimeZone( 'UTC' ) );

			if ( $tz_option ) {

				$expiry_date->setTimezone( Advanced_Ads_Utils::get_wp_timezone() );
				$expiry_date_string = $expiry_date->format( $expiry_date_format );

			} else {

				$tz_name            = Advanced_Ads_Utils::get_timezone_name();
				$tz_offset          = substr( $tz_name, 3 );
				$off_time           = date_create( $expiry_date->format( 'Y-m-d\TH:i:s' ) . $tz_offset );
				$offset_in_sec      = date_offset_get( $off_time );
				$expiry_date        = date_create( '@' . ( $expiry + $offset_in_sec ) );
				$expiry_date_string = date_i18n( $expiry_date_format, absint( $expiry_date->format( 'U' ) ) );

			}
			?>
			<?php if ( $expiry > time() ) : ?>
				<p>
				<?php
				printf(
					// translators: %s is a date.
					esc_html__( 'expires %s', 'advanced-ads' ),
					esc_html( $expiry_date_string )
				);
				?>
					</p>
			<?php else : ?>
				<p>
				<?php
				printf(
					wp_kses(
						// translators: %s is a time and date string.
						__( '<strong>expired</strong> %s', 'advanced-ads' ),
						[
							'strong' => [],
						]
					),
					esc_html( $expiry_date_string )
				);
				?>
					</p>
			<?php endif; ?>
		<?php endif; ?>
		<?php
		// phpcs:ignore
		echo $content_after; ?>
	</div>
</fieldset>
