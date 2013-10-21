<?php
//////////////////////////
/// Run initialization ///
//////////////////////////


/*
 * Don't start on every page, the plugin page is enough.
 */
 
 if(is_admin()){
	add_action('admin_init', 'RegisterSettings');
		if ( ! empty ( $GLOBALS['pagenow'] ) && 'plugins.php' === $GLOBALS['pagenow'] )
				add_action( 'admin_notices', 'WpRPG_check_admin_notices', 0 );

		$wps_options_pages = array(
				'_rpg_options');

		if ( ! empty ( $GLOBALS['pagenow'] ) && 'admin.php' === $GLOBALS['pagenow'] && in_array($_GET['page'], $wps_options_pages) )
				add_action('admin_footer', 'WpRPG_options_page_hack');
}

/**
 * Test current system for the features the plugin needs.
 *
 * @return array Errors or empty array
 */
function WpRPG_check_plugin_requirements()
{
		$errors = array ();
		global $wpdb;
		$wpdb->show_errors();
		if (  get_option('WPRPG_rpg_installed') != 1) {
				if (check_tables() != FALSE){
						update_option('WPRPG_rpg_installed', "1");
				} else {
						$errors[] = "You had an error occur!<br />";
				}
				
		}              
		return $errors;

}

/**
 * Call WpRPG_check_plugin_requirements() and deactivate this plugin if there are error.
 *
 * @wp-hook admin_notices
 * @return  void
 */
function WpRPG_check_admin_notices()
{
		$errors = WpRPG_check_plugin_requirements();
		if ( empty ( $errors ) )
				return;
		
		
		// Suppress "Plugin activated" notice.
		unset( $_GET['activate'] );

		// this plugin's name
		$name = get_file_data( __FILE__, array ( 'Plugin Name' ), 'plugin' );

		printf(
				'<div class="error"><p>%1$s</p>
				<p><i>%2$s</i> has been deactivated.</p></div>',
				join( '</p><p>', $errors ),
				$name[0]
		);
		deactivate_plugins( plugin_basename( __FILE__ ) );
}

function WpRPG_on_activation()
{
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "activate-plugin_{$plugin}" );

	# Uncomment the following line to see the function in action
	//exit( var_dump( $_GET ) );
		get_current_users_activated();
		check_tables();
}

function WpRPG_on_deactivation()
{
		if ( ! current_user_can( 'activate_plugins' ) )
				return;
		$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
		check_admin_referer( "deactivate-plugin_{$plugin}" );
		# Uncomment the following line to see the function in action
		# exit( var_dump( $_GET ) );
	  
}

function WpRPG_on_uninstall()
{
		if ( ! current_user_can( 'activate_plugins' ) )
				return;
		check_admin_referer( 'bulk-plugins' );

		// Important: Check if the file is the one
		// that was registered during the uninstall hook.
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
				return;
		# Uncomment the following line to see the function in action
		# exit( var_dump( $_GET ) );
		if ($wpdb->query( "DROP TABLE  `".$wpdb->prefix."rpg_usermeta` " ) != FALSE){
				if ( $wpdb->query ("DROP TABLE `" . $wpdb->prefix. "_attack_log`") != FALSE){
						if ( $wpdb->query ("DROP TABLE `" . $wpdb->prefix. "rpg_levels`") != FALSE){
							update_option('WPRPG_rpg_installed', "0");
						} else {
							$errors[] = "You had an error occur! RPG_LEVELS wasn't DROPPED!<br />";
							$errors[] = $wpdb->last_error;
						}
				} else { 
						$errors[] = "You had an error occur! Attack Log wasn't DROPPED!<br />";
						$errors[] =  $wpdb->last_error;
				}
		} else {
				$errors[] = "You had an error occur! USERMETA wasn't changed back!<br />";
				$errors[] =  $wpdb->last_error;
		}
}

		
function RegisterSettings()
{
		// Add options to database if they don't already exist
		add_option('WPRPG_rpg_installed', "", "", "yes");
		// Register settings that this form is allowed to update
		register_setting('rpg_settings', 'WPRPG_rpg_installed');

}
//////////////////////////
/// End initialization ///
//////////////////////////

/////////////////////////
/// Install Functions ///
/////////////////////////


        function check_tables(){
			global $wpdb;
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->base_prefix . "rpg_attack_log (
							id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
							attacker int(11) NOT NULL,
							defender int(11) NOT NULL,
							winner int(11) NOT NULL,
							date TIMESTAMP DEFAULT NOW())";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->base_prefix . "rpg_usermeta (
							`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
							`pid` int(11) unsigned NOT NULL,
							`last_active` int(11) unsigned default '0',
							`level` int(11) unsigned default '1',
							`xp` int(11) unsigned default '0',
							`hp` int(11) unsigned default '20',
							`defense` int(11) unsigned NOT NULL default '10',
							`strength` int(11) unsigned default '5'
							)";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->base_prefix . "rpg_levels (
							`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
							`min` int(11) unsigned NOT NULL DEFAULT '0',
							`max` int(11) unsigned NOT NULL DEFAULT '100',
							`group` varchar(50) NOT NULL DEFAULT '',
							`title` varchar(50) NOT NULL DEFAULT ''
							)";
			$wpdb->query($sql);
			$sql = "INSERT INTO ". $wpdb->base_prefix . "rpg_levels (`id` ,`min` ,`max` ,`group` ,`title`)VALUES ( NULL ,  '0',  '99',  'player_levels',  '1')";
			$wpdb->query($sql);
			$sql = "INSERT INTO ". $wpdb->base_prefix . "rpg_levels (`id` ,`min` ,`max` ,`group` ,`title`)VALUES ( NULL ,  '100',  '199',  'player_levels',  '2')";
			$wpdb->query($sql);
			return true;
        }
        function check_column($table, $col_name){
                global $wpdb;
                if ($table != null){
                        $results = $wpdb->get_results("DESC $table");
                        if ($results != null){
                                foreach ($results as $row ) {
                                        if ($row->Field == $col_name) {
                                        return true;
                                        }
                                }
                                return false;
                        }
                        return false;
                }
                return false;
        }
       function WpRPG_user_register($user_id){
                global $wpdb;
                $wpdb->insert( 
                        $wpdb->base_prefix."rpg_usermeta", 
                        array( 
                                'pid' => $user_id
                        ), 
                        array(  
                                '%d' 
                        ) 
                );
        }
        
        function get_current_users_activated(){
                global $wpdb;
                $user_ids = $wpdb->get_col(
                        "
                        SELECT        ID
                        FROM $wpdb->users
                        "
                );
                foreach ( $user_ids as $id ){
                        if ( $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix . "rpg_usermeta WHERE pid = ". $id ) == null) {
                                WpRPG_user_register($id);
                        }
                }
                return null;
        }
        

?>