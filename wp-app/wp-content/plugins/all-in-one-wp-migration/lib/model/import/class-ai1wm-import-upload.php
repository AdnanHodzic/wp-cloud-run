<?php
/**
 * Copyright (C) 2014-2025 ServMask Inc.
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
 * Attribution: This code is part of the All-in-One WP Migration plugin, developed by
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

	public static function execute( $params ) {

		// Get upload tmp name
		if ( isset( $_FILES['upload_file']['tmp_name'] ) ) {
			$upload_tmp_name = $_FILES['upload_file']['tmp_name'];
		} else {
			throw new Ai1wm_Upload_Exception(
				wp_kses(
					__(
						'The uploaded file is missing a temporary path. The process cannot continue.
						<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
						'all-in-one-wp-migration'
					),
					ai1wm_allowed_html_tags()
				),
				400
			);
		}

		// Get upload error
		if ( isset( $_FILES['upload_file']['error'] ) ) {
			$upload_error = $_FILES['upload_file']['error'];
		} else {
			throw new Ai1wm_Upload_Exception(
				wp_kses(
					__(
						'The uploaded file is missing an error code. The process cannot continue.
						<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
						'all-in-one-wp-migration'
					),
					ai1wm_allowed_html_tags()
				),
				400
			);
		}

		// Verify file extension
		if ( ! ai1wm_is_filename_supported( ai1wm_archive_path( $params ) ) ) {
			throw new Ai1wm_Upload_Exception(
				wp_kses(
					__(
						'Invalid file type. Please ensure your file is a <strong>.wpress</strong> backup created with All-in-One WP Migration.
						<a href="https://help.servmask.com/knowledgebase/invalid-backup-file/" target="_blank">Technical details</a>',
						'all-in-one-wp-migration'
					),
					ai1wm_allowed_html_tags()
				),
				415
			);
		}

		// Verify file data
		if ( ! ai1wm_is_filedata_supported( $upload_tmp_name ) ) {
			throw new Ai1wm_Upload_Exception(
				wp_kses(
					__(
						'Invalid file data. Please ensure your file is a <strong>.wpress</strong> backup created with All-in-One WP Migration.
						<a href="https://help.servmask.com/knowledgebase/invalid-backup-file/" target="_blank">Technical details</a>',
						'all-in-one-wp-migration'
					),
					ai1wm_allowed_html_tags()
				),
				415
			);
		}

		// Upload file data
		switch ( $upload_error ) {
			case UPLOAD_ERR_OK:
				try {
					ai1wm_copy( $upload_tmp_name, ai1wm_archive_path( $params ) );
					ai1wm_unlink( $upload_tmp_name );
				} catch ( Exception $e ) {
					/* translators: Error message. */
					throw new Ai1wm_Upload_Exception(
						wp_kses(
							sprintf(
								__(
									'Could not upload the file because %s. The process cannot continue.
									<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
									'all-in-one-wp-migration'
								),
								$e->getMessage()
							),
							ai1wm_allowed_html_tags()
						),
						400
					);
				}

				break;

			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
			case UPLOAD_ERR_PARTIAL:
			case UPLOAD_ERR_NO_FILE:
				throw new Ai1wm_Upload_Exception(
					wp_kses(
						__(
							'The uploaded file is too large for this server. The process cannot continue.
							<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
							'all-in-one-wp-migration'
						),
						ai1wm_allowed_html_tags()
					),
					413
				);

			case UPLOAD_ERR_NO_TMP_DIR:
				throw new Ai1wm_Upload_Exception(
					wp_kses(
						__(
							'No temporary folder is available on the server. The process cannot continue.
							<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
							'all-in-one-wp-migration'
						),
						ai1wm_allowed_html_tags()
					),
					400
				);

			case UPLOAD_ERR_CANT_WRITE:
				throw new Ai1wm_Upload_Exception(
					wp_kses(
						__(
							'Could not save the uploaded file. Please check file permissions and try again.
							<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
							'all-in-one-wp-migration'
						),
						ai1wm_allowed_html_tags()
					),
					400
				);

			case UPLOAD_ERR_EXTENSION:
				throw new Ai1wm_Upload_Exception(
					wp_kses(
						__(
							'A PHP extension blocked this file upload. The process cannot continue.
							<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
							'all-in-one-wp-migration'
						),
						ai1wm_allowed_html_tags()
					),
					400
				);

			default:
				/* translators: Error code. */
				throw new Ai1wm_Upload_Exception(
					wp_kses(
						sprintf(
							__(
								'An unknown error (code: %s) occurred during the file upload. The process cannot continue.
								<a href="https://help.servmask.com/knowledgebase/upload-file-error/" target="_blank">Technical details</a>',
								'all-in-one-wp-migration'
							),
							$upload_error
						),
						ai1wm_allowed_html_tags()
					),
					400
				);
		}

		ai1wm_json_response( array( 'errors' => array() ) );
		exit;
	}
}
