<?php

function wmcz_admin_register() {
	add_menu_page('WMCZ', 'WMCZ', 'manage_options', 'wmcz', 'wmcz_admin_index');
	//add_submenu_page('wmcz', 'WMCZ events', 'WMCZ events', 'manage_options', 'wmcz_events', 'wmcz_admin_events');
}

function wmcz_admin_index() {
	echo "<h1>Hello world</h1>";
}

add_action( 'admin_menu', 'wmcz_admin_register' );