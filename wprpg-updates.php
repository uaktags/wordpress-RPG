<?php
/*
Holds all the updates for wprpg
*/

/**
 * Updates the database schema if it is an old version.
*/
function wpRPG_transition_db() 
{
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
	return true;
}