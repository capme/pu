<?php 
class Migration_Inbound_Document extends Base_migration {
	public function up() {
		parent::up();
		$this->db->trans_start();
		$this->db->query("DELETE FROM module WHERE slug like 'listinbounddoc%'");	
		$new= array(
			"listinbounddoc" => array("name" => "Inbound Document", "slug" => "listinbounddoc", "icon" => "fa-file", "hidden" => 0, "status" => 1, "parent" => 74),
			);
		$newIds = array();
		
		$parentTags = array();
		foreach($new as $tag => $module) {
			$this->db->insert("module", $module);
			$newIds[] = $parentTags[$tag] = $this->db->insert_id();
		}
		
		$newModule = array(
				array("name" => "Inbound Document List", "slug" => "listinbounddoc/inboundDocList", "hidden" => 1, "status" => 1, "parent" => $parentTags['listinbounddoc']),
				
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
		$this->db->query("DELETE FROM module WHERE slug like 'listinbounddoc%'");
	}
}
?>