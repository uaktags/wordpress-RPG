<?php
if ( is_admin() ) {
    add_action( 'admin_menu', array( $rpg, 'wpRPG_settings_page_init' ) );
    add_action( 'admin_init', array( $rpg, 'wpRPG_RegisterSettings' ) );
	add_action( 'admin_init', 'includeJquery');
	add_action( 'in_admin_footer', 'includedJS' );
}
if ( !is_admin() ) {
    add_action( 'init', array( $rpg, 'updateLastActive' ) );
	add_action( 'init', 'includeJquery');
	add_action( 'wp_footer', 'includedJS' );
}
add_action( 'init', 'wpRPG_load_language' );

function wpRPG_load_language(){
	load_plugin_textdomain('wpRPG', false, basename( dirname( __FILE__ ) ) . '/languages' );
}