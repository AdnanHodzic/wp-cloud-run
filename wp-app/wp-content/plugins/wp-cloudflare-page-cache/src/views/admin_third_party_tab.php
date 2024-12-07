<?php
use SPC\Modules\Admin;
use function SPC\Views\Functions\load_view;

$third_party_view_map = Admin::get_third_party_view_map();

foreach ( $third_party_view_map as $view_id => $enabled ) {
	if ( ! $enabled ) {
		continue;
	}
	load_view( 'third_party/' . $view_id );
}

