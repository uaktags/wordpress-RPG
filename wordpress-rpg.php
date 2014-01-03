<?php
/*
  Plugin Name: WP RPG
  Plugin URI: http://wordpress.org/extend/plugins/wp-rpg/
  Version: 0.10.9
  Author: <a href="http://tagsolutions.tk">Tim G.</a>
  Description: RPG Elements added to WP
  Text Domain: wp-rpg
  License: GPL3
 */

/////////////////////
/// File Includes ///
/////////////////////
////////////////////
/// End Includes ///
////////////////////
////////////
/// INIT ///
////////////
if(!class_exists('wpRPG'))
{
	class wpRPG {
		////////////
		/// INIT ///
		////////////
		public function __construct() 
		{
			//add_action('user_register', array($this, 'wpRPG_user_register'));
			//add_action('wp_footer', array($this, 'wpRPG_check_cron'));
			$this->file_name = __FILE__;
			$this->plug_version = '0.10.9';
			$this->plug_slug = basename(dirname(__FILE__));
			$this->wpRPG_load_shortcodes();
			$this->default_tabs = array('homepage' => 'Home', 'pages' => 'Pages', 'cron' => 'Cron Info');
			$this->plugin_slug = basename(dirname(__FILE__));
			//add_filter('registration_errors', array($this,'registration_errors'), 10, 3);
			
		}

		////////////////
		/// END INIT ///
		////////////////
		//////////////////////////////////
		// RPG Functions
		//////////////////////////////////
		public function updateLastActive()
		{
			global $wpdb, $current_user;
			if(!$current_user->ID)
			{
				return false;
			}
			if ($this->wpRPG_is_playing($current_user->ID))
			{
				$last_active =  $wpdb->get_var("SELECT last_active from ". $wpdb->prefix ."rpg_usermeta WHERE pid = $current_user->ID");
				if($last_active <= (time() - 300))
					return $wpdb->query("UPDATE ". $wpdb->prefix ."rpg_usermeta SET last_active = ". time() ." WHERE pid = $current_user->ID");
			}
		}
		
		public static function getOnlineStatus($uid)
		{
			global $wpdb;
			$time = time() - (60 * 5);
			return (bool) $wpdb->get_row("SELECT * FROM ". $wpdb->prefix ."rpg_usermeta WHERE pid = $uid AND last_active > ". $time);
		}
		
		public function wpRPG_is_playing($uid) {
			global $wpdb;
			$sql = "SELECT xp, hp FROM " . $wpdb->prefix . "rpg_usermeta WHERE pid = %d";
			if ($wpdb->get_row($wpdb->prepare($sql, $uid)) != null) {
				return true;
				exit;
			}
			return false;
		}

		public static function wpRPG_get_user_by_id($id) {
			global $wpdb;
			$sql = "Select id, user_login from " . $wpdb->base_prefix . "users where id = %d";
			if ($wpdb->get_row($wpdb->prepare($sql, $id)) != null) {
				return $wpdb->get_row($wpdb->prepare($sql, $id));
			}
			return false;
		}

		public static function wpRPG_player_rank($id) {
			global $wpdb;
			$sql_count = "SELECT * FROM ". $wpdb->prefix . "rpg_usermeta ORDER BY xp DESC";
			$res = $wpdb->get_results($sql_count);
			$rank = 1;
			foreach($res as $item){
			   if ($item->id == $id){
				   return $rank;
			   }
			   ++$rank;
			}
			return 1;
		}
		
		public static function wpRPG_player_level($level) {
			global $wpdb;
			$sql = "SELECT title FROM " . $wpdb->prefix . "rpg_levels l WHERE l.group='wpRPG_player_levels' AND l.min <= " . $level . " Order By l.min DESC";
			$result = $wpdb->get_results($sql);
			//wp_die(var_dump($result));
			return $result[0]->title;
		}
		public function wpRPG_replenish_hp() {
			global $wpdb;
			$wpdb->show_errors();
			$sql = "UPDATE " . $wpdb->prefix. "rpg_usermeta SET hp=hp+1 WHERE hp<100";
			$wpdb->query($sql);
		}

		public function wpRPG_check_cron() {
			$last = get_option('wpRPG_last_cron');
			if ($this->wpRPG_time_elapsed($last)) {
				$i = 1;
				$xs = $this->wpRPG_time_elapsed($last);
				while ($i++ < $xs) {
					$this->wpRPG_replenish_hp();
				}
				$this->wpRPG_replenish_hp();
				$next_t = (time() - (time() % 1800)) + 1800;
				update_option('wpRPG_last_cron', time());
				update_option('wpRPG_next_cron', $next_t);
			}
		}

		public function wpRPG_time_elapsed($secs) {
			return round((time() - $secs) / (60 * 30));
		}

		//////////////////////////////////
		// End RPG Functions
		//////////////////////////////////
		////////////////////////
		/// Start ShortCodes ///
		////////////////////////
		public function wpRPG_load_shortcodes() {
			
		}

		//////////////////////
		/// End ShortCodes ///
		//////////////////////
		public function wpRPG_on_activation() {
			if (!current_user_can('activate_plugins'))
				return;
			$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
			check_admin_referer("activate-plugin_{$plugin}");
			# Uncomment the following line to see the function in action
			//exit( var_dump( $_GET ) );
			$this->wpRPG_check_tables();
			$this->wpRPG_get_current_users_activated();
			return;
		}

		public function wpRPG_on_deactivation() {
			if (!current_user_can('activate_plugins'))
				return;
			$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
			check_admin_referer("deactivate-plugin_{$plugin}");
			# Uncomment the following line to see the function in action
			# exit( var_dump( $_GET ) );
		}

		public function wpRPG_RegisterSettings() {
			// Add options to database if they don't already exist
			add_option('wpRPG_rpg_installed', "0", "", "yes");
			if (get_option('wpRPG_last_cron') == null) {
				update_option('wpRPG_last_cron', time());
			} elseif (get_option('wpRPG_last_cron') == false) {
				add_option('wpRPG_last_cron', time(), "", "yes");
			}
			if (get_option('wpRPG_next_cron') == null) {
				update_option('wpRPG_next_cron', (time() - (time() % 1800) + 1800));
			} elseif (get_option('wpRPG_next_cron') == false) {
				add_option('wpRPG_next_cron', (time() - (time() % 1800) + 1800), "", "yes");
			}
			if (!get_option('wpRPG_Profile_Page')) {
				add_option('wpRPG_Profile_Page', 'Profile', "", "yes");
			}
			register_setting('rpg_settings', 'wpRPG_Profile_Page');
			// Register settings that this form is allowed to update
			register_setting('rpg_settings', 'wpRPG_rpg_installed');
			register_setting('rpg_settings', 'wpRPG_last_cron');
			register_setting('rpg_settings', 'wpRPG_next_cron');
		}
		
		public function wpRPG_SaveSettings() {
			foreach ($_POST as $group => $setting)
			{
				if(get_option($group)){
					update_option($group, $setting);
				}
			}
			
		}

		//////////////////////////
		/// End initialization ///
		//////////////////////////
		/////////////////////////
		/// Install Functions ///
		/////////////////////////
/*
		public function wpRPG_check_tables() {
			global $wpdb;
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_usermeta (
																	`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																	`pid` int(11) unsigned NOT NULL,
																	`last_active` int(11) unsigned default '0',
																	`xp` int(11) unsigned default '0',
																	`hp` int(11) unsigned default '20',
																	`defense` int(11) unsigned NOT NULL default '10',
																	`strength` int(11) unsigned default '5',
																	`gold` int(11) unsigned default '500',
																	`bank` int(11) unsigned default '0'
																	)";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_levels (
																	`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
																	`min` int(11) unsigned NOT NULL DEFAULT '0',
																	`max` int(11) unsigned NOT NULL DEFAULT '100',
																	`group` varchar(50) NOT NULL DEFAULT '',
																	`title` varchar(50) NOT NULL DEFAULT ''
																	)";
			$wpdb->query($sql);
			$this->wpRPG_default_levels();
			return true;
		}
	*/	
		public function wpRPG_default_levels()
		{
			global $wpdb;
			$wpdb->query("SELECT * FROM " . $wpdb->prefix . "rpg_levels");
			if(!$wpdb->num_rows)
			{
				$sql = "INSERT INTO " . $wpdb->prefix . "rpg_levels (`id` ,`min` ,`max` ,`group` ,`title`)VALUES ( NULL ,  '0',  '99',  'wpRPG_player_levels',  '1'), ( NULL ,  '100',  '199',  'wpRPG_player_levels',  '2'), ( NULL ,  '200',  '299',  'wpRPG_player_levels',  '3')";
				$wpdb->query($sql);
			}
		}

		public function wpRPG_check_column($table, $col_name) {
			global $wpdb;
			if ($table != null) {
				$results = $wpdb->get_results("DESC $table");
				if ($results != null) {
					foreach ($results as $row) {
						if ($row->Field == $col_name) {
							return true;
						}
					}
					return false;
				}
				return false;
			}
			return false;
		}

		public function wpRPG_user_register($user_id) {
			global $wpdb;
			$wpdb->show_errors();
			if(!$this->wpRPG_is_playing($user_id))
			{
				$wpdb->insert(
						$wpdb->prefix . "rpg_usermeta", array(
					'pid' => $user_id
						), array(
					'%d'
						)
				);
				$wpdb->query("UPDATE ". $wpdb->prefix ."rpg_usermeta SET race = ".$_POST['race']." WHERE pid = $user_id");
			}
		}

		public function wpRPG_get_current_users_activated() {
			global $wpdb;
			$wpdb->show_errors();
			$user_ids = $wpdb->get_col(
					"
															SELECT        ID
															FROM $wpdb->users
															"
			);
			foreach ($user_ids as $id) {
				if ($wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "rpg_usermeta WHERE pid = " . $id) == null) {
					$this->wpRPG_user_register($id);
				}
			}
		}

		public function wpRPG_settings_page_init() {
			$settings_page = add_menu_page('Wordpress RPG Options', ' WP-RPG', 'manage_options', 'wpRPG_menu', array($this, 'wpRPG_settings_page'));
			add_action("load-{$settings_page}", array($this, 'wpRPG_load_settings_page'));
		}

		public function wpRPG_load_settings_page() {
			if (isset($_POST["wpRPG-settings-submit"]) && $_POST["wpRPG-settings-submit"] == 'Y') {
				check_admin_referer("wpRPG-settings-page");
				$this->wpRPG_SaveSettings();
				$url_parameters = isset($_GET['tab']) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
				wp_redirect(admin_url('admin.php?page=wpRPG_menu&' . $url_parameters));
				exit;
			}
		}
		
		public function wpRPG_get_admin_tab_header()
		{
			$tabs = $this->default_tabs;
			$admin_tabs = apply_filters('wpRPG_add_admin_tab_header', $tabs);
			return $admin_tabs;
		}
		
		public function wprpg_default_tabs($tab)
		{
			switch($tab)
			{
				case 'cron':
					$html = "<tr>";
					$html .= "<td>";
					$html .= "<h3>Cron Information</h3>";
					$html .= "</td>";
					$html .= "</tr>";
					$html .= "<tr>";
					$html .= "<td>";
					$html .= "<span class='description'>Last Cron:</span>"; 
					$html .= "<span>". date('Y-m-d:H:i:s', get_option('wpRPG_last_cron')) ."</span>";
					$html .= "</td>";
					$html .= "</tr>";
					$html .= "<tr>";
					$html .= "<td>";
					$html .= "<span class='description'>Number of 30mins since then:</span>"; 
					$html .= "<span>". $this->wpRPG_time_elapsed(get_option('wpRPG_last_cron')) ."</span>";
					$html .= "</td>";
					$html .= "</tr>";
					$html .= "<tr>";
					$html .= "<td>";
					$html .= "<span class='description'>Next Cron:</span>"; 
					$html .= "<span>" .date('Y-m-d:H:i:s', get_option('wpRPG_next_cron'))."</span>";
					$html .= "</td>";
					$html .= "</tr>";
					return $html;
					break;
				case 'homepage':
					$html = "<tr>";
					$html .= "<td>";
					$html .= "<h3>Welcome to Wordpress RPG!</h3>";
					$html .= "</td>";
					$html .= "</tr>";
					$html .= "<tr>";
					$html .= "<td>";
					$html .= "<span class='description'>Total Players:</span>";
					$users = count_users();
					$html .= "<span>". $users['total_users'] ."</span>";
					$html .= "</td>";
					$html .= "</tr>";
					return $html;
					break;
				case 'pages':
					$html = "<tr>";
					$html .= "<td>";
					$html .= "<h3>Pages!</h3>";
					$html .= "</td>";
					$html .= "</tr>";
					$html .= "<tr>";
					$html .= "<td>";
					$html .= "<table border=1><tr><th>Module Page</th><th>Wordpress Page</th></tr>";
					$html .= "<tr><td>Profile</td><td>".$this->wpRPG_get_pages_select_html()."</td></tr>";
					$html .= "</table>";
					$html .= "</td>";
					$html .= "</tr>";
					return $html;
					break;
			}
		}
		
		public function wpRPG_get_pages_select_html()
		{
			$html = "<select name='wpRPG_Profile_Page'>";
			$pages = $this->wpRPG_get_pages();
			foreach ($pages as $page => $title) {
				$html .= "<option name='".$title['id']."' value='".$title['id']."' ". (get_option('wpRPG_Profile_Page')==$title['id']?'selected=1':'').">".$title['title']."</option>";
			}
			$html .= "</select>";
			return $html;
		}
		
		public static function wpRPG_get_pages()
		{
			$parry = array();
			$pages = get_pages(); 
			foreach ( $pages as $page ) {
				$new = $parry;
				
				$parry = array_merge($new, array(array('title' => $page->post_title, 'id'=>$page->ID)));
			}
			//wp_die(var_dump($parry));
			return $parry;
		}
		
		public function wpRPG_get_admin_tabs()
		{
			$tabs = array('cron'=> $this->wprpg_default_tabs('cron'), 'homepage'=> $this->wprpg_default_tabs('homepage'), 'pages'=> $this->wprpg_default_tabs('pages'));
			$admin_tabs = apply_filters('wpRPG_add_admin_tabs', $tabs);
			return $admin_tabs;
		}
		
		public function wpRPG_admin_tabs($current = 'homepage') {
			$tabs = $this->wpRPG_get_admin_tab_header();
			$links = array();
			echo '<div id="icon-themes" class="icon32"><br></div>';
			echo '<h2 class="nav-tab-wrapper">';
			foreach ($tabs as $tab => $name) {
				$class = ( $tab == $current ) ? ' nav-tab-active' : '';
				echo "<a class='nav-tab$class' href='?page=wpRPG_menu&tab=$tab'>$name</a>";
			}
			echo '</h2>';
		}

		public function wpRPG_settings_page() {
			global $pagenow;
			$settings = get_option("wpRPG_theme_settings");
			?>

			<div class="wrap">
				<h2>wpRPG Settings</h2>

			<?php
			if (isset($_GET['updated']) && 'true' == esc_attr($_GET['updated']))
				echo '<div class="updated" ><p>wpRPG updated.</p></div>';

			if (isset($_GET['tab']))
				$this->wpRPG_admin_tabs($_GET['tab']);
			else
				$this->wpRPG_admin_tabs('homepage');
			?>

				<div id="poststuff">
					<form method="post" name="options">
			<?php
			settings_fields( 'rpg-settings' );
	//		do_settings_sections( 'rpg-settings' );
			wp_nonce_field("wpRPG-settings-page");

			if ($pagenow == 'admin.php' && $_GET['page'] == 'wpRPG_menu') {

				if (isset($_GET['tab']))
					$tab = $_GET['tab'];
				else
					$tab = 'homepage';

				echo '<table class="form-table">';
				$tabs = $this->wpRPG_get_admin_tabs();
				echo $tabs[$tab];
				echo '</table>';
						}
						?>
						<p class="submit" style="clear: both;">
							<input type="hidden" name="wpRPG-settings-submit" id="wpRPG-settings-submit" value="Y" />
							<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
						</p>
					</form>

				</div>

			</div>
			<?php
		}
		   //1. Add a new form element...
		
		function register_form (){
			$race = ( isset( $_POST['race'] ) ) ? $_POST['race']: '';
			?>
			<p>
				<label for="race"><?php _e('Race','mydomain') ?><br />
					<select id="race" name="race">
					<?php
						global $wpdb;
						$races = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."rpg_race");
						foreach($races as $race)
						{
							?>
								<option value="<?php echo esc_attr(stripslashes($race->ID)); ?>" ><?php echo $race->title ?> </option>
							<?php
						}
							?>
					</select>
				</label>
			</p>
			<?php
		}

		//2. Add validation. In this case, we make sure first_name is required.
		function registration_errors ($errors, $sanitized_user_login, $user_email) {

			if ( empty( $_POST['race'] ) )
				$errors->add( 'race_error', __('<strong>ERROR</strong>: You must include a first name.','mydomain') );

			return $errors;
		}
	}
}

if(!class_exists('wpRPG_Profiles'))
{
	class wpRPG_Profiles extends wpRPG
	{
		
		function __construct()
		{
			parent::__construct();
			add_shortcode('view_profile', array($this, 'profileShortCodeVars'));
			add_shortcode('permalink',array($this, 'custom_permalink') );
			add_action('wp_ajax_profile', array($this,'profileCallback'));
			add_action('wp_ajax_nopriv_profile', array($this,'profileCallback'));
			add_action('wp_loaded', array($this,'flushRules') );
			add_filter('rewrite_rules_array', array($this, 'rewriteRules') );
			add_filter('query_vars',array($this, 'insertQueryVars') );
			add_filter('wpRPG_add_admin_tab_header', array($this, 'addAdminTab_Header'));
			add_filter('wpRPG_add_admin_tabs', array($this, 'addAdminTab'));
			if (!is_admin()) 
			{
				add_action('wp_enqueue_scripts', array($this,'includeJquery'));
				add_action('wp_footer', array($this,'includedJS'));
			}
		}

		function addAdminTab($tabs)
		{
			$tab_page = array('profile'=>$this->profileOptions(1));
			return array_merge($tabs, $tab_page);
		}
		
		function addAdminTab_Header($tabs)
		{
			$profile_tabs = array('profile'=>'Profile Settings');
			return array_merge($tabs, $profile_tabs);
		}

		function profileOptions($opt = 0) 
		{
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
			if(!$opt)
				echo $html;
			else
				return $html;
		}
			
		function includeJquery() 
		{
			wp_enqueue_script('jquery');
		}

		function profileShortCodeVars($atts)
		{
			global $current_user;
			extract( shortcode_atts( array(
				'pid' => 0,
				), $atts, 'view_profile') );
			return $this->getProfile($pid);
		}
		
		static function getProfile($player_id ) 
		{
			global $wpdb, $current_user;
			$username = get_query_var( 'username' );
			if($player_id != 0 || !empty($username))
			{
				$sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND u.id=". $player_id." OR u.user_nicename='". $username ."'";
				$res = $wpdb->get_results($sql);
				if($res)
				{
					global $wp;
					  $return_template = dirname( __FILE__ ) . '/templates/view_profile.php';
					  include($return_template);
						die();					  
					//wp_die(var_dump($res));
				}else{
					$result = '<div id="rpg_area">';
					$result .= '<h1>User Not Found</h1>';
					$result .= '</div><br/><br/>';
					$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
					$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>'; 
				}
			}else{
				return wpRPG_Profiles::getProfile($current_user->ID);
			}
			return $result;
		}
			
		function flushRules() 
		{
			$rules = get_option( 'rewrite_rules' );
			if ( ! isset( $rules['(profile)/(.+)$'] ) ) {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
			}
			 
		}

		function rewriteRules( $rules ) 
		{
			$newrules = array();
			$newrules['(profile)/(.+)$'] = 'index.php?pagename=$matches[1]&username=$matches[2]';
			 
			return $newrules + $rules;
		}

		function insertQueryVars( $vars ) 
		{
		 
			array_push($vars, 'username');
			 
			return $vars;
		}
			
		function includedJS() {
			global $current_user;
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('a#view-profile').click(function(event) {
						event.preventDefault();
						var them = $(this).attr('name');
						$.ajax({
							method: 'post',
							url: '<?php echo site_url('wp-admin/admin-ajax.php')?>',
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
		
		function profileCallback() 
		{
				echo $this->getProfile($_POST['user']);
				die();
		}
		
	}
}

if(!class_exists('wpRPG_Members'))
{
	class wpRPG_Members extends wpRPG
	{

		function __construct()
		{
			parent::__construct();
			add_shortcode('list_players', array($this, 'listPlayers'));
		}

		function listPlayers() {
			global $wpdb, $current_user;
			$sql = "SELECT um.hp, um.xp, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id";
			$res = $wpdb->get_results($sql);
			$result = '<div id="rpg_area"><table id="members" border=1>';
			$result .= '<tr><th>MemberName</th><th>XP</th><th>HP</th><th>Level</th>'.(is_user_logged_in()?'<th>Actions</th>':'').'</tr>';
			foreach ($res as $u) {
				$result .= '<tr id="player_'.$u->ID.'"><td><a href="" id="view-profile" name="'.$u->ID.'">' . $u->user_nicename . '</a></td><td>' . $u->xp . '</td><td>' . $u->hp . '</td><td>' . $this->wpRPG_player_level($u->xp) . '</td><td>';
					if(is_user_logged_in()){
						$result .= ($u->ID != $current_user->ID? $this->listPlayers_getLoggedIn_Actions($u->ID):'');
					}
			$result .= '</td></tr>';
			}
			$result .= '</table></div>';
			$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
			$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>';
			return $result;
		}
		
		function listPlayers_getLoggedIn_Actions($uid)
		{
			$result = apply_filters('listPlayers_Loggedin_Actions', array('',$uid));
			return $result[0];
		}

	}
}

if(!class_exists('wpRPG_Hospital'))
{
	class wpRPG_Hospital extends wpRPG
	{

		function __construct()
		{
			parent::__construct();
			add_shortcode('wprpg_hospital', array($this, 'showHospital'));
			add_action('wp_ajax_hospital', array($this,'hospitalCallback'));
			add_action('wp_ajax_nopriv_hospital', array($this,'hospitalCallback'));
			if (!is_admin()) 
			{
				add_action('wp_footer', array($this,'includedJS'));
			}
		}

		function showHospital() 
		{
			global $wpdb, $current_user;
			$sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND um.hp<100 AND u.id=". $current_user->ID;
			$res = $wpdb->get_results($sql);
			if($res)
			{
				//wp_die(var_dump($res));
				$result = '<div id="rpg_area">';
				$result .= '
								<h1>Hospital</h1>
								<div class="simpleTabsContent" id="bio" style="height:500px;">
									<div name="player_heading">
										<h3>'.$res[0]->user_nicename.'</h3>
									</div>
									<div>
<table width=100% style="text-align:center;"><tr><td>Current HP : '. $res[0]->hp .' out of 100. <button id="replenish-hp">Full Heal ('.(100 - $res[0]->hp).' Gold)</button></td></tr></table>
									</div>
									<br/>
									
								</div>';
				$result .= '</div><br/><br/>';
			}else{
				$result = '<div id="rpg_area">';
				$result .= '<h1>Hospital</h1>
<table width=100% style="text-align:center;"><tr><td><h3>You\'re Already Fully Healed! Come Back When You Need Help!</h3></td></tr></table>
									</div>
									<br/>
									
								</div>';
				$result .= '</div><br/><br/>';
				$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>'; 
			}
			return $result;
		}
		
		function includedJS() {
			global $wpdb,$current_user;
			$sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND u.id=". $current_user->ID;
			$res = $wpdb->get_results($sql);
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('button#replenish-hp').click(function(event) {
						event.preventDefault();
						var them = '<?php echo $current_user->ID?>';
						var cost = '<?php echo (100-$res[0]->hp)?>';
						$.ajax({
							method: 'post',
							url: '<?php echo site_url('wp-admin/admin-ajax.php')?>',
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
		
		function buyHealthCare($uid, $hp, $cost)
		{
			global $wpdb;
			$sql = "UPDATE " . $wpdb->prefix. "rpg_usermeta SET hp=hp+$hp, gold=gold-$cost WHERE pid=$uid";
			$wpdb->query($sql);
			wpRPG_Profiles::getProfile($uid); 
		}
		
		function hospitalCallback() 
		{
			global $wpdb, $current_user;
			$sql = "SELECT um.*, u.* FROM " . $wpdb->prefix . "rpg_usermeta um JOIN " . $wpdb->base_prefix . "users u WHERE um.pid=u.id AND u.id=". $current_user->ID;
			$res = $wpdb->get_results($sql);
			//wp_die(var_dump($res[0]));
			if($res[0]->gold >= $_POST['cost'])
			{
				$this->buyHealthCare($res[0]->ID,$res[0]->hp,$_POST['cost']);
				die();
			}else{
				echo $this->showHospital();
				die();
			}
		}
		

	}
}

class testit extends wpRPG
{
	function __construct()
	{
		parent::__construct();
		add_filter('listPlayers_Loggedin_Actions', array($this, 'add_filterss'));
	}
	
	function add_filterss($var)
	{
		//$id = $var[1];
		//return array($var[0].'<button id="attack" name="'.$var[1].'">Attack</button>', $var[1]);
		wp_die($this->file_name);
	}
}


$rpg = new wpRPG;
if (is_admin()) {
    add_action('admin_menu', array($rpg, 'wpRPG_settings_page_init'));
    add_action('admin_init', array($rpg, 'wpRPG_RegisterSettings'));
}
if(!is_admin())
{
	add_action('init', array($rpg, 'updateLastActive'));
	add_action('register_form',array($rpg, 'register_form'));
			
}
$plugin_slug = basename(dirname(__FILE__));

register_activation_hook(__FILE__, array($rpg, 'wpRPG_on_activation'));
register_deactivation_hook(__FILE__, array($rpg, 'wpRPG_on_deactivation'));
$profiles = new wpRPG_Profiles;
$members = new wpRPG_Members;
$hospital = new wpRPG_Hospital;

?>