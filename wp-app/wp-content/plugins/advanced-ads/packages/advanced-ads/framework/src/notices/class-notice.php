<?php
/**
 * The single notice
 *
 * @package AdvancedAds\Framework\Notices
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Notices;

use AdvancedAds\Framework\Interfaces\Integration_Interface;
use Serializable;

defined( 'ABSPATH' ) || exit;

/**
 * Notice class
 */
class Notice implements Serializable {

	/**
	 * Notice type.
	 *
	 * @var string
	 */
	const ERROR = 'error';

	/**
	 * Notice type.
	 *
	 * @var string
	 */
	const SUCCESS = 'success';

	/**
	 * Notice type.
	 *
	 * @var string
	 */
	const INFO = 'info';

	/**
	 * Notice type.
	 *
	 * @var string
	 */
	const WARNING = 'warning';

	/**
	 * Screen check.
	 *
	 * @var string
	 */
	const SCREEN_ANY = 'any';

	/**
	 * Contains optional arguments:
	 *
	 * - type:       The notice type, i.e. 'updated' or 'error'
	 * - persistent: Option name to save dismissal information in.
	 * - screen:     Only display on plugin page or on every page.
	 * - classes:    If you need any extra class to style.
	 *
	 * @var array Options of this notice.
	 */
	private $options = [];

	/**
	 * Internal flag for whether notices has been displayed.
	 *
	 * @var bool
	 */
	private $displayed = false;

	/**
	 * Notice message
	 *
	 * @var string
	 */
	private $message = '';

	/**
	 * Notice id
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * The notice class constructor.
	 *
	 * @param string $id      Notice unique id.
	 * @param string $message Message string.
	 * @param array  $options Set of options.
	 */
	public function __construct( $id, $message, $options = [] ) {
		$this->id      = $id;
		$this->message = $message;
		$this->options = wp_parse_args(
			$options,
			[
				'classes'    => '',
				'persistent' => false,
				'type'       => self::SUCCESS,
				'screen'     => self::SCREEN_ANY,
			]
		);
	}

	/**
	 * Adds string (view) behavior.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->render();
	}

	/**
	 * Serialize data, for PHP version < 7.4.0
	 *
	 * @return string
	 */
	public function serialize(): string {
		return serialize( //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			[
				'id'      => $this->id,
				'message' => $this->message,
				'options' => $this->options,
			]
		);
	}

	/**
	 * Return an array representing the serialized form the object. For PHP version >= 7.4.0
	 *
	 * @return array
	 */
	public function __serialize(): array {
		return [
			'id'      => $this->id,
			'message' => $this->message,
			'options' => $this->options,
		];
	}

	/**
	 * Recreate an instance of this class. For PHP version >= 7.4.0
	 *
	 * @param array $data the array returned by __serialize.
	 *
	 * @return void
	 */
	public function __unserialize( $data ): void {}

	/**
	 * Unserialize string, for PHP version < 7.4.0
	 *
	 * @param string $data Data to unserialize.
	 *
	 * @return void
	 */
	public function unserialize( $data ): void {}

	/**
	 * Return data from options.
	 *
	 * @param string $id ID to get option.
	 *
	 * @return mixed
	 */
	public function option( $id ) {
		return $this->options[ $id ] ?? null;
	}

	/**
	 * Dismiss persistent notice.
	 *
	 * @since  1.0.0
	 */
	public function dismiss() {
		$this->displayed             = true;
		$this->options['persistent'] = false;
	}

	/**
	 * Renders the notice as a string.
	 *
	 * @return string
	 */
	public function render(): string {
		$attributes = [];

		// Default notice classes.
		$classes = [
			'notice',
			'notice-' . $this->option( 'type' ),
		];

		if ( ! empty( $this->option( 'classes' ) ) ) {
			$classes[] = trim( $this->option( 'classes' ) );
		}

		if ( ! empty( $this->option( 'id' ) ) ) {
			$attributes[] = sprintf( 'id="%s"', $this->option( 'id' ) );
		}

		// Maintain WordPress visualization of alerts when they are not persistent.
		if ( $this->is_persistent() ) {
			$classes[]    = 'is-dismissible';
			$attributes[] = sprintf( 'data-key="%s"', $this->option( 'persistent' ) );
			$attributes[] = sprintf( 'data-security="%s"', wp_create_nonce( $this->option( 'id' ) ) );
		}

		$attributes[] = sprintf( 'class="%s"', join( ' ', $classes ) );

		// Build the output DIV.
		return '<div ' . join( ' ', $attributes ) . '>' . wpautop( $this->message ) . '</div>' . PHP_EOL;
	}

	/**
	 * Can display on current screen.
	 *
	 * @return bool
	 */
	public function can_display(): bool {
		// Early Bail!!
		if ( $this->displayed || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( self::SCREEN_ANY === $this->option( 'screen' ) || false !== stristr( $screen->id, $this->option( 'screen' ) ) ) {
			$this->displayed = true;
		}

		return $this->displayed;
	}

	/**
	 * Is this notice persistent.
	 *
	 * @return bool True if persistent, False if fire and forget.
	 */
	public function is_persistent() {
		return ! empty( $this->option( 'persistent' ) );
	}

	/**
	 * Is this notice displayed.
	 *
	 * @return bool
	 */
	public function is_displayed() {
		return $this->displayed;
	}
}
