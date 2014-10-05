<?php
/*
Plugin Name: WPRPG Shop (Official Sample)
Plugin URI: http://wordpress.org/extend/plugins/wprpg/
Version: 1.0.0
WPRPG: 1.0.19
Author: <a href="http://tagsolutions.tk">Tim G.</a>
Description: Creates a Shop concept to coincide with the Official Shop sample
Text Domain: wp-rpg
License: GPL3
*/
if ( !class_exists( 'wpRPG_Shop' ) ) {
    class wpRPG_Shop extends wpRPG {
        
        function __construct( ) {
            parent::__construct();
			require_once("Shop_class.php");
			add_action( 'init', array($this, 'wpRPG_Shop_load_language'));
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			add_shortcode( 'wprpg_Shop', array(
                 $this,
                'showShop' 
            ) );
            add_action( 'wp_ajax_Shop', array(
                 $this,
                'ShopCallback' 
            ) );
            add_action( 'wp_ajax_nopriv_Shop', array(
                 $this,
                'ShopCallback' 
            ) );
            if ( !is_admin() ) {
                add_action( 'wp_footer', array(
                     $this,
                    'includedJS' 
                ) );
            }
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
        
		function wpRPG_Shop_load_language(){
			load_plugin_textdomain('wpRPG-Shop', false, (basename(dirname(dirname(__DIR__))) == 'wprpg'?'/wprpg/plugins/':'').basename( dirname( __FILE__ ) ) . '/languages' );
		}
		
		public function add_page_settings( $pages ) {
			$setting = array(
				'Shop'=> array('name'=>'Shop', 'shortcode'=>'[wprpg_Shop]')
			);
			return array_merge( $pages, $setting );
		}
		
		public function register_settings() {
			if ( !get_option( 'wpRPG_Shop_Page' ) ) {
                add_option( 'wpRPG_Shop_Page', 'Shop', "", "yes" );
            }
			$this->check_tables();
			
        }
	
		function check_tables() 
		{
			global $wpdb;
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_Shop (
									id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									name varchar(50) NOT NULL,
									description varchar(150) NOT NULL)";
			$wpdb->query($sql);
			$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "rpg_Shop_inventory (
									id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
									shop_id int(11) NOT NULL,
									item_id int(11) NOT NULL,
									item_count int(11) NOT NULL,
									item_cost_bonus int(11) NOT NULL DEFAULT '0',
									item_cost_increase int(11) NOT NULL DEFAULT '0'
									)";
			$wpdb->query($sql);
			return true;
		}
		
		function add_Jquery_Code($code)
		{
			global $current_user;
			$attack_code = array(
				"$('#FormSubmit').click(function (e) {
					e.preventDefault();
					if($('#wprpg_Shop').length != 0){
						
						
						var myData = 'title_txt='+ $('#itemTitle_txt').val() +'&cat_txt='+$('#itemCats').val()+'&min_txt='+ $('#itemMin_txt').val()+'&price_txt='+ $('#itemPrice_txt').val()+'&wprpg_Shop='+ $('#wprpg_Shop').val(); //post variables
						
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
			if($('#wprpg_Shop').length != 0){
				var func = 'wprpg_Shop';
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
		
        function showShop( $atts ) {
            global $wpdb;
			if(is_user_logged_in()){
				if(!isset($atts['storeid']) or !$atts['storeid'])
					$atts['storeid'] = 0;
				$shop = new wpRPG_Shoppette($atts['storeid']);
				if(file_exists(get_template_directory() . 'templates/wprpg/Shop.php')){
					ob_start();
					include (get_template_directory() . 'templates/wprpg/Shop.php');
					$result = ob_get_clean();
				}else{
					ob_start();
					include(__DIR__ .'/templates/Shop.php');
					$result = ob_get_clean();
				}
				return $result;
			}else{
				$result = '<div id="rpg_area">';
				$result .= '<h1>'.__("Plugin_Title", "wpRPG-Shop").'</h1>
								<table width=100% style="text-align:center;">
									<tr>
										<td><h3>'.__("Must_Be_Logged_In_MSG", "wpRPG-Shop").'</h3></td>
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
									'action': 'Shop',
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
				
		function add_plugin( $plugins )
		{
			$my_plug = array(
				'Shop'=>array('name'=>'wpRPG_Shop', 'version'=>'1.0.3', 'author'=>'Tim Garrity', 'description'=>'Creates a Shop concept')
			);
			return array_merge($plugins, $my_plug);
		}
		
		function addAdminTab( $tabs ) {
            $tab_page = array(
                 'Shop' => $this->ShopOptions( 1 ),
				 'StoreItems' => $this->ShopItemsOptions(1)
            );
            return array_merge( $tabs, $tab_page );
        }
        
        function addAdminTab_Header( $tabs ) {
            $profile_tabs = array(
                 'Shop' => 'Shop Settings' 
            );
            return array_merge( $tabs, $profile_tabs );
        }
        
        function ShopOptions( $opt = 0 ) {
			global $wpdb;
			$html = "<tr>";
			$html .= "<td>";
			$html .= "<h3>". __("Plugin_Title", "wpRPG-Shop"). "</h3>";
			$html .= "</td>";
			$html .= "</tr>";
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<table border=1 id='responds'><thead><tr><th>Name</th><th>Short Code</th><th>Description</th><th>Actions</th></tr></thead>
				<tbody>";
				$html .= "<input type='hidden' id='wprpg_Shop' name='wprpg_Shop' value=1 /><div class='content_wrapper'>";
						$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_Shop";
						//get all records from add_delete_record table
						foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
						{
							$html .= '<tr id="s_'.$row["id"].'"><td>'.$row["name"].'</td>';
							$html .= '<td>[wpRPG_Shop storeid="'.$row["id"].'"]</td>';
							$html .= '<td>'.$row["description"].'</td>';
							$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
							$path = plugins_url('images/icon_delete.gif', __FILE__);
							$html .= '<img src="'.$path .'" border="0" />';
							$html .= '</a><a href="admin.php?page=wpRPG_menu&tab=StoreItems&storeid='. $row["id"] .'">Inventory</a></div></td></tr>';
						}
							$html .= "</tbody><tfoot><tr><td><input type='text' name='itemTitle_txt' id='itemTitle_txt' /></td><td>This is automatically made</td><td>
							<input type='text' name='shopDescription_txt' id='shopDescription_txt' /></td><td><button id='FormSubmit'>Add record</button></td></tr></tfoot></table>
			
										
										</div>
										</div>";
			
			$html .= "</td>";
			$html .= "</tr>";
            if ( !$opt )
                echo $html;
            else
                return $html;
        }
        
		function ShopItemsOptions( $opt = 0 ) {
			global $wpdb;
			$html = "<tr>";
			$html .= "<td>";
			$html .= "<h3>". __("Shop Inventory", "wpRPG-Shop-Bonuses"). "</h3>";
			$html .= "</td>";
			$html .= "</tr>";
			$html .= "<tr>";
			$html .= "<td>";
			$html .= "<table border=1 id='responds'><thead><tr><th>Item</th><th>Price Bonus</th><th>Increase/Decrease</th><th>Qty</th><th>Actions</th></tr></thead>
				<tbody>";
				$html .= "<div class='content_wrapper'>";
						if(isset($_GET['storeid']) && is_numeric($_GET['storeid']) && $_GET['tab']=='StoreItems'){
						$Result = "SELECT * FROM " . $wpdb->prefix . "rpg_shop_inventory as Inventory INNER JOIN ".$wpdb->prefix."rpg_items as Items ON Items.id=Inventory.item_id WHERE Inventory.shop_id=". $_GET['storeid'];
						foreach ($wpdb->get_results($Result, ARRAY_A) as $row)
						{
							
							$html .= '<tr id="item_'.$row["id"].'"><td>'.ucfirst($row['name']).'</td>';
							$html .= '<td>'.$row["item_cost_bonus"].'</td>';
							$html .= '<td>'.ucfirst(($row["item_cost_increase"]?'Increase':'Decrease')).'</td>';
							$html .= '<td>0</td>';
							$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$row["id"].'">';
							$path = plugins_url('images/icon_delete.gif', __FILE__);
							$html .= '<img src="'.$path .'" border="0" />';
							$html .= '</a></div></td></tr>';
						}
							$html .= "</tbody>
									<tfoot>
										<tr>
											<td><select name='bonus_txt' id='bonus_txt'>";
											$sql = "SELECT * FROM " . $wpdb->prefix . "rpg_items";
											foreach($wpdb->get_results($sql, ARRAY_A) as $item)
											{
												$html .= "<option value='". $item["id"]."'>".ucfirst($item["name"])."</option>";
											}
											$html.="</select></td>
											<td>
												<input type='text' name='value_txt' id='value_txt' value='0' />
											</td>
											<td>
												<select name='increase_txt' id='increase_txt'>
													<option value=1>Increase</option>
													<option value=0>Decrease</option>
												</select>
											</td>
											<td>
												<input type='text' id='item_qty' name='item_qty' value='0' />
											</td>
											<td>
												<input type=hidden id='wprpg_item_id' name='wprpg_item_id' value=".$_GET['storeid']." />
												<input type=hidden id='wprpg_item_bonus' name='wprpg_item_bonus' value=1 />
												<button id='BonusSubmit'>Add Item to Inventory</button>
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
	include_once('/wpRPG_Shop_GetPost_functions.php');	
}