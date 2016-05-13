<?php
class Migration_bbmmoney extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("CREATE TABLE `bbmmoney_order`
            (`id` int(11) AUTO_INCREMENT,
            `client_id` int(2),
			`order_number` varchar(30),
            `name` varchar(100),
            `shipping_address` TEXT,
            `items` TEXT,
            `email` VARCHAR(100),
            `amount` INT(11),
            `status` INT(1),
            `created_at` TIMESTAMP,
            `updated_by` INT,
            `updated_at` DATETIME,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("DELETE FROM module WHERE slug like 'bbmmoneyt%'");
        $new= array(
            "bbmmoneyorder" => array("name" => "BBM Money Order", "slug" => "bbmmoneyorder", "icon" => "fa-user", "hidden" => 0, "status" => 1, "parent" => 1),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "BBM Money Order List", "slug" => "bbmmoneyorder/bbmMoneyOrderList", "hidden" => 1, "status" => 1, "parent" => $parentTags['bbmmoneyorder']),
            array("name" => "BBM Money Order View", "slug" => "bbmmoneyorder/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['bbmmoneyorder']),
            array("name" => "BBM Money Order Cancel", "slug" => "bbmmoneyorder/cancel", "hidden" => 1, "status" => 1, "parent" => $parentTags['bbmmoneyorder']),
            array("name" => "BBM Money Order Save", "slug" => "bbmmoneyorder/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['bbmmoneyorder']),
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
        $this->db->query("DELETE FROM module WHERE slug like 'bbmmoney%'");
        $this->db->query("DROP TABLE bbmmoney_order");
        $this->db->trans_complete();
    }
}
?>