<?php
class Migration_creditcardordercancel extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'creditcardorder/cancel%'");
        $this->db->query("DELETE FROM module WHERE slug like 'creditcardorder/save%'");

        $newModule = array(
            array("name" => "Credit Card Order Cancel", "slug" => "creditcardorder/cancel", "hidden" => 1, "status" => 1, "parent" =>'creditcardorder'),
            array("name" => "Credit Card Order Save", "slug" => "creditcardorder/save", "hidden" => 1, "status" => 1, "parent" =>'creditcardorder'),
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
        $this->db->query("DELETE FROM module WHERE slug like 'creditcardorder/cancel%'");
        $this->db->query("DELETE FROM module WHERE slug like 'creditcardorder/save%'");
        $this->db->trans_complete();
    }
}
?>