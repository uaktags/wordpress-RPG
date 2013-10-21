<?php
/*
   Plugin Name: WP RPG
   Plugin URI: http://wordpress.org/extend/plugins/wp-rpg/
   Version: 0.0.1
   Author: <a href="http://tagsolutions.tk">Tim G.</a>
   Description: RPG Elements added to WP
   Text Domain: wp-rpg
   License: GPL3
  */

/*

    This following part of this file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see <http://www.gnu.org/licenses/>.
*/
        /////////////////////
		/// File Includes ///
		/////////////////////
		include_once('functions.install.php');
        
        //////////////////////////////////
        // Start Options Page
        /////////////////////////////////
        
        /*
         * Options Page
         * Actual WPRPG Options Page
        */
        function wp_rpg_options() {
                global $wpdb, $current_user; 
				echo 'Test';
                //include_once('wp-rpg-admin.php');
        }

        /*
         * Creates Menu Page
        */
        function add_rpg_to_admin_menu()
        {
                add_menu_page( 'Wordpress RPG Options', 'WP-RPG', 'manage_options', 'wp_rpg_menu', 'wp_rpg_options' );
        }

        
        //////////////////////////////////
        // End Options Page
        //////////////////////////////////
        
        //////////////////////////////////
        // Actions and Hooks
        //////////////////////////////////
        register_activation_hook(   __FILE__, 'WpRPG_on_activation' );
        register_deactivation_hook( __FILE__, 'WpRPG_on_deactivation' );
        register_uninstall_hook(    __FILE__, 'WpRPG_on_uninstall' );
        
        add_action('user_register', 'WpRPG_user_register');
        add_action('admin_menu', 'add_rpg_to_admin_menu');
        
        
        //////////////////////////////////
        // End Actions And Hooks
        //////////////////////////////////
        
        //////////////////////////////////
        // RPG Functions
        //////////////////////////////////
        function WpRPG_is_playing($uid){
                global $wpdb, $current_user; 
                $sql = "SELECT xp, hp, level FROM ".$wpdb->base_prefix."rpg_usermeta WHERE uid = %d";
                if ( $wpdb->get_row($wpdb->prepare($sql, $uid)) != null){
                 return true;
                 exit;
                }
                 return false;
        }
        if( ! is_admin() ){
				add_action( 'wp_enqueue_scripts', 'include_jquery' );
                add_action('wp_footer', 'RPG_js_for_attacks');
                if(isset($_POST['attacking']) && $_POST['attacking'] == 1){
				    WpRPG_Attack($_POST['attacker'], $_POST['defender']);
                }else{
					add_action( 'wp_enqueue_scripts', 'include_jquery' );
				    add_action('wp_footer', 'RPG_js_for_attacks');
                }
                
        }
        
        function include_jquery()
		{
			wp_enqueue_script( 'jquery' );
		}
		
        function get_user_by_id($id){
                global $wpdb;
                $sql = "Select id, user_login from ".$wpdb->base_prefix."users where id = %d";
                if ( $wpdb->get_row($wpdb->prepare($sql, $id)) != null ) {
                        return $wpdb->get_row($wpdb->prepare($sql, $id));
                }
                return false;
        }
        function WpRPG_Attack($attacker, $defender){
                global $wpdb;
                $attack = array();
                $defend = array();
                $attack['sql'] = "SELECT xp, hp, level, strength, defense FROM ".$wpdb->base_prefix."rpg_usermeta WHERE uid = %d";
                if ( $attack['result'] = $wpdb->get_row($wpdb->prepare($attack['sql'], $attacker)) != null){
                                $attack['result'] = $wpdb->get_row($wpdb->prepare($attack['sql'], $attacker));
                }
                $defend['sql'] = "SELECT xp, hp, level, strength, defense FROM ".$wpdb->base_prefix."rpg_usermeta WHERE uid = %d";
                
                if ( $defend['result'] = $wpdb->get_row($wpdb->prepare($defend['sql'], $defender)) != null){
                                $defend['result'] = $wpdb->get_row($wpdb->prepare($defend['sql'], $defender));
                }                
                $attack['score'] = calculate_scores($attack['result']->xp, $attack['result']->hp, $attack['result']->level, $attacker);
                $defend['score'] = calculate_scores($defend['result']->xp, $defend['result']->hp, $defend['result']->level, $defender);
                //echo $attack['score'] . " : " . $defend['score']; die;
                if ( $attack['score'] >= $defend['score'] ){ //attacker wins
                        $defend['min'] = max(0, ($defend['result']->strength + $defend['result']->defense) - 3);
                        $defend['max'] = $defend['result']->strength + $defend['result']->defense + 2;
                        $defend['damage'] = rand($defend['min'], $defend['max']);
                        $attack['min'] = max(0, ($attack['result']->strength + $attack['result']->defense) - 3);
                        $attack['max'] = $attack['result']->strength + $attack['result']->defense + 2;
                        $attack['damage'] = rand($attack['min'], $attack['max']);
                        $attack['xp'] = $attack['result']->level * rand(4, 6);
                        return '<p>'. get_user_by_id($attacker)->user_login . ' Won!</p>';
                        update_results_battle($attack['xp'], $attacker, $attack['damage'], $defender, $defend['damage'], $attacker, $defender);
                } else { //attacker loses
                        $attack['min'] = max(0, ($attack['result']->strength + $attack['result']->defense) - 3);
                        $attack['max'] = $attack['result']->strength + $attack['result']->defense + 2;
                        $attack['damage'] = rand($attack['min'], $attack['max']);
                        $defend['min'] = max(0, ($defend['result']->strength + $defend['result']->defense) - 3);
                        $defend['max'] = $defend['result']->strength + $defend['result']->defense + 2;
                        $defend['damage'] = rand($defend['min'], $defend['max']);
                        $defend['xp'] = $defend['result']->level * rand( 4, 7);
                        return '<p>'. get_user_by_id($defender)->user_login . ' Won!</p>';
                        update_results_battle($defend['xp'], $defender, $defend['damage'], $attacker, $attacker['damage'], $attacker, $defender);
                }
        }
        
        function update_results_battle($winxp, $winid, $windam, $loseid, $losedam, $attacker, $defender){
                global $wpdb;
                $wpdb->insert( 
                        $wpdb->base_prefix."_attack_log", 
                        array( 
                                'attacker' => $attacker,
                                'defender' => $defender,
                                'winner' => $winid                                
                        ), 
                        array(  
                                '%d',
                                '%d',
                                '%d'
                        ) 
                );
                $sql = "UPDATE ".$wpdb->prefix."rpg_usermeta SET xp=xp+%d, hp=hp-%d WHERE uid = %d";
                $wpdb->query( $wpdb->prepare( $sql, $winxp, $windam, $winid));
                $sql = "UPDATE ".$wpdb->prefix."rpg_usermeta SET hp=hp-%d WHERE uid = %d";
                $wpdb->query( $wpdb->prepare( $sql, $losedam, $loseid));
        }
        
        function calculate_scores($xp, $hp, $level, $attacker){
                
                $xp_seed = $xp * rand (1, 5) +3;
                $hp_seed = $hp * rand ( 1, 4) + 5;
                $level_seed = $level * rand ( 1, 3);
                $score = $xp_seed + $hp_seed + $level_seed;
                $score = $score * rand (1, 2);
                return $score;
        }
        
        function list_players_func()
		{
			global $wpdb, $current_user; 
                $sql = "SELECT um.hp, um.xp, u.* FROM ".$wpdb->base_prefix."rpg_usermeta um JOIN ".$wpdb->base_prefix."users u WHERE um.pid=u.id";
                $res = $wpdb->get_results($sql);
				$result = '<div id="rpg_area"><table id="members" border=1>';
				$result .= '<tr><th>MemberName</th><th>HP</th><th>Level</th><th>Actions</th></tr>';
				foreach($res as $u)
				{
					$result .= '<tr><td>'.$u->user_nicename.'</td><td>'.$u->hp.'</td><td>'.player_level($u->xp)['0']->title.'</td><td>'.($u->ID != $current_user->ID?'<button id="attack" name="'.$u->ID.'">Attack</button>':'').'</td></tr>';
				}
				$result .= '</table></div>';
				return $result;
		}

		function player_level($level)
		{
			global $wpdb;
			$sql = "SELECT title FROM ".$wpdb->base_prefix."rpg_levels WHERE min <= ". $level;
			$result = $wpdb->get_results($sql);
			return $result;
		}
		
		 function RPG_js_for_attacks(){
		 global $current_user;
                ?>
				<script type='text/javascript'>
					jQuery(document).ready(function($) {
						$('button#attack').click(function() {
							var them = $(this).attr('name');
							var you = <?=$current_user->ID?>;
							$.ajax({
								method: 'post',
								url: 'wp-admin/admin-ajax.php',
								data: {
									'attacker': you,
									'defender': them,
									'ajax': true,
									'attacking': true
								},
								success: function(data) {
									$('#rpg_area').empty();
									$('#rpg_area').html(data);
								}
							});
						});
					});
                </script>
				<?php
        
        }
        //////////////////////////////////
        // End RPG Functions
        //////////////////////////////////
		
		////////////////////////
		/// Start ShortCodes ///
		////////////////////////
		
		add_shortcode('list_players', 'list_players_func');