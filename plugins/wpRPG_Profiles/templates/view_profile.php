<?php
$wpRPG = new wpRPG;
?>
<div id="rpg_area">
					
									<h1>User Profile</h1>
									<div class="simpleTabsContent" id="bio" style="height:500px;">
										<div name="player_heading">
											<h3><?php echo $res->nickname ?></h3>
										</div>
										<div>
										<table width=100% style="text-align:center;">
											<tr>
												<td>Level: 
													<?php echo $wpRPG->wpRPG_player_level($res->xp) ?>
												</td>
												<td>Overall Rank: 
													<?php echo $wpRPG->wpRPG_player_rank($res->ID) ?>
												</td>
											</tr>
											<?php 
												if($res->ID == $current_user->ID) { 
											?> 
											<tr>
												<td>Your Profile Link: <a href="
												<?php 
													$profile = get_option( 'wpRPG_Profile_Page' );
													$permalink = untrailingslashit(substr(get_permalink($profile), strlen(get_option('home').'/')));
													echo get_bloginfo('url').'/'.$permalink.'/'.$current_user->user_nicename 
												?>"><?php echo get_bloginfo('url').'/'.$permalink.'/'.$current_user->user_nicename ?></a>
												</td>
												<td>
												</td>
											</tr>
											<?php }
											?>
										</table>
										</div>
										<br/>
										<div style="width:47%; float:left;">
											<ul style="list-style:none;">
												<li style="width:100%;">
													<table>
													<tr><th>Player</th></tr>
													<tr><td><?php echo get_avatar($res->ID); ?></td></tr>
													<tr><td><?php echo ($wpRPG->getOnlineStatus($res->ID)?'Online':'Offline') ?></td></tr>
													</table>
												</li>
												<li style="width:100%">
													<table>
													<tr><th>Bio</th></tr>
													<tr><td>Male/Female</td></tr>
													<tr><td>Contact Info</td></tr>
													</table>
												</li>
											</ul>
										</div>
										<div style="float:right;margin-right:auto;width:47%">
											<ul style="list-style:none;">
												<li style="width:100%;">
													<table>
													<tr><th>Actions</th></tr>
													<?php $actions = $this->init_user_profile_fields('top_right');
														foreach($actions as $key => $val){
														if(!is_null($val)){
														?>
														<tr><td><?php echo $val; ?></td></tr>
													<?php }} ?>
													</table>
												</li>
												<li style="width:100%">
													<table>
													<tr><th>Stats</th></tr>
													<?php $actions = $this->init_user_profile_fields('mid_right');
														foreach($actions as $key => $val){
														?>
														<tr><td><?php echo $val; ?></td></tr>
														<?php } ?>
	</table>
												</li>
											</ul>
										</div>
									</div>
					</div><br/><br/>
