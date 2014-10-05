<?php
if(!class_exists('wpRPG_Item'))
{
	class wpRPG_Item
	{
		protected $self = array();
		protected $bonuses = array();
		public function __construct($id = '')
		{
			global $wpdb;
			if($id == 0)
				return 0;
			$this->bonuses = $this->get_item_bonuses($id);
			$sql = "SELECT * FROM ".$wpdb->prefix."rpg_items as Item WHERE id=$id";
			foreach($wpdb->get_results($sql, ARRAY_A) as $item)
			{
				foreach($item as $key=>$val)
				{
					$this->self[$key] = $val;
				}
			}
			foreach($this->bonuses as $bonus=>$val)
			{
				$this->self['bonus'][$bonus] = $val;

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
		
		public function get_item_bonuses($id=0)
		{
			global $wpdb;
			if($id){
				$sql = "SELECT * FROM " . $wpdb->prefix. "rpg_items_actions as Action INNER JOIN ".$wpdb->prefix."rpg_items_action_values as Val WHERE Action.id=Val.action_id AND Val.item_id=$id";
				foreach($wpdb->get_results($sql, ARRAY_A) as $bonus)
				{
					$this->bonuses[$bonus['action']] = array('value'=>$bonus['value'], 'description'=>($bonus['increase']?'+':'-').$bonus['value']. ' '.$bonus['action']);
				}
				return $this->bonuses;
			}
			return array();
		}
	}
}
?>