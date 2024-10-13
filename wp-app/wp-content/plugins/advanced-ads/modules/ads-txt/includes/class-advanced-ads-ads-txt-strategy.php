<?php

/**
 * Manage the data.
 */
class Advanced_Ads_Ads_Txt_Strategy {
	const OPTION = 'advanced_ads_ads_txt';

	private $options;
	private $changed = false;

	public function __construct() {
		$this->options = $this->get_options();
		$this->changed = false;
	}

	/**
	 * Whether to include records from other sites of the network.
	 *
	 * @return bool
	 */
	public function is_all_network() {
		$options = $this->get_options();

		return is_multisite() && $options['all_network'];
	}

	/**
	 * Is enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$this->options = $this->get_options();

		return $this->options['enabled'];
	}

	/**
	 * Get additional content.
	 *
	 * @return string.
	 */
	public function get_additional_content() {
		$options = $this->get_options();

		return $options['custom'];
	}

	/**
	 * Toggle the file and add additional conent.
	 *
	 * @return bool.
	 */
	public function toggle( $is_enabled, $all_network, $additional_content ) {
		$prev = $this->get_options();

		$this->options['enabled']     = $is_enabled;
		$this->options['all_network'] = $all_network;
		$this->options['custom']      = $additional_content;

		if ( $this->options !== $prev ) {
			$this->changed = true;
		}

		return true;
	}

	/**
	 * Add ad network data.
	 *
	 * @param string $id Ad network id.
	 * @param string $rec A Record to add.
	 *
	 * @return bool
	 */
	public function add_network_data( $id, $rec ) {
		$prev = $this->get_options();

		$this->options['networks'][ $id ]['rec'] = $rec;

		if ( $this->options !== $prev ) {
			$this->changed = true;
		}

		return true;

	}

	/**
	 * Set additional content.
	 *
	 * @param string $custom Additional content.
	 * @param bool   $replace Whether to replace or not.
	 *
	 * @return bool
	 */
	public function set_additional_content( $custom, $replace = false ) {
		$prev = $this->get_options();

		if ( $replace ) {
			$this->options['custom'] = $custom;
		} else {
			$this->options['custom'] .= "\n" . $custom;
		}

		if ( $this->options !== $prev ) {
			$this->changed = true;
		}

		return true;
	}

	/**
	 * Prepare content of a blog for output.
	 *
	 * @param array $options Options.
	 *
	 * @return string
	 */
	public function parse_content( $options ) {
		$o = '';

		foreach ( $options['networks'] as $_id => $data ) {
			if ( ! empty( $data['rec'] ) ) {
				$o .= $data['rec'] . "\n";
			}
		}

		if ( ! empty( $options['custom'] ) ) {
			$o .= $options['custom'] . "\n";
		}

		return $o;
	}

	/**
	 * Save options.
	 *
	 * @return bool
	 */
	public function save_options() {
		if ( ! $this->changed ) {
			return true;
		}

		$blog_id       = get_current_blog_id();
		$tmp_options   = Advanced_Ads_Ads_Txt_Utils::remove_duplicate_lines(
			[
				$blog_id => $this->options,
			]
		);
		$this->options = $tmp_options[ $blog_id ];

		if ( is_multisite() ) {
			update_site_meta(
				get_current_blog_id(),
				self::OPTION,
				$this->options
			);
		} else {
			update_option(
				self::OPTION,
				$this->options
			);
		}

		$this->changed = false;
		delete_transient( Advanced_Ads_Ads_Txt_Admin::get_transient_key() );
		return true;
	}

	/**
	 * Get options.
	 *
	 * @return array
	 */
	public function get_options() {
		if ( isset( $this->options ) ) {
			return $this->options;
		}

		if ( is_multisite() ) {
			$options = get_site_meta( get_current_blog_id(), self::OPTION, true );
		} else {
			$options = get_option( self::OPTION, [] );
		}
		if ( ! is_array( $options ) ) {
			$options = [];
		}
		$this->options = $this->load_default_options( $options );

		return $this->options;
	}

	/**
	 * Load default options.
	 *
	 * @param array $options Options.
	 *
	 * @return array
	 */
	public function load_default_options( $options ) {
		if ( ! isset( $options['enabled'] ) ) {
			$options['enabled'] = true;
		}
		if ( ! isset( $options['all_network'] ) ) {
			$options['all_network'] = false;
		}
		if ( ! isset( $options['custom'] ) ) {
			$options['custom'] = '';
		}
		if ( ! isset( $options['networks'] ) || ! is_array( $options['networks'] ) ) {
			$options['networks'] = [];
		}

		return $options;
	}

}
