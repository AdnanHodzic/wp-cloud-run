<?php
/**
 * Copyright (C) 2014-2023 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}

class Ai1wm_Import_Upload {

	private static function validate() {
		if ( ! array_key_exists( 'upload-file', $_FILES ) || ! is_array( $_FILES['upload-file'] ) ) {
			throw new Ai1wm_Import_Retry_Exception( __( 'No file was uploaded. Please select a file and try again.', AI1WM_PLUGIN_NAME ), 400 );
		}

		if ( ! array_key_exists( 'error', $_FILES['upload-file'] ) ) {
			throw new Ai1wm_Import_Retry_Exception( __( 'The uploaded file is missing an error code. The process cannot continue.', AI1WM_PLUGIN_NAME ), 400 );
		}

		if ( ! array_key_exists( 'tmp_name', $_FILES['upload-file'] ) ) {
			throw new Ai1wm_Import_Retry_Exception( __( 'The uploaded file is missing a temporary path. The process cannot continue.', AI1WM_PLUGIN_NAME ), 400 );
		}
	}

	public static function execute( $params ) {
		self::validate();

		$error  = $_FILES['upload-file']['error'];
		$upload = $_FILES['upload-file']['tmp_name'];

		// Verify file name extension
		if ( ! ai1wm_is_filename_supported( ai1wm_archive_path( $params ) ) ) {
			throw new Ai1wm_Import_Exception(
				__(
					'Invalid file type. Please ensure your file is a <strong>.wpress</strong> backup created with All-in-One WP Migration. ' .
					'<a href="https://help.servmask.com/knowledgebase/invalid-backup-file/" target="_blank">Technical details</a>',
					AI1WM_PLUGIN_NAME
				)
			);
		}

		switch ( $error ) {
			case UPLOAD_ERR_OK:
				try {
					ai1wm_copy( $upload, ai1wm_archive_path( $params ) );
					ai1wm_unlink( $upload );
				} catch ( Exception $e ) {
					throw new Ai1wm_Import_Retry_Exception( sprintf( __( 'Could not upload the file because %s. The process cannot continue.', AI1WM_PLUGIN_NAME ), $e->getMessage() ), 400 );
				}
				break;

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				// File is too large
				throw new Ai1wm_Import_Retry_Exception( __( 'The uploaded file is too large for this server. The process cannot continue.', AI1WM_PLUGIN_NAME ), 413 );

			case UPLOAD_ERR_NO_TMP_DIR:
				throw new Ai1wm_Import_Retry_Exception( __( 'No temporary folder is available on the server. The process cannot continue.', AI1WM_PLUGIN_NAME ), 400 );

			case UPLOAD_ERR_CANT_WRITE:
				throw new Ai1wm_Import_Retry_Exception( __( 'Could not save the uploaded file. Please check file permissions and try again.', AI1WM_PLUGIN_NAME ), 400 );

			case UPLOAD_ERR_EXTENSION:
				throw new Ai1wm_Import_Retry_Exception( __( 'A PHP extension blocked this file upload. The process cannot continue.', AI1WM_PLUGIN_NAME ), 400 );

			default:
				throw new Ai1wm_Import_Retry_Exception( sprintf( __( 'An unknown error (code: %s) occurred during the file upload. The process cannot continue.', AI1WM_PLUGIN_NAME ), $error ), 400 );
		}

		ai1wm_json_response( array( 'errors' => array() ) );
		exit;
	}
}
