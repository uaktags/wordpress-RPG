<?php
/*
  Plugin Name: WP RPG
  Plugin URI: http://wordpress.org/extend/plugins/wp-rpg/
  Version: 0.5
  Author: <a href="http://tagsolutions.tk">Tim G.</a>
  Description: RPG Elements added to WP
  Text Domain: wp-rpg
  License: GPL3
 */

/////////////////////
/// File Includes ///
/////////////////////
////////////////////
/// End Includes ///
////////////////////
////////////
/// INIT ///
////////////
$rpg = new wpRPG;
if (is_admin()) {
    add_action('admin_menu', array($rpg, 'wpRPG_settings_page_init'));
    add_action('admin_init', array($rpg, 'wpRPG_RegisterSettings'));
}
$api_url = 'http://projects.tagsolutions.tk/';
$plugin_slug = basename(dirname(__FILE__));
// Take over the update check
add_filter('pre_set_site_transient_update_plugins', 'wpRPG_check_for_plugin_update');
// Take over the Plugin info screen
add_filter('plugins_api', 'wpRPG_api_call', 10, 3);


register_activation_hook(__FILE__, array($rpg, 'wpRPG_on_activation'));
register_deactivation_hook(__FILE__, array($rpg, 'wpRPG_on_deactivation'));

class wpRPG {

    static $file = __FILE__;
    static $apiurl = 'http://projects.tagsolutions.tk/';

    ////////////
    /// INIT ///
    ////////////
    public function __construct() 
	{
        add_action('user_register', array($this, 'wpRPG_user_register'));
        add_action('wp_footer', array($this, 'wpRPG_check_cron'));
        $this->wpRPG_load_shortcodes();
    }

    ////////////////
    /// END INIT ///
    ////////////////
    //////////////////////////////////
    // RPG Functions
    //////////////////////////////////
    public function wpRPG_is_playing($uid) {
        global $wpdb;
        $sql = "SELECT xp, hp, level FROM " . $wpdb->base_prefix . "rpg_usermeta WHERE uid = %d";
        if ($wpdb->get_row($wpdb->prepare($sql, $uid)) != null) {
            return true;
            exit;
        }
        return false;
    }

    public static function wpRPG_get_user_by_id($id) {
        global $wpdb;
        $sql = "Select id, user_login from " . $wpdb->base_prefix . "users where id = %d";
        if ($wpdb->get_row($wpdb->prepare($sql, $id)) != null) {
            return $wpdb->get_row($wpdb->prepare($sql, $id));
        }
        return false;
    }

    public static function wpRPG_player_level($level) {
        global $wpdb;
        $sql = "SELECT title FROM " . $wpdb->base_prefix . "rpg_levels l WHERE l.group='wpRPG_player_levels' AND l.min <= " . $level . " Order By l.min DESC";
        $result = $wpdb->get_results($sql);
		//wp_die(var_dump($result));
        return $result[0]->title;
    }
    public function wpRPG_replenish_hp() {
        global $wpdb;
        $wpdb->show_errors();
        $sql = "UPDATE " . $wpdb->base_prefix . "rpg_usermeta SET hp=hp+1";
        $wpdb->query($sql);
    }

    public function wpRPG_check_cron() {
        $last = get_option('wpRPG_last_cron');
        if ($this->wpRPG_time_elapsed($last)) {
            $i = 1;
            $xs = $this->wpRPG_time_elapsed($last);
            while ($i++ < $xs) {
                $this->wpRPG_replenish_hp();
            }
            $this->wpRPG_replenish_hp();
            $next_t = (time() - (time() % 1800)) + 1800;
            update_option('wpRPG_last_cron', time());
            update_option('wpRPG_next_cron', $next_t);
        }
    }

    public function wpRPG_time_elapsed($secs) {
        return round((time() - $secs) / (60 * 30));
    }

    //////////////////////////////////
    // End RPG Functions
    //////////////////////////////////
    ////////////////////////
    /// Start ShortCodes ///
    ////////////////////////
    public function wpRPG_load_shortcodes() {
        
    }

    //////////////////////
    /// End ShortCodes ///
    //////////////////////
    public function wpRPG_on_activation() {
        if (!current_user_can('activate_plugins'))
            return;
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("activate-plugin_{$plugin}");
        # Uncomment the following line to see the function in action
        //exit( var_dump( $_GET ) );
        $this->wpRPG_check_tables();
        $this->wpRPG_get_current_users_activated();
        return;
    }

    public function wpRPG_on_deactivation() {
        if (!current_user_can('activate_plugins'))
            return;
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("deactivate-plugin_{$plugin}");
        # Uncomment the following line to see the function in action
        # exit( var_dump( $_GET ) );
    }

    public function wpRPG_RegisterSettings() {
        // Add options to database if they don't already exist
        add_option('wpRPG_rpg_installed', "0", "", "yes");
        if (get_option('wpRPG_last_cron') == null) {
            update_option('wpRPG_last_cron', time());
        } elseif (get_option('wpRPG_last_cron') == false) {
            add_option('wpRPG_last_cron', time(), "", "yes");
        }
        if (get_option('wpRPG_next_cron') == null) {
            update_option('wpRPG_next_cron', (time() - (time() % 1800) + 1800));
        } elseif (get_option('wpRPG_next_cron') == false) {
            add_option('wpRPG_next_cron', (time() - (time() % 1800) + 1800), "", "yes");
        }
        // Register settings that this form is allowed to update
        register_setting('rpg_settings', 'wpRPG_rpg_installed');
        register_setting('rpg_settings', 'wpRPG_last_cron');
        register_setting('rpg_settings', 'wpRPG_next_cron');
    }

    //////////////////////////
    /// End initialization ///
    //////////////////////////
    /////////////////////////
    /// Install Functions ///
    /////////////////////////


    public function wpRPG_check_tables() {
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
        $this->wpRPG_default_levels();
        return true;
    }
	
	public function wpRPG_default_levels()
	{
		global $wpdb;
		$wpdb->query("SELECT * FROM " . $wpdb->base_prefix . "rpg_levels");
		if(!$wpdb->num_rows)
		{
			$sql = "INSERT INTO " . $wpdb->base_prefix . "rpg_levels (`id` ,`min` ,`max` ,`group` ,`title`)VALUES ( NULL ,  '0',  '99',  'wpRPG_player_levels',  '1'), ( NULL ,  '100',  '199',  'wpRPG_player_levels',  '2'), ( NULL ,  '200',  '299',  'wpRPG_player_levels',  '3')";
			$wpdb->query($sql);
		}
	}

    public function wpRPG_check_column($table, $col_name) {
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

    public function wpRPG_user_register($user_id) {
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

    public function wpRPG_get_current_users_activated() {
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
                $this->wpRPG_user_register($id);
            }
        }
    }

    public function wpRPG_settings_page_init() {
        $settings_page = add_menu_page('Wordpress RPG Options', ' WP-RPG', 'manage_options', 'wpRPG_menu', array($this, 'wpRPG_settings_page'));
        add_action("load-{$settings_page}", array($this, 'wpRPG_load_settings_page'));
    }

    public function wpRPG_load_settings_page() {
        if (isset($_POST["wpRPG-settings-submit"]) && $_POST["wpRPG-settings-submit"] == 'Y') {
            check_admin_referer("wpRPG-settings-page");
            wpRPG_save_theme_settings();
            $url_parameters = isset($_GET['tab']) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
            wp_redirect(admin_url('themes.php?page=theme-settings&' . $url_parameters));
            exit;
        }
    }

    public function wpRPG_admin_tabs($current = 'homepage') {
        $tabs = array('homepage' => 'Home', 'cron' => 'Cron Info');
        $links = array();
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($tabs as $tab => $name) {
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=wpRPG_menu&tab=$tab'>$name</a>";
        }
        echo '</h2>';
    }

    public function wpRPG_settings_page() {
        global $pagenow;
        $settings = get_option("wpRPG_theme_settings");
        ?>

        <div class="wrap">
            <h2>wpRPG Settings</h2>

        <?php
        if ('true' == esc_attr($_GET['updated']))
            echo '<div class="updated" ><p>Theme Settings updated.</p></div>';

        if (isset($_GET['tab']))
            $this->wpRPG_admin_tabs($_GET['tab']);
        else
            $this->wpRPG_admin_tabs('homepage');
        ?>

            <div id="poststuff">
                <form method="post" action="<?php admin_url('admin.php?page=wpRPG_menu'); ?>">
        <?php
        wp_nonce_field("wpRPG-settings-page");

        if ($pagenow == 'admin.php' && $_GET['page'] == 'wpRPG_menu') {

            if (isset($_GET['tab']))
                $tab = $_GET['tab'];
            else
                $tab = 'homepage';

            echo '<table class="form-table">';
            switch ($tab) {
                case 'cron':
                    ?>
                                <th><label for="wpRPG_intro">Cron Information</label></th>
                                <tr>
                                    <td>
                                        <span class="description">Last Cron:</span> 
                                        <span><?php echo date('Y-m-d:H:i:s', get_option('wpRPG_last_cron')); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="description">Number of 30mins since then:</span> 
                                        <span><?php echo $this->wpRPG_time_elapsed(get_option('wpRPG_last_cron')); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="description">Next Cron:</span> 
                                        <span><?php echo date('Y-m-d:H:i:s', get_option('wpRPG_next_cron')); ?></span>
                                    </td>
                                </tr>
                                <?php
                                break;
                            case 'homepage':
                                ?>
                                <tr>
                                    <td>
                                        <h3>Welcome to Wordpress RPG!</h3>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="description">Total Players:</span>
                                        <span><?= count_users()['total_users'] ?></span>
                                    </td>
                                </tr>
                                <?php
                                break;
                        }
                        echo '</table>';
                    }
                    ?>
                    <p class="submit" style="clear: both;">
                        <input type="submit" name="Submit"  class="button-primary" value="Update Settings" />
                        <input type="hidden" name="wpRPG-settings-submit" value="Y" />
                    </p>
                </form>

            </div>

        </div>
        <?php
    }
}

function wpRPG_check_for_plugin_update($checked_data) 
{
	global $plugin_slug, $api_url;
	if (empty($checked_data->checked))
		return $checked_data;

	$request_args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug . '/' . $plugin_slug . '.php'],
	);

	$request_string = wpRPG_prepare_request('basic_check', $request_args);

	// Start checking for an update
	$raw_response = wp_remote_post($api_url, $request_string);

	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);

	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug . '/' . $plugin_slug . '.php'] = $response;

	return $checked_data;
}

function wpRPG_api_call($def, $action, $args) {
	global $plugin_slug, $api_url, $wp_version;
	
	if (!isset($args->slug) || $args->slug != $plugin_slug)
		return false;

	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$plugin_slug . '/' . $plugin_slug . '.php'];
	$args->version = $current_version;

	$request_string = Attack_prepare_request($action, $args);

	$request = wp_remote_post($api_url, $request_string);

	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);

		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}

	return $res;
}

function wpRPG_prepare_request($action, $args) {
	global $wp_version;

	return array(
		'body' => array(
			'action' => $action,
			'request' => serialize($args),
			'api-key' => md5(get_bloginfo('url'))
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);
}