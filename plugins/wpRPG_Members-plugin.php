<?php
if ( !class_exists( 'wpRPG_Members' ) ) {
    class wpRPG_Members extends wpRPG {
        
        function __construct( ) {
            parent::__construct();
            add_shortcode( 'list_players', array(
                 $this,
                'listPlayers' 
            ) );
        }
        
        function listPlayers( ) {
            global $wpdb, $current_user;
            $sql    = "SELECT um.hp, um.xp, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id";
            $res    = $wpdb->get_results( $sql );
            $result = '<div id="rpg_area"><table id="members" border=1>';
            $result .= '<tr><th>MemberName</th><th>XP</th><th>HP</th><th>Level</th>' . ( is_user_logged_in() ? '<th>Actions</th>' : '' ) . '</tr>';
            foreach ( $res as $u ) {
                $result .= '<tr id="player_' . $u->ID . '"><td><a href="" id="view-profile" name="' . $u->ID . '">' . $u->user_nicename . '</a></td><td>' . $u->xp . '</td><td>' . $u->hp . '</td><td>' . $this->wpRPG_player_level( $u->xp ) . '</td><td>';
                if ( is_user_logged_in() ) {
                    $result .= ( $u->ID != $current_user->ID ? $this->listPlayers_getLoggedIn_Actions( $u->ID ) : '' );
                }
                $result .= '</td></tr>';
            }
            $result .= '</table></div>';
            $result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
			if ( get_option ( 'show_wpRPG_Version_footer' ) )	{
				$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
				$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>';
			}
            return $result;
        }
        
        function listPlayers_getLoggedIn_Actions( $uid ) {
            $result = apply_filters( 'listPlayers_Loggedin_Actions', array(
                 '',
                $uid 
            ) );
            return $result[ 0 ];
        }
        
    }
}