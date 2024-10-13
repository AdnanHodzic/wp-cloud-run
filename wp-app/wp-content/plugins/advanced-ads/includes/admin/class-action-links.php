<?php
/**
 * The class manages various admin action links, feedback submission and text overrides in footer.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.47.0
 */

namespace AdvancedAds\Admin;

use WP_User;
use Advanced_Ads_Plugin;
use AdvancedAds\Utilities\Conditional;
use AdvancedAds\Framework\Utilities\Params;
use AdvancedAds\Framework\Interfaces\Integration_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Action Links.
 */
class Action_Links implements Integration_Interface {

	/**
	 * Hook into WordPress.
	 *
	 * @return void
	 */
	public function hooks(): void {
		add_filter( 'plugin_action_links_' . ADVADS_PLUGIN_BASENAME, [ $this, 'add_links' ] );
		add_filter( 'admin_footer', [ $this, 'add_deactivation_popup' ] );
		add_filter( 'admin_footer_text', [ $this, 'admin_footer_text' ], 100 );
		add_action( 'wp_ajax_advads_send_feedback', [ $this, 'send_feedback' ] );
	}

	/**
	 * Add links to the plugins list
	 *
	 * @param array $links array of links for the plugins, adapted when the current plugin is found.
	 *
	 * @return array
	 */
	public function add_links( $links ): array {
		// Early bail!!
		if ( ! is_array( $links ) ) {
			return $links;
		}

		// Add support page link.
		$support_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( admin_url( 'admin.php?page=advanced-ads-settings#top#support' ) ),
			__( 'Support', 'advanced-ads' )
		);

		// Add add-ons link.
		$extend_link = defined( 'AAP_VERSION' )
			? '<a href="https://wpadvancedads.com/add-ons/?utm_source=advanced-ads&utm_medium=link&utm_campaign=plugin-page-add-ons" target="_blank">' . __( 'Add-Ons', 'advanced-ads' ) . '</a>'
			: '<a href="https://wpadvancedads.com/add-ons/all-access/?utm_source=advanced-ads&utm_medium=link&utm_campaign=plugin-page-features" target="_blank" class="aa-get-pro">' . __( 'See Pro Features', 'advanced-ads' ) . '</a>';

		array_unshift( $links, $support_link, $extend_link );

		return $links;
	}

	/**
	 * Display deactivation logic on plugins page
	 *
	 * @since 1.7.14
	 *
	 * @return void
	 */
	public function add_deactivation_popup(): void {
		$screen = get_current_screen();
		if ( ! isset( $screen->id ) || ! in_array( $screen->id, [ 'plugins', 'plugins-network' ], true ) ) {
			return;
		}

		$from         = '';
		$email        = '';
		$current_user = wp_get_current_user();
		if ( $current_user instanceof WP_User ) {
			$from  = sprintf( '%1$s <%2$s>', $current_user->user_nicename, trim( $current_user->user_email ) );
			$email = $current_user->user_email;
		}

		include ADVADS_ABSPATH . 'views/admin/feedback-disable.php';
	}

	/**
	 * Overrides WordPress text in Footer
	 *
	 * @param string $text The footer text.
	 *
	 * @return string
	 */
	public function admin_footer_text( $text ): string {
		if ( Conditional::is_screen_advanced_ads() ) {
			return sprintf(
				/* translators: %1$s is the URL to add a new review */
				__( 'Thank the developer with a &#9733;&#9733;&#9733;&#9733;&#9733; review on <a href="%1$s" target="_blank">wordpress.org</a>', 'advanced-ads' ),
				'https://wordpress.org/support/plugin/advanced-ads/reviews/#new-post'
			);
		}

		return (string) $text;
	}

	/**
	 * Send feedback via email
	 *
	 * @since 1.7.14
	 */
	public function send_feedback() {
		$data = Params::post( 'formdata' );
		if ( ! $data ) {
			wp_die();
		}

		wp_parse_str( wp_unslash( $data ), $form );

		if ( ! wp_verify_nonce( $form['advanced_ads_disable_form_nonce'], 'advanced_ads_disable_form' ) ) {
			wp_die();
		}

		$email = trim( $form['advanced_ads_disable_reply_email'] );
		if ( empty( $email ) || ! is_email( $email ) ) {
			die();
		}

		$text      = '';
		$headers   = [];
		$options   = Advanced_Ads_Plugin::get_instance()->internal_options();
		$installed = isset( $options['installed'] ) ? gmdate( 'd.m.Y', $options['installed'] ) : '–';
		$from      = $form['advanced_ads_disable_from'] ?? '';
		$subject   = ( $form['advanced_ads_disable_reason'] ?? '(no reason given)' ) . ' (Advanced Ads)';

		if ( isset( $form['advanced_ads_disable_text'] ) ) {
			$text = implode( "\n\r", $form['advanced_ads_disable_text'] );
		}
		$text .= "\n\n" . home_url() . " ($installed)";

		// The user clicked on the "don’t disable" button or if an address is given in the form then use that one.
		if (
			isset( $form['advanced_ads_disable_reason'] ) &&
			'get help' === $form['advanced_ads_disable_reason']
		) {
			$current_user = wp_get_current_user();
			$name         = ( $current_user instanceof WP_User ) ? $current_user->user_nicename : '';
			$from         = $name . ' <' . $email . '>';
			$is_german    = ( preg_match( '/\.de$/', $from ) || 'de_' === substr( get_locale(), 0, 3 ) || 'de_' === substr( get_user_locale(), 0, 3 ) );

			if ( '' !== trim( $form['advanced_ads_disable_text'][0] ?? '' ) ) {
				$text .= $is_german
					? "\n\n Hilfe ist auf dem Weg."
					: "\n\n Help is on its way.";
			} else {
				$text .= $is_german
					? "\n\n Vielen Dank für das Feedback."
					: "\n\n Thank you for your feedback.";
			}
		}

		if ( $from ) {
			$headers[] = "From: $from";
			$headers[] = "Reply-To: $from";
		}

		wp_mail( 'improve@wpadvancedads.com', $subject, $text, $headers );
		die();
	}
}
