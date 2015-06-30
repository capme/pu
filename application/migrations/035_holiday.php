<?php
class Migration_Holiday extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DELETE FROM module WHERE slug like 'hol%'");
        $this->db->query("DROP TABLE IF EXISTS holiday");

        $this->db->query("CREATE TABLE `holiday`
            (`id` int(20) AUTO_INCREMENT,`name` varchar(30),
            `date` TIMESTAMP,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $new= array(
            "holiday" => array("name" => "Set Holiday", "slug" => "holiday", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 74),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }
        $newModule = array(
            array("name" => "Set Holiday List", "slug" => "holiday/holidayList", "hidden" => 1, "status" => 1, "parent" => $parentTags['holiday']),
            array("name" => "Set Holiday Add", "slug" => "holiday/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['holiday']),
            array("name" => "Set Holiday Save", "slug" => "holiday/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['holiday']),
            array("name" => "Set Holiday delete", "slug" => "holiday/delete", "hidden" => 1, "status" => 1, "parent" => $parentTags['holiday'])
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
        $this->db->query("DROP TABLE IF EXISTS holiday");
        $this->db->query("DELETE FROM module WHERE slug like 'hol%'");
        $this->db->trans_complete();
    }
}
?>