<?php
if ( !class_exists( 'wpRPG_Hospital' ) ) {
    class wpRPG_Hospital extends wpRPG {
        
        function __construct( ) {
            parent::__construct();
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
			add_filter( 'wpRPG_add_admin_tab_header', array(
                 $this,
                'addAdminTab_Header' 
            ) );
            add_filter( 'wpRPG_add_admin_tabs', array(
                 $this,
                'addAdminTab' 
            ) );
        }
        
		public function register_settings() {
			add_option( 'wpRPG_HP_Replenish_Increment', "1", "", "yes");
			register_setting( 'rpg_settings', 'wpRPG_HP_Replenish_Increment' );
        }
		
        function showHospital( ) {
            global $wpdb, $current_user;
            $sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND um.hp<100 AND u.id=" . $current_user->ID;
            $res = $wpdb->get_results( $sql );
            if ( $res ) {
                $return_template = dirname( __FILE__ ) . '/templates/hospital.php';
                $result = include( $return_template );
            } else {
                $result = '<div id="rpg_area">';
                $result .= '<h1>Hospital</h1>
<table width=100% style="text-align:center;"><tr><td><h3>You\'re Already Fully Healed! Come Back When You Need Help!</h3></td></tr></table>
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
        }
        
        function includedJS( ) {
            global $wpdb, $current_user;
            $sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND u.id=". $current_user->ID;
            $res = $wpdb->get_results( $sql );
?>
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('button#replenish-hp').click(function(event) {
						event.preventDefault();
						var them = '<?php
            echo $current_user->ID;
?>';
						var cost = '<?php
            echo ( 100 - $res[ 0 ]->hp );
?>';
						$.ajax({
							method: 'post',
							url: '<?php
            echo site_url( 'wp-admin/admin-ajax.php' );
?>',
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
        
        function buyHealthCare( $uid, $hp, $cost ) {
            global $wpdb;
            $sql = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET hp=hp+$hp, gold=gold-$cost WHERE pid=$uid";
            $wpdb->query( $sql );
			$profiles    = new wpRPG_Profiles;
            $profiles->getProfile( $uid );
        }
        
        function hospitalCallback( ) {
            global $wpdb, $current_user;
            $sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND u.id=" . $current_user->ID;
            $res = $wpdb->get_results( $sql );
            if ( $res[ 0 ]->gold >= $_POST[ 'cost' ] ) {
                echo "You are now at full health!";
				$this->buyHealthCare( $res[ 0 ]->ID, 100 - $res[0]->hp, $_POST[ 'cost' ] );
                die( );
            } else {
                echo $this->showHospital();
                die( );
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
            $sql = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET hp=hp+".$hpinc." WHERE hp<100";
            $wpdb->query( $sql );
			$sql2 = "UPDATE " . $wpdb->prefix . "rpg_usermeta SET hp=hp-(hp-100) WHERE hp<100";
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
		
		function addAdminTab( $tabs ) {
            $tab_page = array(
                 'hospital' => $this->hospitalOptions( 1 ) 
            );
            return array_merge( $tabs, $tab_page );
        }
        
        function addAdminTab_Header( $tabs ) {
            $profile_tabs = array(
                 'hospital' => 'Hospital Settings' 
            );
            return array_merge( $tabs, $profile_tabs );
        }
        
        function hospitalOptions( $opt = 0 ) {
			$html = "<tr>";
			$html .= "<td>";
			$html .= "<h3>Hospital!</h3>";
			$html .= "</td>";
			$html .= "</tr>";
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<table border=1><tr><th>Setting Name</th><th>Setting</th></tr>";
			$hpinc = get_option('wpRPG_HP_Replenish_Increment');
			$html .= "<tr><td>HP Increment Per Cron:</td><td><input type=text value=$hpinc name='wpRPG_HP_Replenish_Increment' id='wpRPG_HP_Replenish_Increment' /></td></tr>";
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