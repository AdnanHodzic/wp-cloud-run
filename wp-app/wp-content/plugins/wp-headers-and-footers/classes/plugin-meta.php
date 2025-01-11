<?php

/**
 * WordPress Header and Footer plugins meta
 *
 * @package wp-headers-and-footers
 * @since 1.2.1
 */

if (! class_exists('WPHeaderAndFooter_Plugin_Meta')) :

	/**
	 * The Meta class of Header and Footer
	 */
	class WPHeaderAndFooter_Plugin_Meta
	{

		/**
		 * The class constructor
		 *
		 * @since 1.0.0.
		 */
		public function __construct()
		{

			add_filter('plugin_row_meta', array($this, 'row_meta'), 10, 2);
			add_action('plugin_action_links', array($this, 'action_links'), 10, 2);
		}

		/**
		 * Add rating icon on plugins page.
		 *
		 * @param string $meta_fields the meta field.
		 * @param string $file the file name.
		 * @since 1.2.1
		 */
		public function row_meta($meta_fields, $file)
		{

			if ('wp-headers-and-footers/wp-headers-and-footers.php' !== $file) {

				return $meta_fields;
			}

			echo '<style>.wp-headers-and-footers-rate-stars { display: inline-block; color: #ffb900; position: relative; top: 3px; }.wp-headers-and-footers-rate-stars svg{ fill:#ffb900; } .wp-headers-and-footers-rate-stars svg:hover{ fill:#ffb900 } .wp-headers-and-footers-rate-stars svg:hover ~ svg{ fill:none; } </style>';

			$plugin_rate   = 'https://wordpress.org/support/plugin/wp-headers-and-footers/reviews/?rate=5#rate-response';
			$plugin_filter = 'https://wordpress.org/support/plugin/wp-headers-and-footers/reviews/?filter=5';
			$svg_xmlns     = 'https://www.w3.org/2000/svg';
			$svg_icon      = '';

			for ($i = 0; $i < 5; $i++) {
				$svg_icon .= "<svg xmlns='" . esc_url($svg_xmlns) . "' width='15' height='15' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-star'><polygon points='12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2'/></svg>";
			}

			// Set icon for thumbs up.
			$meta_fields[] = '<a href="' . esc_url($plugin_filter) . '" target="_blank"><span class="dashicons dashicons-thumbs-up"></span>' . __('Vote!', 'wp-headers-and-footers') . '</a>';

			// Set icon for 5-star reviews. v1.1.22.
			$meta_fields[] = "<a href='" . esc_url($plugin_rate) . "' target='_blank' title='" . esc_html__('Rate', 'wp-headers-and-footers') . "'><i class='wp-headers-and-footers-rate-stars'>" . $svg_icon . '</i></a>';

			return $meta_fields;
		}

		/**
		 * Add a link to the settings page to the plugins list
		 *
		 * @param URL    $links the action link.
		 * @param string $file the file name.
		 * @since  1.2.1
		 */
		public function action_links($links, $file)
		{

			static $this_plugin;

			if (empty($this_plugin)) {

				$this_plugin = 'wp-headers-and-footers/wp-headers-and-footers.php';
			}

			if ($file === $this_plugin) {
				/* Translators: Settings tab */
				$settings_link = sprintf(esc_html__('%1$s Settings %2$s', 'wp-headers-and-footers'), '<a href="' . admin_url('options-general.php?page=wp-headers-and-footers') . '">', '</a>');
				
				$sdk_data = json_decode(get_option('wpb_sdk_wp-headers-and-footers'), true);
				// Initialize the options or set defaults if not found
				$communication   = isset($sdk_data['communication']) ? $sdk_data['communication'] : '0';
				$diagnostic_info = isset($sdk_data['diagnostic_info']) ? $sdk_data['diagnostic_info'] : '0';
				$extensions      = isset($sdk_data['extensions']) ? $sdk_data['extensions'] : '0';

				// Check if any option is set to '1' and build the settings link
				if ('1' == $communication || '1' == $diagnostic_info || '1' == $extensions) {
					$settings_link .= sprintf(esc_html__('|  %1$s Opt Out %2$s ', 'wp-headers-and-footers'), '<a class="opt-out" href="' . admin_url('options-general.php?page=wp-headers-and-footers') . '">', '</a>');
				} else {
                    if('yes' == get_option( '_wpheaderandfooter_optin' )) {
                        update_option('_wpheaderandfooter_optin', 'no');
                    }
					$settings_link .= sprintf(esc_html__('|  %1$s Opt In %2$s ', 'wp-headers-and-footers'),'<a href="' . admin_url('admin.php?page=wpheadersandfooters-optin') . '">','</a>');
				}
				array_unshift($links, $settings_link);
			}

			return $links;
		}
	}
endif;

new WPHeaderAndFooter_Plugin_Meta();
