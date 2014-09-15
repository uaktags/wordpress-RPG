<?php
/*
Plugin Name: WPRPG Members (Official Sample)
Plugin URI: http://wordpress.org/extend/plugins/wprpg/
Version: 1.0.3
WPRPG: 1.0.13
Author: <a href="http://tagsolutions.tk">Tim G.</a>
Description: Adds a Player list
Text Domain: wp-rpg
License: GPL3
*/
if ( !class_exists( 'wpRPG_Members' ) && class_exists( 'wpRPG' ) ) {
    class wpRPG_Members extends wpRPG {
        
		/**
		 * Initialize the Members Class
		 * creates the $this variable that combines wpRPG
		 */
        function __construct( ) {
            parent::__construct();
            add_action( 'init', array($this, 'wpRPG_Members_load_language'));
			add_shortcode( 'list_players', array(
                 $this,
                'listPlayers' 
            ) );
			add_filter( 'wpRPG_add_pages_settings', array(
				$this,
				'add_page_settings'
			) );
			if ( !get_option( 'wpRPG_Members_Page' ) ) {
                add_option( 'wpRPG_Members_Page', 'Members', "", "yes" );
            }
        }
        
		function wpRPG_Members_load_language(){
			load_plugin_textdomain('wpRPG-Members', false, (basename(dirname(dirname(__DIR__))) == 'wprpg'?'/wprpg/plugins/':'').basename( dirname( __FILE__ ) ) . '/languages' );
		}
		
		/**
		 * Lists the players and renders the template.
		 * @returns string HTML outpage
		 */
        function listPlayers( ) {
            global $wpdb;
			if(file_exists(get_template_directory() . 'templates/wprpg/list_players.php')){
				ob_start();
				include (get_template_directory() . 'templates/wprpg/list_players.php');
				$result = ob_get_clean();
			}else{
				ob_start();
				include(__DIR__ .'/templates/list_players.php');
				$result = ob_get_clean();
			}
			if ( get_option ( 'show_wpRPG_Version_footer' ) )	{
				$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
				$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. WPRPG_VERSION .'</a></footer>';
			}
            return $result;
        }
        
		/*
		 * Grabs all objects that are suppose to go in the Actions 
		 * @return string HTML
		 */
        static function listPlayers_getLoggedIn_Actions( $uid ) {
            $result = apply_filters( 'listPlayers_Loggedin_Actions', $uid );
            return $result;
        }
        
		public function add_page_settings( $pages ) {
			$setting = array(
				'Members'=> array('name'=>'Members', 'shortcode'=>'[list_players]')
			);
			return array_merge( $pages, $setting );
		}
    }
}
