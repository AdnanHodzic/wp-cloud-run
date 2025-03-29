<?php
/**
 * Google Sheet.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use WP_Error;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Interfaces\Importer as Interface_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * Google Sheet.
 */
class Google_Sheet extends Importer implements Interface_Importer {

	/**
	 * Ads from sheets
	 *
	 * @var array
	 */
	private $sheet_ads = null;

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'google_sheet';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'PubGuru Importer', 'advanced-ads' );
	}

	/**
	 * Get a description of the importer.
	 *
	 * @return string The description of the importer.
	 */
	public function get_description(): string {
		return __( 'For MonetizeMore clients using PubGuru, you will be able to upload all of your new ads from your Google sheet. Please make sure that you support rep has confirmed that you are ready to do so. Below you will a “rollback changes” option, in case of any error. As a warning, these ad placements will over take your current ad setup.', 'advanced-ads' );
	}

	/**
	 * Get the icon to this importer.
	 *
	 * @return string The icon for the importer.
	 */
	public function get_icon(): string {
		return '<span class="dashicons dashicons-media-spreadsheet"></span>';
	}

	/**
	 * Detect the importer in database.
	 *
	 * @return bool True if detected; otherwise, false.
	 */
	public function detect(): bool {
		return false;
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( null === $this->sheet_ads ) :
			?>
		<input type="url" name="sheet_url" id="google-sheet-url" class="w-full" required value="" />
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<td></td>
						<td>Ad Unit</td>
						<td>Device</td>
						<td>Display Condition</td>
						<td>Page Type</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $this->sheet_ads as $index => $ad ) : ?>
					<tr>
						<td>
							<input type="checkbox" name="google_ads[<?php echo $index; ?>][enabled]" value="1"<?php checked( true, $ad['is_active'] ); ?>>
							<input type="hidden" name="google_ads[<?php echo $index; ?>][ad_unit]" value="<?php echo $ad['ad_unit']; ?>">
							<input type="hidden" name="google_ads[<?php echo $index; ?>][placement]" value="<?php echo $ad['placement']; ?>">
							<input type="hidden" name="google_ads[<?php echo $index; ?>][placement_conditions]" value="<?php echo $ad['placement_conditions']; ?>">
							<input type="hidden" name="google_ads[<?php echo $index; ?>][in_content_position]" value="<?php echo $ad['in_content_position']; ?>">
							<input type="hidden" name="google_ads[<?php echo $index; ?>][in_content_element]" value="<?php echo $ad['in_content_element']; ?>">
							<input type="hidden" name="google_ads[<?php echo $index; ?>][in_content_count]" value="<?php echo $ad['in_content_count']; ?>">
							<input type="hidden" name="google_ads[<?php echo $index; ?>][in_content_repeat]" value="<?php echo $ad['in_content_repeat']; ?>">
						</td>
						<td><?php echo $ad['ad_unit']; ?></td>
						<td>
							<select name="google_ads[<?php echo $index; ?>][device]">
							<option value="all"<?php selected( $ad['device'], 'all' ); ?>><?php esc_html_e( 'All', 'advanced-ads' ); ?></option>
								<option value="desktop"<?php selected( $ad['device'], 'desktop' ); ?>><?php esc_html_e( 'Desktop', 'advanced-ads' ); ?></option>
								<option value="mobile"<?php selected( $ad['device'], 'mobile' ); ?>><?php esc_html_e( 'Mobile', 'advanced-ads' ); ?></option>
							</select>
						</td>
						<td><?php echo $ad['placement']; ?></td>
						<td><?php echo $ad['placement_conditions']; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php
		endif;
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Import data.
	 *
	 * @return WP_Error|string
	 */
	public function import() {
		// Final import create ads.
		$google_ads = Params::post( 'google_ads', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( $google_ads ) {
			return $this->create_ads( $google_ads );
		}

		// Parse ads from google sheet using URL.
		$sheet_url = Params::post( 'sheet_url' );
		if ( empty( $sheet_url ) ) {
			return new WP_Error( 'not_sheet_url_found', __( 'No google sheet url found.', 'advanced-ads' ) );
		}

		$sheet_id  = $this->get_sheet_id( $sheet_url );
		$sheet_ads = $this->get_ads( $sheet_id, 'AdvAdsMap' );

		if ( is_wp_error( $sheet_ads ) ) {
			return $sheet_ads;
		}

		if ( empty( $sheet_ads ) ) {
			return new WP_Error( 'not_ads_found', __( 'No ads found in google sheet.', 'advanced-ads' ) );
		}

		$this->sheet_ads = $sheet_ads;
	}

	/**
	 * Parse sheet id from url
	 *
	 * @param string $url Sheet url.
	 *
	 * @return string
	 */
	private function get_sheet_id( $url ): string {
		$url = str_replace( 'https://docs.google.com/spreadsheets/d/', '', $url );
		$url = explode( '/', $url );

		return current( $url );
	}

	/**
	 * Retrieves ads from a Google Sheet.
	 *
	 * @param string $sheet_id   The ID of the Google Sheet.
	 * @param string $sheet_name The name of the sheet within the Google Sheet.
	 *
	 * @return array|WP_Error An array of ads retrieved from the Google Sheet or error if any
	 */
	private function get_ads( $sheet_id, $sheet_name ) {
		$ads           = [];
		$sheet_api_url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $sheet_id . '/values/' . $sheet_name . '?alt=json&key=AIzaSyBky3Y-0NwlCBSHFYquhN15Y-5QovYXSdM';

		$response = wp_remote_get( $sheet_api_url );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$error = json_decode( wp_remote_retrieve_body( $response ), true );
			return new WP_Error( $error['error']['status'], $error['error']['message'] );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( is_array( $body ) ) {
			$rows = $body['values'];
			array_shift( $rows );

			foreach ( $rows as $row ) {
				if ( ! empty( $row[0] ) && ! empty( $row[1] ) ) {
					$ad = [
						'ad_unit'              => $row[0],
						'device'               => strtolower( $row[1] ),
						'placement'            => $row[2],
						'placement_conditions' => $row[3],
						'is_active'            => 'FALSE' === $row[4] ? false : true,
						'in_content_position'  => $row[5],
						'in_content_element'   => $row[6],
						'in_content_count'     => $row[7],
						'in_content_repeat'    => 'FALSE' === $row[8] ? false : true,
					];

					$ads[] = $ad;
				}
			}
		}

		return $ads;
	}

	/**
	 * Get ads from sheet by device
	 *
	 * @param array $ads Ads selected by user.
	 *
	 * @return string
	 */
	private function create_ads( $ads ): string {
		$count       = 0;
		$history_key = $this->get_id() . '_' . wp_rand() . '_' . count( $ads );

		foreach ( $ads as $data ) {

			if ( ! isset( $data['enabled'] ) ) {
				continue;
			}

			$ad = wp_advads_create_new_ad( 'plain' );
			$ad->set_title( '[Migrated from Googlesheet] Ad # ' . $data['ad_unit'] );
			$ad->set_content( sprintf( '<pubguru data-pg-ad="%s"></pubguru>', $data['ad_unit'] ) );

			if ( 'all' !== $data['device'] ) {
				$ad->set_visitor_conditions(
					[
						[
							'type'  => 'mobile',
							'value' => [ $data['device'] ],
						],
					]
				);
			}

			$ad_id = $ad->save();

			if ( $ad_id > 0 ) {
				++$count;

				$placement = wp_advads_create_new_placement( $this->map_placement_type( $data['placement'] ) );
				$placement->set_title( '[Migrated from Googlesheet] Placement # ' . $ad_id );
				$placement->set_item( 'ad_' . $ad_id );
				$display_conditions = $this->parse_display_conditions( $data['placement_conditions'] );
				if ( ! empty( $display_conditions ) ) {
					$placement->set_display_conditions( [ $display_conditions ] );
				}

				if ( $placement->is_type( 'post_content' ) ) {
					$placement->set_prop( 'position', $data['in_content_position'] );
					$placement->set_prop( 'index', $data['in_content_count'] );
					$placement->set_prop( 'tag', $this->map_position_tag( $data['in_content_element'] ) );
					$placement->set_prop( 'repeat', boolval( $data['in_content_repeat'] ) );
				}

				$placement->save();

				$this->add_session_key( $ad, $placement, $history_key );
			}
		}

		wp_advads()->importers->add_session_history( $this->get_id(), $history_key, $count );

		/* translators: 1: counts 2: Importer title */
		return sprintf( __( '%1$d ads migrated from %2$s', 'advanced-ads' ), $count, $this->get_title() );
	}

	/**
	 * Maps the placement type to a corresponding value.
	 *
	 * This function takes a placement type as input and returns the corresponding value based on a predefined mapping.
	 *
	 * @param string $type The placement type to be mapped.
	 *
	 * @return string The mapped placement type value.
	 */
	private function map_placement_type( $type ): string {
		$type = strtolower( str_replace( ' ', '_', $type ) );
		$hash = [
			'after_content'  => 'post_bottom',
			'before_content' => 'post_top',
			'in_content'     => 'post_content',
			'sidebar'        => 'sidebar_widget',
		];

		return $hash[ $type ] ?? $type;
	}

	/**
	 * Maps the position tag to its corresponding value.
	 *
	 * @param string $tag The position tag to be mapped.
	 *
	 * @return string The mapped position tag value.
	 */
	private function map_position_tag( $tag ): string {
		$hash = [
			'paragraph (<p>)'                 => 'p',
			'paragraph without image (<p>)'   => 'pwithoutimg',
			'headline 2 (<h2>)'               => 'h2',
			'headline 3 (<h3>)'               => 'h3',
			'headline 4 (<h4>)'               => 'h4',
			'any headline (<h2>, <h3>, <h4>)' => 'headlines',
			'image (<img>)'                   => 'img',
			'table (<table>)'                 => 'table',
			'list item (<li>)'                => 'li',
			'quote (<blockquote>)'            => 'blockquote',
			'iframe (<iframe>)'               => 'iframe',
			'container (<div>)'               => 'div',
		];
		$tag  = strtolower( $tag );

		return $hash[ $tag ] ?? 'anyelement';
	}

	/**
	 * Parse display conditions
	 *
	 * @param string $term Dictionary term.
	 *
	 * @return array|null
	 */
	private function parse_display_conditions( $term ) {
		$term = str_replace( ' ', '_', strtolower( $term ) );
		if ( 'all' === $term ) {
			return null;
		}

		if ( 'homepage' === $term ) {
			return [
				'type'  => 'general',
				'value' => [ 'is_front_page' ],
			];
		}

		if ( 'post_pages' === $term ) {
			return [
				'type'  => 'general',
				'value' => [ 'is_singular' ],
			];
		}

		if ( 'category_pages' === $term ) {
			return [
				'type'  => 'general',
				'value' => [ 'is_archive' ],
			];
		}
	}
}
