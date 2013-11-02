<?php
/*
  Plugin Name: WP RPG
  Plugin URI: http://wordpress.org/extend/plugins/wp-rpg/
  Version: 0.0.8
  Author: <a href="http://tagsolutions.tk">Tim G.</a>
  Description: RPG Elements added to WP
  Text Domain: wp-rpg
  License: GPL3
 */

/////////////////////
/// File Includes ///
/////////////////////
//include_once('updater.php');
//include_once('function.updates.php');
////////////////////
/// End Includes ///
////////////////////

////////////
/// INIT ///
////////////
$rpg = new WP_RPG;
$rpg->file = __FILE__;

register_activation_hook(__FILE__, array($rpg,'WpRPG_on_activation'));
register_deactivation_hook(__FILE__, array($rpg,'WpRPG_on_deactivation'));

////////////
/// INIT ///
////////////

class WP_RPG
{
	public $file;

	////////////
	/// INIT ///
	////////////
	public function __construct()
	{
		add_action('user_register', array($this,'WpRPG_user_register'));
		add_action('admin_menu', array($this,'add_rpg_to_admin_menu'));
		add_action('wp_footer', array($this,'check_cron'));
		add_action('admin_init', array($this,'RegisterSettings'));
		$this->load_shortcodes();
	}
	
	public function add_rpg_to_admin_menu() {
		add_menu_page('Wordpress RPG Options', 'WP-RPG', 'manage_options', 'wp_rpg_menu', array($this,'wp_rpg_options'));
	}
	
	////////////////
	/// END INIT ///
	////////////////
	
	//////////////////////////////////
	// RPG Functions
	//////////////////////////////////
	public function WpRPG_is_playing($uid) {
		global $wpdb;
		$sql = "SELECT xp, hp, level FROM " . $wpdb->base_prefix . "rpg_usermeta WHERE uid = %d";
		if ($wpdb->get_row($wpdb->prepare($sql, $uid)) != null) {
			return true;
			exit;
		}
		return false;
	}

	public static function get_user_by_id($id) {
		global $wpdb;
		$sql = "Select id, user_login from " . $wpdb->base_prefix . "users where id = %d";
		if ($wpdb->get_row($wpdb->prepare($sql, $id)) != null) {
			return $wpdb->get_row($wpdb->prepare($sql, $id));
		}
		return false;
	}


	public static function player_level($level) {
		global $wpdb;
		$sql = "SELECT title FROM " . $wpdb->base_prefix . "rpg_levels l WHERE l.group='player_levels' AND l.min <= " . $level;
		$result = $wpdb->get_results($sql);
		return $result[0]->title;
	}


	public function replenish_hp() {
		global $wpdb;
		$wpdb->show_errors();
		$sql = "UPDATE " . $wpdb->base_prefix . "rpg_usermeta SET hp=hp+1";
		$wpdb->query($sql);
	}
	public function check_cron() {
    $last = get_option('WPRPG_last_cron');
    if ($this->time_elapsed($last)) {
        $i = 1;
        $xs = $this->time_elapsed($last);
        while ($i++ < $xs) {
            $this->replenish_hp();
        }
        $this->replenish_hp();
        $next_t = (time() - (time() % 1800)) + 1800;
        update_option('WPRPG_last_cron', time());
        update_option('WPRPG_next_cron', $next_t);
    }
	}

	public function time_elapsed($secs) {
		return round((time() - $secs) / (60 * 30));
	}

	//////////////////////////////////
	// End RPG Functions
	//////////////////////////////////
	////////////////////////
	/// Start ShortCodes ///
	////////////////////////
	public function load_shortcodes()
	{
		
	}
	
	//////////////////////
	/// End ShortCodes ///
	//////////////////////
	public function WpRPG_on_activation() {
		if (!current_user_can('activate_plugins'))
			return;
		$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
		check_admin_referer("activate-plugin_{$plugin}");
		# Uncomment the following line to see the function in action
		//exit( var_dump( $_GET ) );
		$this->check_tables();
		$this->get_current_users_activated();
		return;
	}

	public function WpRPG_on_deactivation() {
		if (!current_user_can('activate_plugins'))
			return;
		$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
		check_admin_referer("deactivate-plugin_{$plugin}");
		# Uncomment the following line to see the function in action
		# exit( var_dump( $_GET ) );
	}
	
	public function RegisterSettings() {
		// Add options to database if they don't already exist
		add_option('WPRPG_rpg_installed', "0", "", "yes");
		if (get_option('WPRPG_last_cron') == null) {
			update_option('WPRPG_last_cron', time());
		} elseif (get_option('WPRPG_last_cron') == false) {
			add_option('WPRPG_last_cron', time(), "", "yes");
		}
		if (get_option('WPRPG_next_cron') == null) {
			update_option('WPRPG_next_cron', (time() - (time() % 1800) + 1800));
		} elseif (get_option('WPRPG_next_cron') == false) {
			add_option('WPRPG_next_cron', (time() - (time() % 1800) + 1800), "", "yes");
		}
		// Register settings that this form is allowed to update
		register_setting('rpg_settings', 'WPRPG_rpg_installed');
		register_setting('rpg_settings', 'WPRPG_last_cron');
		register_setting('rpg_settings', 'WPRPG_next_cron');
	}

	//////////////////////////
	/// End initialization ///
	//////////////////////////
	/////////////////////////
	/// Install Functions ///
	/////////////////////////


	public function check_tables() {
		global $wpdb;
		$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->base_prefix . "rpg_usermeta (
								`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
								`pid` int(11) unsigned NOT NULL,
								`last_active` int(11) unsigned default '0',
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
		$sql = "INSERT INTO " . $wpdb->base_prefix . "rpg_levels (`id` ,`min` ,`max` ,`group` ,`title`)VALUES ( NULL ,  '0',  '99',  'player_levels',  '1'), ( NULL ,  '100',  '199',  'player_levels',  '2'), ( NULL ,  '200',  '299',  'player_levels',  '3')";
		$wpdb->query($sql);
		return true;
	}

	public function check_column($table, $col_name) {
		global $wpdb;
		if ($table != null) {
			$results = $wpdb->get_results("DESC $table");
			if ($results != null) {
				foreach ($results as $row) {
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

	public function WpRPG_user_register($user_id) {
		global $wpdb;
		$wpdb->show_errors();
		$wpdb->insert(
				$wpdb->base_prefix . "rpg_usermeta", array(
			'pid' => $user_id
				), array(
			'%d'
				)
		);
	}

	public function get_current_users_activated() {
		global $wpdb;
		$wpdb->show_errors();
		$user_ids = $wpdb->get_col(
				"
							SELECT        ID
							FROM $wpdb->users
							"
		);
		foreach ($user_ids as $id) {
			if ($wpdb->get_row("SELECT * FROM " . $wpdb->base_prefix . "rpg_usermeta WHERE pid = " . $id) == null) {
				$this->WpRPG_user_register($id);
			}
		}
	}
	
	/*
	 * Options Page
	 * Actual WPRPG Options Page
	 */
	function wp_rpg_options() {
		echo 'Test';
		echo '<br />Last Cron: ' . date('Y-m-d:H:i:s', get_option('WPRPG_last_cron'));
		echo '<br />Number of 30mins since then: ' . $this->time_elapsed(get_option('WPRPG_last_cron'));
		echo '<br />Next Cron: ' . date('Y-m-d:H:i:s', get_option('WPRPG_next_cron'));
	}
}