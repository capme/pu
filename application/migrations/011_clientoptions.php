<?php
class Migration_Clientoptions extends Base_migration {
	public function up() {
		parent::up();
		$this->db->trans_start();
		$this->db->query("DELETE FROM module WHERE slug like 'clientop%'");	
		$new= array(
			"clientoptions" => array("name" => "Client Options", "slug" => "clientoptions", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 4),
			);
		$parentTags = array();
		foreach($new as $tag => $module) {
			$this->db->insert("module", $module);
			$parentTags[$tag] = $this->db->insert_id();
		}
		
		$newModule = array(
				array("name" => "Client Options List", "slug" => "clientoptions/ClientOptionsList", "hidden" => 1, "status" => 1, "parent" => $parentTags['clientoptions']),
				array("name" => "Client Options View", "slug" => "clientoptions/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['clientoptions']),
				array("name" => "Client Options Save", "slug" => "clientoptions/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['clientoptions']),
				array("name" => "Client Options Add", "slug" => "clientoptions/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['clientoptions']),
				
		);
		
		foreach($newModule as $module) {
			$this->db->insert("module", $module);
		}		
		$this->db->trans_complete();
	}
	
	public function down() {
		parent::down();
		$this->db->query("DELETE FROM module WHERE slug like 'clientop%'");
	}
}
?>