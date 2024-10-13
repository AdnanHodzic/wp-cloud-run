<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName

/**
 * Basic Modal class to separate concerns.
 */
class Advanced_Ads_Modal {
	/**
	 * Default values for the view file.
	 *
	 * @var array
	 */
	private $view_arguments = [
		'modal_slug'       => '',
		'modal_content'    => '',
		'modal_title'      => '',
		'close_action'     => '',
		'close_form'       => '',
		'close_validation' => '',
	];

	/**
	 * Create modal.
	 *
	 * @param array $arguments The passed view arguments, overwriting the default values.
	 * @param bool  $render    Whether to render the modal from the constructor. Defaults to true.
	 *
	 * @return Advanced_Ads_Modal
	 */
	public static function create( array $arguments, $render = true ) {
		return new Advanced_Ads_Modal( $arguments, $render );
	}

	/**
	 * Create modal from file.
	 *
	 * @param array  $arguments The passed view arguments, overwriting the default values.
	 * @param string $file      File path to include content from.
	 * @param bool   $render    Whether to render the modal from the constructor. Defaults to true.
	 *
	 * @return Advanced_Ads_Modal
	 */
	public static function create_from_file( array $arguments, $file, $render = true ) {
		ob_start();
		require $file;

		$arguments['modal_content'] = ob_get_clean();

		return new Advanced_Ads_Modal( $arguments, $render );
	}

	/**
	 * Modal constructor.
	 *
	 * @param array $arguments The passed view arguments, overwriting the default values.
	 * @param bool  $render    Whether to render the modal from the constructor. Defaults to true.
	 */
	public function __construct( array $arguments, $render = true ) {
		$this->view_arguments = array_intersect_key(
			wp_parse_args(
				array_map(
					function ( $value ) {
						return (string) $value;
					},
					$arguments
				),
				$this->view_arguments
			),
			$this->view_arguments
		);

		if ( $render ) {
			$this->render();
		}
	}

	/**
	 * Render the modal.
	 *
	 * @return void
	 */
	public function render() {
		extract( $this->view_arguments, EXTR_OVERWRITE ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		require ADVADS_ABSPATH . 'admin/views/modal.php';
	}
}
