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

class Ai1wm_Feedback {

	/**
	 * Submit customer feedback to servmask.com
	 *
	 * @param  string  $type      Feedback type
	 * @param  string  $email     User e-mail
	 * @param  string  $message   User message
	 * @param  integer $terms     User accept terms
	 * @param  string  $purchases Purchases IDs
	 *
	 * @return array
	 */
	public static function add( $type, $email, $message, $terms, $purchases ) {
		// Validate email
		if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) === false ) {
			throw new Ai1wm_Feedback_Exception( __( 'Please enter a valid email address.', AI1WM_PLUGIN_NAME ) );
		}

		// Validate type
		if ( empty( $type ) ) {
			throw new Ai1wm_Feedback_Exception( __( 'Please select a feedback type.', AI1WM_PLUGIN_NAME ) );
		}

		// Validate message
		if ( empty( $message ) ) {
			throw new Ai1wm_Feedback_Exception( __( 'Please describe your issue or feedback.', AI1WM_PLUGIN_NAME ) );
		}

		// Validate terms
		if ( empty( $terms ) ) {
			throw new Ai1wm_Feedback_Exception( __( 'Please check the consent box so we can use your email to respond to you.', AI1WM_PLUGIN_NAME ) );
		}

		$response = wp_remote_post(
			AI1WM_FEEDBACK_URL,
			array(
				'timeout' => 15,
				'body'    => array(
					'type'      => $type,
					'email'     => $email,
					'message'   => $message,
					'purchases' => $purchases,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new Ai1wm_Feedback_Exception( sprintf( __( 'An error occurred while submitting your request: %s', AI1WM_PLUGIN_NAME ), $response->get_error_message() ) );
		}

		return $response;
	}
}
