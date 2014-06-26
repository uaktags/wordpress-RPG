<?php
/*
Plugin Name: WPRPG Registration (Official Sample)
Plugin URI: http://wordpress.org/extend/plugins/wprpg/
Version: 1.0.3
WPRPG: 1.0.13
Author: <a href="http://tagsolutions.tk">Tim G.</a>
Description: Adds a Registration Concept
Text Domain: wp-rpg
License: GPL3
*/
if ( !class_exists( 'wpRPG_Registration' ) ) {
    class wpRPG_Registration extends wpRPG {
        function __construct( ) {
            parent::__construct();
			$this->default_usermeta = array(
				'gold' => 0,
				'xp' => 0,
				'hp' => 100,
				'strength' => 5,
				'defense' => 10,
				'last_active' => time(),
				'bank' => 500
			);
			if( is_admin() ){
				add_action( 'admin_init', array( $this, 'register_settings' ) );
				add_action( 'admin_init', array( $this, 'register_hooks' ) );
			}
			add_action( 'user_register', array($this, 'save_registration'), 10, 1 );

		}
        
		public function save_registration( $uid )
		{
			return $this->checkUserMeta($uid);
		}
		
		public function register_settings() {
			add_option( 'wpRPG_default_gold' , '0', '', 'yes' );
			add_option( 'wpRPG_default_xp' , '0', '', 'yes' );
			add_option( 'wpRPG_default_strength' , '5', '', 'yes' );
			add_option( 'wpRPG_default_defense' , '10', '', 'yes' );
			add_option( 'wpRPG_default_bank' , '500', '', 'yes' );
			add_option( 'wpRPG_default_hp' , '100', '', 'yes' );
			register_setting( 'rpg_settings', 'wpRPG_default_hp');
			register_setting( 'rpg_settings', 'wpRPG_default_bank');
			register_setting( 'rpg_settings', 'wpRPG_default_defense');
			register_setting( 'rpg_settings', 'wpRPG_default_strength');
			register_setting( 'rpg_settings', 'wpRPG_default_xp');
			register_setting( 'rpg_settings', 'wpRPG_default_gold');
		}
		
		public function register_hooks() {
			add_filter( 'wpRPG_add_admin_tab_header', array(
                 $this,
                'addAdminTab_Header' 
            ) );
            add_filter( 'wpRPG_add_admin_tabs', array(
                 $this,
                'addAdminTab' 
            ) );
		}
		
		
		function addAdminTab( $tabs ) {
            $tab_page = array(
                 'registration' => $this->registrationOptions( 1 ) 
            );
            return array_merge( $tabs, $tab_page );
        }
        
        function addAdminTab_Header( $tabs ) {
            $profile_tabs = array(
                 'registration' => 'Registration Settings' 
            );
            return array_merge( $tabs, $profile_tabs );
        }
        
        function registrationOptions( $opt = 0 ) {
            $html = "<tr>";
            $html .= "<td>";
            $html .= "<h3>Welcome to Wordpress RPG Registration Module!</h3>";
            $html .= "</td>";
            $html .= "</tr>";
            $html .= "<tr>";
            $html .= "<td>";
            $html .= "<table border=1><tr><th>Settings Name</th><th>Setting Value</th></tr>";
			$html .= "<tr><td>Default HP</td><td><input type=text id='wpRPG_default_hp' name='wpRPG_default_hp' value='".get_option('wpRPG_default_hp')."' /></td>";
			$html .= "<tr><td>Default XP</td><td><input type=text id='wpRPG_default_xp' name='wpRPG_default_xp' value='".get_option('wpRPG_default_xp')."' /></td>";
			$html .= "<tr><td>Default Gold</td><td><input type=text id='wpRPG_default_gold' name='wpRPG_default_gold' value='".get_option('wpRPG_default_gold')."' /></td>";
			$html .= "<tr><td>Default Bank</td><td><input type=text id='wpRPG_default_bank' name='wpRPG_default_bank' value='".get_option('wpRPG_default_bank')."' /></td>";
			$html .= "<tr><td>Default Strength</td><td><input type=text id='wpRPG_default_strength' name='wpRPG_default_strength' value='".get_option('wpRPG_default_strength')."' /></td>";
			$html .= "<tr><td>Default Defense</td><td><input type=text id='wpRPG_default_defense' name='wpRPG_default_defense' value='".get_option('wpRPG_default_defense')."' /></td>";
			$html .= "</td></tr>";
            $html .= "</td>";
            $html .= "</tr>";
            if ( !$opt )
                echo $html;
            else
                return $html;
        }
		
		
       

    }
}