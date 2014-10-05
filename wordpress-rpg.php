<?php
/*
Plugin Name: WP RPG
Plugin URI: http://wordpress.org/extend/plugins/wprpg/
Version: 1.0.19
Author: <a href="http://tagsolutions.tk">Tim G.</a>
Description: RPG Elements added to WP
Text Domain: wp-rpg
License: GPL3
*/
define('WPRPG_VERSION', '1.0.19');

/*
	WPRPG Class Loader
	@since 1.0.8
*/
if ( !class_exists( 'wpRPG' ) ) {
	include(__DIR__. '/wprpg-class.php');
}

/*
	WPRPG Plugin Loader
	@since 1.0.6
*/
function wprpg_plugin_autoload($class_name) {
    if(file_exists( __DIR__ . '/plugins/'.$class_name . '/'.$class_name.'-plugin.php')){
		include __DIR__ . '/plugins/'. $class_name . '/'.$class_name.'-plugin.php';
	}
}

if (!get_option('wpRPG_transition_db')){
	add_option('wpRPG_transition_db', 1, '', 'yes');
	wpRPG_transition_db();
}

spl_autoload_register('wprpg_plugin_autoload');

//Main wpRPG plugin
$rpg         = new wpRPG;
// "External" Modules as would be found by other developers. Will be removed in later versions
$profiles    = new wpRPG_Profiles;
$members     = new wpRPG_Members;
$hospital    = new wpRPG_Hospital;
$rpgRegister = new wpRPG_Registration;
$rpgItems = new wpRPG_Items;
$rpgShop = new wpRPG_Shop;
global $wpdb;
include ( __DIR__.'/wprpg-library.php');

/*
	WPRPG Activation/Deactivation Hooks
	@since 1.0.0
*/
register_activation_hook( __FILE__, array( $rpg, 'wpRPG_on_activation' ) );
register_deactivation_hook( __FILE__, array( $rpg, 'wpRPG_on_deactivation' ) );

/**
 * Updates the database schema if it is an old version.
 */
function wpRPG_transition_db() {

	global $wpdb;

	// The meta was stored in a special table before 1.0.13.
	$table_name = $wpdb->base_prefix . 'rpg_usermeta';

	if ( $table_name === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) ) {
		$sql = 'select * from ' . $table_name;
		$res = $wpdb->get_results($sql);
		foreach ( $res as $usermeta )
		{
			update_user_meta($usermeta->pid, 'gold', $usermeta->gold);
			update_user_meta($usermeta->pid, 'xp', $usermeta->xp);
			update_user_meta($usermeta->pid, 'hp', $usermeta->hp);
			update_user_meta($usermeta->pid, 'strength', $usermeta->strength);
			update_user_meta($usermeta->pid, 'defense', $usermeta->defense);
			update_user_meta($usermeta->pid, 'bank', $usermeta->bank);
			update_user_meta($usermeta->pid, 'last_active', $usermeta->race);
			update_user_meta($usermeta->pid, 'race', $usermeta->race);
		}
	}
}
?>
