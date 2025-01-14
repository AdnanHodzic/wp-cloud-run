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

if ( $should_reset_permalinks ) {
	printf(
		__(
			'» Permalinks are set to default. <a class="ai1wm-no-underline" href="https://help.servmask.com/knowledgebase/permalinks-are-set-to-default/" target="_blank">Why?</a><br />' .
			'» <a class="ai1wm-no-underline" href="https://oxygenbuilder.com/documentation/other/importing-exporting/#resigning" target="_blank">Re-sign Oxygen Builder shortcodes</a>.<br />' .
			'» <a class="ai1wm-no-underline" href="https://wordpress.org/support/view/plugin-reviews/all-in-one-wp-migration?rate=5#postform" target="_blank">Review your migration experience</a>.<br />' .
			'» Protect your site with automatic backups - upgrade to <a href="%s" target="_blank">Unlimited Extension</a>',
			AI1WM_PLUGIN_NAME
		),
		'https://servmask.com/products/unlimited-extension?utm_source=import-success&utm_medium=plugin&utm_campaign=ai1wm'
	);
} else {
	printf(
		__(
			'» <a class="ai1wm-no-underline" href="%s" target="_blank">Save permalinks structure</a>.<br />' .
			'» <a class="ai1wm-no-underline" href="https://oxygenbuilder.com/documentation/other/importing-exporting/#resigning" target="_blank">Re-sign Oxygen Builder shortcodes</a>.<br />' .
			'» <a class="ai1wm-no-underline" href="https://wordpress.org/support/view/plugin-reviews/all-in-one-wp-migration?rate=5#postform" target="_blank">Review your migration experience</a>.<br />' .
			'» Protect your site with automatic backups - upgrade to <a href="%s" target="_blank">Unlimited Extension</a>',
			AI1WM_PLUGIN_NAME
		),
		admin_url( 'options-permalink.php#submit' ),
		'https://servmask.com/products/unlimited-extension?utm_source=import-success&utm_medium=plugin&utm_campaign=ai1wm'
	);
}
