<?php
class Migration_Inbounds extends Base_migration {
	public function up() {
		parent::up();
		$this->db->trans_start();
		$this->db->query("DELETE FROM module WHERE slug like 'inbo%'");	
		$new= array(
			"inbounds" => array("name" => "Inbound", "slug" => "inbounds", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 74),
			);            
		$newIds = array();
       
		$parentTags = array();
		foreach($new as $tag => $module) {
			$this->db->insert("module", $module);
			$newIds[] = $parentTags[$tag] = $this->db->insert_id();
		}
        
		$newModule = array(
                array("name" => "Inbound List", "slug" => "inbounds/InboundList", "hidden" => 1, "status" => 1, "parent" => $parentTags['inbounds']),
				array("name" => "Inbound Download", "slug" => "inbounds/download", "hidden" => 1, "status" => 1, "parent" => $parentTags['inbounds']),
				array("name" => "Inbound Delete", "slug" => "inbounds/delete", "hidden" => 1, "status" => 1, "parent" => $parentTags['inbounds']),
				array("name" => "Inbound Add", "slug" => "inbounds/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['inbounds']),
                array("name" => "Inbound Save", "slug" => "inbounds/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['inbounds']),        
		);
		
		foreach($newModule as $module) {
			$this->db->insert("module", $module);
			$newIds[] = $this->db->insert_id();
		}		
		
		$authData = $this->db->get_where("auth_group", array("id" => 1))->row_array();
		$authData['auth_module'] = json_decode($authData['auth_module']);
		$authData['auth_module'] = array_merge($authData['auth_module'], $newIds);
		
		$this->db->where("id", 1);
		$this->db->update("auth_group", array("auth_module" => json_encode($authData['auth_module'])));
		
		$this->db->trans_complete();
	}
	
	public function down() {
		parent::down();
		$this->db->query("DELETE FROM module WHERE slug like 'inbo%'");
	}
}
?>