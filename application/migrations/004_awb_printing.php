<?php
class Migration_Awb_printing extends Base_migration {
	public function up() {
		parent::up();
	
		$uGroups = $this->db->get_where("auth_group", array("id" => 1))->result_array();
		$newRoles = array();
		$newModule = array(74,75,76);
	
		foreach($uGroups as $uGroup) {
			$authModule = array_merge(json_decode($uGroup['auth_module']), $newModule);
			$newRoles[] = array("id" => $uGroup['id'], "auth_module" => json_encode($authModule));
		}
	
		$this->db->update_batch("auth_group", $newRoles, "id");
	}
	
	public function down() {
		$uGroups = $this->db->get_where("auth_group", array("id" => 1))->result_array();
		$newRoles = array();
		$newModule = array(74,75,76);
	
		foreach($uGroups as $uGroup) {
			$authModule = json_decode($uGroup['auth_module']);
			foreach($newModule as $id) {
				if(($key = array_search($id, $authModule)) !== false) {
					unset($authModule[$key]);
				}
			}
	
			$newRoles[] = array("id" => $uGroup['id'], "auth_module" => json_encode(array_values($authModule)));
		}
	
		$this->db->update_batch("auth_group", $newRoles, "id");
	
		parent::down();
	}
	
}
?>