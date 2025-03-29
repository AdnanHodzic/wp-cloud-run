<?php
/**
 * WP Quads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Importers;

use AdvancedAds\Options;
use AdvancedAds\Constants;
use AdvancedAds\Abstracts\Ad;
use AdvancedAds\Abstracts\Group;
use AdvancedAds\Abstracts\Placement;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Interfaces\Importer as Interface_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * WP Quads.
 */
class WP_Quads extends Importer implements Interface_Importer {
	/**
	 * Holds the options.
	 *
	 * @var null
	 */
	private $options = null;

	/**
	 * Session key for rollback
	 *
	 * @var string
	 */
	private $history_key = '';

	/**
	 * Array of ad mappings for creating groups.
	 *
	 * @var array
	 */
	private $ad_mapping = [];

	/**
	 * Get the unique identifier (ID) of the importer.
	 *
	 * @return string The unique ID of the importer.
	 */
	public function get_id(): string {
		return 'wp_quads';
	}

	/**
	 * Get the title or name of the importer.
	 *
	 * @return string The title of the importer.
	 */
	public function get_title(): string {
		return __( 'WP Quads', 'advanced-ads' );
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
		return false;
		return ! empty( $this->get_plugin_options() );
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render_form(): void {
		?>
		<p class="text-base m-0">
			<?php
			printf(
				/* translators: Number of ads */
				__( 'We found <strong>%d ads</strong>.', 'advanced-ads' ), // phpcs:ignore
				count( (array) $this->get_plugin_options()['ads'] )
			);
			?>
		</p>
		<p>
			<label>
				<input type="checkbox" name="quads_import[ads]" checked="checked" />
				<?php esc_html_e( 'Import Ads', 'advanced-ads' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input type="checkbox" name="quads_import[settings]" checked="checked" />
				<?php esc_html_e( 'Import Settings', 'advanced-ads' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Import data.
	 *
	 * @return WP_Error|string
	 */
	public function import() {
		$what_to_import = Params::post( 'quads_import', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		// Early bail!!
		if ( empty( $what_to_import ) ) {
			return __( 'Nothing imported.', 'advanced-ads' );
		}

		$this->history_key = $this->generate_history_key();
		$count             = 0;
		$messages          = [];

		if ( isset( $what_to_import['ads'] ) ) {
			$count = $this->import_ads();
		}

		if ( isset( $what_to_import['settings'] ) ) {
			$this->import_settings();
		}

		if ( isset( $what_to_import['ads'] ) ) {
			$messages[] = sprintf(
				/* translators: 1: counts 2: Importer title */
				esc_html__( '%1$d ads migrated from %2$s', 'advanced-ads' ),
				$count,
				$this->get_title()
			);
		}

		if ( isset( $what_to_import['settings'] ) ) {
			$messages[] = __( 'Settings imported.', 'advanced-ads' );
		}

		return join( ' ', $messages );
	}

	/**
	 * Retrieves the WP Quads options.
	 *
	 * @return array|false WP Quads options, or false if not found.
	 */
	private function get_plugin_options() {
		if ( null !== $this->options ) {
			return $this->options;
		}

		$this->options = get_option( 'quads_settings' );

		return $this->options;
	}

	/**
	 * Import settings.
	 *
	 * @return void
	 */
	private function import_settings(): void {
		$options = $this->get_plugin_options();

		$general   = Options::instance()->get( 'advanced-ads', [] );
		$pro       = Options::instance()->get( 'pro', [] );
		$ads_txt   = Options::instance()->get( 'ads-txt', [] );
		$adsense   = Options::instance()->get( 'adsense', [] );
		$adblocker = Options::instance()->get( 'adblocker', [] );
		$tracking  = Options::instance()->get( 'tracking', [] );

		$tracking['everything'] = $options['ad_performance_tracking'] ?? 'false';

		$adsense['adsense-wp-widget'] = ! empty( $options['reports_settings'] ) && true === $options['reports_settings'] ? 1 : null;
		if ( null === $adsense['adsense-wp-widget'] ) {
			unset( $adsense['adsense-wp-widget'] );
		}

		if ( ! empty( $options['adsTxtEnabled'] ) ) {
			$ads_txt['enabled'] = true === $options['adsTxtEnabled'] ? 1 : 0;
		}

		if ( ! empty( $options['ad_blocker_support'] ) && true === $options['ad_blocker_support'] ) {
			$adblocker['overlay']['time_frequency'] = 2 === intval( $options['notice_behaviour'] ) ? 'everytime' : 'never';

			if ( 'popup' === $options['notice_type'] ) {
				$adblocker['method'] = 'overlay';

				$content = '';
				if ( ! empty( $options['notice_title'] ) ) {
					$content = "<h2 style='text-align: center;'>{$options['notice_title']}</h2>";
				}
				if ( ! empty( $options['notice_description'] ) ) {
					$content .= "<p style='text-align: center;'>{$options['notice_description']}</p>";
				}
				if ( ! empty( $options['notice_txt_color'] ) ) {
					$content = "<div style='color: {$options['notice_txt_color']}'>{$content}</div>";
				}
				if ( $content ) {
					$adblocker['overlay']['content'] = $content;
				}

				$adblocker['overlay']['background_style'] = $options['notice_bg_color'] ? "background-color: {$options['notice_bg_color']};" : '';
				$adblocker['overlay']['dismiss_style']    = $options['notice_btn_txt_color'] ? "color: {$options['notice_btn_txt_color']};" : '';
				$adblocker['overlay']['dismiss_style']   .= $options['notice_btn_bg_color'] ? "background-color: {$options['notice_btn_bg_color']};" : '';
			} elseif ( 'page_redirect' === $options['notice_type'] ) {
				$adblocker['method']          = 'redirect';
				$adblocker['redirect']['url'] = get_permalink( $options['page_redirect_path']['value'] ?? null );
			}
		}

		$pro['cfp']['enabled'] = ! empty( $options['click_fraud_protection'] ) && true === $options['reports_settings'] ? 1 : null;
		if ( null === $pro['cfp']['enabled'] ) {
			unset( $pro['cfp']['enabled'] );
		}

		if ( ! empty( $options['exclude_admin_tracking'] ) ) {
			true === $options['exclude_admin_tracking']
				? $tracking['disabled-roles'][] = 'administrator'
				: $tracking['disabled-roles']   = array_diff( $tracking['disabled-roles'] ?? [], [ 'administrator' ] );
		}

		if ( ! empty( $options['RoleBasedAccess'] ) ) {
			global $wp_roles;
			$roles   = array_keys( $wp_roles->get_names() );
			$show_to = wp_list_pluck( $options['RoleBasedAccess'], 'value' );

			$general['hide-for-user-role'] = array_diff( $roles, $show_to );
		}

		$pro['lazy-load']['enabled'] = ! empty( $options['delay_ad_sec'] ) && true === $options['delay_ad_sec'] ? 1 : null;
		if ( null === $pro['lazy-load']['enabled'] ) {
			unset( $pro['lazy-load']['enabled'] );
		}
	}

	/**
	 * Import ads
	 *
	 * @return int Number of ads imported.
	 */
	private function import_ads(): int {
		$count          = 0;
		$quads_ads      = $this->get_plugin_options()['ads'];
		$group_entities = [];

		// Create ads and separate groups.
		foreach ( $quads_ads as $quad_ad ) {
			$ad_type = $this->parse_ad_type( $quad_ad['ad_type'] );
			if ( false === $ad_type ) {
				continue;
			}

			switch ( $ad_type ) {
				case 'create_group':
				case 'slider':
					$group_entities[] = [
						'type' => $ad_type,
						'data' => $quad_ad,
					];
					break;
				case 'layer':
					$ad_id = ! empty( $quad_ad['ads_list'][0] ) ? $quad_ad['ads_list'][0]['value'] : 0;
					$ad    = wp_advads_get_ad( $ad_id );
					if ( $ad_id && ! $ad ) {
						$quad_ad['position'] = 'layer';
						$this->handle_item( $ad, $quad_ad, $this->history_key );
						++$count;
					}
					break;
				default:
					$ads = $this->create_ad( $ad_type, $quad_ad );
					if ( ! empty( $ads ) ) {
						[ $ad, $desktop_ad ] = $ads;
						if ( $ad->save() ) {
							$this->handle_item( $desktop_ad, $quad_ad, $this->history_key );
							$this->handle_item( $ad, $quad_ad, $this->history_key );
							++$count;
						}
					}
			}
		}

		// Create groups.
		foreach ( $group_entities as $entity ) {
			$created_entity = 'create_group' === $entity['type'] ? $this->create_group( $entity['data'] ) : $this->create_slider( $entity['data'] );
			$this->handle_item( $created_entity, $entity['data'] );
			++$count;
		}

		return $count;
	}

	/**
	 * Save ad and create a placement.
	 *
	 * @param Ad|Group $item    Ad or Group object to be saved.
	 * @param object   $quad_ad The Quad ad object.
	 *
	 * @return void
	 */
	private function handle_item( $item, $quad_ad ): void {
		if ( ! $item ) {
			return;
		}

		$item_id = $item->save();

		if ( is_an_ad( $item ) ) {
			$this->ad_mapping[ $quad_ad['ad_id'] ] = $item_id;
		}

		$placement = $this->create_placement( $item, $quad_ad );
		if ( $placement && $placement->save() ) {
			$this->add_session_key( $item, $placement, $this->history_key );
		}
	}

	/**
	 * Retrieves the corresponding Advanced Ads ad type based on the provided Quad ad type.
	 *
	 * @param string $ad_type The Quad ad type.
	 *
	 * @return string|false The corresponding Advanced Ads ad type, or false if not found.
	 */
	private function parse_ad_type( $ad_type ) {
		// map quad types to advanced ads types.
		$quad_ad_types = [
			'plain_text'    => 'plain',
			'background_ad' => 'image',
			'random_ads'    => 'create_group',
			'loop_ads'      => 'plain',
			'parallax_ads'  => 'image',
			'video_ads'     => 'content',
			'ad_image'      => 'image',
			'popup_ads'     => 'layer',
			'carousel_ads'  => 'slider',
			'adsense'       => 'adsense',
		];
		return $quad_ad_types[ $ad_type ] ?? false;
	}

	/**
	 * Sets the content.
	 *
	 * @param Ad    $ad        The ad object.
	 * @param array $quad_ad   The quad ad data.
	 * @param bool  $is_mobile Whether the ad is for mobile or not.
	 *
	 * @return void
	 */
	private function parse_type_plain( $ad, $quad_ad, $is_mobile = false ): void {
		$ad->set_type( 'plain' );
		$ad->set_content( $quad_ad[ $is_mobile ? 'mob_code' : 'code' ] ?? '' );

		// need to set placement position for loop ads.
		if ( 'loop_ads' === $quad_ad['ad_type'] ) {
			$quad_ad['position']         = 'after_paragraph';
			$quad_ad['paragraph_number'] = $quad_ad['ads_loop_number'];
			$quad_ad['repeat_paragraph'] = $quad_ad['display_after_every'];
		}
	}

	/**
	 * Sets the content.
	 *
	 * @param Ad    $ad        The ad object.
	 * @param array $quad_ad   The quad ad data.
	 * @param bool  $is_mobile Whether the ad is for mobile or not.
	 *
	 * @return void
	 */
	private function parse_type_content( $ad, $quad_ad, $is_mobile = false ): void {
		$ad->set_type( 'content' );

		if ( 'video_ads' === $quad_ad['ad_type'] && ! empty( $quad_ad['image_src'] ) ) {
			$video_width = $quad_ad['video_width'] ?? 350;
			$content     = sprintf( '[video width="%d" mp4="%s" loop="true" autoplay="true"][/video]', $video_width, $quad_ad['image_src'] );

			$url = $quad_ad['image_redirect_url'];
			if ( empty( $url ) ) {
				$content = "<a href='$url'>$content</a>";
			}
			$ad->set_content( $content );

			// need to set sticky placement position for video ads.
			$quad_ad['position'] = 'sticky_footer';
		}
	}

	/**
	 * Sets the content.
	 *
	 * @param Ad    $ad        The ad object.
	 * @param array $quad_ad   The quad ad data.
	 * @param bool  $is_mobile Whether the ad is for mobile or not.
	 *
	 * @return void
	 */
	private function parse_type_image( $ad, $quad_ad, $is_mobile = false ): void {
		$ad->set_type( 'image' );
		$prefix  = $is_mobile ? 'mob_' : '';
		$url_key = $is_mobile ? 'image_mobile_src' : 'image_src_id';
		$url     = '';
		if ( isset( $quad_ad[ $url_key ] ) ) {
			$url = $is_mobile ? str_replace( '\\', '', $quad_ad[ $url_key ] ) : $quad_ad[ $url_key ];
		}
		$img_id = $is_mobile ? attachment_url_to_postid( $url ) : $url;
		$meta   = wp_get_attachment_metadata( $img_id );
		if ( $meta ) {
			$ad->set_image_id( $img_id );
			$ad->set_width(
				! empty( $quad_ad[ $prefix . 'banner_ad_width' ] )
				? absint( $quad_ad[ $prefix . 'banner_ad_width' ] )
				: absint( $meta['width'] )
			);
			$ad->set_height(
				! empty( $quad_ad[ $prefix . 'banner_ad_height' ] )
				? absint( $quad_ad[ $prefix . 'banner_ad_height' ] )
				: absint( $meta['height'] )
			);
		}
		$ad->set_url( $quad_ad['image_redirect_url'] ?? '' );

		// need to set placement position for background ad.
		if ( 'background_ad' === $quad_ad['ad_type'] ) {
			$quad_ad['position'] = 'background';
		} elseif ( 'background_ad' === $quad_ad['ad_type'] ) {
			$quad_ad['position'] = 'parallax';
		}
	}

	/**
	 * Sets the content.
	 *
	 * @param Ad    $ad        The ad object.
	 * @param array $quad_ad   The quad ad data.
	 * @param bool  $is_mobile Whether the ad is for mobile or not.
	 *
	 * @return void
	 */
	private function parse_type_adsense( $ad, $quad_ad, $is_mobile = false ): void {
		$ad->set_type( 'adsense' );

		$content = [];
		if ( ! empty( $quad_ad['g_data_ad_slot'] ) ) {
			$content['slotId'] = $quad_ad['g_data_ad_slot'];
		}

		$ad_client = $quad_ad['g_data_ad_client'];
		if ( ! empty( $ad_client ) ) {
			$content['pubId'] = strpos( $ad_client, 'ca-' ) === 0 ? substr( $ad_client, 3 ) : $ad_client;
		}

		switch ( $quad_ad['adsense_ad_type'] ) {
			case 'display_ads':
				if ( 'normal' === $quad_ad['adsense_type'] ) {
					$content['unitType'] = 'normal';
					$width               = filter_var( $quad_ad['g_data_ad_width'], FILTER_VALIDATE_INT );
					$height              = filter_var( $quad_ad['g_data_ad_height'], FILTER_VALIDATE_INT );
					$ad->set_width( false !== $width ? $width : 300 );
					$ad->set_height( false !== $height ? $height : 250 );
				} else {
					$content['unitType'] = 'responsive';
					$content['resize']   = 'auto';
				}
				break;
			case 'in_feed_ads':
				$content['unitType']   = 'in-feed';
				$content['layout_key'] = $quad_ad['data_layout_key'];
				break;
			case 'in_article_ads':
				$content['unitType'] = 'in-article';
				break;
			case 'matched_content':
				$content['unitType'] = 'matched-content';
				break;
			default:
		}
		$ad->set_content( wp_json_encode( $content ) );
	}

	/**
	 * Parses the ad display conditions for a quad ad.
	 *
	 * @param array $quad_ad The quad ad to parse.
	 *
	 * @return array The parsed display conditions.
	 */
	private function parse_ad_display_conditions( $quad_ad ): array {
		$display_conditions = [];

		if ( ! isset( $quad_ad['visibility_include'] ) || empty( $quad_ad['visibility_include'] ) ) {
			return $display_conditions;
		}

		$index           = 1;
		$condition_types = [
			'post_type'     => 'posttypes',
			'taxonomy'      => 'taxonomy',
			'general'       => 'general',
			'post'          => 'post',
			'page'          => 'post',
			'post_category' => 'taxonomy_category',
			'post_format'   => 'taxonomy_post_format',
			'tags'          => 'taxonomy_post_tag',
			'page_template' => 'page_template',
		];

		$post_page_merged_values = [];

		foreach ( $condition_types as $quad_name => $aa_name ) {
			$conditions = array_filter(
				$quad_ad['visibility_include'],
				fn( $condition ) => $quad_name === $condition['type']['value']
			);

			$values = array_values(
				array_map(
					fn( $condition ) => $condition['value']['value'],
					$conditions
				)
			);

			// we need to merge (post and pages) condition types into one.
			if ( 'page' === $quad_name || 'post' === $quad_name ) {
				$post_page_merged_values = array_merge( $post_page_merged_values, $values );
				continue;
			}

			// quad uses post tag slug as values and we use tag id.
			if ( 'tags' === $quad_name ) {
				$ids = [];
				foreach ( $values as $slug ) {
					$tag = get_term_by( 'slug', $slug, 'post_tag' );
					if ( $tag && ! is_wp_error( $tag ) ) {
						$ids[] = $tag->term_id;
					}
				}
				$values = $ids;
			}

			if ( ! empty( $values ) ) {
				$condition_data = [
					'type'      => $aa_name,
					'value'     => $values,
					'connector' => 'or',
				];
				if ( in_array( $quad_name, [ 'post_type', 'post_category', 'post_format', 'tags' ], true ) ) {
					$condition_data['operator'] = 'is';
				}
				$display_conditions[ $index++ ] = $condition_data;
			}
		}

		if ( ! empty( $post_page_merged_values ) ) {
			$display_conditions[ $index++ ] = [
				'type'      => 'post',
				'value'     => $post_page_merged_values,
				'connector' => 'or',
				'operator'  => 'is',
			];
		}

		return $display_conditions;
	}

	/**
	 * Parses the ad visitor conditions for a quad ad.
	 *
	 * @param array $quad_ad The quad ad to parse.
	 *
	 * @return array The parsed visitor conditions.
	 */
	private function parse_ad_visitor_conditions( $quad_ad ): array {
		$visitor_conditions = [];

		if ( ! isset( $quad_ad['targeting_include'] ) || empty( $quad_ad['targeting_include'] ) ) {
			return $visitor_conditions;
		}

		$condition_types = [
			'device_type'      => 'mobile',
			'browser_language' => 'browser_lang',
			'logged_in'        => 'loggedin',
			'user_agent'       => 'user_agent',
			'user_type'        => 'role',
			'cookie'           => 'cookie',
			'referrer_url'     => 'referrer_url',
			'browser_width'    => 'device_width',
		];

		foreach ( $quad_ad['targeting_include'] as $condition ) {
			if ( ! isset( $condition_types[ $condition['type']['value'] ] ) ) {
				continue;
			}
			$quad_type = $condition['type']['value'];
			$quad_val  = $condition['value']['value'];

			$condition_data['type']      = $condition_types[ $quad_type ];
			$condition_data['connector'] = strtolower( $condition['condition'] ) ?? 'or';

			// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( in_array(
				$quad_type,
				[
					'device_type',
					'browser_language',
					'user_agent',
					'user_type',
					'referrer_url',
				],
				true
			) ) {
				$condition_data['value'] = $quad_val;
			}

			if ( 'device_type' === $quad_type ) {
				$condition_data['value'] = [ $quad_val ];
			} elseif ( 'logged_in' === $quad_type ) {
				$condition_data['operator'] = 'true' === $quad_val ? 'is' : 'is_not';
				unset( $condition_data['value'] );
			} elseif ( 'user_agent' === $quad_type ) {
				$condition_data['operator'] = 'match';
			} elseif ( 'cookie' === $quad_type ) {
				$condition_data['operator'] = 'contain';
				$condition_data['cookie']   = $quad_val;
				$condition_data['value']    = '';
			} elseif ( 'browser_language' === $quad_type || 'user_type' === $quad_type ) {
				$condition_data['operator'] = 'is';
			} elseif ( 'referrer_url' === $quad_type ) {
				$condition_data['operator'] = 'contain';
			} elseif ( 'browser_width' === $quad_type ) {
				switch ( $quad_val ) {
					case '320': // Extra Small Devices.
						$condition_data['value']    = '320';
						$condition_data['operator'] = 'is_lower';
						break;
					case '600': // Small Devices.
						$visitor_conditions[]       = [
							'type'     => $condition_types[ $quad_type ],
							'value'    => '321',
							'operator' => 'is_higher',
						];
						$condition_data['value']    = '600';
						$condition_data['operator'] = 'is_lower';
						break;
					case '768': // Medium Devices.
						$visitor_conditions[]       = [
							'type'     => $condition_types[ $quad_type ],
							'value'    => '601',
							'operator' => 'is_higher',
						];
						$condition_data['value']    = '768';
						$condition_data['operator'] = 'is_lower';
						break;
					case '992': // Large Devices.
						$visitor_conditions[]       = [
							'type'     => $condition_types[ $quad_type ],
							'value'    => '769',
							'operator' => 'is_higher',
						];
						$condition_data['value']    = '992';
						$condition_data['operator'] = 'is_lower';
						break;
					case '1200': // Extra Large Devices.
						$visitor_conditions[]       = [
							'type'     => $condition_types[ $quad_type ],
							'value'    => '993',
							'operator' => 'is_higher',
						];
						$condition_data['value']    = '1200';
						$condition_data['operator'] = 'is_lower';
						break;
					default:
						$condition_data['value']    = $quad_val;
						$condition_data['operator'] = 'is_lower';
				}
			}

			$visitor_conditions[] = $condition_data;
		}

		return $visitor_conditions;
	}

	/**
	 * Finds display conditions found in quad visitor conditions.
	 *
	 * @param array $quad_ad The quad ad to parse.
	 *
	 * @return array The parsed display conditions.
	 */
	private function parse_cross_display_conditions( $quad_ad ): array {
		$display_conditions = [];

		if ( ! isset( $quad_ad['targeting_include'] ) || empty( $quad_ad['targeting_include'] ) ) {
			return $display_conditions;
		}

		// url_parameter in visitor conditions, move it to display conditions.
		$url_parameters = array_filter(
			$quad_ad['targeting_include'],
			fn( $condition ) => 'url_parameter' === $condition['type']['value']
		);

		foreach ( $url_parameters as $url_parameter ) {
			$display_conditions[] = [
				'type'      => 'request_uri',
				'value'     => $url_parameter['value']['value'],
				'operator'  => 'match',
				'connector' => strtolower( $url_parameter['condition'] ) ?? 'or',
			];
		}

		return $display_conditions;
	}

	/**
	 * Finds visitor conditions found in quad display conditions.
	 *
	 * @param array $quad_ad The quad ad to parse.
	 *
	 * @return array The parsed visitor conditions.
	 */
	private function parse_cross_visitor_conditions( $quad_ad ): array {
		$visitor_conditions = [];

		if ( ! isset( $quad_ad['visibility_include'] ) || empty( $quad_ad['visibility_include'] ) ) {
			return $visitor_conditions;
		}

		// user_type in visitor conditions, move it to display conditions.
		$user_types = array_filter(
			$quad_ad['visibility_include'],
			fn( $condition ) => 'user_type' === $condition['type']['value']
		);

		foreach ( $user_types as $user_type ) {
			$visitor_conditions[] = [
				'type'      => 'role',
				'value'     => $user_type['value']['value'],
				'operator'  => 'is',
				'connector' => strtolower( $user_type['condition'] ) ?? 'or',
			];
		}

		return $visitor_conditions;
	}

	/**
	 * Parses the align and sets it for the ad.
	 *
	 * @param int $align The align of the quads ad.
	 *
	 * @return string Ad position.
	 */
	private function parse_align( $align ): string {
		// map quads positions to advanced ads positions.
		$quad_positions = [
			0 => 'left_float',
			1 => 'center_nofloat',
			2 => 'right_float',
			3 => 'none',
		];

		return $quad_positions[ $align ] ?? 'none';
	}

	/**
	 * Creates a new Ad based quad ad data.
	 *
	 * @param string $ad_type The type of the ad.
	 * @param array  $quad_ad The quad ad data.
	 *
	 * @return array|false Ad and Mobile ad if applicable, or false if the ad creation fails.
	 */
	private function create_ad( $ad_type, $quad_ad ) {
		$ad = wp_advads_create_new_ad( $ad_type );
		if ( ! $ad ) {
			return false;
		}
		$ad->set_title( sprintf( '[%s] %s', $this->get_title(), $quad_ad['label'] ) );
		$ad->set_position( $this->parse_align( $quad_ad['align'] ) );
		$ad->set_margin(
			[
				'top'    => $quad_ad['margin'] ?? 0,
				'right'  => $quad_ad['margin_right'] ?? 0,
				'bottom' => $quad_ad['margin_bottom'] ?? 0,
				'left'   => $quad_ad['margin_left'] ?? 0,
			]
		);
		$display_conditions = $this->parse_ad_display_conditions( $quad_ad );
		$visitor_conditions = $this->parse_ad_visitor_conditions( $quad_ad );

		// cross conditions.
		$cross_display_conditions = $this->parse_cross_display_conditions( $quad_ad );
		if ( ! empty( $cross_display_conditions ) ) {
			$display_conditions = array_merge( $display_conditions, $cross_display_conditions );
		}
		$cross_visitor_conditions = $this->parse_cross_visitor_conditions( $quad_ad );
		if ( ! empty( $cross_visitor_conditions ) ) {
			$visitor_conditions = array_merge( $visitor_conditions, $cross_visitor_conditions );
		}

		if ( method_exists( $this, 'parse_type_' . $ad_type ) ) {
			call_user_func( [ $this, 'parse_type_' . $ad_type ], $ad, $quad_ad, false );
		}

		$ad->set_display_conditions( $display_conditions );

		$desktop_ad = null;
		// mobile is checked.
		if ( ! empty( $quad_ad['mobile_html_check'] ) || ! empty( $quad_ad['mobile_image_check'] ) ) {
			// duplicate this same ad for desktop but set visitor conditionf for desktop instead of mobile.
			$desktop_ad = clone $ad;
			$desktop_ad->set_title( $desktop_ad->get_title() . ' - ' . __( 'Desktop', 'advanced-ads' ) );
			$desktop_ad->set_visitor_conditions(
				array_merge(
					$visitor_conditions,
					[
						[
							'type'  => 'mobile',
							'value' => [ 'desktop' ],
						],
					]
				)
			);
			// set mobile data & condition.
			if ( method_exists( $this, 'parse_type_' . $ad_type ) ) {
				call_user_func( [ $this, 'parse_type_' . $ad_type ], $ad, $quad_ad, true );
			}
			$visitor_conditions = array_merge(
				$visitor_conditions,
				[
					[
						'type'  => 'mobile',
						'value' => [ 'mobile' ],

					],
				]
			);
			$ad->set_title( $ad->get_title() . ' - ' . __( 'Mobile', 'advanced-ads' ) );
		}

		$ad->set_visitor_conditions( $visitor_conditions );
		return [ $ad, $desktop_ad ];
	}

	/**
	 * Creates a group for a random quad ad.
	 *
	 * @param array $quad_ad Quad ad data.
	 *
	 * @return Group|false Group object or false if creation fails.
	 */
	private function create_group( $quad_ad ) {
		$group = wp_advads_create_new_group();
		if ( ! $group || empty( $quad_ad['random_ads_list'] ) ) {
			return false;
		}
		$group->set_name( sprintf( '[%s] %s', $this->get_title(), $quad_ad['label'] ) );
		$group->set_ad_count( 1 );

		$ads = [];
		foreach ( $quad_ad['random_ads_list'] as $ad ) {
			$aa_ad_id = $this->ad_mapping[ $ad['value'] ] ?? null;
			if ( null !== $aa_ad_id ) {
				$ads[ $aa_ad_id ] = Constants::GROUP_AD_DEFAULT_WEIGHT;
			}
		}

		$group->set_ad_weights( $ads );

		return $group;
	}

	/**
	 * Creates slider group for a quad ad.
	 *
	 * @param array $quad_ad Quad ad data.
	 *
	 * @return Group|false Group object or false if creation fails.
	 */
	private function create_slider( $quad_ad ) {
		$slider = wp_advads_create_new_group( 'slider' );
		if ( ! $slider || empty( $quad_ad['ads_list'] ) ) {
			return false;
		}

		$slider->set_name( sprintf( '[%s] %s', $this->get_title(), $quad_ad['label'] ) );

		$slider_options          = [];
		$slider_options['delay'] = ! empty( $quad_ad['carousel_speed'] ) ? $quad_ad['carousel_speed'] : 2000;
		if ( ! empty( $quad_ad['carousel_rndms'] ) && true === $quad_ad['carousel_rndms'] ) {
			$slider_options['random'] = 1;
		}
		$slider->set_prop( 'slider', $slider_options );

		$ads = [];
		foreach ( $quad_ad['ads_list'] as $ad ) {
			$aa_ad_id = $this->ad_mapping[ $ad['value'] ] ?? null;
			if ( null !== $aa_ad_id ) {
				$ads[ $aa_ad_id ] = Constants::GROUP_AD_DEFAULT_WEIGHT;
			}
		}
		$slider->set_ad_weights( $ads );

		return $slider;
	}

	/**
	 * Creates a new placement for the given ad.
	 *
	 * @param Ad|Group $item    The ad to create the placement for.
	 * @param array    $quad_ad The Quad ad.
	 *
	 * @return Placement|false The created placement, or false if not created.
	 */
	private function create_placement( $item, $quad_ad ) {
		$placement_data = $this->parse_placement_position( $quad_ad );
		$placement      = wp_advads_create_new_placement( $placement_data['type'] );

		if ( ! $placement ) {
			return false;
		}

		$props          = [];
		$props['type']  = $placement_data['type'];
		$props['title'] = sprintf( '[%s] %s', $this->get_title(), $quad_ad['label'] );
		$props['item']  = ( is_a_group( $item ) ? 'group_' : 'ad_' ) . $item->get_id();

		if ( $quad_ad['ad_label_check'] ) {
			$props['ad_label'] = $quad_ad['ad_label_check'];
		}

		foreach ( $placement_data['props'] as $key => $value ) {
			$props[ $key ] = $value;
		}

		$placement->set_props( $props );
		$placement->save();

		return $placement;
	}

	/**
	 * Parses the Quad ad position and returns the corresponding Advanced Ads placement position.
	 *
	 * @param string $quad_ad The Quad ad.
	 *
	 * @return array The parsed type and props.
	 */
	private function parse_placement_position( $quad_ad ): array {
		$position    = $quad_ad['position'];
		$parsed_type = '';
		$props       = [];

		switch ( $position ) {
			case 'random_ad_placement':
				$parsed_type = 'post_content_random';
				break;
			case 'beginning_of_post':
			case 'after_more_tag':
			case 'after_word_count':
			case 'after_the_percentage':
				$parsed_type = 'post_top';
				break;
			case 'middle_of_post':
				$parsed_type = 'post_content_middle';
				break;
			case 'end_of_post':
				$parsed_type = 'post_bottom';
				break;
			case 'before_last_paragraph':
				$parsed_type = 'post_content';
				$props = [ // phpcs:ignore
					'position'          => 'before',
					'index'             => 1,
					'tag'               => 'p',
					'start_from_bottom' => 1,
				];
				break;
			case 'after_paragraph':
				$parsed_type = 'post_content';
				$props = [ // phpcs:ignore
					'position' => 'after',
					'index'    => $quad_ad['paragraph_number'] ?? 1,
					'tag'      => 'p',
					'repeat'   => isset( $quad_ad['repeat_paragraph'] ) ? 1 : null,
				];
				break;
			case 'after_image':
				$parsed_type = 'post_content';
				$props = [ // phpcs:ignore
					'position' => 'after',
					'index'    => $quad_ad['paragraph_number'] ?? 1,
					'tag'      => 'img',
				];
				break;
			case 'before_image':
				$parsed_type = 'post_content';
				$props = [ // phpcs:ignore
					'position'          => 'before',
					'index'             => $quad_ad['paragraph_number'] ?? 1,
					'tag'               => 'img',
					'start_from_bottom' => 1,
				];
				break;
			case 'ad_after_id':
				$parsed_type = 'custom_position';
				$props = [ // phpcs:ignore
					'inject_by'           => 'pro_custom_element',
					'pro_custom_position' => 'insertAfter',
					'pro_custom_element'  => '#' . $quad_ad['after_class_name'],
				];
				break;
			case 'ad_after_class':
				$parsed_type = 'custom_position';
				$props = [ // phpcs:ignore
					'inject_by'           => 'pro_custom_element',
					'pro_custom_position' => 'insertAfter',
					'pro_custom_element'  => '.' . $quad_ad['after_class_name'],
				];
				break;
			case 'ad_after_customq':
				$parsed_type = 'post_content';
				$props = [ // phpcs:ignore
					'position' => 'after',
					'index'    => 1,
					'tag'      => 'custom',
					'xpath'    => $quad_ad['after_customq_name'] ?? '',
				];
				break;
			case 'ad_after_html_tag':
			case 'ad_before_html_tag':
				$parsed_type = 'post_content';
				$quads_to_aa = [
					'p_tag'   => 'p',
					'img_tag' => 'img',
					'div_tag' => 'div',
					'h2'      => 'h2',
					'h3'      => 'h3',
					'h4'      => 'h4',
				];
				$tag         = $quads_to_aa[ $quad_ad['count_as_per'] ] ?? 'p';
				$xpath       = null;
				if ( in_array( $quad_ad['count_as_per'], [ 'h1', 'h5', 'h6', 'custom' ], true ) ) {
					$tag   = 'custom';
					$xpath = $quad_ad['count_as_per'] . '[1]';
				}
				$props = [ // phpcs:ignore
					'position' => strpos( $position, 'after' ) !== false ? 'after' : 'before',
					'index'    => $quad_ad['paragraph_number'] ?? 1,
					'tag'      => $tag,
					'xpath'    => $xpath,
					'repeat'   => isset( $quad_ad['repeat_paragraph'] ) ? 1 : null,
				];
				break;
			case 'ad_sticky_ad':
				$parsed_type = empty( $quad_ad['sticky_slide_ad'] ) || 'sticky_ad_bot' !== $quad_ad['sticky_slide_ad'] ? 'sticky_footer' : 'sticky_header';
				$props       = [];
				if ( ! empty( $quad_ad['cls_btn'] ) && true === $quad_ad['cls_btn'] ) {
					$props['close']['enabled'] = 1;
				}
				if ( ! empty( $quad_ad['sticky_ad_anim'] ) && true === $quad_ad['sticky_ad_anim'] ) {
					$props['sticky'] = [
						'effect'   => 'fadein',
						'duration' => $quad_ad['sticky_ad_anim_txt'] ?? 0,
					];
				}
				break;
			case 'amp_ads_in_loops':
				$parsed_type = 'archive_pages';
				$props 	 = [ // phpcs:ignore
					'pro_archive_pages_index' => $quad_ad['ads_loop_number'] ?? 1,
				];
				break;
			case 'background':
				$parsed_type = 'background';
				break;
			case 'parallax':
				$parsed_type = 'post_content';
				$props = [ // phpcs:ignore
					'position' => 'after',
					'index'    => 1,
					'tag'      => 'p',
					'parallax' => [
						'enabled' => 'on',
						'height'  => [
							'value' => '30',
							'unit'  => 'vh',
						],
					],
				];
				break;
			case 'sticky_footer':
				$parsed_type = 'sticky_footer';
				break;
			case 'layer':
				$parsed_type = 'layer';
				if ( 'specific_time_popup' === $quad_ad['popup_type'] ) {
					$props['layer_placement'] = [
						'trigger'   => 'delay',
						'delay_sec' => ! empty( $quad_ad['specific_time_interval_sec'] ) ? $quad_ad['specific_time_interval_sec'] / 1000 : 0,
					];
				} elseif ( 'on_scroll_popup' === $quad_ad['popup_type'] ) {
					$props['layer_placement'] = [
						'trigger' => 'custom',
						'offset'  => '200',
					];
				}
				break;
			case 'ad_shortcode':
			default:
				$parsed_type = 'default';
		}

		return [
			'type'  => $parsed_type,
			'props' => $props,
		];
	}
}
