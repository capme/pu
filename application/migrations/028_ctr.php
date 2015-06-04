<?php
class Migration_Ctr extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("CREATE TABLE `ctr`
            (`id` int(11) AUTO_INCREMENT,
            `product_id` int(100),
            `ctr` varchar(100),
			`conversion` varchar(100),
            `filename` varchar(100),
            `created_at` TIMESTAMP,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("DELETE FROM module WHERE slug like 'ctrcon%'");
        $new= array(
            "ctrconversion" => array("name" => "CTR Conversion", "slug" => "ctrconversion", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 108),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "CTR Conversion List", "slug" => "ctrconversion/ctrConversionList", "hidden" => 1, "status" => 1, "parent" => $parentTags['ctrconversion']),
            array("name" => "CTR Conversion Add", "slug" => "ctrconversion/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['ctrconversion']),
            array("name" => "CTR ConversionSave", "slug" => "ctrconversion/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['ctrconversion']),

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
        $this->db->query("DELETE FROM module WHERE slug like 'ctrcon%'");
        $this->db->query("DROP TABLE ctr");
        $this->db->trans_complete();
    }
}
?>