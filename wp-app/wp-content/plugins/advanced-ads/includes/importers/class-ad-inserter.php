<?php // phpcs:ignoreFile
/**
 * Ad Inserter.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Interfaces\Importer as Interface_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * Ad Inserter.
 */
class Ad_Inserter extends Importer implements Interface_Importer {

	/**
	 * Hold AI options
	 *
	 * @var array
	 */
	private $ai_options = null;

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'ad_inserter';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'Ad Inserter', 'advanced-ads' );
	}

	/**
	 * Get a description of the importer.
	 *
	 * @return string The description of the importer.
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Get the icon to this importer.
	 *
	 * @return string The icon for the importer.
	 */
	public function get_icon(): string {
		return '<span class="dashicons dashicons-insert"></span>';
	}

	/**
	 * Detect the importer in database.
	 *
	 * @return bool True if detected; otherwise, false.
	 */
	public function detect(): bool {
		$options = $this->get_ai_options();

		return ! empty( $options );
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void {
		$blocks = $this->get_ai_options();
		unset( $blocks['global'], $blocks['extract'] );
		?>
		<p class="text-base m-0">
			<?php
			/* translators: %d: number of ad blocks */
			printf( __( 'We found Ad Inserter configuration with <strong>%d ad blocks</strong>.', 'advanced-ads' ), count( $blocks ) ); // phpcs:ignore
			?>
		</p>
		<p><label><input type="checkbox" name="ai_import[blocks]" checked="checked" /> <?php esc_html_e( 'Import Ad Blocks', 'advanced-ads' ); ?></label></p>
		<p><label><input type="checkbox" name="ai_import[options]" checked="checked" /> <?php esc_html_e( 'Import Settings', 'advanced-ads' ); ?></label></p>
		<?php
	}

	/**
	 * Import data.
	 *
	 * @return WP_Error|string
	 */
	public function import() {
		$what        = Params::post( 'ai_import', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$history_key = $this->generate_history_key();

		$count = 0;
		if ( isset( $what['blocks'] ) ) {
			$count = $this->import_ads( $history_key );
		}

		if ( isset( $what['options'] ) ) {
			$this->import_options( $history_key );
		}

		wp_advads()->importers->add_session_history( $this->get_id(), $history_key, $count );

		/* translators: 1: counts 2: Importer title */
		return sprintf( esc_html__( '%1$d ads migrated from %2$s', 'advanced-ads' ), $count, $this->get_title() );
	}

	/**
	 * Return Ad Inserter block and option array
	 *
	 * @return array|bool
	 */
	private function get_ai_options() {
		if ( null !== $this->ai_options ) {
			return $this->ai_options;
		}

		$this->ai_options = get_option( 'ad_inserter' );
		if ( false === $this->ai_options ) {
			return $this->ai_options;
		}

		if ( is_string( $this->ai_options ) && substr( $this->ai_options, 0, 4 ) === ':AI:' ) {
			$this->ai_options = unserialize( base64_decode( substr( $this->ai_options, 4 ), true ) ); // phpcs:ignore
			$this->ai_options = array_filter( $this->ai_options );
		}

		return $this->ai_options;
	}

	/**
	 * Import ads
	 *
	 * @param string $history_key Session key for rollback.
	 *
	 * @return int
	 */
	private function import_ads( $history_key ): int {
		$count  = 0;
		$blocks = $this->get_ai_options();
		unset( $blocks['global'], $blocks['extract'] );

		foreach ( $blocks as $index => $block ) {
			$ad = wp_advads_create_new_ad( 'plain' );
			$ad->set_title( '[Migrated from Ad Inserter] Ad # ' . $index );
			$ad->set_content( $block['code'] );

			if ( isset( $block['block_width'] ) ) {
				$ad->set_width( absint( $block['block_width'] ) );
			}

			if ( isset( $block['block_height'] ) ) {
				$ad->set_height( absint( $block['block_height'] ) );
			}

			$visitor_conditions = $this->parse_visitor_conditions_ad( $block );
			if ( ! empty( $visitor_conditions ) ) {
				$ad->set_visitor_conditions( $visitor_conditions );
			}
			$ad_id = $ad->save();

			if ( $ad_id > 0 ) {
				++$count;

				$placement = wp_advads_create_new_placement( 'post_content' );
				$placement->set_title( '[Migrated from Ad Inserter] Placement # ' . $index );
				$placement->set_item( 'ad_' . $ad_id );
				$placement->set_prop( 'position', 'after' );
				$placement->set_prop( 'index', 3 );
				$placement->set_prop( 'tag', 'p' );
				$display_conditions = $this->parse_display_conditions_placements( $block );
				if ( ! empty( $display_conditions ) ) {
					$placement->set_display_conditions( $display_conditions );
				}

				$placement->save();

				$this->add_session_key( $ad, $placement, $history_key );
			}
		}

		return $count;
	}

	/**
	 * Parse visitor conditions for ad.
	 *
	 * @param array $block Block array.
	 *
	 * @return array
	 */
	private function parse_visitor_conditions_ad( $block ) {
		$devices    = [];
		$conditions = [];

		if ( isset( $block['detect_viewport_1'] ) ) {
			$devices[] = 'desktop';
		}

		if ( isset( $block['detect_viewport_2'] ) ) {
			$devices[] = 'tablet';
		}

		if ( isset( $block['detect_viewport_3'] ) ) {
			$devices[] = 'mobile';
		}

		if ( ! empty( $devices ) ) {
			$conditions[] = [
				'type'  => 'mobile',
				'value' => $devices,
			];
		}

		return $conditions;
	}

	/**
	 * Parse display conditions for placement
	 *
	 * @param array $block Block array.
	 *
	 * @return array|null
	 */
	private function parse_display_conditions_placements( $block ) {
		$conditions = [];

		if ( isset( $block['display_on_homepage'] ) ) {
			$conditions[] = [
				'type'  => 'general',
				'value' => [ 'is_front_page' ],
			];
		}

		return $conditions;
	}

	/**
	 * Import options
	 *
	 * @return void
	 */
	private function import_options(): void {}

	/**
	 * Imports all Ad Inserter ads.
	 *
	 * This method is responsible for importing all Ad Inserter ads.
	 * It is part of the Ad Inserter importer class.
	 *
	 * @return void
	 */
	public function adsforwp_import_all_ad_inserter_ads() {
		global $block_object, $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$wpdb->query( 'START TRANSACTION' );
		$result  = [];
		$user_id = get_current_user_id();

		if ( is_array( $block_object ) ) {
			$i = 0;
			foreach ( $block_object as $object ) {

				$wp_options = $object->wp_options;

				$ad_code = $wp_options['code'];

				if ( '' !== $ad_code ) {

					$ads_post = [
						'post_author' => $user_id,
						'post_title'  => 'Custom Ad '.$i.' (Migrated from Ad Inserter)',
						'post_status' => 'publish',
						'post_name'   => 'Custom Ad '.$i.' (Migrated from Ad Inserter)',
						'post_type'   => 'adsforwp',
					];

					$post_id = wp_insert_post( $ads_post );

					$wheretodisplay = '';
					$ad_align       = '';
					$pragraph_no    = '';
					$adposition     = '';

					if ( $wp_options['display_type'] == 3 || $wp_options['display_type'] == 1) {
						$wheretodisplay = 'before_the_content';
					}elseif ( $wp_options['display_type'] == 4 || $wp_options['display_type'] == 2) {
						$wheretodisplay = 'after_the_content';
					}elseif ( $wp_options['display_type'] == 5) {
						$wheretodisplay = 'between_the_content';
						$pragraph_no    = $wp_options['paragraph_number'];
						$adposition     = 'number_of_paragraph';
					}

					if ( 1 == $wp_options['alignment_type'] ) {
						$ad_align = 'left';
					}elseif ( 2 == $wp_options['alignment_type'] ) {
						$ad_align = 'right';
					}elseif ( 3 == $wp_options['alignment_type'] ) {
						$ad_align = 'center';
					}

					$data_group_array['group-0'] = array(
												'data_array' => array(
													array(
														'key_1' => 'show_globally',
														'key_2' => 'equal',
														'key_3' => 'post',
													)
													)
												);

					$adforwp_meta_key = array(
						'select_adtype'     => 'custom',
						'custom_code'       => $ad_code,
						'adposition'        => $adposition,
						'paragraph_number'  => $pragraph_no,
						'adsforwp_ad_align' => $ad_align,
						'imported_from'     => 'ad_inserter',
						'wheretodisplay'    => $wheretodisplay,
						'data_group_array'  => $data_group_array
						);

					foreach ($adforwp_meta_key as $key => $val) {

						$result[] =  update_post_meta($post_id, $key, $val);

					}
				}

			$i++;
			}
		}

		if (is_wp_error($result) ) {
			echo $result->get_error_message();
			$wpdb->query('ROLLBACK');
		} else {
			$wpdb->query('COMMIT');
			return $result;
		}
	}
}
