<?php
class Migration_Content extends Base_migration {
    public function up(){
        parent::up();
        $this->db->trans_start();
        $this->db->query("CREATE TABLE `cnt_brand_description`
            (`id` int(5) AUTO_INCREMENT,
            `client_id` int(2),
			`brand_code` varchar(30),
            `description_id` TEXT,
            `description_en` TEXT,
            `filename` VARCHAR(20),
            `updated_at` DATETIME,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("DELETE FROM module WHERE slug like 'cont%'");
        $this->db->query("DELETE FROM module WHERE slug like 'branddesc%'");
        $new = array(
            "content" => array("name" => "Content", "slug" => "", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 0, 'sort' => 15),
        );
        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $new= array(
            "branddescription" => array("name" => "Brand Description", "slug" => "branddescription", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => $parentTags['content'], 'sort' => 15),
        );

        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Brand Description List", "slug" => "branddescription/brandDescriptionList", "hidden" => 1, "status" => 1, "parent" => $parentTags['branddescription']),
            array("name" => "Brand Description Add", "slug" => "branddescription/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['branddescription']),
            array("name" => "Brand Description View", "slug" => "branddescription/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['branddescription']),
            array("name" => "Brand Description Save", "slug" => "branddescription/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['branddescription']),
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

    public function down(){
        parent::down();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'cont%'");
        $this->db->query("DELETE FROM module WHERE slug like 'branddesc%'");
        $this->db->query("DROP TABLE cnt_brand_description");
        $this->db->trans_complete();
    }
}
?>
