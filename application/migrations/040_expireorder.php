<?php
class Migration_Expireorder extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'readyto%'");

        $exist=$this->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        where table_name='expired_order'
        and table_schema='oms_live'
        and column_name='created_date'
        ");

        if ($exist->num_rows() > 0){
            $this->db->query("ALTER TABLE `expired_order` DROP COLUMN `created_date` ");
        }
        else{
            $this->db->query("ALTER TABLE `expired_order` ADD `created_date` TIMESTAMP");
        }

        $new= array(
            "expireorder" => array("name" => "Expire Order", "slug" => "readytocancel", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 1),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }
        $newModule = array(
            array("name" => "Expire Order List", "slug" => "readytocancel/readyToCancelList", "hidden" => 1, "status" => 1, "parent" => $parentTags['expireorder']),
            array("name" => "Expire Order View", "slug" => "readytocancel/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['expireorder']),
            array("name" => "Expire Order Save", "slug" => "readytocancel/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['expireorder']),
            array("name" => "Expire Order Update", "slug" => "readytocancel/update", "hidden" => 1, "status" => 1, "parent" => $parentTags['expireorder'])
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
        $exist=$this->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        where table_name='expired_order'
        and table_schema='oms_live'
        and column_name='created_date'
        ");

        if ($exist->num_rows() > 0){
            $this->db->query("ALTER TABLE `expired_order` DROP COLUMN `created_date` ");
        }

        $this->db->query("DELETE FROM module WHERE slug like 'readyto%'");
        $this->db->trans_complete();
    }
}