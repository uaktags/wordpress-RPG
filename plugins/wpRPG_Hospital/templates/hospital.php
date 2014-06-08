<div id="rpg_area">
	<h1>Hospital</h1>
	<div class="simpleTabsContent" id="bio" style="height:500px;">
		<div name="player_heading">
			<h3><?php echo $res->nickname; ?></h3>
		</div>
		<div>
			<table width=100% style="text-align:center;">
				<tr>
					<td>Current HP : <?php echo $res->hp; ?> out of 100. 
						<button id="replenish-hp">Full Heal ( <?php echo( 100 - $res->hp ); ?> Gold)</button>
					</td>
				</tr>
			</table>
		</div>
		<br/>
	</div>
</div>
<br/>
<br/>