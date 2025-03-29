<?php
/**
 * The class provides utility functions for retrieving and managing plugin data and choices.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use WP_Role;
use AdvancedAds\Framework\Utilities\HTML;
use AdvancedAds\Framework\Utilities\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Data and Choices.
 */
class Data {

	/**
	 * Get the list of all add-ons.
	 *
	 * @return array
	 */
	public static function get_addons(): array {
		static $advads_addons = null;

		if ( null === $advads_addons ) {
			$advads_addons = [];
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$plugins = \get_plugins();
			$allowed = [
				'advanced-ads-pro',
				'advanced-ads-responsive',
				'advanced-ads-gam',
				'advanced-ads-layer',
				'advanced-ads-selling',
				'advanced-ads-sticky',
				'advanced-ads-tracking',
			];

			foreach ( $plugins as $plugin_file => $plugin_data ) {
				$slug = $plugin_data['TextDomain'];
				if ( ! in_array( $slug, $allowed, true ) ) {
					continue;
				}

				$name = str_replace( [ '– ', 'Advanced Ads ' ], '', $plugin_data['Name'] );

				$advads_addons[ $slug ] = [
					'id'           => str_replace( 'advanced-ads-', '', $slug ),
					'name'         => $name,
					'version'      => $plugin_data['Version'] ?? '0.0.1',
					'path'         => $plugin_file,
					'options_slug' => $slug,
					'uri'          => $plugin_data['PluginURI'] ?? 'https://wpadvancedads.com',
				];
			}
		}

		return $advads_addons;
	}

	/**
	 * Get the admin screen ids.
	 *
	 * @return array
	 */
	public static function get_admin_screen_ids(): array {
		return apply_filters(
			'advanced-ads-dashboard-screens',
			[
				'advanced_ads',
				'edit-advanced_ads',
				'edit-advanced_ads_plcmnt',
				'toplevel_page_advanced-ads',
				'admin_page_advanced-ads-debug',
				'admin_page_advanced-ads-import-export',
				'advanced-ads_page_advanced-ads-groups',
				'advanced-ads_page_advanced-ads-placements',
				'advanced-ads_page_advanced-ads-settings',
				'advanced-ads_page_advanced-ads-tools',
			]
		);
	}

	/**
	 * Get ad ids
	 *
	 * @return array
	 */
	public static function get_ads_ids(): array {
		static $ad_ids = null;

		if ( null !== $ad_ids ) {
			return $ad_ids;
		}

		$ad_ids = wp_advads_get_ads_dropdown();
		$ad_ids = array_keys( $ad_ids );

		return $ad_ids;
	}

	/**
	 * Get the array of known bots.
	 *
	 * @param bool $filter Whether to apply filters.
	 *
	 * @return array
	 */
	public static function get_bots( $filter = true ) {
		// List of bots and crawlers to exclude from ad impressions.
		$bots = [
			'bot',
			'spider',
			'crawler',
			'scraper',
			'parser',
			'008',
			'Accoona-AI-Agent',
			'ADmantX',
			'alexa',
			'appie',
			'Apple-PubSub',
			'Arachmo',
			'Ask Jeeves',
			'avira\.com',
			'B-l-i-t-z-B-O-T',
			'boitho\.com-dc',
			'BUbiNG',
			'Cerberian Drtrs',
			'Charlotte',
			'cosmos',
			'Covario IDS',
			'curl',
			'Datanyze',
			'DataparkSearch',
			'Dataprovider\.com',
			'DDG-Android',
			'Ecosia',
			'expo9',
			'facebookexternalhit',
			'Feedfetcher-Google',
			'FindLinks',
			'Firefly',
			'froogle',
			'Genieo',
			'heritrix',
			'Holmes',
			'htdig',
			'https://developers\.google\.com',
			'ia_archiver',
			'ichiro',
			'igdeSpyder',
			'InfoSeek',
			'inktomi',
			'Kraken',
			'L\.webis',
			'Larbin',
			'Linguee',
			'LinkWalker',
			'looksmart',
			'lwp-trivial',
			'mabontland',
			'Mnogosearch',
			'mogimogi',
			'Morning Paper',
			'MVAClient',
			'NationalDirectory',
			'NetResearchServer',
			'NewsGator',
			'NG-Search',
			'Nusearch',
			'NutchCVS',
			'Nymesis',
			'oegp',
			'Orbiter',
			'Peew',
			'Pompos',
			'PostPost',
			'proximic',
			'PycURL',
			'Qseero',
			'rabaz',
			'Radian6',
			'Reeder',
			'savetheworldheritage',
			'SBIder',
			'Scooter',
			'ScoutJet',
			'Scrubby',
			'SearchSight',
			'semanticdiscovery',
			'Sensis',
			'ShopWiki',
			'silk',
			'Snappy',
			'Spade',
			'Sqworm',
			'StackRambler',
			'TechnoratiSnoop',
			'TECNOSEEK',
			'Teoma',
			'Thumbnail\.CZ',
			'TinEye',
			'truwoGPS',
			'updated',
			'Vagabondo',
			'voltron',
			'Vortex',
			'voyager',
			'VYU2',
			'WebBug',
			'webcollage',
			'WebIndex',
			'Websquash\.com',
			'WeSEE:Ads',
			'wf84',
			'Wget',
			'WomlpeFactory',
			'WordPress',
			'yacy',
			'Yahoo! Slurp',
			'Yahoo! Slurp China',
			'YahooSeeker',
			'YahooSeeker-Testing',
			'YandexBot',
			'YandexMedia',
			'YandexBlogs',
			'YandexNews',
			'YandexCalendar',
			'YandexImages',
			'Yeti',
			'yoogliFetchAgent',
			'Zao',
			'ZyBorg',
			'okhttp',
			'ips-agent',
			'ltx71',
			'Optimizer',
			'Daum',
			'Qwantify',
		];

		return (array) ( $filter ? apply_filters( 'advanced-ads-bots', $bots ) : $bots );
	}

	/**
	 * Get the roles that are allowed to edit ads.
	 *
	 * @return array
	 */
	public static function get_filtered_roles_by_cap(): array {
		return array_filter(
			wp_roles()->role_objects,
			static function ( WP_Role $role ) {
				return $role->has_cap( 'advanced_ads_edit_ads' );
			}
		);
	}

	/**
	 * Render items dropdown html.
	 *
	 * @param array $args Arguments for the dropdown.
	 *
	 * @return void
	 */
	public static function items_dropdown( $args = [] ): void {
		$items = self::items_for_select();

		$attrs = [
			'id'    => $args['id'] ?? 'advads-items-select',
			'name'  => $args['name'] ?? 'advads-items-select',
			'class' => $args['class'] ?? 'advads-items-select',
		];
		?>
		<select <?php echo HTML::build_attributes( $attrs ); // phpcs:ignore ?>>
			<option value=""><?php esc_html_e( '--empty--', 'advanced-ads' ); ?></option>
			<?php if ( isset( $items['ads'] ) ) : ?>
				<optgroup label="<?php esc_html_e( 'Ads', 'advanced-ads' ); ?>">
					<?php foreach ( $items['ads'] as $ad_id => $ad_title ) : ?>
						<option value="<?php echo esc_attr( $ad_id ); ?>"><?php echo esc_html( $ad_title ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
			<?php if ( isset( $items['groups'] ) ) : ?>
				<optgroup label="<?php esc_html_e( 'Ad Groups', 'advanced-ads' ); ?>">
					<?php foreach ( $items['groups'] as $group_id => $group_title ) : ?>
						<option value="<?php echo esc_attr( $group_id ); ?>"><?php echo esc_html( $group_title ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
			<?php if ( isset( $items['placements'] ) ) : ?>
				<optgroup label="<?php esc_html_e( 'Placements', 'advanced-ads' ); ?>">
					<?php foreach ( $items['placements'] as $placement_id => $placement_title ) : ?>
						<option value="<?php echo esc_attr( $placement_id ); ?>"><?php echo esc_html( $placement_title ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
		</select>
		<?php
	}

	/**
	 * Get items for item select field
	 *
	 * @param array $args Arguments for the dropdown.
	 *
	 * @return array
	 */
	public static function items_for_select( $args = [] ): array {
		$select = [];
		$args   = wp_parse_args(
			$args,
			[
				'ads'        => true,
				'groups'     => true,
				'placements' => true,
			]
		);

		if ( $args['ads'] ) {
			$ads = wp_advads_get_ads_dropdown();
			foreach ( $ads as $ad_id => $ad_title ) {
				$select['ads'][ 'ad_' . $ad_id ] = $ad_title;
			}
		}

		if ( $args['groups'] ) {
			$groups = wp_advads_get_groups_dropdown();
			foreach ( $groups as $group_id => $group_title ) {
				$select['groups'][ 'group_' . $group_id ] = $group_title;
			}
		}

		if ( $args['placements'] ) {
			$placements = wp_advads_get_placements_dropdown();
			foreach ( $placements as $placement_id => $placement_title ) {
				$select['placements'][ 'placement_' . $placement_id ] = $placement_title;
			}
		}

		return $select;
	}

	/**
	 * Get the correct support URL: wp.org for free users and website for those with any add-on installed
	 *
	 * @param string $utm add UTM parameter to the link leading to https://wpadvancedads.com, if given.
	 *
	 * @return string URL.
	 */
	public static function support_url( $utm = '' ) {

		$utm = empty( $utm ) ? '?utm_source=advanced-ads&utm_medium=link&utm_campaign=support' : $utm;
		$url = 'https://wpadvancedads.com/support/' . $utm . '-free-user';

		if ( Conditional::is_any_addon_activated() ) {
			$url = 'https://wpadvancedads.com/support/' . $utm . '-with-addons';
		}

		return $url;
	}

	/**
	 * Show feed from wpadvancedads.com
	 *
	 * @return void
	 */
	public static function display_rss_feed(): void {
		$cache_key = 'advads_feed_posts_v2';
		$cache     = get_transient( $cache_key );

		// Check for cached content.
		$cached_content = get_transient( $cache_key );
		if ( false !== $cached_content ) {
			echo $cached_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		// Generate feed data.
		$content = self::generate_adsense_data();

		// Cache the content if it's valid.
		if ( ! empty( $content ) ) {
			set_transient( $cache_key, $content, 2 * HOUR_IN_SECONDS );
		}

		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get feed from wpadvancedads.com
	 *
	 * @return bool|string HTML content.
	 */
	private static function generate_adsense_data() {
		// Get AdSense data.
		$adsense_obj = \AdSense_Report_Data::get_data_from_options( 'domain' );
		$adsense_num = $adsense_obj->get_sums()['28days'] ?? 0;
		$currency    = $adsense_obj->get_currency() ?? '';

		// Define thresholds and campaigns.
		$feed_id          = '1';
		$campaign         = 'dashboard';
		$adsense_num      = 1500;
		$valid_currencies = [ 'EUR', 'USD', 'GBP', 'CHF' ];

		if ( $adsense_num > 1000 && in_array( $currency, $valid_currencies, true ) ) {
			$feed_id  = '8361';
			$campaign = 'dashboard-adsense-motors';
		} elseif ( $adsense_num > 100 && in_array( $currency, $valid_currencies, true ) ) {
			$feed_id  = '8364';
			$campaign = 'dashboard-adsense';
		}

		$url      = sprintf( 'https://wpadvancedads.com/wp-json/wp/v2/posts?categories=%s&per_page=3', $feed_id );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$rss_posts = json_decode( wp_remote_retrieve_body( $response ), true );

		ob_start();
		include ADVADS_ABSPATH . 'views/admin/widgets/rss-posts.php';
		$content = ob_get_clean();

		return $content;
	}
}
