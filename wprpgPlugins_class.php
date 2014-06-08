<?php

	class wpRPG_Modules {
		
		public function __construct(){
			//parent::__construct();
			$this->checkRegistration();
		}
		
		public function getModules()
		{
			$modules = array();
			return apply_filters( 'wpRPG_add_plugins', $modules );
		}
		
		public function checkRegistration()
		{
			foreach($this->getModules() as $module => $info)
			{
				$status = get_option("wpRPG_plugin_status_$module");
				if($status == null)
				{
					add_option("wpRPG_plugin_status_$module", false, "", "yes");
					register_setting( 'rpg_settings', "wpRPG_plugin_status_$module");
				}
			}
		}
	}
	
	class wprpgModules extends wpRPG_Modules{
		function __construct(){
			parent::__construct();
		}
	}