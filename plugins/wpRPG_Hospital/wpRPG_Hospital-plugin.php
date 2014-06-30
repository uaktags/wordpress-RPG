<?php
/*
Plugin Name: WPRPG Hospital (Official Sample)
Plugin URI: http://wordpress.org/extend/plugins/wprpg/
Version: 1.0.3
WPRPG: 1.0.13
Author: <a href="http://tagsolutions.tk">Tim G.</a>
Description: Creates a Hospital concept
Text Domain: wp-rpg
License: GPL3
*/
if ( !class_exists( 'wpRPG_Hospital' ) ) {
    class wpRPG_Hospital extends wpRPG {
        
        function __construct( ) {
            parent::__construct();
			add_action( 'init', array($this, 'wpRPG_Hospital_load_language'));
			add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_shortcode( 'wprpg_hospital', array(
                 $this,
                'showHospital' 
            ) );
            add_action( 'wp_ajax_hospital', array(
                 $this,
                'hospitalCallback' 
            ) );
            add_action( 'wp_ajax_nopriv_hospital', array(
                 $this,
                'hospitalCallback' 
            ) );
            if ( !is_admin() ) {
                add_action( 'wp_footer', array(
                     $this,
                    'includedJS' 
                ) );
            }
			add_filter( 'wpRPG_add_crons', array(
                 $this,
                'add_mycrons' 
            ) );
			add_filter( 'wpRPG_add_plugins', array(
					$this, 'add_plugin'
				)
			);
			add_filter( 'wpRPG_add_admin_tab_header', array(
                 $this,
                'addAdminTab_Header' 
            ) );
            add_filter( 'wpRPG_add_admin_tabs', array(
                 $this,
                'addAdminTab' 
            ) );
			add_filter( 'wpRPG_add_pages_settings', array(
				$this,
				'add_page_settings'
			) );
		}
        
		function wpRPG_Hospital_load_language(){
			load_plugin_textdomain('wpRPG-Hospital', false, (basename(dirname(dirname(__DIR__))) == 'wprpg'?'/wprpg/plugins/':'').basename( dirname( __FILE__ ) ) . '/languages' );
		}
		
		public function add_page_settings( $pages ) {
			$setting = array(
				'Hospital'=> array('name'=>'Hospital', 'shortcode'=>'[wprpg_hospital]')
			);
			return array_merge( $pages, $setting );
		}
		
		public function register_settings() {
			if ( !get_option( 'wpRPG_Hospital_Page' ) ) {
                add_option( 'wpRPG_Hospital_Page', 'Hospital', "", "yes" );
            }
			add_option( 'wpRPG_HP_Replenish_Increment', "1", "", "yes");
			register_setting( 'rpg_settings', 'wpRPG_HP_Replenish_Increment' );
        }
	
		
        function showHospital( ) {
            global $wpdb;
			if(is_user_logged_in()){
				$current_user = wp_get_current_user();
				$res = new wpRPG_Player($current_user->ID);
				if ( $res ) {
					$this->checkUserMeta($current_user->ID);
					if(file_exists(get_template_directory() . 'templates/wprpg/hospital.php')){
						ob_start();
						include (get_template_directory() . 'templates/wprpg/hospital.php');
						$result = ob_get_clean();
					}else{
						ob_start();
						include(__DIR__ .'/templates/hospital.php');
						$result = ob_get_clean();
					}
				} else {
					$result = '<div id="rpg_area">';
					$result .= '<h1>'.__("Plugin_Title", "wpRPG-Hospital").'</h1>
	<table width=100% style="text-align:center;"><tr><td><h3>'._e("Already_Healed_MSG", "wpRPG-Hospital").'</h3></td></tr></table>
										</div>
										<br/>
										
									</div>';
					$result .= '</div><br/><br/>';
					if ( get_option ( 'show_wpRPG_Version_footer' ) )	{
						$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
						$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>';
					}
				}
				return $result;
			}else{
				$result = '<div id="rpg_area">';
				$result .= '<h1>'.__("Plugin_Title", "wpRPG-Hospital").'</h1>
<table width=100% style="text-align:center;"><tr><td><h3>'._e("Already_Healed_MSG", "wpRPG-Hospital").'</h3></td></tr></table>
									</div>
									<br/>
									
								</div>';
				$result .= '</div><br/><br/>';
				if ( get_option ( 'show_wpRPG_Version_footer' ) )	{
					$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
					$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>';
				}
				return $result;
			}
		}
        
        function includedJS( ) {
            global $wpdb;
			if(is_user_logged_in()){
				$current_user = wp_get_current_user();
				if($current_user){
				$res = new wpRPG_Player($current_user->ID);
	?>
				<script type='text/javascript'>
					jQuery(document).ready(function($) {
						$('button#replenish-hp').click(function(event) {
							event.preventDefault();
							var them = '<?php echo $current_user->ID; ?>';
							var cost = '<?php echo ( 100 - $res->hp ); ?>';
							$.ajax({
								method: 'post',
								url: '<?php echo site_url( 'wp-admin/admin-ajax.php' ); ?>',
								data: {
									'action': 'hospital',
									'user': them,
									'cost': cost,
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
			}
        }
        
        function buyHealthCare( $uid, $hp, $cost ) {
            global $wpdb;
			$player = new wpRPG_Player($uid);
			$player->update_meta('hp', $player->hp + $hp);
			$player->update_meta('gold', $player->gold - $cost);
			$profiles    = new wpRPG_Profiles;
            $profiles->getProfile( $uid );
        }
        
        function hospitalCallback( ) {
            global $wpdb;
			if(is_user_logged_in()){
				$current_user = wp_get_current_user();
				$res = new wpRPG_Player($current_user->ID);
				if ( $res->gold >= $_POST[ 'cost' ] ) {
					_e("Now_Full_Health_MSG", "wpRPG-Hospital");
					$this->buyHealthCare( $res->ID, 100 - $res->hp, $_POST[ 'cost' ] );
					die( );
				} else {
					_e("Need_More_Gold_MSG", "wpRPG-Hospital");
					echo $this->showHospital();
					die( );
				}
			}
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
            $sql = "UPDATE " . $wpdb->prefix . "usermeta SET meta_value=meta_value+".$hpinc." WHERE meta_key='hp' and meta_value<100";
            $wpdb->query( $sql );
			$sql2 = "UPDATE " . $wpdb->prefix . "usermeta SET meta_value=meta_value-(meta_value-100) WHERE meta_key='hp' and meta_value>100";
			$wpdb->query( $sql2 );
        }
		
		/**
		 * Adds Cron to wpRPG cron
		 * @param array | $crons contains wpRPG::crons
		 * @returns array | Merge of new cron with old crons
		 * @since 1.0.3
		 */
		function add_mycrons( $crons )
		{
			$my_crons = array(
				 '30min_HPGain'=>array('class'=>'wpRPG_Hospital', 'func'=>'wpRPG_replenish_hp', 'duration'=>1800)
			);
			return array_merge( $crons, $my_crons );
		}
		
		function add_plugin( $plugins )
		{
			$my_plug = array(
				'hospital'=>array('name'=>'wpRPG_Hospital', 'version'=>'1.0.3', 'author'=>'Tim Garrity', 'description'=>'Creates a Hospital concept')
			);
			return array_merge($plugins, $my_plug);
		}
		
		function addAdminTab( $tabs ) {
            $tab_page = array(
                 'hospital' => $this->hospitalOptions( 1 ) 
            );
            return array_merge( $tabs, $tab_page );
        }
        
        function addAdminTab_Header( $tabs ) {
            $profile_tabs = array(
                 'hospital' => __('Plugin_Admin_Tab_Title', 'wpRPG-Hospital') 
            );
            return array_merge( $tabs, $profile_tabs );
        }
        
        function hospitalOptions( $opt = 0 ) {
			$html = "<tr>";
			$html .= "<td>";
			$html .= "<h3>". __("Plugin_Title", "wpRPG-Hospital"). "</h3>";
			$html .= "</td>";
			$html .= "</tr>";
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<table border=1><tr><th>".__('Setting Name', 'wpRPG')."</th><th>".__('Setting','wpRPG')."</th></tr>";
			$hpinc = get_option('wpRPG_HP_Replenish_Increment');
			$html .= "<tr><td>".__('HP_Cron_Title', 'wpRPG-Hospital').":</td><td><input type=text value=$hpinc name='wpRPG_HP_Replenish_Increment' id='wpRPG_HP_Replenish_Increment' /></td></tr>";
			$html .= "</table>";
			$html .= "</td>";
			$html .= "</tr>";
            if ( !$opt )
                echo $html;
            else
                return $html;
        }
        
    }
}