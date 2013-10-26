<?php
/*
   Plugin Name: WP RPG
   Plugin URI: http://wordpress.org/extend/plugins/wp-rpg/
   Version: 0.0.4
   Author: <a href="http://tagsolutions.tk">Tim G.</a>
   Description: RPG Elements added to WP
   Text Domain: wp-rpg
   License: GPL3
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
				echo '<br />Last Cron: ' . date('Y-m-d:H:i:s', get_option('WPRPG_last_cron'));
				echo '<br />Number of 30mins since then: '. time_elapsed(get_option('WPRPG_last_cron') );
                echo '<br />Next Cron: ' . date('Y-m-d:H:i:s', get_option('WPRPG_next_cron'));
				//include_once('wp-rpg-admin.php');
        }

        /*
         * Creates Menu Page
        */
        function add_rpg_to_admin_menu()
        {
                add_menu_page( 'Wordpress RPG Options', 'WP-RPG', 'manage_options', 'wp_rpg_menu', 'wp_rpg_options' );
        }
		
		function time_elapsed($secs)
		{
			return round( (time() - $secs) / (60*30));
			
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
        add_action('wp_footer', 'check_cron');
        
		function check_cron()
		{
			$last = get_option('WPRPG_last_cron');
			if(time_elapsed($last))
			{
				$i = 1;
				$xs = time_elapsed($last);
				while($i++ < $xs)
				{
					replenish_hp();
				}
				replenish_hp();
				$next_t = (time() - (time() % 1800)) + 1800;
				update_option('WPRPG_last_cron', time());
				update_option('WPRPG_next_cron', $next_t);
			}
		}
		function replenish_hp()
		{
			global $wpdb;
			$wpdb->show_errors();
			$sql = "UPDATE ". $wpdb->base_prefix ."rpg_usermeta SET hp=hp+1";
			$wpdb->query($sql);
		}
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
				add_action('wp_enqueue_scripts', 'include_jquery');
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
                $attack['sql'] = "SELECT xp, hp, level, strength, defense FROM ".$wpdb->base_prefix."rpg_usermeta WHERE pid = %d";
                if ( $attack['result'] = $wpdb->get_row($wpdb->prepare($attack['sql'], $attacker)) != null){
                                $attack['result'] = $wpdb->get_row($wpdb->prepare($attack['sql'], $attacker));
                }
                $defend['sql'] = "SELECT xp, hp, level, strength, defense FROM ".$wpdb->base_prefix."rpg_usermeta WHERE pid = %d";
                
                if ( $defend['result'] = $wpdb->get_row($wpdb->prepare($defend['sql'], $defender)) != null){
                                $defend['result'] = $wpdb->get_row($wpdb->prepare($defend['sql'], $defender));
                }
				$attack['level'] = player_level($attack['result']->xp)['0']->title;
				$defend['level'] = player_level($defend['result']->xp)['0']->title;
                $attack['score'] = calculate_scores($attack['result']->xp, $attack['result']->hp, $attack['level'], $attacker);
                $defend['score'] = calculate_scores($defend['result']->xp, $defend['result']->hp, $defend['level'], $defender);
				if( $attack['result']->hp == 0 )
				{
					return 'You can not attack with 0 HP!<br /><a href="#" onclick="location.reload(true); return false;">Reload Members List</a>';
				}elseif( $defend['result']->hp == 0 )
				{
					$defend['score'] = $defend['score'] * '.75'; //Attacker bonus! 
				}
                if ( $attack['score'] >= $defend['score'] ){ //attacker wins
						$loser['pid'] = $defender;
						$loser['score'] = $defend['score'];
						$loser['level'] = $defend['level'];
                        $loser['min'] = max(0, ($defend['result']->strength + $defend['result']->defense) - 3);
                        $loser['max'] = $defend['result']->strength + $defend['result']->defense + 2;
                        $loser['damage'] = rand($loser['min'], $loser['max']);
						$loser['hp'] = $defend['result']->hp - $loser['damage'];
						if($loser['hp'] <= 0)
						{
							$loser['hp'] = 0;
						}
						$winner['pid'] = $attacker;
                        $winner['score'] = $attack['score'];
						$winner['level'] = $attack['level'];
						$winner['min'] = max(0, ($attack['result']->strength + $attack['result']->defense) - 3);
                        $winner['max'] = $attack['result']->strength + $attack['result']->defense + 2;
                        $winner['damage'] = rand($winner['min'], $winner['max']);
						$winner['hp'] = $attack['result']->hp - $winner['damage'];
						if($winner['hp'] <= 0)
						{
							$winner['hp'] = 0;
						}
                        $winner['xp'] = $attack['result']->xp * rand(1, 4) + $attack['result']->xp;
                        update_results_battle($winner, $loser, $attacker, $defender);
						return '<p>'. get_user_by_id($attacker)->user_login . ' Won!</p>'.print_results_battle($winner, $loser, $attacker, $defender);
                } else { //attacker loses
						$loser['pid'] = $attacker;
						$loser['score'] = $attack['score'];
						$loser['level'] = $attack['level'];
                        $loser['min'] = max(0, ($attack['result']->strength + $attack['result']->defense) - 3);
                        $loser['max'] = $attack['result']->strength + $attack['result']->defense + 2;
                        $loser['damage'] = rand($loser['min'], $loser['max']);
						$loser['hp'] = $attack['result']->hp - $loser['damage'];
						if($loser['hp'] <= 0)
						{
							$loser['hp'] = 0;
						}
						$winner['pid'] = $defender;
						$winner['score'] = $defend['score'];
						$winner['level'] = $defend['level'];
                        $winner['min'] = max(0, ($defend['result']->strength + $defend['result']->defense) - 3);
                        $winner['max'] = $defend['result']->strength + $defend['result']->defense + 2;
                        $winner['damage'] = rand($winner['min'], $winner['max']);
						$winner['hp'] = $defend['result']->hp - $winner['damage'];
                        if($winner['hp'] <= 0)
						{
							$winner['hp'] = 0;
						}
						$winner['xp'] = $defend['result']->xp * rand( 1, 5) + $defend['result']->xp;
                        update_results_battle( $winner, $loser, $attacker, $defender);
                        return '<p>'. get_user_by_id($defender)->user_login . ' Won!</p>'. print_results_battle($winner, $loser, $attacker, $defender);
                }
        }
        
		function print_results_battle( $winner, $loser, $attacker, $defender)
		{
			$a_name = get_user_by_id($attacker)->user_login;
			$d_name = get_user_by_id($defender)->user_login;
			$result = '<strong><h2>Battle Results</h2></strong><p>';
			$result .= $a_name . ' attacked ' . $d_name . ' and ' . ($winner['pid'] == $attacker ? 'won.' : 'lost.') . '<br />';
			$result .= $a_name . ' attacked with a score of '. ($winner['pid'] == $attacker ? $winner['score'] : $loser['score']). '<br />';
			$result .= $d_name . ' defended against the attack with a score of '. ($winner['pid'] == $defender ? $winner['score'] : $loser['score']). '<br />';
			$result .= $a_name . ' suffered ' . ($winner['pid'] == $attacker ? $winner['damage'] : $loser['damage']). ' damage. <br />';
			$result .= $d_name . ' suffered ' . ($winner['pid'] == $defender ? $winner['damage'] : $loser['damage']). ' damage. <br />';
			if( $winner['level'] < player_level($winner['xp'])['0']->title )
			{
				if( $winner['pid'] == $attacker )
				{
					$result .= 'Congrats! You earned a new level! <br /> Now you\'re level ' . player_level($winner['xp'])['0']->title;
				}else{
					$result .= 'Congrats, attacking '. $d_name .' and losing, earned them a new level! <br /> They are now level ' . player_level($winner['xp'])['0']->title;
				}
			}
			$result .= '</p><br /><a href="#" onclick="location.reload(true); return false;">Reload Members List</a>';
			return $result;
		}
		
        function update_results_battle( $winner, $loser, $attacker, $defender){
                global $wpdb;
                $wpdb->insert( 
                        $wpdb->prefix."rpg_attack_log", 
                        array( 
                                'attacker' => $attacker,
                                'defender' => $defender,
                                'winner' => $winner['pid']                                
                        ), 
                        array(  
                                '%d',
                                '%d',
                                '%d'
                        ) 
                );
                $sql = "UPDATE ".$wpdb->prefix."rpg_usermeta SET xp=%d, hp=%d WHERE pid = %d";
                $wpdb->query( $wpdb->prepare( $sql, $winner['xp'], $winner['hp'], $winner['pid']));
                $sql = "UPDATE ".$wpdb->prefix."rpg_usermeta SET hp=%d WHERE pid = %d";
                $wpdb->query( $wpdb->prepare( $sql, $loser['hp'], $loser['pid']));
        }
        
        function calculate_scores($xp, $hp, $level, $attacker)
		{
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
					$result .= '<tr><td>'.$u->user_nicename.'</td><td>'.$u->hp.'</td><td>'.player_level($u->xp)['0']->title.'</td><td>';
						if(is_user_logged_in()){
							$result .= ($u->ID != $current_user->ID?'<button id="attack" name="'.$u->ID.'">Attack</button>':'');
						}
						$result .= '</td></tr>';
				}
				$result .= '</table></div>';
				return $result;
		}

		function player_level($level)
		{
			global $wpdb;
			$sql = "SELECT title FROM ".$wpdb->base_prefix."rpg_levels l WHERE l.group='player_levels' AND l.min <= ". $level;
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
									'action': 'attack',
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
		function attack_callback()
		{
			echo WpRPG_Attack($_POST['attacker'], $_POST['defender']);
			die();
		}
		add_action('wp_ajax_attack', 'attack_callback');