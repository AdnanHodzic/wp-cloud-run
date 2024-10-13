<?php
/**
 * Form rendering utility functions
 *
 * @package AdvancedAds\Framework\Form
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.0.0
 */

namespace AdvancedAds\Framework\Form;

use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Form class
 */
class Form {

	/**
	 * Hold fields.
	 *
	 * @var array
	 */
	private $fields = [];

	/**
	 * Current field
	 *
	 * @var array
	 */
	private $field_types = [];

	/**
	 * The constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->field_types = [
			'checkbox' => Field_Checkbox::class,
			'color'    => Field_Color::class,
			'number'   => Field_Text::class,
			'position' => Field_Position::class,
			'password' => Field_Text::class,
			'radio'    => Field_Radio::class,
			'selector' => Field_Selector::class,
			'size'     => Field_Size::class,
			'switch'   => Field_Switch::class,
			'text'     => Field_Text::class,
			'textarea' => Field_Textarea::class,
		];
	}

	/**
	 * Add field.
	 *
	 * @throws Exception If no id is define.
	 * @throws Exception If no type is define.
	 *
	 * @param array $args Field args.
	 *
	 * @return void
	 */
	public function add_field( $args ) {
		// Checks.
		if ( ! isset( $args['id'] ) || empty( $args['id'] ) ) {
			throw new Exception( 'A field must have an id.' );
		}

		if ( ! isset( $args['type'] ) || empty( $args['type'] ) ) {
			throw new Exception( 'A field must have a type.' );
		}

		$this->fields[ $args['id'] ] = $args;
	}

	/**
	 * Remove field.
	 *
	 * @param string $id Field id to remove.
	 *
	 * @return void
	 */
	public function remove_field( $id ) {
		if ( isset( $this->fields[ $id ] ) ) {
			unset( $this->fields[ $id ] );
		}
	}

	/**
	 * Render form.
	 *
	 * @return void
	 */
	public function render() {
		foreach ( $this->fields as $field ) {
			$type = $field['type'];

			if ( isset( $this->field_types[ $type ] ) ) {
				$class = $this->field_types[ $type ];
				( new $class( $field ) )->render_field();
			}
		}
	}
}
