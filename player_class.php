<?php
/**
 * Creates a player object to Get/Set usermeta
 * @since WPRPG 1.0.15
 */

class wpRPG_Player
{
	protected $self = array();
	public function __construct($uid = '')
	{
		global $wpdb;
		if(!is_numeric($uid)){
			$data = get_user_by('login', $uid);
			$uid = $data->data->ID; //WP_USER object is stupid
		}elseif($uid == 0)
		{
			return 0;
		}
		$meta = get_user_meta($uid);
			
	foreach ( $meta as $key => $val ){
			foreach ($val as $valkey => $truval){
				$this->self[$key] = $truval;
			}
		}
		foreach ( get_userdata($uid) as $key=>$val){
			$this->self[$key]= $val;
		}
	}
	
	public function __get( $name = null )
	{
		if (array_key_exists($name, $this->self)){ 
			return $this->self[$name];
		}else{
			return false;
		}
	}
	
	public function __set ( $name = null, $value = null )
	{
		return $this->self[$name] = $value;
	}

	public function __isset( $name = null )
	{
		if(array_key_exists($name, $this->self))
		{
			return true;
		}
		return false;
	}
	
	public function update_meta ($name = null, $value = null )
	{
		if ( get_user_meta($this->self['ID'], $name, true) !== FALSE )
		{
			update_user_meta($this->self['ID'], $name, $value);
			return $this->self[$name] = $value;
		}
		return false;
	}
	
	public function getOnlineStatus()
	{
		$time = time() - ( 60 * 5 );
        return ($this->self['last_active'] > $time);
	}
	
	public function getRank()
	{
		global $wpdb;
		$sql_count = "SELECT * FROM " . $wpdb->prefix . "usermeta where meta_key='xp' ORDER BY meta_value DESC";
		$res       = $wpdb->get_results( $sql_count );
		$rank      = 1;
		foreach ( $res as $item ) {
			if ( $item->user_id == $this->self['ID'] ) {
				return $rank;
			}
			++$rank;
		}
		return 1;
	}
	public function test()
	{
		return 'hello world';
	}
}

class Player extends wpRPG_Player
{
	function __construct($uid = ''){
		parent::__construct($uid);
	}
}