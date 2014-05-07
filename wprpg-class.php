<?php
    /**
     * wpRPG
     */
	class wpRPG {
		
		/**
		 * Consrtuctor for wpRPG Class
		 * @since 1.0.0
		 */
        public function __construct( ) {
			global $current_user;
            add_action( 'wp_footer', array(
                 $this,
                'wpRPG_check_cron' 
            ) );
            $this->file_name    = __FILE__;
            $this->plug_version = '1.0.6';
            $this->plug_slug    = basename( dirname( __FILE__ ) );
            $this->wpRPG_load_shortcodes();
			$this->crons = array(
			);
            $this->default_tabs = array(
                 'homepage' => 'Home',
                'pages' => 'Pages',
                'cron' => 'Cron Info',
				'debug' => 'Debug Tab',
				'levels' => 'Level Manager'
            );
        }
        	
		/**
		 * Updates player's last active time
		 * @return DBQuery or False on failure
		 * @since 1.0.0
		 */
        public function updateLastActive( ) {
            global $wpdb, $current_user;
            if ( !$current_user->ID ) {
                return false;
            }
            if ( $this->wpRPG_is_playing( $current_user->ID ) ) {
                $last_active = $wpdb->get_var( "SELECT last_active from " . $wpdb->prefix . "rpg_usermeta WHERE pid = $current_user->ID" );
                if ( $last_active <= ( time() - 300 ) )
                    return $wpdb->query( "UPDATE " . $wpdb->prefix . "rpg_usermeta SET last_active = " . time() . " WHERE pid = $current_user->ID" );
            }
        }
        
		/**
		 * Get's online status of player by ID
		 * @param int $uid PlayerID
		 * @return bool Checks against last active within 5minutes
		 * @since 1.0.0
		 */
        public static function getOnlineStatus( $uid ) {
            global $wpdb;
            $time = time() - ( 60 * 5 );
            return (bool) $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "rpg_usermeta WHERE pid = $uid AND last_active > " . $time );
        }
        
		/**
		 * Get all players that are playing
		 * @return bool
		 * @since 1.0.0
		 */
        public function wpRPG_is_playing( $uid ) {
            global $wpdb;
            $sql = "SELECT xp, hp FROM " . $wpdb->prefix . "rpg_usermeta WHERE pid = %d";
            if ( $wpdb->get_row( $wpdb->prepare( $sql, $uid ) ) != null ) {
                return true;
                exit;
            }
            return false;
        }
        
		/**
		 * Get users by their ID
		 * @return bool|object Returns either boolean if no user is found or object of the user
		 * @since 1.0.0
		 */
        public static function wpRPG_get_user_by_id( $id ) {
            global $wpdb;
            $sql = "Select id, user_login from " . $wpdb->base_prefix . "users where id = %d";
            if ( $wpdb->get_row( $wpdb->prepare( $sql, $id ) ) != null ) {
                return $wpdb->get_row( $wpdb->prepare( $sql, $id ) );
            }
            return false;
        }
        
		/**
		 * Get player's rank by their id
		 * @return int Rank
		 * @since 1.0.0
		 */
        public static function wpRPG_player_rank( $id ) {
            global $wpdb;
            $sql_count = "SELECT * FROM " . $wpdb->prefix . "rpg_usermeta ORDER BY xp DESC";
            $res       = $wpdb->get_results( $sql_count );
            $rank      = 1;
            foreach ( $res as $item ) {
                if ( $item->id == $id ) {
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
        public static function wpRPG_player_level( $level ) {
            global $wpdb;
            $sql    = "SELECT title FROM " . $wpdb->prefix . "rpg_levels l WHERE l.group='wpRPG_player_levels' AND l.min <= " . $level . " Order By l.min DESC";
            $result = $wpdb->get_results( $sql );
            return $result[ 0 ]->title;
        }
		
		/**
		 * Replenish player's HP by x points
		 * @since 1.0.2
		 * @version 1.0.6
		 * @changelog added variable increment
		 */
        public function wpRPG_replenish_hp( ) {
            global $wpdb;
            $wpdb->show_errors();
			$hpinc = get_option('wpRPG_HP_Replenish_Increment');
            $sql = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET hp=hp+".$hpinc." WHERE hp<100";
            $wpdb->query( $sql );
			$sql2 = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET hp=hp-(hp-100) WHERE hp>100";
			$wpdb->query( $sql2);
        }
        
		/**
		 * Check the Cron and Execute when needed
		 * @since 1.0.2
		 */
        public function wpRPG_check_cron( ) {
			foreach($this->get_crons() as $cron => $info)
			{
				$last = get_option( 'wpRPG_last_'.$cron );
				if ( !empty( $last ) && $this->wpRPG_time_elapsed( $last, $info['duration'] ) ) {
					$i  = 1;
					$xs = $this->wpRPG_time_elapsed( $last, $info['duration'] );
					while ( $i++ <= $xs ) {
						if( class_exists( $info['class'] ) )
						{
							$class = new $info['class'];
							if( method_exists( $class, $info['func'] ) )
							{
								$class->$info['func']();
							}
						}
					}
					$next_t = ( time() - ( time() % $info['duration'] ) ) + $info['duration'];
					update_option( 'wpRPG_last_'.$cron, time() );
					update_option( 'wpRPG_next_'.$cron, $next_t );//$next_t
				}
			}
        }
        
		/**
		 * Get all the crons that are added
		 * @return array 
		 * @since 1.0.3
		 */
		public function get_crons()
		{
			$crons       = $this->crons;
            $new_crons = apply_filters( 'wpRPG_add_crons', $crons );
            return $new_crons;
		}
		
		/**
		 * Get the time that's elapsed by the seconds
		 * @return int 
		 * @param int $secs
		 * @since 1.0.0
		 */
        public function wpRPG_time_elapsed( $secs, $duration ) {
            return round( ( time() - $secs ) / ( $duration ) );
        }
        
		/**
		 * Load shortcodes
		 * Intentionally left empty
		 * @since 1.0.0
		 */
        public function wpRPG_load_shortcodes( ) {
            
        }
        
        /**
		 * Execute these functions during Plugin Execution 
		 * @since 1.0.0
		 */
        public function wpRPG_on_activation( ) {
            if ( !current_user_can( 'activate_plugins' ) )
                return;
            $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : '';
            check_admin_referer( "activate-plugin_{$plugin}" );
            $this->wpRPG_check_tables();
            $this->wpRPG_get_current_users_activated();
            return;
        }
        
		/**
		 * Execute these function during Plugin Deactivation
		 * @since 1.0.0
		 */
        public function wpRPG_on_deactivation( ) {
            if ( !current_user_can( 'activate_plugins' ) )
                return;
            $plugin = isset( $_REQUEST[ 'plugin' ] ) ? $_REQUEST[ 'plugin' ] : '';
            check_admin_referer( "deactivate-plugin_{$plugin}" );
        }
        
		/**
		 * Register and create these settings
		 * @since 1.0.0
		 * @version 1.0.6
		 * @changelog Added variable hp replenish
		 */
        public function wpRPG_RegisterSettings( ) {
			foreach($this->get_crons() as $cron => $info)
			{
				if ( get_option( 'wpRPG_last_'.$cron ) == null ) {
					update_option( 'wpRPG_last_'.$cron, time() );
				} elseif ( get_option( 'wpRPG_last_'.$cron ) == false ) {
					add_option( 'wpRPG_last_'.$cron, time(), "", "yes" );
				}
				if ( get_option( 'wpRPG_next_'.$cron ) == false ) {
					add_option( 'wpRPG_next_'.$cron, ( time() - ( time() % $info['duration'] ) ) + $info['duration'], "", "yes" );
				}
				if ( get_option( 'wpRPG_status_'.$cron ) == false ) {
					add_option ( 'wpRPG_status_'.$cron, ( isset($info['status']) ? $info['status'] : true), '', 'yes' );
				}
				register_setting( 'rpg_settings', 'wpRPG_last_'.$cron );
				register_setting( 'rpg_settings', 'wpRPG_next_'.$cron );
			}
			
            add_option( 'show_wpRPG_Version_footer', "0", "", "yes" );
            register_setting( 'rpg_settings', 'show_wpRPG_Version_footer' );
            add_option( 'wpRPG_rpg_installed', "0", "", "yes" );
			add_option( 'wpRPG_status_debug_reset_xp', '0', '', 'yes' );
			add_option( 'wpRPG_status_debug_reset_hp', '0', '', 'yes' );
			add_option( 'wpRPG_status_debug_reset_lvls', '0', '', 'yes' );
			add_option( 'wpRPG_status_debug_reset_all', '0', '', 'yes' );
			register_setting( 'rpg_settings', 'wpRPG_status_debug_reset_xp');
			register_setting( 'rpg_settings', 'wpRPG_status_debug_reset_hp');
			register_setting( 'rpg_settings', 'wpRPG_status_debug_reset_lvls');
			register_setting( 'rpg_settings', 'wpRPG_status_debug_reset_all');
            register_setting( 'rpg_settings', 'show_wpRPG_Version_footer' );
            register_setting( 'rpg_settings', 'wpRPG_rpg_installed' );
        }
        
		/**
		 * Save the settings sent by the Options page
		 * @since 1.0.0
		 */
        public function wpRPG_SaveSettings( ) {
			$options = wp_load_alloptions();
			$debug_array = array('wpRPG_status_debug_reset_all', 'wpRPG_status_debug_reset_lvls', 'wpRPG_status_debug_reset_xp', 'wpRPG_status_debug_reset_hp',);
            foreach ( $_POST as $group => $setting ) {
				
				if ( array_key_exists($group, $options)) {
					if ( array_key_exists($group, $debug_array) ){
						update_option( $group, $setting );
					}else{
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
        public function wpRPG_check_tables( ) {
            global $wpdb;
            $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_usermeta (
																	`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																	`pid` int(11) unsigned NOT NULL,
																	`last_active` int(11) unsigned default '0',
																	`xp` int(11) unsigned default '0',
																	`hp` int(11) unsigned default '20',
																	`defense` int(11) unsigned NOT NULL default '10',
																	`strength` int(11) unsigned default '5',
																	`gold` int(11) unsigned default '500',
																	`bank` int(11) unsigned default '0',
																	`race` int(11) unsigned default '1'
																	)";
            $wpdb->query( $sql );
            $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_levels (
																	`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																	`min` int(11) unsigned NOT NULL DEFAULT '0',
																	`max` int(11) unsigned NOT NULL DEFAULT '100',
																	`group` varchar(50) NOT NULL DEFAULT '',
																	`title` varchar(50) NOT NULL DEFAULT ''
																	)";
            $wpdb->query( $sql );
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_races (
																	`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																	`title` varchar(50) NOT NULL,
																	`strength` int(11) NOT NULL DEFAULT '0',
																	`gold` int(11) NOT NULL DEFAULT '0',
																    `defense` int(11) NOT NULL DEFAULT '0'
																	)";
            $wpdb->query( $sql );
            $this->wpRPG_default_levels();
			$this->default_races();
            return true;
        }
        
		/**
		 * Create the default races during install
		 * @since 1.0.1
		 */
        public function default_races( ) {
            global $wpdb;
            $wpdb->query( "SELECT * FROM " . $wpdb->prefix . "rpg_races" );
            if ( !$wpdb->num_rows ) {
                $sql = "INSERT INTO " . $wpdb->prefix . "rpg_races (`id` ,`title` ,`strength` ,`gold` ,`defense`)VALUES ( NULL ,  'human',  '10',  '5',  '10'), ( NULL ,  'orc',  '15',  '0',  '10'), ( NULL ,  'Elf',  '10',  '10',  '5')";
                $wpdb->query( $sql );
            }
        } 		
		/**
		 * Get the rpg_meta for player
		 * @return object
		 * @param int|string $user
		 * @since 1.0.4
		 */
		public function get_player_meta( $user ) {
			global $wpdb;
			$username='';
			if(!is_numeric($user)){
				$username = $user;
				$user = 0;
			}
			$sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND u.id=" .$user . " OR u.user_nicename='" . $username . "'";
            
			return $wpdb->get_results( $sql );
		}
		
		/**
		 * Create the default levels during install
		 * @since 1.0.0
		 */
        public function wpRPG_default_levels( ) {
            global $wpdb;
            $wpdb->query( "SELECT * FROM " . $wpdb->prefix . "rpg_levels" );
            if ( !$wpdb->num_rows ) {
                $sql = "INSERT INTO " . $wpdb->prefix . "rpg_levels (`id` ,`min` ,`max` ,`group` ,`title`)VALUES ( NULL ,  '0',  '99',  'wpRPG_player_levels',  '1'), ( NULL ,  '100',  '199',  'wpRPG_player_levels',  '2'), ( NULL ,  '200',  '299',  'wpRPG_player_levels',  '3')";
                $wpdb->query( $sql );
            }
        }
        
		/**
		 * Check for the column of the db
		 * @deprecated 1.0.0
		 */
        public function wpRPG_check_column( $table, $col_name ) {
            global $wpdb;
            if ( $table != null ) {
                $results = $wpdb->get_results( "DESC $table" );
                if ( $results != null ) {
                    foreach ( $results as $row ) {
                        if ( $row->Field == $col_name ) {
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
        public function wpRPG_get_current_users_activated( ) {
            global $wpdb;
			$rpg_reg = new wpRPG_Registration; //Added in 1.0.1
            //$wpdb->show_errors(); // Removed in 1.0.1
            $user_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->users" );
            foreach ( $user_ids as $id ) {
                if ( $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "rpg_usermeta WHERE pid = " . $id ) == null ) {
					$rpg_reg->user_register( $id );
                }
            }
        }
        
		/**
		 * Initialize the Settings Page
		 * @since 1.0.0
		 */
        public function wpRPG_settings_page_init( ) {
            $settings_page = add_menu_page( 'Wordpress RPG Options', ' WP-RPG', 'manage_options', 'wpRPG_menu', array(
                 $this,
                'wpRPG_settings_page' 
            ) );
            add_action( "load-{$settings_page}", array(
                 $this,
                'wpRPG_load_settings_page' 
            ) );
        }
        
		/**
		 * Render the settings page and handle the Settings Submission Requests
		 * @since 1.0.0
		 */
        public function wpRPG_load_settings_page( ) {
			
            if ( isset( $_POST[ "wpRPG-settings-submit" ] ) && $_POST[ "wpRPG-settings-submit" ] == 'Y' ) {
                check_admin_referer( "wpRPG-settings-page" );
				//wp_die('hi');
                $this->wpRPG_SaveSettings();
				$url_parameters = isset( $_GET[ 'tab' ] ) ? 'updated=true&tab=' . $_GET[ 'tab' ] : 'updated=true';
                wp_redirect( admin_url( 'admin.php?page=wpRPG_menu&' . $url_parameters ) );
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
					$sql = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET xp=0";
					$wpdb->query( $sql );
					break;
				case 'wpRPG_status_debug_reset_hp':
					$sql = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET hp=100";
					$wpdb->query( $sql );
					break;
				case 'wpRPG_status_debug_reset_all':
					$sql = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET hp=100, xp=0, gold=0";
					$wpdb->query( $sql );
					break;
			}
		}
		
		/**
		 * Get the Admin Tab headers
		 * @since 1.0.0
		 */
        public function wpRPG_get_admin_tab_header( ) {
            $tabs       = $this->default_tabs;
            $admin_tabs = apply_filters( 'wpRPG_add_admin_tab_header', $tabs );
            return $admin_tabs;
        }
        
		/**
		 * Grab the initial default tabs
		 * @param string $tab Name of the tab being grabbed
		 * @todo Make this be external templates
		 * @since 1.0.0
		 */
        public function wprpg_default_tabs( $tab ) {
			global $wpdb;
			$content_block_starts = '<tr><td>';
			$content_block_ends = '</td></tr>';
            switch ( $tab ) {
                case 'cron':
                    $html = "<tr>";
                    $html .= "<td>";
                    $html .= "<h3>Cron Information</h3>";
                    $html .= "</td>";
                    $html .= "</tr>";
					$html .= "<table class='form-table' border=1>";
					$html .= "<tr><th>Name</th><th>Last Execution</th><th>Pending Executions</th><th>Next Execution</th><th>Actions</th>";
                    foreach($this->get_crons() as $cron => $info)
					{
					$html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<h4 class='description'>$cron Cron:</h4>";
                    $html .= "</td>";
                    $html .= "<td>";
                    $html .= "<span> " . date( 'Y-m-d H:i:s', get_option( 'wpRPG_last_'.$cron ) ) . "</span>";
                    $html .= "</td>";
                    $html .= "<td>";
                    $html .= "<span> " . $this->wpRPG_time_elapsed( get_option( 'wpRPG_last_'.$cron ), $info['duration'] ) . "</span>";
                    $html .= "</td>";
                    $html .= "<td>";
                    $html .= "<span> " . date( 'Y-m-d H:i:s', get_option( 'wpRPG_next_'.$cron ) ) . "</span>";
                    $html .= "</td>";
					$html .= "<td>";
                    $html .= "<span> <input type='hidden' name='wpRPG_status_$cron' id='wpRPG_status_$cron' value='0' />
										Enabled?: <input name='wpRPG_status_$cron' id='wpRPG_status_$cron' type='checkbox' " . 
										(get_option( 'wpRPG_status_'.$cron )? 'checked':'') . " value=1></span>";
                    $html .= "</td>";
                    $html .= "</tr>";
					}
					$html .= "</table>";
					$html .= date('Y-m-d H:i:s');
                    return $html;
                    break;                    
                case 'homepage':
                    $html = "<tr>";
                    $html .= "<td>";
                    $html .= "<h3>Welcome to Wordpress RPG!</h3>";
                    $html .= "</td>";
                    $html .= "</tr>";
                    $html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<span class='description'>Total Players:</span>";
                    $users = count_users();
                    $html .= "<span>" . $users[ 'total_users' ] . "</span>";
                    $html .= "</td>";
                    $html .= "</tr>";
					$html .= "<tr>";
					$html .= "<td>";
					$html .= "Thank you for using Wordpress RPG!<br/>";
					$html .= "As you can see, this is still a work in progress with alot to be done. While this is a working plugin, it's not finalized as of yet, but growing more and more capable by each version.";
					$html .= "<h4>Todo immediately:</h4>";
					$html .= "<ul><li>- Actions are coming more and more to light. Create a tab to list all actions, filters, and shortcodes available</li>
					<li>- Add a tab for Race edits and expand on Race bonuses</li>
					<li>- Add options to disable the internal plugins like Registration/Profile/Members, or create an option to allow admin to select what to use as default plugin. (ie. Use default Profile, wpRPG default, or External Profile Plugin).</li></ul>";
					$html .= "</td>";
					$html .= "</tr>";					
					$html .= "<tr>";
					$html .= "<td>";
					$html .= "<span class='description'>Show link love?:</span>";
					$html .= "<span><input type='hidden' name='show_wpRPG_Version_footer' id='show_wpRPG_Version_footer' value='0' />
									<input type='checkbox' " . ( get_option ( 'show_wpRPG_Version_footer' ) ? 'checked ' : '' ) . " name='show_wpRPG_Version_footer' id='show_wpRPG_Version_footer' value='1'>We'd appreciate it!<br></span>";
					$html .= "</td>";
					$html .= "</tr>";
                    return $html;
                    break;
                case 'pages':
                    $html = "<tr>";
                    $html .= "<td>";
                    $html .= "<h3>Pages!</h3>";
                    $html .= "</td>";
                    $html .= "</tr>";
                    $html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<table border=1><tr><th>Module Page</th><th>Wordpress Page</th></tr>";
                    $html .= "<tr><td>Profile</td><td>" . $this->wpRPG_get_pages_select_html() . "</td></tr>";
                    $html .= "</table>";
                    $html .= "</td>";
                    $html .= "</tr>";
                    return $html;
                    break;
				case 'debug':
					$html = "<tr>";
					$html .= "<td>";
                    $html .= "<h3>Debug. Admin Functions</h3>";
                    $html .= "</td>";
                    $html .= "</tr>";
                    $html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<table border=1><tr><th>Function</th><th>Action</th></tr>";
                    $html .= "<tr><td>Reset XP</td><td><input type='checkbox' name='wpRPG_status_debug_reset_xp' id='wpRPG_status_debug_reset_xp' value='0' />Reset XP</td></tr>";
					$html .= "<tr><td>Reset HP</td><td><input type='checkbox' name='wpRPG_status_debug_reset_hp' id='wpRPG_status_debug_reset_hp' value='0' />Reset HP</td></tr>";
					$html .= "<tr><td>Reset Everything</td><td><input type='checkbox' name='wpRPG_status_debug_reset_all' id='wpRPG_status_debug_reset_all' value='0' />Reset Everything</td></tr>";
                    $html .= "</table>";
                    $html .= "</td>";
                    $html .= "</tr>";
					return $html;
					break;
				case 'levels':
					$html = "<tr>";
					$html .= "<td>";
                    $html .= "<h3>Level Manager</h3>";
                    $html .= "</td>";
                    $html .= "</tr>";
                    $html .= "<tr>";
                    $html .= "<td>";
					$html .= "<table border=1 id='responds'><thead><tr><th>Level</th><th>Minimum Lvl</th><th>Delete</th></tr></thead><tbody>";
					$html .= "<div class='content_wrapper'>";
							$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_levels ORDER BY min";
							//get all records from add_delete_record table
							foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
							{
								$html .= '<tr id="item_'.$row["id"].'"><td>'.$row["title"].'</td>';
								$html .= '<td>'.$row["min"].'</td>';
								$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
								$path = plugins_url('images/icon_del.gif', __FILE__);
								$html .= '<img src="'.$path .'" border="0" />';
								$html .= '</a></div></td></tr>';
							}
								$html .= "</tbody><tfoot><tr><td><input type='text' name='title_txt' id='title_txt' /></td><td><input type='text' name='min_txt' id='min_txt' /></td><td><button id='FormSubmit'>Add record</button></td></tr><tfoot></table>
											
											</div>
											</div>";
                    $html .= "</td>";
                    $html .= "</tr>";
					return $html;
					break;
            }
        }
		
		/**
		 * Gets the correct template
		 * @return string path to template
		 * @param string $file filename of template
		 * @since 1.0.4
		 */
		public function render ( $file )
		{
			if (file_exists(get_stylesheet_directory_uri() . '/'. $file))
			{
				return get_stylesheet_directory_uri() . '/'. $file ;
			} else {
				return 'templates/'. $file ;
			}
		}
		
		/**
		 * HTML <Select> function for the Pages option | Not used elsewhere
		 * @return string $html HTML code
		 * @todo remove from core and/or make more generic
		 * @since 1.0.0
		 */
        public function wpRPG_get_pages_select_html( ) {
            $html  = "<select name='wpRPG_Profile_Page'>";
            $pages = $this->wpRPG_get_pages();
            foreach ( $pages as $page => $title ) {
                $html .= "<option name='" . $title[ 'id' ] . "' value='" . $title[ 'id' ] . "' " . ( get_option( 'wpRPG_Profile_Page' ) == $title[ 'id' ] ? 'selected=1' : '' ) . ">" . $title[ 'title' ] ."</option>";
            }
            $html .= "</select>";
            return $html;
        }
        
		/**
		 * Get all of the pages in Wordpress
		 * @return array 
		 * @since 1.0.0
		 */
        public static function wpRPG_get_pages( ) {
            $parry = array( );
            $pages = get_pages();
            foreach ( $pages as $page ) {
                $new = $parry;
                
                $parry = array_merge( $new, array(
                     array(
                         'title' => $page->post_title,
                        'id' => $page->ID 
                    ) 
                ) );
            }
            return $parry;
        }
        
		/**
		 * Get Admin Tabs that were made by default and by other plugins
		 * @return array
		 * @since 1.0.0
		 */
        public function wpRPG_get_admin_tabs( ) {
            $tabs       = array(
                 'cron' => $this->wprpg_default_tabs( 'cron' ),
                'homepage' => $this->wprpg_default_tabs( 'homepage' ),
                'pages' => $this->wprpg_default_tabs( 'pages' ),
				'debug' => $this->wprpg_default_tabs( 'debug' ),
				'levels' => $this->wprpg_default_tabs( 'levels' )
				
            );
            $admin_tabs = apply_filters( 'wpRPG_add_admin_tabs', $tabs );
            return $admin_tabs;
        }
        
		/**
		 * Get the current tab
		 * @param string name of the tab
		 * @return string HTML code
		 * @since 1.0.0
		 */
        public function wpRPG_admin_tabs( $current = 'homepage' ) {
            $tabs  = $this->wpRPG_get_admin_tab_header();
            $links = array( );
            echo '<div id="icon-themes" class="icon32"><br></div>';
            echo '<h2 class="nav-tab-wrapper">';
            foreach ( $tabs as $tab => $name ) {
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab$class' href='?page=wpRPG_menu&tab=$tab'>$name</a>";
            }
            echo '</h2>';
        }
        
		/**
		 * Serve the wpRPG Settings page
		 * @return string HTML code
		 * @since 1.0.0
		 */
        public function wpRPG_settings_page( ) {
            global $pagenow;
            $settings = get_option( "wpRPG_theme_settings" );
?>

			<div class="wrap">
				<h2>wpRPG Settings</h2>

			<?php
            if ( isset( $_GET[ 'updated' ] ) && 'true' == esc_attr( $_GET[ 'updated' ] ) )
                echo '<div class="updated" ><p>wpRPG updated.</p></div>';
            
            if ( isset( $_GET[ 'tab' ] ) )
                $this->wpRPG_admin_tabs( $_GET[ 'tab' ] );
            else
                $this->wpRPG_admin_tabs( 'homepage' );
?>

				<div id="poststuff">
					<form method="post" name="options">
			<?php
            settings_fields( 'rpg-settings' );
            wp_nonce_field( "wpRPG-settings-page" );
            
            if ( $pagenow == 'admin.php' && $_GET[ 'page' ] == 'wpRPG_menu' ) {
                
                if ( isset( $_GET[ 'tab' ] ) )
                    $tab = $_GET[ 'tab' ];
                else
                    $tab = 'homepage';
                
                echo '<table class="form-table">';
                $tabs = $this->wpRPG_get_admin_tabs();
                echo $tabs[ $tab ];
                echo '</table>';
            }
?>
						<p class="submit" style="clear: both;">
							<input type="hidden" name="wpRPG-settings-submit" id="wpRPG-settings-submit" value="Y" />
							<input type="submit" class="button-primary" value="<?php
            _e( 'Save Changes' );
?>" />
						</p>
					</form>

				</div>

			</div>
			<?php
        }
    }
	
?>