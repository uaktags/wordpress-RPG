<?php
if(!class_exists('wpRPG_Shoppette'))
{
	class wpRPG_Shoppette
	{
		protected $self = array();
		protected $inventory = array();
		public function __construct($id = '')
		{
			global $wpdb;
			if($id == 0)
				return 0;
			$this->inventory = $this->get_shop_stock($id);
			$sql = "SELECT * FROM ".$wpdb->prefix."rpg_shop as Shop WHERE id=$id";
			foreach($wpdb->get_results($sql, ARRAY_A) as $item)
			{
				foreach($item as $key=>$val)
				{
					$this->self[$key] = $val;
				}
			}
			foreach($this->inventory as $bonus=>$val)
			{
				$this->self['items'][$bonus] = $val;

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
		
		public function get_shop_stock($id=0)
		{
			global $wpdb;
			if($id){
				$sql = "SELECT * FROM " . $wpdb->prefix. "rpg_shop_inventory as Inventory INNER JOIN ".$wpdb->prefix."rpg_items as Items on Items.id=Inventory.item_id WHERE Inventory.shop_id=$id";
				foreach($wpdb->get_results($sql, ARRAY_A) as $bonus)
				{
					$this->inventory[$bonus['name']] = array('price'=>$bonus['price'], 'description'=>$bonus['description'], 'levelreq'=>$bonus['levelreq'], 'cat_id'=>$bonus['cat_id']);
				}
				return $this->inventory;
			}
			return array();
		}
	}
}
?>