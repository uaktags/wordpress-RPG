<?php
global $wpdb;
	if(isset($_POST["title_txt"]) && isset($_POST["wprpg_Shop"]))
	{
		//sanitize post value, PHP filter FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH
		$titleToSave = filter_var($_POST["title_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$descriptionToSave = filter_var($_POST["description_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		//wp_die($contentToSave);
		$wpdb->show_errors();
		// Insert sanitize string in record
		if($wpdb->insert($wpdb->prefix . 'rpg_Shop', array('name'=>$titleToSave, 'description'=>$descriptionToSave), array('%s', '%s')))
		{
			//Record is successfully inserted, respond to ajax request
			$my_id = $wpdb->insert_id; //Get ID of last inserted record from MySQL
			$html .= '<tr id="shop_'.$my_id.'"><td>'.$titleToSave.'</td>';
			$html .= '<td>[wpRPG_Shop storeid="'.$my_id.'"]</td>';
			$html .= '<td>'.$descriptionToSave.'</td>';
			$html .= '<td><div class="del_wrapper"><a href="#" class="del_button" id="del-'.$my_id.'">';
			$path = plugins_url('images/icon_delete.gif', __FILE__);
			$html .= '<img src="'.$path .'" border="0" />';
			$html .= '</a><a href="admin.php?page=wpRPG_menu&tab=StoreItems&storeid='. $my_id .'">Inventory</a></div></td></tr>';
			echo $html;
			$_POST = array();
		}else{
			//output error
			header('HTTP/1.1 500 Looks like mysql error, could not insert record!');
			exit();
		}    
	}     
	elseif(isset($_POST["recordToDelete"]) && strlen($_POST["recordToDelete"])>0 && is_numeric($_POST["recordToDelete"]))
	{
		 $idToDelete = filter_var($_POST["recordToDelete"],FILTER_SANITIZE_NUMBER_INT);
		 if(isset($_POST['wprpg_Shop'])){
			if(!$wpdb->query("DELETE FROM ". $wpdb->prefix . "rpg_Shop WHERE id=".$idToDelete))
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
			$itemcount = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "rpg_Shop WHERE id=" . $itemID );
			if(!$itemcount)
			{
				header("Location:admin.php?page=wpRPG_menu&tab=Shop");
				exit;
			}
		}else{
			header("Location:admin.php?page=wpRPG_menu&tab=Shop");
			exit;
		}
	}
	if(isset($_GET['wprpg_itemCats'])){
		$sql = "Select * FROM ". $wpdb->prefix ."rpg_Shop_cats";
		$opts = 0;
		foreach($wpdb->get_results($sql, ARRAY_A) as $row=>$val) 
		{
			$opts++;
			echo "<option>".$val['name']."</option>";
		}
		if(empty($opts))
			echo "<option>ERROR: You must create Categories First!</option>";
		
	}
?>