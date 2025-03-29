<?php
/**
 * Frontend Debug Ads.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.50.0
 */

namespace AdvancedAds\Frontend;

use AdvancedAds\Abstracts\Ad;
use Advanced_Ads_Display_Conditions;
use Advanced_Ads_Visitor_Conditions;
use AdvancedAds\Utilities\Validation;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Str;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend Debug Ads.
 */
class Debug_Ads implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_action( 'advanced-ads-ad-pre-output', [ $this, 'override_ad_output' ], 50, 2 );
	}

	/**
	 * Override ad output.
	 *
	 * @param string $output Override content.
	 * @param Ad     $ad     Ad object.
	 *
	 * @return string
	 */
	public function override_ad_output( $output, $ad ) {
		$user_can_manage_ads = Conditional::user_can( 'advanced_ads_manage_options' );
		if (
			$ad->is_debug_mode() &&
			( $user_can_manage_ads || ( ! $user_can_manage_ads && ! defined( 'ADVANCED_ADS_AD_DEBUG_FOR_ADMIN_ONLY' ) ) )
		) {
			return $this->prepare_output( $ad );
		}

		return $output;
	}

	/**
	 * Prepare debug mode output.
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return string The ad debug output.
	 */
	private function prepare_output( Ad $ad ): string {
		global $post, $wp_query;

		$width  = 300;
		$height = 250;
		if ( $ad->get_width() > 100 && $ad->get_height() > 100 ) {
			$width  = $ad->get_width();
			$height = $ad->get_height();
		}

		$style      = "width:{$width}px;height:{$height}px;background-color:#ddd;overflow:scroll;";
		$style_full = 'width: 100%; height: 100vh; background-color: #ddd; overflow: scroll; position: fixed; top: 0; left: 0; min-width: 600px; z-index: 99999;';
		$wrapper_id = Str::is_non_empty( $ad->get_wrapper_id() )
			? $ad->get_wrapper_id()
			: wp_advads()->get_frontend_prefix() . wp_rand();

		$content = [];

		if ( $ad->can_display( [ 'ignore_debugmode' => true ] ) ) {
			$content[] = __( 'The ad is displayed on the page', 'advanced-ads' );
		} else {
			$content[] = __( 'The ad is not displayed on the page', 'advanced-ads' );
		}

		// Compare current wp_query with global wp_main_query.
		if ( ! $wp_query->is_main_query() ) {
			$content[] = sprintf( '<span style="color: red;">%s</span>', __( 'Current query is not identical to main query.', 'advanced-ads' ) );
			$content[] = $this->build_query_diff_table();
		}

		if ( isset( $post->post_title ) && isset( $post->ID ) ) {
			$content[] = sprintf( '%s: %s, %s: %s', __( 'current post', 'advanced-ads' ), $post->post_title, 'ID', $post->ID );
		}

		// Compare current post with global post.
		if ( $wp_query->post !== $post ) {
			$error = sprintf( '<span style="color: red;">%s</span>', __( 'Current post is not identical to main post.', 'advanced-ads' ) );
			if ( isset( $wp_query->post->post_title ) && $wp_query->post->ID ) {
				$error .= sprintf( '<br />%s: %s, %s: %s', __( 'main post', 'advanced-ads' ), $wp_query->post->post_title, 'ID', $wp_query->post->ID );
			}
			$content[] = $error;
		}

		$content[] = $this->build_call_chain( $ad );
		$content[] = $this->build_display_conditions_table( $ad );
		$content[] = $this->build_visitor_conditions_table( $ad );

		$message = Validation::is_ad_https( $ad );
		if ( $message ) {
			$content[] = sprintf( '<span style="color: red;">%s</span>', $message );
		}

		$content = apply_filters( 'advanced-ads-ad-output-debug-content', $content, $ad );
		$content = array_filter(
			$content,
			fn( $value ) => ! empty( $value )
		);

		ob_start();

		include ADVADS_ABSPATH . '/public/views/ad-debug.php';

		$output = ob_get_clean();
		$output = apply_filters( 'advanced-ads-ad-output-debug', $output, $ad );
		$output = apply_filters( 'advanced-ads-ad-output', $output, $ad );

		return $output;
	}

	/**
	 * Build table with differences between current and main query
	 *
	 * @since 1.7.0.3
	 */
	private function build_query_diff_table() {
		global $wp_query, $wp_the_query;

		$diff_current = array_diff_assoc( $wp_query->query_vars, $wp_the_query->query_vars );
		$diff_main    = array_diff_assoc( $wp_the_query->query_vars, $wp_query->query_vars );

		if ( ! is_array( $diff_current ) || ! is_array( $diff_main ) ) {
			return '';
		}

		ob_start();

		?>
		<table>
			<thead>
				<tr>
					<th></th>
					<th><?php esc_html_e( 'current query', 'advanced-ads' ); ?></th>
					<th><?php esc_html_e( 'main query', 'advanced-ads' ); ?></th>
				</tr>
			</thead>
			<?php foreach ( $diff_current as $_key => $_value ) : ?>
			<tr>
				<td><?php echo esc_html( $_key ); ?></td>
				<td><?php echo esc_html( $_value ); ?></td>
				<td><?php echo esc_html( $diff_main[ $_key ] ?? '' ); ?></td>
			</tr>
			<?php endforeach; ?>
		</table>
		<?php

		return ob_get_clean();
	}

	/**
	 * Build call chain (placement->group->ad)
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return string
	 */
	private function build_call_chain( Ad $ad ) {
		$output = '';

		$output .= sprintf(
			'%s: %s (%s)',
			__( 'Ad', 'advanced-ads' ),
			esc_html( $ad->get_title() ),
			$ad->get_id()
		);

		if ( $ad->get_parent() ) {
			$output .= sprintf(
				'<br />%s: %s (%s)',
				$ad->get_parent_entity_name(),
				esc_html( $ad->get_parent()->get_title() ),
				$ad->get_parent()->get_id()
			);
		}

		return $output;
	}

	/**
	 * Build display conditions table.
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return string
	 */
	private function build_display_conditions_table( Ad $ad ) {
		$conditions = $ad->get_display_conditions();
		if ( ! is_array( $conditions ) || empty( $conditions ) ) {
			return;
		}

		$display_conditions = Advanced_Ads_Display_Conditions::get_instance()->conditions;
		$the_query          = Advanced_Ads_Display_Conditions::get_instance()->ad_select_args_callback( [] );

		ob_start();
		esc_html_e( 'Display Conditions', 'advanced-ads' );

		foreach ( $conditions as $_condition ) {
			if (
				! is_array( $_condition ) ||
				! isset( $_condition['type'] ) ||
				! isset( $display_conditions[ $_condition['type'] ]['check'][1] )
			) {
				continue;
			}

			printf(
				'<div style="margin-bottom: 20px; white-space: pre-wrap; font-family: monospace; width: 100%%; background: %s;"><strong>%s</strong>',
				Advanced_Ads_Display_Conditions::frontend_check( $_condition, $ad ) ? '#e9ffe9' : '#ffe9e9',
				esc_html( $display_conditions[ $_condition['type'] ]['label'] )
			);

			$check = $display_conditions[ $_condition['type'] ]['check'][1];
			if ( 'check_general' === $check ) {
				printf( '<table border="1"><thead><tr><th></th><th>%s</th><th>%s</th></tr></thead>', esc_html__( 'Ad', 'advanced-ads' ), 'wp_the_query' );
			} else {
				printf( '<table border="1"><thead><tr><th>%s</th><th>%s</th></tr></thead>', esc_html__( 'Ad', 'advanced-ads' ), 'wp_the_query' );
			}

			switch ( $check ) {
				case 'check_post_type':
					printf(
						'<tr><td>%s</td><td>%s</td></tr>',
						isset( $_condition['value'] ) && is_array( $_condition['value'] ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['post']['post_type'] ) ? esc_html( $the_query['post']['post_type'] ) : ''
					);
					break;
				case 'check_general':
					if ( isset( $the_query['wp_the_query'] ) && is_array( $the_query['wp_the_query'] ) ) {
						$ad_vars = ( isset( $_condition['value'] ) && is_array( $_condition['value'] ) ) ? $_condition['value'] : [];

						if ( in_array( 'is_front_page', $ad_vars, true ) ) {
							$ad_vars[] = 'is_home';
						}

						foreach ( $the_query['wp_the_query'] as $_var => $_flag ) {
							printf(
								'<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
								esc_html( $_var ),
								in_array( $_var, $ad_vars, true ) ? 1 : 0,
								esc_html( $_flag )
							);
						}
					}
					break;
				case 'check_author':
					printf(
						'<tr><td>%s</td><td>%s</td></tr>',
						isset( $_condition['value'] ) && is_array( $_condition['value'] ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['post']['author'] ) ? esc_html( $the_query['post']['author'] ) : ''
					);
					break;
				case 'check_post_ids':
				case 'check_taxonomies':
					printf(
						'<tr><td>%s</td><td>post_id: %s<br />is_singular: %s</td></tr>',
						isset( $_condition['value'] ) && is_array( $_condition['value'] ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['post']['id'] ) ? esc_html( $the_query['post']['id'] ) : '',
						! empty( $the_query['wp_the_query']['is_singular'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
					break;
				case 'check_taxonomy_archive':
					printf(
						'<tr><td>%s</td><td>term_id: %s<br />is_archive: %s</td></tr>',
						isset( $_condition['value'] ) && is_array( $_condition['value'] ) ? esc_html( implode( ',', $_condition['value'] ) ) : '',
						isset( $the_query['wp_the_query']['term_id'] ) ? esc_html( $the_query['wp_the_query']['term_id'] ) : '',
						! empty( $the_query['wp_the_query']['is_archive'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					);
					break;
				default:
					printf(
						'<tr><td>%s</td><td>%s</td></tr>',
						esc_html( print_r( $_condition, true ) ), // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
						print_r( $the_query, true ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.PHP.DevelopmentFunctions.error_log_print_r
					);
					break;
			}

			echo '</table></div>';
		}

		return ob_get_clean();
	}

	/**
	 * Build visitor conditions table.
	 *
	 * @param Ad $ad Ad instance.
	 *
	 * @return string
	 */
	private function build_visitor_conditions_table( Ad $ad ) {
		$conditions = $ad->get_visitor_conditions();
		if ( ! is_array( $conditions ) || empty( $conditions ) ) {
			return;
		}

		ob_start();

		$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance()->conditions;
		esc_html_e( 'Visitor Conditions', 'advanced-ads' );

		foreach ( $conditions as $_condition ) {
			if (
				! is_array( $_condition ) ||
				! isset( $_condition['type'] ) ||
				! isset( $visitor_conditions[ $_condition['type'] ]['check'][1] )
			) {
				continue;
			}

			$content = '';
			foreach ( $_condition as $_k => $_v ) {
				$content .= esc_html( $_k ) . ': ' . esc_html( is_array( $_v ) ? implode( ', ', $_v ) : $_v ) . '<br>';
			}

			printf(
				'<div style="margin-bottom: 20px; white-space: pre-wrap; font-family: monospace; width: 100%%; background: %s;">%s</div>',
				Advanced_Ads_Visitor_Conditions::frontend_check( $_condition, $ad ) ? '#e9ffe9' : '#ffe9e9',
				$content // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}

		return ob_get_clean();
	}
}
