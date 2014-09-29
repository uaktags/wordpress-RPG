<?php
global $wpdb;
	if(isset($_POST["min_txt"]) && is_numeric($_POST["min_txt"]) && isset($_POST["wprpg_Shop"]))
	{
		//sanitize post value, PHP filter FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH
		$titleToSave = filter_var($_POST["title_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$minToSave = filter_var($_POST["min_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$priceToSave = filter_var($_POST["price_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$catIDToSave = filter_var($_POST["cat_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		//wp_die($contentToSave);
		$wpdb->show_errors();
		// Insert sanitize string in record
		if($wpdb->insert($wpdb->prefix . 'rpg_Shop', array('levelreq'=>$minToSave, 'cat_id'=>$catIDToSave, 'name'=>$titleToSave, 'price'=>$priceToSave), array('%d', '%s', '%d')))
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
		if($wpdb->insert($wpdb->prefix . 'rpg_Shop_cats', array('name'=>$titleToSave,'description'=>$descriptionToSave,'equipable'=>$equipableToSave), array('%s','%s','%s')))
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
		if($wpdb->insert($wpdb->prefix . 'rpg_Shop_action_values', array('item_id'=>$itemIDToSave, 'action_id'=>$bonusToSave, 'value'=>$valueToSave, 'increase'=>$increaseToSave), array('%d','%d', '%d','%d')))
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