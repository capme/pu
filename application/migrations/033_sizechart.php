<?php
class Migration_sizechart extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("CREATE TABLE `brand_size_import`
            (`client_id` int(3), `brand_code` varchar(64),
            `notes` TEXT,
            `filename` varchar(100),
            `created_at` TIMESTAMP,
			primary key (`brand_code`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE `brand_size_map`
            (`client_id` int(3),
            `brand_code` varchar(64),
            `attribute_set` varchar(10),
            `brand_size` varchar(64),
            `brand_size_system` varchar(64) default 'LABEL',
            `paraplou_size` varchar(64),
            `position` int(10),
            `created_at` TIMESTAMP,
			primary key (`client_id`,`brand_code`,`attribute_set`,`brand_size`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("DELETE FROM module WHERE slug like 'sizecha%'");
        $new= array(
            "sizechart" => array("name" => "Size Chart Mapping", "slug" => "sizechart", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 108),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Size Chart Mapping List", "slug" => "sizechart/sizeChartList", "hidden" => 1, "status" => 1, "parent" => $parentTags['sizechart']),
            array("name" => "Size Chart Mapping Add", "slug" => "sizechart/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['sizechart']),
            array("name" => "Size Chart Mapping Save", "slug" => "sizechart/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['sizechart']),
            array("name" => "Size Chart Mapping Delete", "slug" => "sizechart/delete", "hidden" => 1, "status" => 1, "parent" => $parentTags['sizechart']),
            array("name" => "Size Chart Mapping Export", "slug" => "sizechart/export", "hidden" => 1, "status" => 1, "parent" => $parentTags['sizechart']),
            array("name" => "Size Chart Mapping View", "slug" => "sizechart/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['sizechart'])
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
        $this->db->query("DELETE FROM module WHERE slug like 'sizecha%'");
        $this->db->query("DROP TABLE IF EXISTS brand_size_import");
        $this->db->query("DROP TABLE IF EXISTS brand_size_map");
        $this->db->trans_complete();
    }
}
?>
