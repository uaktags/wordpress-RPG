<?php
if ( !class_exists( 'wpRPG_Registration' ) ) {
    class wpRPG_Registration extends wpRPG {
        function __construct( ) {
            parent::__construct();
			if( is_admin() ){
				add_action( 'admin_init', array( $this, 'register_settings' ) );
				add_action( 'admin_init', array( $this, 'register_hooks' ) );
			}else{
				add_action( 'register_form', array( $this, 'register_form' ) );
			}
		}
        
		public function register_hooks() {
			if(get_option('show_race_selection'))
			{
				add_action( 'user_register', array(
					 $this,
					'user_register' 
				) );
				add_filter( 'registration_errors', array(
					 $this,
					'registration_errors' 
				), 10, 3 );
			}
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
            $html .= "<span class='description'>Nothing To See Here Yet</span>";
            $html .= "</td>";
            $html .= "</tr>";
            if ( !$opt )
                echo $html;
            else
                return $html;
        }
		
		public function register_settings() {
			add_option( 'show_race_selection', "1", "", "yes" );
            register_setting( 'rpg_settings', 'show_race_selection' );
        }
		
        public function user_register( $user_id ) {
            global $wpdb;
            //$wpdb->show_errors();
			//wp_die(var_dump($user_id) . '<br />'. var_dump($_POST));
            if ( !$this->wpRPG_is_playing( $user_id ) ) {
                $wpdb->insert( $wpdb->prefix . "rpg_usermeta", array(
                     'pid' => $user_id 
                ), array(
                     '%d' 
                ) );
				$race = ( isset( $_POST[ 'race' ] ) ? $_POST['race'] : '1' );
                $wpdb->query( "UPDATE " . $wpdb->prefix . "rpg_usermeta SET race = " . $race . " WHERE pid = $user_id" );
            }
        }
        
        //1. Add a new form element...
        
        function register_form( ) {
            //$race = ( isset( $_POST[ 'race' ] ) ) ? $_POST[ 'race' ] : ''); ?>
			<p>
				<label for="race">
				<?php _e( 'Race', 'mydomain' ); ?>
				<br />
					<select id="race" name="race">
					<?php
            global $wpdb;
            $races = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "rpg_races" );
            foreach ( $races as $race ) { 
						?>
						<option value="<?php echo esc_attr( stripslashes( $race->id ) );?>" ><?php echo ucfirst($race->title); ?> </option>
			<?php } ?>
					</select>
				</label>
			</p>
			<?php
        }
        
        //2. Add validation. In this case, we make sure first_name is required.
        function registration_errors( $errors, $sanitized_user_login, $user_email ) {
            
            if ( empty( $_POST[ 'race' ] ) )
                $errors->add( 'race_error', __( '<strong>ERROR</strong>: You must include a first name.', 'mydomain' ) );
            
            return $errors;
        }

    }
}