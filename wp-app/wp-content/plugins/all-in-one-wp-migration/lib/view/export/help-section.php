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
?>

<p>
	<?php _e( 'Use this screen to export your database, media, themes, and plugins into one .wpress file. Later, import that file to another WordPress site.', AI1WM_PLUGIN_NAME ); ?>
</p>

<p>
	<strong><?php _e( 'Hints', AI1WM_PLUGIN_NAME ); ?></strong>
</p>

<ul>
	<li>
		<i class="ai1wm-icon-arrow-right"></i>
		<?php _e( 'In the advanced settings section you can configure more precisely the way of exporting.', AI1WM_PLUGIN_NAME ); ?>
	</li>
	<li>
		<i class="ai1wm-icon-arrow-right"></i>
		<?php _e( 'Press "Export" button and the site archive file will pop up in your browser.', AI1WM_PLUGIN_NAME ); ?>
	</li>
	<li>
		<i class="ai1wm-icon-arrow-right"></i>
		<?php _e( 'Once the file is successfully downloaded on your computer, you can import it to any of your WordPress sites.', AI1WM_PLUGIN_NAME ); ?>
	</li>
</ul>
