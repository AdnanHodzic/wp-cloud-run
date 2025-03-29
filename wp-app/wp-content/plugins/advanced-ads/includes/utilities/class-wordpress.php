<?php
/**
 * The class provides utility functions related to WordPress.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Utilities;

use DateTimeZone;
use AdvancedAds\Constants;
use AdvancedAds\Admin\Upgrades;
use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Framework\Utilities\Params;

defined( 'ABSPATH' ) || exit;

/**
 * Utilities WordPress.
 */
class WordPress {

	/**
	 * Debug function
	 *
	 * @return void
	 */
	public static function dd(): void {
		echo '<pre>';
		foreach ( func_get_args() as $arg ) {
			print_r( $arg ); // phpcs:ignore
		}
		echo '</pre>';
		die();
	}

	/**
	 * Function to calculate percentage
	 *
	 * @param int $part  Part of total.
	 * @param int $total Total value.
	 *
	 * @return string
	 */
	public static function calculate_percentage( $part, $total ): string {
		$percentage = ( $part / $total ) * 100;
		return number_format( $percentage, 2 ) . '%';
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	public static function current_action() {
		$action = Params::request( 'action' );
		if ( '-1' !== $action ) {
			return sanitize_key( $action );
		}

		$action = Params::request( 'action2' );
		if ( '-1' !== $action ) {
			return sanitize_key( $action );
		}

		return false;
	}

	/**
	 * Get count of ads
	 *
	 * @param string $status Status need count for.
	 *
	 * @return int
	 */
	public static function get_count_ads( $status = 'any' ): int {
		$counts = (array) wp_count_posts( Constants::POST_TYPE_AD );

		if ( 'any' === $status ) {
			return array_sum( $counts );
		}

		return $counts[ $status ] ?? 0;
	}

	/**
	 * Get site domain
	 *
	 * @param string $part Part of domain.
	 *
	 * @return string
	 */
	public static function get_site_domain( $part = 'host' ): string {
		$domain = wp_parse_url( home_url( '/' ), PHP_URL_HOST );

		if ( 'name' === $part ) {
			$domain = explode( '.', $domain );
			$domain = count( $domain ) > 2 ? $domain[1] : $domain[0];
		}

		return $domain;
	}

	/**
	 * Get SVG content as string
	 *
	 * @param string $file   File name.
	 * @param string $folder Folder name if not default.
	 *
	 * @return string
	 */
	public static function get_svg( $file, $folder = '/assets/img/' ): string {
		$file_path = \untrailingslashit( ADVADS_ABSPATH ) . $folder . $file;

		if ( file_exists( $file_path ) ) {
			ob_start();
			include $file_path;
			return ob_get_clean();
		}

		return '';
	}

	/**
	 * Retrieves the timezone of the site as a DateTimeZone object.
	 *
	 * @return DateTimeZone
	 */
	public static function get_timezone(): DateTimeZone {
		static $advads_timezone;

		// Early bail!!
		if ( null !== $advads_timezone ) {
			return $advads_timezone;
		}

		if ( function_exists( 'wp_timezone' ) ) {
			$advads_timezone = wp_timezone();
			return $advads_timezone;
		}

		$date_time_zone = new DateTimeZone( self::get_timezone_string() );

		return $date_time_zone;
	}

	/**
	 * Retrieves the timezone of the site as a string.
	 *
	 * @return string
	 */
	public static function get_timezone_string(): string {
		$timezone_string = get_option( 'timezone_string' );

		if ( $timezone_string ) {
			return $timezone_string;
		}

		$offset  = (float) get_option( 'gmt_offset' );
		$hours   = (int) $offset;
		$minutes = ( $offset - $hours );

		$sign     = ( $offset < 0 ) ? '-' : '+';
		$abs_hour = abs( $hours );
		$abs_mins = abs( $minutes * 60 );

		return sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
	}

	/**
	 * Get literal expression of timezone.
	 *
	 * @return string Human readable timezone name.
	 */
	public static function get_timezone_name(): string {
		$time_zone = self::get_timezone()->getName();
		if ( 'UTC' === $time_zone ) {
			return 'UTC+0';
		}

		if ( 0 === strpos( $time_zone, '+' ) || 0 === strpos( $time_zone, '-' ) ) {
			return 'UTC' . $time_zone;
		}

		/* translators: timezone name */
		return sprintf( __( 'time of %s', 'advanced-ads' ), $time_zone );
	}

	/**
	 * Render icon of the type.
	 *
	 * @param string $icon Icon url.
	 *
	 * @return void
	 */
	public static function render_icon( $icon ): void {
		printf( '<img src="%s" width="50" height="50" />', esc_url( $icon ) );
	}

	/**
	 * Applies image loading optimization attributes to an image HTML tag based on WordPress version.
	 *
	 * @param string $img     HTML image tag.
	 * @param string $context Image context.
	 *
	 * @return string Updated HTML image tag with loading optimization attributes.
	 */
	public static function img_tag_add_loading_attr( $img, $context ) {
		if ( is_array( $context ) ) {
			$context = end( $context );
		}

		// Check if the current WordPress version is compatible.
		if ( is_wp_version_compatible( '6.3' ) ) {
			return wp_img_tag_add_loading_optimization_attrs( $img, $context );
		}

		return wp_img_tag_add_loading_attr( $img, $context ); // phpcs:ignore WordPress.WP.DeprecatedFunctions.wp_img_tag_add_loading_attrFound
	}

	/**
	 * Improve WP_Query performance
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array
	 */
	public static function improve_wp_query( $args ): array {
		$args['no_found_rows']          = true;
		$args['update_post_meta_cache'] = false;
		$args['update_post_term_cache'] = false;

		return $args;
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $data Data to sanitize.
	 *
	 * @return string|array
	 */
	public static function sanitize_clean( $data ) {
		if ( is_array( $data ) ) {
			return array_map( __CLASS__ . '::sanitize_clean', $data );
		}

		return is_scalar( $data ) ? sanitize_text_field( $data ) : $data;
	}

	/**
	 * Sanitize conditions
	 *
	 * @param array $conditions Conditions to sanitize.
	 *
	 * @return array
	 */
	public static function sanitize_conditions( $conditions ): array {
		foreach ( $conditions as $index => $condition ) {
			if ( isset( $condition['operator'] ) && in_array( $condition['operator'], [ 'match', 'match_not' ], true ) ) {
				continue;
			}

			if ( empty( $condition['value'] ) ) {
				unset( $conditions[ $index ] );
			}
		}

		return $conditions;
	}

	/**
	 * Check if the current user is a bot prepopulating the cache
	 * Ads should be loaded for the bot, because they should show up on the cached site
	 *
	 * @return bool
	 */
	public static function is_cache_bot(): bool {
		$user_agent = Params::server( 'HTTP_USER_AGENT', '' );
		if ( '' !== $user_agent ) {
			$current = sanitize_text_field( wp_unslash( $user_agent ) );

			// WP Rocket.
			if ( Str::contains( 'wprocketbot', $current ) ) {
				return true;
			}

			// WP Super Cache.
			$wp_useragent = apply_filters( 'http_headers_useragent', 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) );
			if ( $current === $wp_useragent ) {
				return true;
			}

			// LiteSpeed Cache: `lscache_runner` and `lscache_walker` user agents.
			if ( Str::contains( 'lscache_', $current ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Unserializes data only if it was serialized.
	 *
	 * @link https://patchstack.com/articles/unauthenticated-php-object-injection-in-flatsome-theme-3-17-5/
	 *
	 * @param string $data Data that might be unserialized.
	 *
	 * @return mixed Unserialized data can be any type.
	 */
	public static function maybe_unserialize( $data ) {
		if ( is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
			return @unserialize( trim( $data ), [ 'allowed_classes' => false ] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		}
		return $data;
	}

	/**
	 * Renders a setting view.
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $view The path to the view file to be included.
	 * }
	 *
	 * @return void
	 */
	public static function render_setting_view( $args ): void {
		include $args['view'];
	}

	/**
	 * Create a wrapper for a single option line
	 *
	 * @param   string $id     internal id of the option wrapper.
	 * @param   string $title  label of the option.
	 * @param   string $content    content of the option or full path to template file or custom flag to show a pre-defined information.
	 * @param   string $description  description of the option.
	 */
	public static function render_option( $id, $title, $content, $description = '' ) {
		/**
		 * This filter allows to extend the class dynamically by add-ons
		 * this would allow add-ons to dynamically hide/show only attributes belonging to them, practically not used now
		 */
		$class = apply_filters( 'advanced-ads-option-class', $id );
		?>
		<div class="advads-option advads-option-<?php echo esc_attr( $class ); ?>">
			<span><?php echo esc_html( $title ); ?></span>
			<div>
			<?php
			if ( 'is_pro_pitch' === $content ) { // phpcs:ignore
				// Skip this step and place an upgrade link below the description if there is one.
			} elseif ( strlen( $content ) < 500 && file_exists( $content ) ) { // Check length of the string because too long content can break `file_exists`.
				include $content;
			} else {
				// phpcs:ignore
				echo $content; // could include various HTML elements.
			}
			?>
			<?php
			if ( $description ) :
				// phpcs:ignore
				echo '<p class="description">' . $description . '</p>'; // could include various HTML elements.
			endif;

			// place an upgrade link below the description if there is one.
			if ( 'is_pro_pitch' === $content ) {
				Upgrades::pro_feature_link( 'upgrade-pro-' . $id );
			}
			?>
			</div>
		</div>
		<?php
	}

	/**
	 * Show a note about a deprecated feature and link to the appropriate page in our manual
	 *
	 * @param string $feature simple string to indicate the deprecated feature. Will be added to the UTM campaign attribute.
	 */
	public static function show_deprecated_notice( $feature = '' ) {
		$url = 'https://wpadvancedads.com/manual/deprecated-features/';

		if ( '' !== $feature ) {
			$url .= '#utm_source=advanced-ads&utm_medium=link&utm_campaign=deprecated-' . sanitize_title_for_query( $feature );
		}

		echo '<br/><br/><span class="advads-notice-inline advads-error">';
		printf(
			/* translators: %1$s is the opening link tag, %2$s is closing link tag */
			esc_html__( 'This feature is deprecated. Please find the removal schedule %1$shere%2$s', 'advanced-ads' ),
			'<a href="' . esc_url( $url ) . '" target="_blank">',
			'</a>'
		);
		echo '</span>';
	}

	/**
	 * Sort visitor and display condition arrays alphabetically by their label.
	 *
	 * @param array $a array to be compared.
	 * @param array $b array to be compared.
	 *
	 * @return mixed
	 */
	public static function sort_array_by_label( $a, $b ) {
		if ( ! isset( $a['label'] ) || ! isset( $b['label'] ) ) {
			return;
		}

		return strcmp( strtolower( $a['label'] ), strtolower( $b['label'] ) );
	}

	/**
	 * Render a manual link
	 *
	 * @param string $url           target URL.
	 * @param string $utm_campaign  utm_campaign value to attach to the URL.
	 * @param string $title         link text.
	 *
	 * @return void
	 */
	public static function manual_link( $url, $utm_campaign, $title = '' ) {
		$title = ! empty( $title ) ? $title : __( 'Manual', 'advanced-ads' );

		$url = add_query_arg(
			[
				'utm_source'   => 'advanced-ads',
				'utm_medium'   => 'link',
				'utm_campaign' => $utm_campaign,
			],
			$url
		);

		include ADVADS_ABSPATH . 'views/admin/manual-link.php';
	}

	/**
	 * Get installed plugins.
	 *
	 * @return array
	 */
	public static function get_wp_plugins(): array {
		wp_cache_delete( 'plugins', 'plugins' );

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$normalized = [];
		$plugins    = \get_plugins();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$normalized[ $plugin_data['TextDomain'] ] = [
				'file'    => $plugin_file,
				'version' => $plugin_data['Version'] ?? '0.0.1',
			];
		}

		return $normalized;
	}
}
