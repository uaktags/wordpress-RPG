<?php
global $wpdb;
$res = get_users();
$wprpg = new wpRPG;
?>
<div id="rpg_area">
	<table id="members" border=1>
		<tr>
			<th><?php _e('MemberName', 'wpRPG-Members') ?></th>
			<th><?php _e('XP', 'wpRPG-Members') ?></th>
			<th><?php _e('HP', 'wpRPG-Members') ?></th>
			<th><?php _e('Level', 'wpRPG-Members') ?></th>
			<th><?php _e('Gold', 'wpRPG-Members') ?></th>
			<?php echo ( is_user_logged_in() ? '<th>'.__('Actions', 'wpRPG-Members').'</th>' : '' );?> 
		</tr>
<?php
foreach ( $res as $u ) 
{
$wprpg->checkUserMeta($u->ID);
?>
		<tr id="player_<?php echo $u->ID;?>">
			<td>
				<?php
					global $wp_query;
					$profile = get_option( 'wpRPG_Profile_Page' );
					$permalink = get_permalink($profile);
				?>
				<a href="<?php echo $permalink  . $u->user_nicename; ?>"  name="<?php echo $u->ID; ?>"><?php echo $u->user_nicename; ?></a>
			</td>
			<td> <?php echo $u->xp; ?></td>
			<td> <?php echo $u->hp;?></td>
			<td> <?php echo $wprpg->wpRPG_player_level( $u->xp ); ?></td>
			<td> <?php echo $u->gold;?></td>
<?php 
	if ( is_user_logged_in() ) {
?>	
			<td>
<?php
	if(is_user_logged_in()){
		$current_user = wp_get_current_user();
		if( $u->ID != $current_user->ID){
			$members = new wpRPG_Members;
			$actions = $members->listPlayers_getLoggedIn_Actions( array('id'=>$u->ID) );
			foreach($actions as $action=>$html){
				if($html != $u->ID)
					echo $html;
			}
		}
	}else{
		$members = new wpRPG_Members;
		$actions = $members->listPlayers_getLoggedIn_Actions( array('id'=>$u->ID) );
		foreach($actions as $action=>$html){
			if($html != $u->ID)
				echo $html;
		}
	}
	
?>
<?php
	}
?>
			</td>
		</tr>
<?php
}
?>
	</table>
</div>
<?php  ?>
