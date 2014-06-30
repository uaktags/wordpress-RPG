<?php

/**
 * wpRPG
 */
class wpRPG {

    /**
     * Consrtuctor for wpRPG Class
     * @since 1.0.0
     */
    public function __construct() {
        add_action('wp_footer', array(
            $this,
            'wpRPG_check_cron'
        ));
        $this->file_name = __FILE__;
        $this->displayed_player = '';
        $this->wpRPG_load_shortcodes();
        $this->crons = array(); //Empty Crons [Default]
    }

    /**
     * Updates player's last active time
     * @return DBQuery or False on failure
     * @since 1.0.0
     */
    public function updateLastActive() {
        if(!is_user_logged_in())
			return false;
		else{
			$current_user = wp_get_current_user();
			if ($this->wpRPG_is_playing($current_user->ID)) {
				$player = new wpRPG_Player($current_user->ID);
				if ($player->last_active <= (time() - 300))
					return $player->update_meta('last_active', time());
			}
		}
    }

    /**
     * Get's online status of player by ID
     * @param int $uid PlayerID
     * @return bool Checks against last active within 5minutes
     * @since 1.0.0
     * @deprecated since 1.0.15. Use $player = new wpRPG_Player(ID); $player->getOnlineStatus();
     */
    public static function getOnlineStatus($uid) {
        $time = time() - ( 60 * 5 );
        return (get_user_meta($uid, 'last_active') > $time);
    }

    /**
     * Get all players that are playing
     * @return bool
     * @since 1.0.0
     */
    public function wpRPG_is_playing($uid) {
        if (get_user_meta($uid, 'xp')) {
            return true;
            exit;
        }
        return false;
    }

    /**
     * Get player's rank by their id
     * @return int Rank
     * @since 1.0.0
     * @deprecated since 1.0.15. Use $player = new wpRPG_Player(ID); $player->getRank();
     */
    public static function wpRPG_player_rank($id) {
        global $wpdb;
        $sql_count = "SELECT * FROM " . $wpdb->prefix . "usermeta where meta_key='xp' ORDER BY meta_value DESC";
        $res = $wpdb->get_results($sql_count);
        $rank = 1;
        foreach ($res as $item) {
            if ($item->user_id == $id) {
                return $rank;
            }
            ++$rank;
        }
        return 1;
    }

    /**
     * Get player's level title by their level integer
     * @param int $level
     * @return string title
     * @since 1.0.0
     */
    public static function wpRPG_player_level($level) {
        global $wpdb;
        $sql = "SELECT title FROM " . $wpdb->prefix . "rpg_levels l WHERE l.group='wpRPG_player_levels' AND l.min <= " . $level . " Order By l.min DESC";
        $result = $wpdb->get_results($sql);
        return $result[0]->title;
    }

    /**
     * Check the Cron and Execute when needed
     * @since 1.0.2
     */
    public function wpRPG_check_cron() {
        foreach ($this->get_crons() as $cron => $info) {
            $last = get_option('wpRPG_last_' . $cron);
            if (!empty($last) && $this->wpRPG_time_elapsed($last, $info['duration']) && get_option('wpRPG_status_' . $cron)) {
                $i = 1;
                $xs = $this->wpRPG_time_elapsed($last, $info['duration']);
                while ($i++ <= $xs) {
                    if (class_exists($info['class'])) {
                        if ($info['class'] == 'wpRPG')
                            $class = $this;
                        else
                            $class = new $info['class'];
                        if (method_exists($class, $info['func'])) {
                            $class->$info['func']();
                        }
                    }
                }
                $next_t = ( time() - ( time() % $info['duration'] ) ) + $info['duration'];
                update_option('wpRPG_last_' . $cron, time());
                update_option('wpRPG_next_' . $cron, $next_t); //$next_t
            }
        }
    }

    /**
     * Get all the crons that are added
     * @return array 
     * @since 1.0.3
     */
    public function get_crons() {
        $crons = $this->crons;
        $new_crons = apply_filters('wpRPG_add_crons', $crons);
        return $new_crons;
    }

    /**
     * Get the time that's elapsed by the seconds
     * @return int 
     * @param int $secs
     * @since 1.0.0
     */
    public function wpRPG_time_elapsed($secs, $duration) {
        return round(( time() - $secs ) / ( $duration ));
    }

    /**
     * Load shortcodes
     * Intentionally left empty
     * @since 1.0.0
     */
    public function wpRPG_load_shortcodes() {
        
    }

    /**
     * Execute these functions during Plugin Execution 
     * @since 1.0.0
     */
    public function wpRPG_on_activation() {
        if (!current_user_can('activate_plugins'))
            return;
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("activate-plugin_{$plugin}");
        $this->wpRPG_check_tables();
        $this->wpRPG_get_current_users_activated();
        return;
    }

    /**
     * Execute these function during Plugin Deactivation
     * @since 1.0.0
     */
    public function wpRPG_on_deactivation() {
        if (!current_user_can('activate_plugins'))
            return;
        $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
        check_admin_referer("deactivate-plugin_{$plugin}");
    }

    /**
     * Register and create these settings
     * @since 1.0.0
     * @version 1.0.6
     * @changelog Added variable hp replenish
     */
    public function wpRPG_RegisterSettings() {
        foreach ($this->get_crons() as $cron => $info) {
            if (get_option('wpRPG_last_' . $cron) == null) {
                update_option('wpRPG_last_' . $cron, time());
            } elseif (get_option('wpRPG_last_' . $cron) == false) {
                add_option('wpRPG_last_' . $cron, time(), "", "yes");
            }
            if (get_option('wpRPG_next_' . $cron) == false) {
                add_option('wpRPG_next_' . $cron, ( time() - ( time() % $info['duration'] ) ) + $info['duration'], "", "yes");
            }
            if (get_option('wpRPG_status_' . $cron) == false) {
                add_option('wpRPG_status_' . $cron, ( isset($info['status']) ? $info['status'] : true), '', 'yes');
            }
            register_setting('rpg_settings', 'wpRPG_last_' . $cron);
            register_setting('rpg_settings', 'wpRPG_next_' . $cron);
        }

        add_option('show_wpRPG_Version_footer', "0", "", "yes");
        register_setting('rpg_settings', 'show_wpRPG_Version_footer');
        add_option('wpRPG_rpg_installed', "0", "", "yes");
        add_option('wpRPG_status_debug_reset_xp', '0', '', 'yes');
        add_option('wpRPG_status_debug_reset_hp', '0', '', 'yes');
        add_option('wpRPG_status_debug_reset_gold', '500', '', 'yes');
        add_option('wpRPG_status_debug_reset_all', '0', '', 'yes');
        add_option('wpRPG_show_Tab_Title', '1', '', 'yes');
        add_option('wpRPG_show_Page_Titles', '1', '', 'yes');
        add_option('wpRPG_admin_enable_debug_funcs', '0', '', 'yes');
        register_setting('rpg_settings', 'wpRPG_status_debug_reset_xp');
        register_setting('rpg_settings', 'wpRPG_status_debug_reset_hp');
        register_setting('rpg_settings', 'wpRPG_status_debug_reset_gold');
        register_setting('rpg_settings', 'wpRPG_status_debug_reset_all');
        register_setting('rpg_settings', 'show_wpRPG_Version_footer');
        register_setting('rpg_settings', 'wpRPG_rpg_installed');
        register_setting('rpg_settings', 'wpRPG_show_Tab_Title');
        register_setting('rpg_settings', 'wpRPG_show_Page_Titles');
        register_setting('rpg_settings', 'wpRPG_admin_enable_debug_funcs');
    }

    /**
     * Save the settings sent by the Options page
     * @since 1.0.0
     */
    public function wpRPG_SaveSettings() {
        $options = wp_load_alloptions();
        $debug_array = array('wpRPG_status_debug_reset_all', 'wpRPG_status_debug_reset_gold', 'wpRPG_status_debug_reset_xp', 'wpRPG_status_debug_reset_hp');
        foreach ($_POST as $group => $setting) {
            if (array_key_exists($group, $options)) {
                if (!in_array($group, $debug_array)) {
                    update_option($group, $setting);
                } else {
                    $this->wpRPG_DebugFuncs($group);
                }
            }
        }
    }

    /**
     * Check the tables and install when need be
     * @return bool True
     * @todo Test these tables to make sure they're up to snuff for this version
     * @since 1.0.0
     */
    public function wpRPG_check_tables() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_levels (
																	`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																	`min` int(11) unsigned NOT NULL DEFAULT '0',
																	`max` int(11) unsigned NOT NULL DEFAULT '100',
																	`group` varchar(50) NOT NULL DEFAULT '',
																	`title` varchar(50) NOT NULL DEFAULT ''
																	)";
        $wpdb->query($sql);
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_races (
																	`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																	`title` varchar(50) NOT NULL,
																	`strength` int(11) NOT NULL DEFAULT '0',
																	`gold` int(11) NOT NULL DEFAULT '0',
																    `defense` int(11) NOT NULL DEFAULT '0'
																	)";
        $wpdb->query($sql);
        $this->wpRPG_default_levels();
        return true;
    }

    /**
     * Get the rpg_meta for player.
     * @return object
     * @param int|string $user
     * @since 1.0.4
     */
    public function get_player_meta($user) {
        global $wpdb;
        if (!$user)
            return false;
        $username = '';
        if (!is_numeric($user)) {
            $user = $wpdb->get_var("Select ID from " . $wpdb->base_prefix . "users where user_nicename='" . $user . "'");
        }
        $player = new wpRPG_Player($user);
        return $player;
    }

    /**
     * Combines all WP_UserMeta with RPG_UserMeta for a super variable of usermeta
     * Used In get_player_meta
     * @param int userID (MUST BE INT)
     * @return object
     * @since 1.0.13
     * @deprecated since 1.0.15. No longer needed, just a wrapper for Player Class.
     */
    public function get_meta($user) {
        $player = new wpRPG_Player($user);
        return $player;
    }

    /**
     * Create the default levels during install
     * @since 1.0.0
     */
    public function wpRPG_default_levels() {
        global $wpdb;
        $wpdb->query("SELECT * FROM " . $wpdb->prefix . "rpg_levels");
        if (!$wpdb->num_rows) {
            $sql = "INSERT INTO " . $wpdb->prefix . "rpg_levels (`id` ,`min` ,`max` ,`group` ,`title`)VALUES ( NULL ,  '0',  '99',  'wpRPG_player_levels',  '1'), ( NULL ,  '100',  '199',  'wpRPG_player_levels',  '2'), ( NULL ,  '200',  '299',  'wpRPG_player_levels',  '3')";
            $wpdb->query($sql);
        }
    }

    /**
     * Check for the column of the db
     * @deprecated 1.0.0
     */
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

    /**
     * Get player's that are currently not playing and make them players
     * @since 1.0.1
     */
    public function wpRPG_get_current_users_activated() {
        global $wpdb;
        $user_ids = $wpdb->get_col("SELECT ID FROM $wpdb->users");
        foreach ($user_ids as $id) {
            $this->checkUserMeta($id);
        }
    }

    /**
     * Checks to make sure that each user has the default user_metas
     * @param int User ID
     */
    public function checkUserMeta($id) {
        $rpg_reg = new wpRPG_Registration; //Added in 1.0.1
        $default_usermeta = $rpg_reg->default_usermeta;
        foreach ($default_usermeta as $meta => $val) {

            if (!get_user_meta($id, $meta))
                update_user_meta($id, $meta, $val);
        }
        return true;
    }

    /**
     * Initialize the Settings Page
     * @since 1.0.0
     */
    public function wpRPG_settings_page_init() {
        $settings_page = add_menu_page(__('Wordpress RPG Options', 'wpRPG'), __(' WP-RPG', 'wpRPG'), 'manage_options', 'wpRPG_menu', array(
            $this,
            'wpRPG_settings_page'
        ));
        add_action("load-{$settings_page}", array(
            $this,
            'wpRPG_load_settings_page'
        ));
    }

    /**
     * Render the settings page and handle the Settings Submission Requests
     * @since 1.0.0
     */
    public function wpRPG_load_settings_page() {

        if (isset($_POST["wpRPG-settings-submit"]) && $_POST["wpRPG-settings-submit"] == 'Y') {
            check_admin_referer("wpRPG-settings-page");
            $this->wpRPG_SaveSettings();
            $url_parameters = isset($_GET['tab']) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
            wp_redirect(admin_url('admin.php?page=wpRPG_menu&' . $url_parameters));
            exit;
        }
    }

    /**
     * Creates Debug Functions to reset the game
     * @since 1.0.0
     */
    public function wpRPG_DebugFuncs($func) {
        global $wpdb;
        switch ($func) {
            case 'wpRPG_status_debug_reset_xp':
                $sql = "UPDATE " . $wpdb->prefix . "usermeta SET meta_value=0 WHERE meta_key='xp'";
                $wpdb->query($sql);
                break;
            case 'wpRPG_status_debug_reset_hp':
                $sql = "UPDATE " . $wpdb->prefix . "usermeta SET meta_value=100 WHERE meta_key='hp'";
                $wpdb->query($sql);
                break;
            case 'wpRPG_status_debug_reset_gold':

                $sql = "UPDATE " . $wpdb->prefix . "usermeta SET meta_value=500 WHERE meta_key='gold'";
                $wpdb->query($sql);
                $sql = "UPDATE " . $wpdb->prefix . "usermeta SET meta_value=0 WHERE meta_key='bank'";
                $wpdb->query($sql);
                break;
            case 'wpRPG_status_debug_reset_all':
                $this->wpRPG_DebugFuncs('wpRPG_status_debug_reset_xp');
                $this->wpRPG_DebugFuncs('wpRPG_status_debug_reset_hp');
                $this->wpRPG_DebugFuncs('wpRPG_status_debug_reset_gold');
                break;
        }
    }

    /**
     * Gets the correct template
     * @return string path to template
     * @param string $file filename of template
     * @since 1.0.4
     */
    public function render($file) {
        if (file_exists(get_stylesheet_directory_uri() . '/' . $file)) {
            return get_stylesheet_directory_uri() . '/' . $file;
        } else {
            return 'templates/' . $file;
        }
    }

    /**
     * HTML <Select> function for the Pages option | Not used elsewhere
     * @return string $html HTML code
     * @todo remove from core and/or make more generic
     * @since 1.0.0
     */
    public function wpRPG_get_pages_select_html($page) {
        $html = "<select name='wpRPG_" . $page . "_Page'>";
        $pages = $this->wpRPG_get_pages();
        $option = get_option("wpRPG_" . $page . "_Page");
        foreach ($pages as $page => $title) {
            $html .= "<option name='" . $title['id'] . "' value='" . $title['id'] . "' " . ( $option == $title['id'] ? 'selected=1' : '' ) . ">" . $title['title'] . "</option>";
        }
        $html .= "</select>";
        return $html;
    }

    /**
     * Get all of the pages in Wordpress
     * @return array 
     * @since 1.0.0
     */
    public static function wpRPG_get_pages() {
        $parry = array();
        $pages = get_pages();
        foreach ($pages as $page) {
            $new = $parry;

            $parry = array_merge($new, array(
                array(
                    'title' => $page->post_title,
                    'id' => $page->ID
                )
            ));
        }
        return $parry;
    }

    /**
     * Get Admin Page Settings
     * @return array
     * @since 1.0.13
     */
    public function wpRPG_get_pages_settings() {
        $pages = array();
        return apply_filters('wpRPG_add_pages_settings', $pages);
    }

    /**
     * Serve the wpRPG Settings page
     * @return string HTML code
     * @since 1.0.0
     */
    public function wpRPG_settings_page() {
        global $pagenow;
        $settings = get_option("wpRPG_theme_settings");
        $adminPages = new adminPages();
        ?>

        <div class="wrap">
            <h2>wpRPG Settings</h2>

            <?php
            if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated']))
                echo '<div class="updated" ><p>' . (isset($_GET['update_msg']) ? $_GET['update_msg'] : __('wpRPG updated.', 'wpRPG')) . '</p></div>';

            if (isset($_GET['tab']))
                $adminPages->wpRPG_admin_tabs($_GET['tab']);
            else
                $adminPages->wpRPG_admin_tabs('homepage');
            ?>

            <div id="poststuff">
                <form method="post" name="options">
                    <?php
                    settings_fields('rpg-settings');
                    wp_nonce_field("wpRPG-settings-page");

                    if ($pagenow == 'admin.php' && $_GET['page'] == 'wpRPG_menu') {

                        if (isset($_GET['tab']))
                            $tab = $_GET['tab'];
                        else
                            $tab = 'homepage';

                        echo '<table class="form-table">';
                        $tabs = $adminPages->wpRPG_get_admin_tabs();
                        //$tabs = $this->wpRPG_get_admin_tabs();
                        echo $tabs[$tab];
                        echo '</table>';
                    }
                    ?>
                    <p class="submit" style="clear: both;">
                        <input type="hidden" name="wpRPG-settings-submit" id="wpRPG-settings-submit" value="Y" />
                        <input type="submit" class="button-primary" value="<?php
                               _e('Save Changes');
                               ?>" />
                    </p>
                </form>

            </div>

        </div>
        <?php
    }

}
