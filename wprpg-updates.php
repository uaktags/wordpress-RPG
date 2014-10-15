<?php
/*
Holds all the updates for wprpg
*/

/**
 * Updates the database schema if it is an old version.
*/
function wpRPG_Upgrade() 
{
	global $wpdb;
	if(!get_option('wpRPG_Version')){ //This was pre-1.0.22 AND install could be pre-1.0.13
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
		}else{
			$sql = "INSERT INTO  ".$wpdb->prefix."rpg_player_metas (`id` ,`name` ,`type` ,`value`)VALUES (
						NULL ,  'gold',  'int',  '100'), (
						NULL ,  'hp',  'int',  '100'), (
						NULL , 'xp', 'int', '0'), (
						NULL , 'strength', 'int', '5'), (
						Null , 'defense', 'int', '5'), (
						NULL , 'bank', 'int', '0'), (
						NULL , 'last_active', 'time', '0'), (
						NULL , 'race', 'int', '0'
						);";
			$wpdb->query($sql);
		}
		return true;
	}
	if(version_compare(WPRPG_VERSION, get_option('wpRPG_Version'), '>='))
	{
		switch(get_option('wpRPG_Version')){
			//Here we'll put specific version that have DB updates that need to be made.
			default:
				return true;
				exit;
		}
	}else{
		return true;
	}
}