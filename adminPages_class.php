<?php

	class adminPages{
		public function __construct(){
			$this->wpRPG = new wpRPG();
			$this->default_tabs = array( //Default Admin Tabs. [keyword] => [Page Title]
					'homepage' => 'Home',
					'pages' => 'Pages',
					'cron' => 'Cron Info',
					'levels' => 'Level Manager',
					'plugins' => 'Module Manager'
            );
			if(get_option ( 'wpRPG_admin_enable_debug_funcs' )){
				$this->default_tabs = array_merge($this->default_tabs, array('debug'=>'Debug Tab'));
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
			$html = '';
            switch ( $tab ) {
                case 'cron':
					if ( get_option( 'wpRPG_show_Tab_Title') ) {
						$html = "<tr>";
						$html .= "<td>";
						$html .= "<h3>Cron Information</h3>";
						$html .= "</td>";
						$html .= "</tr>";
					}
					$html .= "<table class='form-table' id='responds' border=1>";
					$html .= "<tr><th>Name</th><th>Last Execution</th><th>Pending Executions</th><th>Next Execution</th><th>Actions</th></tr>";
                    foreach($this->wpRPG->get_crons() as $cron => $info)
					{
					$html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<h4 class='description'>$cron Cron:</h4>";
                    $html .= "</td>";
                    $html .= "<td>";
                    $html .= "<span> " . date( 'Y-m-d H:i:s', get_option( 'wpRPG_last_'.$cron ) ) . "</span>";
                    $html .= "</td>";
                    $html .= "<td>";
                    $html .= "<span> " . $this->wpRPG->wpRPG_time_elapsed( get_option( 'wpRPG_last_'.$cron ), $info['duration'] ) . "</span>";
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
                    if ( get_option( 'wpRPG_show_Tab_Title') ) {
						$html = "<tr>";
						$html .= "<td>";
						$html .= "<h3>Welcome to Wordpress RPG!</h3>";
						$html .= "</td>";
					    $html .= "</tr>";
					}
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
					$html .= "<tr><td>";
					$html .= "<span class='description'>Enable Debug Functions?:</span>";
					$html .= "<span><input type='hidden' name='wpRPG_admin_enable_debug_funcs' id='wpRPG_admin_enable_debug_funcs' value='0' />
									<input type='checkbox' " . ( get_option ( 'wpRPG_admin_enable_debug_funcs' ) ? 'checked ' : '' ) . " name='wpRPG_admin_enable_debug_funcs' id='wpRPG_admin_enable_debug_funcs' value='1'>Shows Advance Admin functions for Game Debugging!<br></span>";
                    return $html;
                    break;
                case 'pages':
					if ( get_option( 'wpRPG_show_Tab_Title') ) {
						$html = "<tr>";
						$html .= "<td>";
						$html .= "<h3>Pages!</h3>";
						$html .= "</td>";
						$html .= "</tr>";
					}
                    $html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<table border=1><tr><th>Module Page</th><th>Shortcode</th><th>Wordpress Page</th></tr>";
                    foreach($this->wpRPG->wpRPG_get_pages_settings() as $page=>$setting){
						$html .= "<tr><td>".$setting['name']."</td><td>".$setting['shortcode']."</td><td>" . $this->wpRPG->wpRPG_get_pages_select_html( $page ) . "</td></tr>";
                    }
					$html .= "</table>";
					$html .= "<table border=1><tr><th>Settings Name</th><th>Setting Value</th></tr>";
					$html .= "<tr><td>Show Admin Tab Titles</td><td><select name='wpRPG_show_Tab_Title' id='wpRPG_show_Tab_Title'><option name='wpRPG_show_Tab_Title' id='wpRPG_show_Tab_Title' ". (get_option('wpRPG_show_Tab_Title')? 'selected' : '') ." value=1>Show</option><option name='wpRPG_show_Tab_Title' id='wpRPG_show_Tab_Title' ". (!get_option('wpRPG_show_Tab_Title')? 'selected' : '') ." value=0>Hide</option></select> ";
					$html .= "</td></tr>";
					$html .= "</table>";
                    $html .= "</td>";
                    $html .= "</tr>";
                    return $html;
                    break;
				case 'debug':
					if ( get_option( 'wpRPG_show_Tab_Title') ) {
						$html = "<tr>";
						$html .= "<td>";
						$html .= "<h3>Debug. Admin Functions</h3>";
						$html .= "</td>";
						$html .= "</tr>";
					}
                    $html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<table border=1><tr><th>Function</th><th>Action</th></tr>";
                    $html .= "<tr><td>Reset XP</td><td><input type='checkbox' name='wpRPG_status_debug_reset_xp' id='wpRPG_status_debug_reset_xp' value='0' />Reset XP</td></tr>";
					$html .= "<tr><td>Reset HP</td><td><input type='checkbox' name='wpRPG_status_debug_reset_hp' id='wpRPG_status_debug_reset_hp' value='0' />Reset HP</td></tr>";
					$html .= "<tr><td>Reset Gold</td><td><input type='checkbox' name='wpRPG_status_debug_reset_gold' id='wpRPG_status_debug_reset_gold' value='0' />Reset Gold</td></tr>";
					$html .= "<tr><td>Reset Everything</td><td><input type='checkbox' name='wpRPG_status_debug_reset_all' id='wpRPG_status_debug_reset_all' value='0' />Reset Everything</td></tr>";
                    $html .= "</table>";
                    $html .= "</td>";
                    $html .= "</tr>";
					return $html;
					break;
				case 'levels':
					if ( get_option( 'wpRPG_show_Tab_Title') ) {
						$html = "<tr>";
						$html .= "<td>";
						$html .= "<h3>Level Manager</h3>";
						$html .= "</td>";
						$html .= "</tr>";
					}
                    $html .= "<tr>";
                    $html .= "<td>";
					$html .= "<table border=1 id='responds'><thead><tr><th>Level</th><th>Minimum Lvl</th><th>Delete</th></tr></thead><tbody>";
					$html .= "<input type='hidden' id='wprpg_levels' name='wprpg_levels' value=1 /><div class='content_wrapper'>";
							$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_levels ORDER BY min";
							//get all records from add_delete_record table
							foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
							{
								$html .= '<tr id="item_'.$row["id"].'"><td>'.$row["title"].'</td>';
								$html .= '<td>'.$row["min"].'</td>';
								$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
								$path = plugins_url('images/icon_delete.gif', __FILE__);
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
				case 'races':
					if ( get_option( 'wpRPG_show_Tab_Title') ) {
						$html = "<tr>";
						$html .= "<td>";
						$html .= "<h3>Level Manager</h3>";
						$html .= "</td>";
						$html .= "</tr>";
					}
					$html .= "<tr><td><span class='description'>Races currently isn't in use yet. But here are a list of them right now</span></td></tr>";
					$html .= "<tr>";
                    $html .= "<td>";
					$html .= "<table border=1 id='responds'><thead><tr><th>Race</th><th>Strength</th><th>Defense</th><th>Gold</th><th>Delete</th></tr></thead><tbody>";
					$html .= "<div class='content_wrapper'>";
							$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_races";
							//get all records from add_delete_record table
							foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
							{
								$html .= '<tr id="item_'.$row["id"].'"><td>'.$row["title"].'</td>';
								$html .= '<td>'.$row["strength"].'</td>';
								$html .= '<td>'.$row["defense"].'</td>';
								$html .= '<td>'.$row["gold"].'</td>';
								$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
								$path = plugins_url('images/icon_del.gif', __FILE__);
								$html .= '<img src="'.$path .'" border="0" />';
								$html .= '</a></div></td></tr>';
							}
								$html .= "</tbody><tfoot><tr><td><input type='text' name='title_txt' id='title_txt' /></td><td><input type='text' name='strength_txt' id='strength_txt' value=0 /></td><td><input type='text' name='defense_txt' id='defense_txt' value=0 /></td><td><input type='text' name='gold_txt' id='gold_txt' value=0 /></td><td><input type='hidden' id='wprpg_races' name='wprpg_races' value=1 /><button id='FormSubmit'>Add Race</button></td></tr><tfoot></table>
											
											</div>
											</div>";
                    $html .= "</td>";
                    $html .= "</tr>";
					return $html;
					break;
				case 'plugins':
					if ( get_option( 'wpRPG_show_Tab_Title') ) {
						$html = "<tr>";
						$html .= "<td>";
						$html .= "<h3>Module Manager</h3>";
						$html .= "</td>";
						$html .= "</tr>";
					}
					$html .= "<table class='form-table' id='responds' border=1>";
					$html .= "<tr><th>Name</th><th>Description</th><th>Author</th><th>Actions</th></tr>";
					$module = new wprpgModules();
                    foreach($module->getModules() as $plugin => $info)
					{
					$html .= "<tr>";
                    $html .= "<td>";
                    $html .= "<h4 class='description'>". $info['name'].' ' . $info['version'] . "</h4>";
                    $html .= "</td>";
                    $html .= "<td>";
                    $html .= "<span>".$info['description']."</span>";
                    $html .= "</td>";
                    $html .= "<td>";
                    $html .= "<span>".$info['author']."</span>";
                    $html .= "</td>";
					$html .= "<td>";
                    $html .= "<span> <input type='hidden' name='wpRPG_plugin_status_$plugin' id='wpRPG_plugin_status_$plugin' value='0' />
										Enabled?: <input name='wpRPG_plugin_status_$plugin' id='wpRPG_plugin_status_$plugin' type='checkbox' " . 
										(get_option( 'wpRPG_plugin_status_'.$plugin )? 'checked':'') . " value=1><br /><a href='?page=wpRPG_menu&tab=$plugin'>Settings</a></span>";
                    $html .= "</td>";
                    $html .= "</tr>";
					}
					$html .= "</table>";
                    return $html;
                    break;           
            }
        }
		
		/**
		 * Get Admin Tabs that were made by default and by other plugins
		 * @return array
		 * @since 1.0.0
		 */
        public function wpRPG_get_admin_tabs( ) {
			$tabs = array();
			foreach ($this->default_tabs as $tab => $val){
					$tabs = array_merge($tabs, array($tab => $this->wprpg_default_tabs($tab)));
			}
            return apply_filters( 'wpRPG_add_admin_tabs', $tabs );
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
	
	}