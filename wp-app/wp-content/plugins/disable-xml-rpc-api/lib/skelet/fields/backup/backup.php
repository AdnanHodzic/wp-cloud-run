<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: backup
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'SKELET_Field_backup' ) ) {
  class SKELET_Field_backup extends SKELET_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $unique = $this->unique;
      $nonce  = wp_create_nonce( 'skelet_backup_nonce' );
      $export = add_query_arg( array( 'action' => 'skelet-export', 'unique' => $unique, 'nonce' => $nonce ), admin_url( 'admin-ajax.php' ) );

      echo $this->field_before();

      echo '<textarea name="skelet_import_data" class="skelet-import-data"></textarea>';
      echo '<button type="submit" class="button button-primary skelet-confirm skelet-import" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Import', 'skelet' ) .'</button>';
      echo '<hr />';
      echo '<textarea readonly="readonly" class="skelet-export-data">'. esc_attr( json_encode( get_option( $unique ) ) ) .'</textarea>';
      echo '<a href="'. esc_url( $export ) .'" class="button button-primary skelet-export" target="_blank">'. esc_html__( 'Export & Download', 'skelet' ) .'</a>';
      echo '<hr />';
      echo '<button type="submit" name="skelet_transient[reset]" value="reset" class="button skelet-warning-primary skelet-confirm skelet-reset" data-unique="'. esc_attr( $unique ) .'" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html__( 'Reset', 'skelet' ) .'</button>';

      echo $this->field_after();

    }

  }
}
