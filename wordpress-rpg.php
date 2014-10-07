<?php
/*
Plugin Name: WP RPG
Plugin URI: http://wordpress.org/extend/plugins/wprpg/
Version: 1.0.22
Author: <a href="http://tagsolutions.tk">Tim G.</a> | <a href="http://wordpressrpg.com">wpRPG Official</a> | <a href="http://aioxperts.com">AIOXperts</a>
Description: RPG Elements added to WP
Text Domain: wp-rpg
License: GPL3
*/
define('WPRPG_VERSION', '1.0.22');
require_once(__DIR__. '/wprpg-updates.php');
/*
// I've added a wpRPG_Version option in 1.0.22 to start tracking Version updates.
*/
if(!get_option('wpRPG_Version')){
	if(wpRPG_Upgrade())
		add_option('wpRPG_Version',WPRPG_VERSION,'','yes');	
}elseif(version_compare(WPRPG_VERSION, get_option('wpRPG_Version'), '>=')){
	if(wpRPG_Upgrade())
		update_option('wpRPG_Version', WPRPG_VERSION);
}
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

spl_autoload_register('wprpg_plugin_autoload');

//Main wpRPG plugin
$rpg         = new wpRPG;
$wpRPG_Modules = array();
// "External" Modules as would be found by other developers. Will be removed in later versions
$moduleDIR    = __DIR__ . '/plugins/';
$files = scandir($moduleDIR);
foreach($files as $file){
	if($file != '.' && $file != '..')
		$wpRPG_Modules[$file] = new $file;
}
global $wpdb;
include ( __DIR__.'/wprpg-library.php');

/*
	WPRPG Activation/Deactivation Hooks
	@since 1.0.0
*/
register_activation_hook( __FILE__, array( $rpg, 'wpRPG_on_activation' ) );
register_deactivation_hook( __FILE__, array( $rpg, 'wpRPG_on_deactivation' ) );


?>