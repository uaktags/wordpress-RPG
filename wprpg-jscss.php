<?php

//check $_POST["content_txt"] is not empty
/*if(!empty($_POST))
	wp_die(var_dump($_POST));
*/
if(isset($_POST["min_txt"]) && strlen($_POST["min_txt"])>0 && isset($_POST["wprpg_levels"]))
{
    //sanitize post value, PHP filter FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH
    $titleToSave = filter_var($_POST["title_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	$minToSave = filter_var($_POST["min_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    //wp_die($contentToSave);
	$wpdb->show_errors();
    // Insert sanitize string in record
    if($wpdb->insert($wpdb->prefix . 'rpg_levels', array('min'=>$minToSave, 'title'=>$titleToSave, 'group'=>'wpRPG_player_levels'), array('%d', '%s', '%s')))
    {
        //Record is successfully inserted, respond to ajax request
        $my_id = $wpdb->insert_id; //Get ID of last inserted record from MySQL
		echo '<tr id="item_'.$my_id.'"><td>'.$titleToSave.'</td>';
		echo '<td>'.$minToSave.'</td>';
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
}
elseif(isset($_POST["title_txt"]) && strlen($_POST["title_txt"])>0 && isset($_POST["wprpg_races"])){
	//sanitize post value, PHP filter FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH
    $titleToSave = filter_var($_POST["title_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	if(isset($_POST['strength_txt']))
		$strToSave = filter_var($_POST["strength_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	else
		$strToSave = 0;
	if(isset($_POST['defense_txt']))
		$defToSave = filter_var($_POST["defense_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	else
		$defToSave = 0;
	if(isset($_POST['gold_txt']))
		$goldToSave = filter_var($_POST["gold_txt"],FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	else
		$goldToSave = 0;
    //wp_die($contentToSave);
	$wpdb->show_errors();
    // Insert sanitize string in record
    if($wpdb->insert($wpdb->prefix . 'rpg_races', array('strength'=>$strToSave, 'defense'=>$strToSave, 'gold'=>$goldToSave, 'title'=>$titleToSave), array('%d', '%d', '%d', '%s')))
    {
        //Record is successfully inserted, respond to ajax request
        $my_id = $wpdb->insert_id; //Get ID of last inserted record from MySQL
		echo '<tr id="item_'.$my_id.'"><td>'.$titleToSave.'</td>';
		echo '<td>'.$strToSave.'</td>';
		echo '<td>'.$defToSave.'</td>';
		echo '<td>'.$goldToSave.'</td>';
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
}
elseif(isset($_POST["recordToDelete"]) && strlen($_POST["recordToDelete"])>0 && is_numeric($_POST["recordToDelete"]))
{//do we have a delete request? $_POST["recordToDelete"]
    
    //sanitize post value, PHP filter FILTER_SANITIZE_NUMBER_INT removes all characters except digits, plus and minus sign.
    $idToDelete = filter_var($_POST["recordToDelete"],FILTER_SANITIZE_NUMBER_INT);
    //try deleting record using the record ID we received from POST
	if(isset($_POST['wprpg_levels'])){
		if(!$wpdb->query("DELETE FROM ". $wpdb->prefix . "rpg_levels WHERE id=".$idToDelete))
		{
		}
    }elseif(isset($_POST['wprpg_races'])){
		if(!$wpdb->query("DELETE FROM ". $wpdb->prefix . "rpg_races WHERE id=".$idToDelete))
		{
		}
	}
}


function includedJS( ) {
            
?>
	<script type='text/javascript'>
		jQuery(document).ready(function($) 
		{
			<?php echo wpRPG_jquery_code(); ?>
		});
	</script>
	<?php
}

	
function includeJquery( ) {
	wp_enqueue_script( 'jquery' );
	add_action( 'admin_init', 'includedJS' );
	add_action( 'wp_footer', 'includedJS' );
}

function wpRPG_get_plugin_code( ) {
	$code       = $defaultJqueryCode = array(
		"1"=>"//##### Add record when Add Record Button is clicked #########
		$('#FormSubmit').click(function (e) {
			e.preventDefault();
			if($('#wprpg_levels').length != 0){
				if($('#title_txt').val()==='' || $('#min_txt').val()==='') //simple validation
				{
					alert('Please enter some text!');
					return false;
				}
				
				var myData = 'title_txt='+ $('#title_txt').val() +'&min_txt='+ $('#min_txt').val()+'&wprpg_levels='+ $('#wprpg_levels').val(); //post variables
				
				jQuery.ajax({
					type: 'POST', // HTTP method POST or GET
					url: '". site_url( 'wp-admin/admin-ajax.php' )."', //Where to make Ajax calls
					dataType:'text', // Data type, HTML, json etc.
					data:myData, //post variables
					success:function(response){
					$('#responds').append(response);
					$('#title_txt').val('');
					$('#min_txt').val('');//empty text field after successful submission
					
					},
					error:function (xhr, ajaxOptions, thrownError){
						alert(thrownError); //throw any errors
					}
				});
			}
			if($('#wprpg_races').length != 0){
				if($('#title_txt').val()==='') //simple validation
				{
					alert('Please enter some text!');
					return false;
				}
				
				var myData = 'title_txt='+ $('#title_txt').val() +'&strength_txt='+ $('#strength_txt').val() +'&defense_txt='+ $('#defense_txt').val() +'&gold_txt='+ $('#gold_txt').val() + '&wprpg_races=1'; //post variables
				
				jQuery.ajax({
					type: 'POST', // HTTP method POST or GET
					url: '". site_url( 'wp-admin/admin-ajax.php' )."', //Where to make Ajax calls
					dataType:'text', // Data type, HTML, json etc.
					data:myData, //post variables
					success:function(response){
					$('#responds').append(response);
					$('#title_txt').val('');
					$('#strength_txt').val('0');
					$('#gold_txt').val('0');
					$('#defense_txt').val('0');//empty text field after successful submission
					
					},
					error:function (xhr, ajaxOptions, thrownError){
						alert(thrownError); //throw any errors
					}
				});
			}
			
		}); " , "2"=>
		"//##### Delete record when delete Button is clicked #########
		$('a.del_button').click( function(e) {
			e.preventDefault();
			var clickedID = this.id.split('-'); //Split string (Split works as PHP explode)
			var DbNumberID = clickedID[1]; //and get number from array
			if($('#wprpg_races').length != 0){
				var func = 'wprpg_races';
			}else if($('#wprpg_levels').length != 0) {
				var func = 'wprpg_levels';
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
		});
		"
		
	);
	$all_code = apply_filters( 'wpRPG_add_plugin_code', $code );
	if($all_code != NULL)
		return $all_code;
	else
		$all_code = $code;
	//var_dump($all_code);
	return $all_code;
}

function wpRPG_jquery_code(){
	$jquerycode = wpRPG_get_plugin_code();
	
	foreach ( $jquerycode as $j => $code ) {
		echo $code;
	}
}

?>