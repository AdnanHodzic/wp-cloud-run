<?php
/**
 * Add quick/bulk edit fields on the ad overview page
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   2.0
 */

namespace AdvancedAds\Admin;

use DateTime;
use Exception;
use Advanced_Ads_Privacy;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Framework\Utilities\Params;

/**
 * WP integration
 */
class Quick_Bulk_Edit {
	/**
	 * Hooks into WordPress
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'quick_edit_custom_box', [ $this, 'add_quick_edit_fields' ], 10, 2 );
		add_action( 'bulk_edit_custom_box', [ $this, 'add_bulk_edit_fields' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'save_post', [ $this, 'save_quick_edits' ], 100 );
		add_action( 'save_post', [ $this, 'save_bulk_edit' ], 100 );
	}

	/**
	 * Save changes made during bulk edit
	 *
	 * @return void
	 */
	public function save_bulk_edit() {
		// Not bulk edit, not ads or not enough permissions.
		if (
			! wp_verify_nonce( sanitize_key( Params::get( '_wpnonce', '', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ), 'bulk-posts' )
			|| Constants::POST_TYPE_AD !== sanitize_key( Params::get( 'post_type' ) )
			|| ! current_user_can( 'advanced_ads_edit_ads' )
		) {
			return;
		}

		$changes = [ 'on', 'off' ];

		$debug_mode     = Params::get( 'debug_mode' );
		$set_expiry     = Params::get( 'expiry_date' );
		$ad_label       = Params::get( 'ad_label', false );
		$ignore_privacy = Params::get( 'ignore_privacy' );

		$has_change = in_array( $debug_mode, $changes, true ) || in_array( $set_expiry, $changes, true ) || in_array( $ignore_privacy, $changes, true ) || false !== $ad_label;

		/**
		 * Allow add-ons to confirm early abort if no change has been made and avoid iterating through an ad stack.
		 *
		 * @param bool $has_change whether some ads have been changed.
		 */
		$has_change = apply_filters( 'advanced-ads-bulk-edit-has-change', $has_change );

		// No changes, bail out.
		if ( ! $has_change ) {
			return;
		}

		$expiry_date = 'on' === $set_expiry ?
			$this->get_expiry_timestamp( 'get' ) : 0;

		$ads = array_map(
			function ( $ad ) {
				return wp_advads_get_ad( absint( $ad ) );
			},
			wp_unslash( Params::get( 'post', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) )
		);

		foreach ( $ads as $ad ) {
			if ( in_array( $debug_mode, $changes, true ) ) {
				$ad->set_debugmode( 'on' === $debug_mode );
			}

			if ( in_array( $set_expiry, $changes, true ) ) {
				$ad->set_prop( 'expiry_date', $expiry_date );
			}

			if ( false !== $ad_label ) {
				$ad->set_prop( 'ad_label', esc_html( trim( $ad_label ) ) );
			}

			if ( 'on' === $ignore_privacy ) {
				$ad->set_prop( 'privacy', [ 'ignore-consent' => 'on' ] );
			} elseif ( 'off' === $ignore_privacy ) {
				$ad->unset_prop( 'privacy' );
			}

			/**
			 * Allow add-on to bulk save ads.
			 *
			 * @param Ad $ad current ad being saved.
			 */
			$ad = apply_filters( 'advanced-ads-bulk-edit-save', $ad );

			$ad->save();
		}
	}

	/**
	 * Save ad edited with quick edit
	 *
	 * @param int $id the ad being saved.
	 *
	 * @return void
	 */
	public function save_quick_edits( $id ) {
		// Not inline edit, or no permission.
		if (
			! wp_verify_nonce( sanitize_key( Params::post( '_inline_edit' ) ), 'inlineeditnonce' ) ||
			! current_user_can( 'advanced_ads_edit_ads' )
		) {
			return;
		}

		$ad = wp_advads_get_ad( $id );

		// Not an ad.
		if ( ! $ad ) {
			return;
		}

		// Render columns properly.
		( new Ad_List_Table() )->hooks();

		$ad->set_prop( 'debugmode', Params::post( 'debugmode', false, FILTER_VALIDATE_BOOLEAN ) );
		$ad->set_prop(
			'expiry_date',
			Params::post( 'enable_expiry' ) ? $this->get_expiry_timestamp() : 0
		);

		if ( isset( Advanced_Ads_Privacy::get_instance()->options()['enabled'] ) ) {
			if ( Params::post( 'ignore_privacy' ) ) {
				$ad->set_prop( 'privacy', [ 'ignore-consent' => 'on' ] );
			} else {
				$ad->unset_prop( 'privacy' );
			}
		}

		$ad_label = Params::post( 'ad_label', false );
		if ( false !== $ad_label ) {
			$ad->set_prop( 'ad_label', esc_html( trim( $ad_label ) ) );
		}

		/**
		 * Allow add-ons to edit and ad before it is saved.
		 *
		 * @param Ad $ad the ad being saved.
		 */
		$ad = apply_filters( 'advanced-ads-quick-edit-save', $ad );

		$ad->save();
	}

	/**
	 * Get UNIx timestamp from the date time inputs values
	 *
	 * @param string $method method used for the form - `post` og `get`.
	 *
	 * @return int
	 */
	private function get_expiry_timestamp( $method = 'post' ) {
		$day     = absint( 'get' === $method ? Params::get( 'day' ) : Params::post( 'day' ) );
		$month   = absint( 'get' === $method ? Params::get( 'month' ) : Params::post( 'month' ) );
		$year    = 'get' === $method ? Params::get( 'year', 0, FILTER_VALIDATE_INT ) : Params::post( 'year', 0, FILTER_VALIDATE_INT );
		$hours   = absint( 'get' === $method ? Params::get( 'hour' ) : Params::post( 'hour' ) );
		$minutes = absint( 'get' === $method ? Params::get( 'minute' ) : Params::post( 'minute' ) );

		try {
			$date = new DateTime( 'now', wp_timezone() );
			$date->setDate( $year, $month, $day );
			$date->setTime( $hours, $minutes );

			return (int) $date->format( 'U' );
		} catch ( Exception $e ) {
			return 0;
		}
	}

	/**
	 * Enqueue scripts and print inline JS variable.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( 'edit-advanced_ads' !== $screen->id ) {
			return;
		}

		wp_advads()->registry->enqueue_script( 'screen-ads-listing' );
	}

	/**
	 * Add the bulk edit inputs
	 *
	 * @param string $column_name the current column.
	 * @param string $post_type   the current post type.
	 *
	 * @return void
	 */
	public function add_bulk_edit_fields( $column_name, $post_type ) {
		if ( Constants::POST_TYPE_AD !== $post_type || 'ad_type' !== $column_name ) {
			return;
		}

		$privacy_options = \Advanced_Ads_Privacy::get_instance()->options();
		include plugin_dir_path( ADVADS_FILE ) . 'views/admin/bulk-edit.php';

		/**
		 * Allow add-ons to add more fields.
		 */
		do_action( 'advanced-ads-bulk-edit-fields' );
	}

	/**
	 * Add the quick edit inputs
	 *
	 * @param string $column_name the current column.
	 * @param string $post_type   the current post type.
	 *
	 * @return void
	 */
	public function add_quick_edit_fields( $column_name, $post_type ) {
		if ( Constants::POST_TYPE_AD !== $post_type || 'ad_date' !== $column_name ) {
			return;
		}

		$privacy_options = \Advanced_Ads_Privacy::get_instance()->options();
		include plugin_dir_path( ADVADS_FILE ) . 'views/admin/quick-edit.php';

		/**
		 * Allow add-ons to add more fields.
		 */
		do_action( 'advanced-ads-quick-edit-fields' );
	}

	/**
	 * Print date and time inputs for the ad expiry
	 *
	 * @param int    $timestamp default expiry date.
	 * @param string $prefix    prefix for input names.
	 * @param bool   $seconds   whether to add seconds input.
	 *
	 * @return void
	 */
	public static function print_date_time_inputs( $timestamp = 0, $prefix = '', $seconds = false ) {
		try {
			$initial_date = (bool) $timestamp ? new \DateTimeImmutable( "@$timestamp", wp_timezone() ) : current_datetime();
		} catch ( Exception $e ) {
			$initial_date = current_datetime();
		}

		$current_year = (int) ( current_datetime()->format( 'Y' ) );

		global $wp_locale;
		?>
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Month', 'advanced-ads' ); ?></span>
			<select name="<?php echo esc_attr( $prefix ); ?>month">
				<?php for ( $mo = 1; $mo < 13; $mo++ ) : ?>
					<?php $month = zeroise( $mo, 2 ); ?>
					<option value="<?php echo esc_attr( $month ); ?>" <?php selected( $month, $initial_date->format( 'm' ) ); ?>>
						<?php echo esc_html( $month . '-' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $mo, 2 ) ) ); ?>
					</option>
				<?php endfor; ?>
			</select>
		</label>
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Day', 'advanced-ads' ); ?></span>
			<input type="number" name="<?php echo esc_attr( $prefix ); ?>day" min="1" max="31" value="<?php echo esc_attr( $initial_date->format( 'd' ) ); ?>"/>
		</label>,
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Year', 'advanced-ads' ); ?></span>
			<select name="<?php echo esc_attr( $prefix ); ?>year">
				<?php for ( $y = $current_year; $y < $current_year + 11; $y++ ) : ?>
					<option value="<?php echo esc_attr( $y ); ?>" <?php selected( $y, (int) $initial_date->format( 'Y' ) ); ?>><?php echo esc_html( $y ); ?></option>
				<?php endfor; ?>
			</select>
		</label>
		@
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Hour', 'advanced-ads' ); ?></span>
			<input type="number" name="<?php echo esc_attr( $prefix ); ?>hour" min="0" max="23" value="<?php echo esc_attr( $initial_date->format( 'h' ) ); ?>"/>
		</label>:
		<label>
			<span class="screen-reader-text"><?php esc_html_e( 'Minute', 'advanced-ads' ); ?></span>
			<input type="number" name="<?php echo esc_attr( $prefix ); ?>minute" min="0" max="59" value="<?php echo esc_attr( $initial_date->format( 'i' ) ); ?>"/>
		</label>
		<?php if ( $seconds ) : ?>
			:
			<label>
				<span class="screen-reader-text"><?php esc_html_e( 'Second', 'advanced-ads' ); ?></span>
				<input type="number" name="<?php echo esc_attr( $prefix ); ?>second" min="0" max="59" value="<?php echo esc_attr( $initial_date->format( 's' ) ); ?>"/>
			</label>
		<?php endif; ?>
		<?php $timezone = wp_timezone_string(); ?>
		<span><?php echo esc_html( strlen( $timezone ) !== strlen( str_replace( [ '+', '-' ], '', $timezone ) ) ? "UTC$timezone" : $timezone ); ?></span>
		<?php
	}
}
