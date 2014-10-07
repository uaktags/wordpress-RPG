<?php
/*
Plugin Name: WPRPG Items (Official Sample)
Plugin URI: http://wordpress.org/extend/plugins/wprpg/
Version: 1.0.0
WPRPG: 1.0.19
Author: <a href="http://tagsolutions.tk">Tim G.</a>
Description: Creates a Items concept
Text Domain: wp-rpg
License: GPL3
*/
if ( !class_exists( 'wpRPG_Items' ) ) {
    class wpRPG_Items extends wpRPG {
        
        function __construct( ) {
            parent::__construct();
			require_once("items_class.php");
			add_action( 'init', array($this, 'wpRPG_Items_load_language'));
			add_action( 'admin_init', array( $this, 'register_settings' ) );
            add_shortcode( 'wprpg_Inventory', array(
                 $this,
                'showInventory' 
            ) );
            add_action( 'wp_ajax_Items', array(
                 $this,
                'ItemsCallback' 
            ) );
            add_action( 'wp_ajax_nopriv_Items', array(
                 $this,
                'ItemsCallback' 
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
			add_filter( 'wpRPG_add_plugin_code', array(
				$this, 
				'add_Jquery_Code'
			) );
		}
        
		function wpRPG_Items_load_language(){
			load_plugin_textdomain('wpRPG-Items', false, (basename(dirname(dirname(__DIR__))) == 'wprpg'?'/wprpg/plugins/':'').basename( dirname( __FILE__ ) ) . '/languages' );
		}
		
		public function add_page_settings( $pages ) {
			$setting = array(
				'Inventory'=> array('name'=>'Inventory', 'shortcode'=>'[wprpg_Inventory]'),
				'Shop'=> array('name'=>'Shop', 'shortcode'=>'[wprpg_Shop]')
			);
			return array_merge( $pages, $setting );
		}
		
		public function register_settings() {
			if ( !get_option( 'wpRPG_Inventory_Page' ) ) {
                add_option( 'wpRPG_Inventory_Page', 'Inventory', "", "yes" );
            }
			if ( !get_option( 'wpRPG_Shop_Page' ) ) {
                add_option( 'wpRPG_Shop_Page', 'Shop', "", "yes" );
            }
			$this->check_tables();
			
        }
	
		function check_tables() 
		{
			global $wpdb;
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_items (
									id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									name varchar(50) NOT NULL,
									description varchar(150) NOT NULL,
									price int(11) NOT NULL DEFAULT '0',
									levelreq INT(10) UNSIGNED NOT NULL DEFAULT '0',
									cat_id int(11) NOT NULL )";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_items_cats (
									id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									name varchar(50) NOT NULL,
									description varchar(150) NOT NULL,
									equipable tinyint(1) NOT NULL DEFAULT '1'
									)";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_items_inventory (
									id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									player_id int(11) NOT NULL,
									equipped int(11) NOT NULL DEFAULT '0',
									item_id int(11) NOT NULL
									)";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_items_actions (
									id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									action varchar(50) NOT NULL,
									description text NOT NULL)";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_items_action_values (
									id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									item_id int(11) NOT NULL,
									action_id int(11) NOT NULL,
									value int(11) NOT NULL DEFAULT '0',
									increase tinyint(1) NOT NULL DEFAULT '1')";
			$wpdb->query($sql);
			return true;
		}
		
		function add_Jquery_Code($code)
		{
			global $current_user;
			$attack_code = array(
				"$('#FormSubmit').click(function (e) {
					e.preventDefault();
					if($('#wprpg_items').length != 0){
						
						
						var myData = 'title_txt='+ $('#itemTitle_txt').val() +'&cat_txt='+$('#itemCats').val()+'&min_txt='+ $('#itemMin_txt').val()+'&price_txt='+ $('#itemPrice_txt').val()+'&wprpg_items='+ $('#wprpg_items').val(); //post variables
						
						jQuery.ajax({
							type: 'POST', // HTTP method POST or GET
							url: '". site_url( 'wp-admin/admin-ajax.php' )."', //Where to make Ajax calls
							dataType:'text', // Data type, HTML, json etc.
							data:myData, //post variables
							success:function(response){
							$('#responds').append(response);
							$('#itemTitle_txt').val('');
							$('#itemMin_txt').val('');
							$('#itemPrice_txt').val('');//empty text field after successful submission
							
							},
							error:function (xhr, ajaxOptions, thrownError){
								alert(thrownError); //throw any errors
							}
						});
					}});
			
			$('#BonusSubmit').click(function (e) {
				e.preventDefault();
				console.log('clicked on Add New Bonus');
				if($('#wprpg_item_bonus').length != 0){
					console.log('everythings set lets proceed')
					if($('#bonus_txt').val()==='') //simple validation
					{
						alert('Please enter some text!');
						return false;
					}
					
					var myData = 'bonus_txt='+ $('#bonus_txt').val() +'&value_txt='+ $('#value_txt').val()+ '&wprpg_item_id='+ $('#wprpg_item_id').val()+ '&increase_txt='+ $('#increase_txt').val() +'&wprpg_item_bonus='+ $('#wprpg_item_bonus').val(); //post variables
					console.log('submitting ' + myData );
					jQuery.ajax({
						type: 'POST', // HTTP method POST or GET
						url: '". site_url( 'wp-admin/admin-ajax.php' )."', //Where to make Ajax calls
						dataType:'text', // Data type, HTML, json etc.
						data:myData, //post variables
						success:function(response){
						$('#responds').append(response);
						$('#bonus_txt').val('');
						$('#value_txt').val('');
						$('#increase_txt').val('');//empty text field after successful submission
						},
						error:function (xhr, ajaxOptions, thrownError){
							alert(thrownError); //throw any errors
						}
					});
				}
			});
			$('#CatSubmit').click(function (e) {
			e.preventDefault();
			console.log('clicked on Add New Category');
			if($('#wprpg_item_cats').length != 0){
				console.log('cats doesnt = 0')
				if($('#title_txt').val()==='') //simple validation
				{
					alert('Please enter some text!');
					return false;
				}
				if($('#equipable_txt').val()==='')
				{
					alert('Please enter some text!');
					return false;
				}
				if($('#description_txt').val()==='')
				{
					alert('Please enter some text!');
					return false;
				}
				
				var myData = 'title_txt='+ $('#title_txt').val() +'&description_txt='+$('#description_txt').val()+'&equipable_txt='+$('#equipable_txt').val()+'&wprpg_item_cats='+ $('#wprpg_item_cats').val(); //post variables
				
				jQuery.ajax({
					type: 'POST', // HTTP method POST or GET
					url: '". site_url( 'wp-admin/admin-ajax.php' )."', //Where to make Ajax calls
					dataType:'text', // Data type, HTML, json etc.
					data:myData, //post variables
					success:function(response){
					$('#respondsCats').append(response);
					$('#title_txt').val('');
					$('#description_txt').val('');
					$('#equipable_txt').val('');//empty text field after successful submission
					
					},
					error:function (xhr, ajaxOptions, thrownError){
						alert(thrownError); //throw any errors
					}
				});
			}});
			$('#responds').on('click', 'a.del_button',(function(e) {
			e.preventDefault();
			var clickedID = this.id.split('-'); //Split string (Split works as PHP explode)
			var DbNumberID = clickedID[1]; //and get number from array
			if($('#wprpg_items').length != 0){
				var func = 'wprpg_items';
			}
			var myData = 'recordToDelete='+ DbNumberID + '&' + func + '=1'; //build a post data structure
			
			jQuery.ajax({
				type: 'POST', // HTTP method POST or GET
				url: '".site_url( 'wp-admin/admin-ajax.php' )."', //Where to make Ajax calls
				dataType:'text', // Data type, HTML, json etc.
				data:myData, //post variables
				success:function(response){
				//on success, hide element user wants to delete.
					$('#item_'+DbNumberID).fadeOut('slow');
				},
				error:function (xhr, ajaxOptions, thrownError){
					//On error, we alert user
					alert(thrownError);
				}
			});
		}));"
			);
			return array_merge($code, $attack_code);
		}
		
        function showInventory( ) {
            global $wpdb;
			if(is_user_logged_in()){
				$current_user = wp_get_current_user();
				$res = new wpRPG_Player($current_user->ID);
				if ( $res ) {
					$this->checkUserMeta($current_user->ID);
					if(file_exists(get_template_directory() . 'templates/wprpg/Inventory.php')){
						ob_start();
						include (get_template_directory() . 'templates/wprpg/Inventory.php');
						$result = ob_get_clean();
					}else{
						ob_start();
						include(__DIR__ .'/templates/Inventory.php');
						$result = ob_get_clean();
					}
				} else {
					$result = '<div id="rpg_area">';
					$result .= '<h1>'.__("Plugin_Title", "wpRPG-Items").'</h1>
								<table width=100% style="text-align:center;">
								<tr>
									<td>
										<h3>'.__("Error_MSG_Player_Inventory_Not_Found", "wpRPG-Items").'</h3>
									</td>
								</tr>
								</table>
										
							   </div>
										<br/>
										
								';
					//$result .= '</div><br/><br/>';
					if ( get_option ( 'show_wpRPG_Version_footer' ) )	{
						$result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
						$result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG '. $this->plug_version .'</a></footer>';
					}
				}
				return $result;
			}else{
				$result = '<div id="rpg_area">';
				$result .= '<h1>'.__("Plugin_Title", "wpRPG-Items").'</h1>
								<table width=100% style="text-align:center;">
									<tr>
										<td><h3>'.__("Must_Be_Logged_In_MSG", "wpRPG-Items").'</h3></td>
									</tr>
								</table>
							</div>
									<br/>
									
								';
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
									'action': 'Items',
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
        
        function ItemsCallback( ) {
            global $wpdb;
			if(is_user_logged_in()){
				$current_user = wp_get_current_user();
				$res = new wpRPG_Player($current_user->ID);
				if ( $res->gold >= $_POST[ 'cost' ] ) {
					_e("Now_Full_Health_MSG", "wpRPG-Items");
					$this->buyHealthCare( $res->ID, 100 - $res->hp, $_POST[ 'cost' ] );
					die( );
				} else {
					_e("Need_More_Gold_MSG", "wpRPG-Items");
					echo $this->showItems();
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
				 '30min_HPGain'=>array('class'=>'wpRPG_Items', 'func'=>'wpRPG_replenish_hp', 'duration'=>1800)
			);
			return array_merge( $crons, $my_crons );
		}
		
		function add_plugin( $plugins )
		{
			$my_plug = array(
				'Items'=>array('name'=>'wpRPG_Items', 'version'=>'1.0.3', 'author'=>'Tim Garrity', 'description'=>'Creates a Items concept')
			);
			return array_merge($plugins, $my_plug);
		}
		
		function addAdminTab( $tabs ) {
            $tab_page = array(
                 'Items' => $this->ItemsOptions( 1 ),
				 'Bonuses' => $this->ItemsBonusesOptions(1)
            );
            return array_merge( $tabs, $tab_page );
        }
        
        function addAdminTab_Header( $tabs ) {
            $profile_tabs = array(
                 'Items' => __('Plugin_Admin_Tab_Title', 'wpRPG-Items') 
            );
            return array_merge( $tabs, $profile_tabs );
        }
        
        function ItemsOptions( $opt = 0 ) {
			global $wpdb;
			$html = "<tr>";
			$html .= "<td>";
			$html .= "<h3>". __("Plugin_Title", "wpRPG-Items"). "</h3>";
			$html .= "</td>";
			$html .= "</tr>";
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<table border=1 id='respondsCats'><thead><tr><th>Name</th><th>Description</th><th>Equippable / Usable</th><th>Actions</th></tr></thead>
				<tbody>";
				$html .= "<input type='hidden' id='wprpg_item_cats' name='wprpg_item_cats' value=1 /><div class='content_wrapper'>";
						$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_items_cats";
						//get all records from add_delete_record table
						foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
						{
							$html .= '<tr id="item_cat_'.$row["id"].'"><td>'.$row["name"].'</td>';
							$html .= '<td>'.$row["description"].'</td>';
							$html .= '<td>'.($row["equipable"]?'Equippable':'Usable').'</td>';
							$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
							$path = plugins_url('images/icon_delete.gif', __FILE__);
							$html .= '<img src="'.$path .'" border="0" />';
							$html .= '</a></div></td></tr>';
						}
							$html .= "</tbody><tfoot><tr><td><input type='text' name='title_txt' id='title_txt' /></td>
							<td>
								<input type='text' name='description_txt' id='description_txt' />
							</td>
							<td>
								<select name='equipable_txt' id='equipable_txt'>
									<option value=1>Equippable</option>
									<option value=0>Usable</option>
								</select>
							</td>
							<td>
								<button id='CatSubmit'>Add Category</button>
							</td>
						</tr></tfoot></table>
			
										
										</div>
										</div>";
			
			$html .= "</td>";
			$html .= "</tr>";
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<table border=1 id='responds'><thead><tr><th>Name</th><th>Category</th><th>Level Req</th><th>Price</th><th>Actions</th></tr></thead>
				<tbody>";
				$html .= "<input type='hidden' id='wprpg_items' name='wprpg_items' value=1 /><div class='content_wrapper'>";
						$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_items";
						//get all records from add_delete_record table
						foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
						{
							$html .= '<tr id="item_'.$row["id"].'"><td>'.$row["name"].'</td><td></td>';
							$html .= '<td>'.$row["levelreq"].'</td>';
							$html .= '<td>'.$row["price"].'</td>';
							$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
							$path = plugins_url('images/icon_delete.gif', __FILE__);
							$html .= '<img src="'.$path .'" border="0" />';
							$html .= '</a><a href="admin.php?page=wpRPG_menu&tab=Bonuses&itemid='. $row["id"] .'">Bonuses</a></div></td></tr>';
						}
							$html .= "</tbody><tfoot><tr><td><input type='text' name='itemTitle_txt' id='itemTitle_txt' /></td><td><select id='itemCats' name='itemCats'>";
								$sql = "Select * FROM ". $wpdb->prefix ."rpg_items_cats";
								$opts = 0;
								foreach($wpdb->get_results($sql, ARRAY_A) as $row=>$val) 
								{
									$opts++;
									$html.= "<option>".$val['name']."</option>";
								}
								if(empty($opts))
									$html .= "<option>ERROR: You must create Categories First!</option>";
							$html.="</select></td><td>
							<input type='text' name='itemMin_txt' id='itemMin_txt' /></td><td><input type='text' name='itemPrice_txt' id='itemPrice_txt' /></td><td><button id='FormSubmit'>Add record</button></td></tr></tfoot></table>
			
										
										</div>
										</div>";
			
			$html .= "</td>";
			$html .= "</tr>";
            if ( !$opt )
                echo $html;
            else
                return $html;
        }
        
		function ItemsBonusesOptions( $opt = 0 ) {
			global $wpdb;
			$html = "<tr>";
			$html .= "<td>";
			$html .= "<h3>". __("Plugin_Title", "wpRPG-Items-Bonuses"). "</h3>";
			$html .= "</td>";
			$html .= "</tr>";
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<table border=1 id='responds'><thead><tr><th>Action</th><th>Value</th><th>Increase/Decrease</th><th>Actions</th></tr></thead>
				<tbody>";
				$html .= "<div class='content_wrapper'>";
						if(isset($_GET['itemid']) && is_numeric($_GET['itemid']) && $_GET['tab']=='Bonuses'){
						$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_items_action_values as ItemValues WHERE itemvalues.item_id=". $_GET['itemid'];
						//get all records from add_delete_record table
						foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
						{
							$bonusTxt = '';
							$sql = "SELECT * FROM ".$wpdb->prefix."rpg_player_metas WHERE id=".$row['action_id']." AND type!='time'";
							foreach($wpdb->get_results($sql, ARRAY_A) as $res)
								$bonusTxt = $res['name'];
							$html .= '<tr id="item_'.$row["id"].'"><td>'.ucfirst($bonusTxt).'</td>';
							$html .= '<td>'.$row["value"].'</td>';
							$html .= '<td>'.ucfirst(($row["increase"]?'Increase':'Decrease')).'</td>';
							$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
							$path = plugins_url('images/icon_delete.gif', __FILE__);
							$html .= '<img src="'.$path .'" border="0" />';
							$html .= '</a></div></td></tr>';
						}
							$html .= "</tbody>
									<tfoot>
										<tr>
											<td><select name='bonus_txt' id='bonus_txt'>";
											$sql = "SELECT * FROM " . $wpdb->prefix . "rpg_player_metas";
											foreach($wpdb->get_results($sql, ARRAY_A) as $meta)
											{
												$html .= "<option value='". $meta["id"]."'>".ucfirst($meta["name"])."</option>";
											}
											$html.="</select></td>
											<td>
												<input type='text' name='value_txt' id='value_txt' />
											</td>
											<td>
												<select name='increase_txt' id='increase_txt'>
													<option value=1>Increase</option>
													<option value=0>Decrease</option>
												</select>
											</td>
											<td>
												<input type=hidden id='wprpg_item_id' name='wprpg_item_id' value=".$_GET['itemid']." />
												<input type=hidden id='wprpg_item_bonus' name='wprpg_item_bonus' value=1 />
												<button id='BonusSubmit'>Add Bonus</button>
											</td>
										</tr>
									</tfoot>
								</table></div>	</div>";
						}
			
			$html .= "</td>";
			$html .= "</tr>";
            if ( !$opt )
                echo $html;
            else
                return $html;
		}
    }
	global $wpdb;
	if(isset($_POST["min_txt"]) && is_numeric($_POST["min_txt"]) && isset($_POST["wprpg_items"]))
	{
		//sanitize post value, PHP filter FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH
		$titleToSave = filter_var($_POST["title_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$minToSave = filter_var($_POST["min_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$priceToSave = filter_var($_POST["price_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$catIDToSave = filter_var($_POST["cat_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		//wp_die($contentToSave);
		$wpdb->show_errors();
		// Insert sanitize string in record
		if($wpdb->insert($wpdb->prefix . 'rpg_items', array('levelreq'=>$minToSave, 'cat_id'=>$catIDToSave, 'name'=>$titleToSave, 'price'=>$priceToSave), array('%d', '%s', '%d')))
		{
			//Record is successfully inserted, respond to ajax request
			$my_id = $wpdb->insert_id; //Get ID of last inserted record from MySQL
			echo '<tr id="item_'.$my_id.'"><td>'.$titleToSave.'</td>';
			echo '<td>'.$catIDToSave.'</td><td>'.$minToSave.'</td>';
			echo '<td>'.$priceToSave.'</td>';
			echo '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$my_id.'">';
			$path = plugins_url('images/icon_del.gif', __FILE__);
			echo '<img src="'.$path .'" border="0" />';
			echo '</a><a href="admin.php?page=wpRPG_menu&tab=Bonuses&itemid='. $my_id .'">Bonuses</a></div></td></tr>';   
			$_POST = array();
		}else{
			//output error
			header('HTTP/1.1 500 Looks like mysql error, could not insert record!');
			exit();
		}    
	} //Item Category AJAX Check
	elseif(isset($_POST["title_txt"]) && isset($_POST["wprpg_item_cats"]))
	{
		//sanitize post value, PHP filter FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH
		$titleToSave = filter_var($_POST["title_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$descriptionToSave = filter_var($_POST["description_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$equipableToSave = filter_var($_POST["equipable_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		//wp_die($contentToSave);
		$wpdb->show_errors();
		// Insert sanitize string in record
		if($wpdb->insert($wpdb->prefix . 'rpg_items_cats', array('name'=>$titleToSave,'description'=>$descriptionToSave,'equipable'=>$equipableToSave), array('%s','%s','%s')))
		{
			//Record is successfully inserted, respond to ajax request
			$my_id = $wpdb->insert_id; //Get ID of last inserted record from MySQL
			echo '<tr id="item_'.$my_id.'"><td>'.$titleToSave.'</td>';
			echo '<td>'.$descriptionToSave.'</td>';
			echo '<td>'.$equipableToSave.'</td>';
			echo '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$my_id.'">';
			$path = plugins_url('images/icon_del.gif', __FILE__);
			echo '<img src="'.$path .'" border="0" />';
			echo '</a></div></td></tr>';   
			$_POST = array();
		}else{
			//output error
			header('HTTP/1.1 500 Looks like mysql error, could not insert record!');
			exit();
		}    
	}//Bonus AJAX Check	
	elseif(isset($_POST["bonus_txt"]) && isset($_POST["wprpg_item_id"]) && is_numeric($_POST["wprpg_item_id"]) && isset($_POST["wprpg_item_bonus"]))
	{
		//sanitize post value, PHP filter FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH
		$bonusToSave = filter_var($_POST["bonus_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$itemIDToSave = filter_var($_POST["wprpg_item_id"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$valueToSave = filter_var($_POST["value_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$increaseToSave = filter_var($_POST["increase_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		//wp_die($contentToSave);
		$wpdb->show_errors();
		// Insert sanitize string in record
		if($wpdb->insert($wpdb->prefix . 'rpg_items_action_values', array('item_id'=>$itemIDToSave, 'action_id'=>$bonusToSave, 'value'=>$valueToSave, 'increase'=>$increaseToSave), array('%d','%d', '%d','%d')))
		{
			//Record is successfully inserted, respond to ajax request
			$my_id = $wpdb->insert_id; //Get ID of last inserted record from MySQL
			$bonusTxt = '';
			$sql = "SELECT * FROM ".$wpdb->prefix."rpg_player_metas WHERE id=$bonusToSave";
			foreach($wpdb->get_results($sql, ARRAY_A) as $res)
				$bonusTxt = $res['name'];
			echo '<tr id="item_'.$my_id.'"><td>'.$bonusTxt.'</td>';
			echo '<td>'.$valueToSave.'</td><td>'.($increaseToSave?'Increase':'Decrease').'</td>';
			echo '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$my_id.'">';
			$path = plugins_url('images/icon_del.gif', __FILE__);
			echo '<img src="'.$path .'" border="0" />';
			echo '</a></div></td></tr>';   
			$_POST = array();
		}else{
			//output error
			header('HTTP/1.1 500 Looks like mysql error, could not insert record!');
			exit();
		}    
	}elseif(isset($_POST["recordToDelete"]) && strlen($_POST["recordToDelete"])>0 && is_numeric($_POST["recordToDelete"]))
	{
		 $idToDelete = filter_var($_POST["recordToDelete"],FILTER_SANITIZE_NUMBER_INT);
		 if(isset($_POST['wprpg_items'])){
			if(!$wpdb->query("DELETE FROM ". $wpdb->prefix . "rpg_items WHERE id=".$idToDelete))
			{
			}
		}
	}
	if(isset($_GET['tab']) && $_GET['tab'] == 'Bonuses')
	{
		if(isset($_GET['itemid']) && is_numeric($_GET['itemid']))
		{
			$itemID = filter_var($_GET["itemid"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
			$wpdb->show_errors();
			$itemcount = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "rpg_items WHERE id=" . $itemID );
			if(!$itemcount)
			{
				header("Location:admin.php?page=wpRPG_menu&tab=Items");
				exit;
			}
		}else{
			header("Location:admin.php?page=wpRPG_menu&tab=Items");
			exit;
		}
	}
	if(isset($_GET['wprpg_itemCats'])){
		$sql = "Select * FROM ". $wpdb->prefix ."rpg_items_cats";
		$opts = 0;
		foreach($wpdb->get_results($sql, ARRAY_A) as $row=>$val) 
		{
			$opts++;
			echo "<option>".$val['name']."</option>";
		}
		if(empty($opts))
			echo "<option>ERROR: You must create Categories First!</option>";
		
	}
	
}