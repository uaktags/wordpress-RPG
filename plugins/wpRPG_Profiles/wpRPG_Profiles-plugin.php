<?php


if ( !class_exists( 'wpRPG_Profiles' ) ) {
    class wpRPG_Profiles extends wpRPG {
        
        function __construct( ) {
            parent::__construct();
            add_shortcode( 'view_profile', array(
                 $this,
                'profileShortCodeVars' 
            ) );
            add_shortcode( 'permalink', array(
                 $this,
                'custom_permalink' 
            ) );
            add_action( 'wp_ajax_profile', array(
                 $this,
                'profileCallback' 
            ) );
            add_action( 'wp_ajax_nopriv_profile', array(
                 $this,
                'profileCallback' 
            ) );
            add_action( 'wp_loaded', array(
                 $this,
                'flushRules' 
            ) );
            add_filter( 'rewrite_rules_array', array(
                 $this,
                'rewriteRules' 
            ) );
            add_filter( 'query_vars', array(
                 $this,
                'insertQueryVars' 
            ) );
            /*add_filter( 'wpRPG_add_admin_tab_header', array(
                 $this,
                'addAdminTab_Header' 
            ) );*/
			add_filter( 'wpRPG_add_plugins', array(
					$this, 'add_plugin'
				)
			);
            add_filter( 'wpRPG_add_admin_tabs', array(
                 $this,
                'addAdminTab' 
            ) );
            if ( !is_admin() ) {
                add_action( 'wp_enqueue_scripts', array(
                     $this,
                    'includeJquery' 
                ) );
                add_action( 'wp_footer', array(
                     $this,
                    'includedJS' 
                ) );
            }
			else
			{
				add_action( 'admin_init', array( 
					$this, 
					'register_settings' ) 
				);
				add_action( 'admin_init', array(
                     $this,
                    'includeJquery' 
                ) );
				add_action( 'in_admin_footer', array(
                     $this,
                    'includedJS' 
                ) );
				add_filter( 'wpRPG_add_pages_settings', array(
					$this,
					'add_page_settings'
				) );
			}
			
        }
        
		function register_settings() {
			if ( !get_option( 'wpRPG_Profile_Page' ) ) {
                add_option( 'wpRPG_Profile_Page', 'Profile', "", "yes" );
            }
			register_setting( 'rpg_settings', 'wpRPG_Profile_Page' );
		}
		
		function add_page_settings( $pages ) {
			$setting = array(
				'Profile'=> array('name'=>'Profile', 'shortcode'=>'[view_profile]')
			);
			return array_merge( $pages, $setting );
		}
		
		/**
		 * Gets the "VIEWED" Player and their meta
		 * @return object Player's Meta
		 * @since 1.0.4
		 */
		function get_viewed_player() {
			$viewed = get_user_by('login',get_query_var( 'username' ));
			if(!$viewed)
			{
				if(is_user_logged_in()){
					$current_user = wp_get_current_user();
					$player = new wpRPG_Player($current_user->ID);
				}
			}else{
				$viewed = $viewed->ID;
				$player = $this->get_meta($viewed);
			}
			if($player !== false)
				return $player;
			else
				return false;
		}
		
		/**
		 * Adds info to profile section
		 * @return array
		 * @since 1.0.4
		 */
		function add_profile_section_top_right($actions)
		{
			$profile_tabs = array(); //Intentionally left blank
            return array_merge( $actions, $profile_tabs );
        }		
		
		/**
		 * Adds info to profile section
		 * @return array
		 * @since 1.0.4
		 */
		function add_profile_section_mid_right($actions)
		{
			$player = $this->get_viewed_player();
			if($player !== false){
			$profile_tabs = array(
				 'gold' =>  'Gold: '.$player->gold,
				 'hp' => 'HP: '.$player->hp
            );
            return array_merge( $actions, $profile_tabs );
			}
        }
		
		/**
		 * Adds info to profile section
		 * @return array
		 * @since 1.0.4
		 */
		function add_profile_section_bottom_right($actions)
		{
			$profile_tabs = array(
                 
            );
            return array_merge( $actions, $profile_tabs );
        }	
		
        function addAdminTab( $tabs ) {
            $tab_page = array(
                 'profile' => $this->profileOptions( 1 ) 
            );
            return array_merge( $tabs, $tab_page );
        }
        
        function addAdminTab_Header( $tabs ) {
            $profile_tabs = array(
                 'profile' => 'Profile Settings' 
            );
            return array_merge( $tabs, $profile_tabs );
        }
        
        function profileOptions( $opt = 0 ) {
            $html = "<tr>";
            $html .= "<td>";
            $html .= "<h3>Welcome to Wordpress RPG Profile Module!</h3>";
            $html .= "</td>";
            $html .= "</tr>";
            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<span class='description'>Nothing To See Here Yet</span>";
            $html .= "</td>";
            $html .= "</tr>";
            if ( !$opt )
                echo $html;
            else
                return $html;
        }
        
        function includeJquery( ) {
            wp_enqueue_script( 'jquery' );
			//jQuery ScrollTo Plugin
			wp_register_script( 'jquery-scrollto', 'http://balupton.github.io/jquery-scrollto/lib/jquery-scrollto.js' );
			//History.js
			wp_register_script( 'history.js', 'http://browserstate.github.io/history.js/scripts/bundled/html4+html5/jquery.history.js' );
			//Ajaxify
			wp_register_script( 'ajaxify', 'http://rawgithub.com/browserstate/ajaxify/master/ajaxify-html5.js' );
			//wp_enqueue_script( 'jquery-scrollto' );
			wp_enqueue_script( 'history.js' );
			//wp_enqueue_script( 'ajaxify' );
			add_action( 'admin_init', array(
                     $this,
                    'includedJS' 
                ) );
        }
        
        function profileShortCodeVars( $atts ) {
            extract( shortcode_atts( array(
                 'pid' => 0 
            ), $atts, 'view_profile' ) );
			//wp_die($pid);
            return $this->getProfile( $pid );
        }
        
		/**
		 * initializes the Profile Fields
		 * @since 1.0.4
		 */
		function get_profile_fields() {
			add_filter( 'profile_section_top_right', array(
						$this,
						'add_profile_section_top_right'
					) );
			add_filter( 'profile_section_mid_right', array(
						$this,
						'add_profile_section_mid_right'
					) );
			add_filter( 'profile_section_bottom_right', array(
						$this,
						'add_profile_section_bottom_right'
					) );
			add_filter( 'profile_section_top_left', array(
						$this,
						'add_profile_section_top_left'
					) );
			add_filter( 'profile_section_mid_left', array(
						$this,
						'add_profile_section_mid_left'
					) );
			add_filter( 'profile_section_bottom_left', array(
						$this,
						'add_profile_section_bottom_left'
					) );
			
		}
		
        public function getProfile( $player_id ) {
			global $current_user;
            $username = get_query_var( 'username' );
            if ( $player_id != 0 || !empty( $username ) ) {
					if(isset($username) && $player_id == 0)
					{
						$player_id= $username;
					}
				
				$res = new wpRPG_Player($player_id);
                if ( $res ) {
                    global $wp;
					$this->checkUserMeta($res->ID);
					$this->get_profile_fields();
					if(file_exists(get_template_directory() . 'templates/wprpg/view_profile.php')){
						ob_start();
						include (get_template_directory() . 'templates/wprpg/view_profile.php');
						$result = ob_get_clean();
					}else{
						ob_start();
						include (__DIR__ .'/templates/view_profile.php');
						$result = ob_get_clean();
					}
					return $result;
                } else {
                    $result = '<div id="rpg_area">';
                    $result .= '<h1>User Not Found</h1>';
                    $result .= '</div><br/><br/>';
					if ( get_option ( 'show_wpRPG_Version_footer' ) )	{
						$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
						$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>';
					}
					return $result;
                }
            } else {
                if ( $current_user->ID != 0 ) {
                    return $this->getProfile( $current_user->ID );
                } else {
                    return 'Not Logged In';
                }
            }
        }
        
        function flushRules( ) {
			$profile = get_option( 'wpRPG_Profile_Page' );
			$permalink = untrailingslashit(substr(get_permalink($profile), strlen(get_option('home').'/')));
            $rules = get_option( 'rewrite_rules' );
            if ( !isset( $rules[ '('.$permalink.')/(.+)$' ] ) ) {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
            }
            
        }
        
        function rewriteRules( $rules ) {
			$profile = get_option( 'wpRPG_Profile_Page' );
			$permalink = untrailingslashit(substr(get_permalink($profile), strlen(get_option('home').'/')));
            $newrules                      = array( );
            $newrules[ '('.$permalink.')/(.+)$' ] = 'index.php?pagename=$matches[1]&username=$matches[2]';
            return $newrules + $rules;
        }
        
        function insertQueryVars( $vars ) {
            array_push( $vars, 'username' );
            return $vars;
        }
        
        function includedJS( ) {
            
?>
			<script type='text/javascript'>
				jQuery(document).ready(function($) 
				{
					$('a#view-profile').click(function(event) {
						event.preventDefault();
						var pageurl = $(this).attr('href');
						var url = '<?php echo get_bloginfo( 'wpurl' ); ?>';
						var them = $(this).attr('name');
						$.ajax({
							method: 'post',
							url: '<?php
            echo site_url( 'wp-admin/admin-ajax.php' );
?>',
							data: {
								'action': 'profile',
								'user': them,
								'ajax': true
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
        
        function profileCallback( ) {
            echo $this->getProfile( $_POST[ 'user' ] );
            die( );
        }
		
		/**
		 * Get Admin Tabs that were made by default and by other plugins
		 * @return array
		 * @since 1.0.0
		 */
        public function init_user_profile_fields( $field ) {
            $sections = array();
			$section = apply_filters( 'profile_section_'.$field, $sections );
			return $section;
        }
		
		function add_plugin( $plugins )
		{
			$my_plug = array(
				'profile'=>array('name'=>'wpRPG_Profiles', 'version'=>'', 'author'=>'Tim Garrity', 'description'=>'Creates a Profile concept')
			);
			return array_merge($plugins, $my_plug);
		}
        
    }
}