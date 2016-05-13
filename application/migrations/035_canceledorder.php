<?php
class Migration_canceledorder extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'canceled%'");
        $new= array(
            "canceledorder" => array("name" => "Canceled Order", "slug" => "canceledorder", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 83),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Canceled Order List", "slug" => "canceledorder/canceledOrderList", "hidden" => 1, "status" => 1, "parent" => $parentTags['canceledorder']),
            array("name" => "Canceled Order View", "slug" => "canceledorder/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['canceledorder']),
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
        $this->db->query("DELETE FROM module WHERE slug like 'canceled%'");
    }
}
?>
