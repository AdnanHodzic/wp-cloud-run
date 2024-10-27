<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: icon
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'SKELET_Field_icon' ) ) {
  class SKELET_Field_icon extends SKELET_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'button_title' => esc_html__( 'Add Icon', 'skelet' ),
        'remove_title' => esc_html__( 'Remove Icon', 'skelet' ),
      ) );

      echo $this->field_before();

      $nonce  = wp_create_nonce( 'skelet_icon_nonce' );
      $hidden = ( empty( $this->value ) ) ? ' hidden' : '';

      echo '<div class="skelet-icon-select">';
      echo '<span class="skelet-icon-preview'. esc_attr( $hidden ) .'"><i class="'. esc_attr( $this->value ) .'"></i></span>';
      echo '<a href="#" class="button button-primary skelet-icon-add" data-nonce="'. esc_attr( $nonce ) .'">'. $args['button_title'] .'</a>';
      echo '<a href="#" class="button skelet-warning-primary skelet-icon-remove'. esc_attr( $hidden ) .'">'. $args['remove_title'] .'</a>';
      echo '<input type="text" name="'. esc_attr( $this->field_name() ) .'" value="'. esc_attr( $this->value ) .'" class="skelet-icon-value"'. $this->field_attributes() .' />';
      echo '</div>';

      echo $this->field_after();

    }

    public function enqueue() {
      add_action( 'admin_footer', array( &$this, 'add_footer_modal_icon' ) );
      add_action( 'customize_controls_print_footer_scripts', array( &$this, 'add_footer_modal_icon' ) );
    }

    public function add_footer_modal_icon() {
    ?>
      <div id="skelet-modal-icon" class="skelet-modal skelet-modal-icon hidden">
        <div class="skelet-modal-table">
          <div class="skelet-modal-table-cell">
            <div class="skelet-modal-overlay"></div>
            <div class="skelet-modal-inner">
              <div class="skelet-modal-title">
                <?php esc_html_e( 'Add Icon', 'skelet' ); ?>
                <div class="skelet-modal-close skelet-icon-close"></div>
              </div>
              <div class="skelet-modal-header">
                <input type="text" placeholder="<?php esc_html_e( 'Search...', 'skelet' ); ?>" class="skelet-icon-search" />
              </div>
              <div class="skelet-modal-content">
                <div class="skelet-modal-loading"><div class="skelet-loading"></div></div>
                <div class="skelet-modal-load"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    }

  }
}
