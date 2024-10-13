<?php

namespace GRIM_SG;

use GRIM_SG\Vendor\Controller;

class ImportExport extends Controller {
	public function __construct() {
		add_action( 'wp_ajax_export_sitemap_settings', array( $this, 'export_settings' ) );
	}

	public static function import_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! empty( $_FILES['import_file']['tmp_name'] ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$import_data = file_get_contents( $_FILES['import_file']['tmp_name'] );

			if ( ! empty( $import_data ) ) {
				$import_data = json_decode( $import_data, true );

				if ( ! empty( $import_data ) ) {
					update_option( self::$slug, $import_data );

					add_settings_error( self::$slug, 'import_settings', esc_html__( 'Settings imported successfully.', 'xml-sitemap-generator-for-google' ), 'success' );

					return;
				}
			}
		}

		add_settings_error(
			self::$slug,
			'import_settings',
			esc_html__( 'Invalid settings file or data.', 'xml-sitemap-generator-for-google' ),
		);
	}

	public function export_settings() {
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'sgg_export_settings' ) ) {
			return;
		}

		$settings    = get_option( self::$slug );
		$export_data = wp_json_encode( $settings );

		header( 'Content-Description: File Transfer' );
		header( 'Content-type: application/txt' );
		header( 'Content-Disposition: attachment; filename="sitemap_settings.json"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );

		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $export_data;

		exit;
	}
}
