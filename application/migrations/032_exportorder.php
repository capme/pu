<?php
class Migration_Exportorder extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'exporto%'");
        $new= array(
            "exportorder" => array("name" => "Export Order", "slug" => "exportorder", "icon" => "glyphicon glyphicon-download-alt", "hidden" => 0, "status" => 1, "parent" => 1),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Export Order List", "slug" => "exportorder/clientOrderList", "hidden" => 1, "status" => 1, "parent" => $parentTags['exportorder']),
            array("name" => "Export Order Export", "slug" => "exportorder/export", "hidden" => 1, "status" => 1, "parent" => $parentTags['exportorder']),
            array("name" => "Export Order Save", "slug" => "exportorder/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['exportorder'])
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
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'exporto%'");
        $this->db->trans_complete();
    }
}
?>