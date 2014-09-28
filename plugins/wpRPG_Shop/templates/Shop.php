<div id="rpg_area">
	<h1><?php __("My_Shop", "wpRPG-Shop") ?></h1>
	<div class="simpleTabsContent" id="bio" style="height:500px;">
		<div>
			<?php
				//var_dump($shop->get_shop_stock(1));
				$shopID = $shop->id;
				?>
				<div name="player_heading">
					<h3><?php echo $shop->name; ?></h3>
				</div>
				<?php
				$sql = "SELECT * FROM ".$wpdb->prefix."rpg_shop_inventory as ShopInventory INNER JOIN ".$wpdb->prefix."rpg_items as Items on ShopInventory.item_id=Items.id WHERE shop_id=$shopID";
				$Shops = $wpdb->get_results($sql, ARRAY_A);
				if($Shops){
					$shopCatsSQL = "SELECT * FROM ".$wpdb->prefix."rpg_items_cats";
					$shopCats = $wpdb->get_results($shopCatsSQL, ARRAY_A);
					if($shopCats)
					{
						foreach($shopCats as $cat)
						{
							echo "<h2>".$cat['name']."</h2><br /><table><thead><th>Name</th><th>Price</th><th>Bonus</th><th>Actions</th></thead><tbody>";
							$shopItemsSQL = "SELECT * FROM ".$wpdb->prefix."rpg_shop_inventory as ShopInventory INNER JOIN ".$wpdb->prefix."rpg_items as Items on ShopInventory.item_id=Items.id WHERE shop_id=$shopID AND Items.cat_id=".$cat['id'];
							$shopItems = $wpdb->get_results($shopItemsSQL, ARRAY_A);
							if($shopItems)
							{
								foreach($shopItems as $item)
								{
									$MyItem = new wpRPG_Item($item['item_id']);
									echo "<tr>
									<td>".$MyItem->name."</td>
									<td>".$MyItem->price."</td>
									<td><ul>"; 
									if(isset($MyItem->bonus)){
										foreach($MyItem->bonus as $bonus)
										{
											echo "<li>". $bonus['description']."</li>";
										}
									}
									echo "</ul></td>";
									echo "<td><ul><li>Buy</li></td>
									</tr>";
								}
								echo "</table>";
							}else{
								echo "</table>";
								echo "No Items For Sale";
							}
						}
					}
				}
				else{
					echo "This Shop is Closed";
				}
			?>
		</div>
		<br/>
	</div>
</div>
<br/>
<br/>