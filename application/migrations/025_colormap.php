<?php
class Migration_Colormap extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("CREATE TABLE `color_map`
            (`id` int(11) AUTO_INCREMENT,
            `original_color` varchar(30),
			`color_map` varchar(30),
            `color_code` varchar(100),
            `updated_at` TIMESTAMP,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("DELETE FROM module WHERE slug like 'colorm%'");
        $new= array(
            "colormap" => array("name" => "Color Map", "slug" => "colormap", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 108),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Color Map List", "slug" => "colormap/colorMapList", "hidden" => 1, "status" => 1, "parent" => $parentTags['colormap']),
            array("name" => "Color Map Add", "slug" => "colormap/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['colormap']),
            array("name" => "Color Map Export", "slug" => "colormap/export", "hidden" => 1, "status" => 1, "parent" => $parentTags['colormap']),
            array("name" => "Color Map Save", "slug" => "colormap/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['colormap']),

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
        $this->db->query("DELETE FROM module WHERE slug like 'colorm%'");
        $this->db->query("DROP TABLE color_map");
        $this->db->trans_complete();
    }
}
?>