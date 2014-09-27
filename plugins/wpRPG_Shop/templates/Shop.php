<div id="rpg_area">
	<h1><?php __("My_Shop", "wpRPG-Shop") ?></h1>
	<div class="simpleTabsContent" id="bio" style="height:500px;">
		<div name="player_heading">
			<h3><?php echo $res->nickname; ?></h3>
		</div>
		<div>
			<?php
				var_dump($shop->get_shop_stock(1));
				$shopID = $shop->id;
				$sql = "SELECT * FROM ".$wpdb->prefix."rpg_shop_inventory WHERE shop_id=$shopID";
				foreach($wpdb->get_results($sql, ARRAY_A) as $shop)
				{
					echo "<h2>".$cat['name']."</h2><br /><table><thead><th>Name</th><th>Price</th><th>Bonus</th><th>Actions</th></thead><tbody>";
					
					foreach($shop->id as $item)
					{
						wp_die(var_dump($item));
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