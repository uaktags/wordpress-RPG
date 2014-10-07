<div id="rpg_area">
	<h1><?php __("My_Inventory", "wpRPG-Items") ?></h1>
	<div class="simpleTabsContent" id="bio" style="height:500px;">
		<div name="player_heading">
			<h3><?php echo $res->nickname; ?></h3>
		</div>
		<div>
			<?php
				
				$sql = "SELECT * FROM ".$wpdb->prefix."rpg_items_cats";
				foreach($wpdb->get_results($sql, ARRAY_A) as $cat)
				{
					echo "<h2>".$cat['name']."</h2><br /><table><thead><th>Name</th><th>Price</th><th>Bonus</th><th>Actions</th></thead><tbody>";
					$itemsql = "SELECT * FROM ".$wpdb->prefix."rpg_items as Items inner join ".$wpdb->prefix."rpg_items_inventory as inventory on inventory.item_id=Items.id WHERE inventory.player_id=".$res->ID." AND Items.cat_id=".$cat['id'];
					foreach($wpdb->get_results($itemsql, ARRAY_A) as $item)
					{
						$MyItem = new wpRPG_Item($item['item_id']);
						echo "<tr>
								<td>".$item['name']."</td>
								<td>".$item['price']."</td>
								<td><ul>"; 
								if(isset($MyItem->bonus)){
									foreach($MyItem->bonus as $bonus)
									{
										echo "<li>". $bonus['description']."</li>";
									}
								}
								echo "</ul></td>";
								echo "<td><ul><li>".($cat['equipable']?($item['equipped']?"UnEquip":"Equip"):"Use")."</li><li>Drop</li></td>
							 </tr>";
					}
					echo "</table>";
				}
			?>
		</div>
		<br/>
	</div>
</div>
<br/>
<br/>