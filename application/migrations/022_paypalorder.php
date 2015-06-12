<?php
class Migration_paypalorder extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
		$this->db->query("CREATE TABLE `paypal_order`
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
			
        $this->db->query("DELETE FROM module WHERE slug like 'paypa%'");
        $this->db->query("DELETE FROM module WHERE slug like 'exportpay%'");
        /*$new= array(
            "paypalorder" => array("name" => "Paypal Order", "slug" => "paypalorder", "icon" => "fa-paypal", "hidden" => 0, "status" => 1, "parent" => 1)
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }*/

        $this->db->insert("module", array("name" => "Paypal Order", "slug" => "paypalorder", "icon" => "fa-paypal", "parent" => 1, "hidden" => 0, "status" => 1));
        $newIds = array();

        $parentModule = $this->db->insert_id();
        $newIds[] = $parentModule;

        $newModule = array(
            "paypalorder" => array("name" => "Paypal Order", "slug" => "paypalorder", "icon" => "fa-paypal", "hidden" => 0, "status" => 1, "parent" => $parentModule),
            "exportpaypayl" => array("name" => "Export Paypal Order", "slug" => "exportpaypal", "icon" => "glyphicon glyphicon-download-alt", "hidden" => 0, "status" => 1, "parent" => $parentModule)
        );
        $parentTags = array();
        foreach($newModule as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Paypal Order List", "slug" => "paypalorder/paypalOrderList", "hidden" => 1, "status" => 1, "parent" => $parentTags['paypalorder']),
            array("name" => "Paypal Order View", "slug" => "paypalorder/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['paypalorder']),
			array("name" => "Paypal Order Cancel", "slug" => "paypalorder/cancel", "hidden" => 1, "status" => 1, "parent" => $parentTags['paypalorder']),
            array("name" => "Paypal Order Approve", "slug" => "paypalorder/approve", "hidden" => 1, "status" => 1, "parent" => $parentTags['paypalorder']),

            array("name" => "Paypal Order List", "slug" => "exportpaypal/exportPaypalList", "hidden" => 1, "status" => 1, "parent" => $parentTags['paypalorder']),
            array("name" => "Export Paypal Order", "slug" => "exportpaypal/export", "hidden" => 1, "status" => 1, "parent" => $parentTags['paypalorder'])
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
        $this->db->query("DELETE FROM module WHERE slug like 'paypa%'");
        $this->db->query("DELETE FROM module WHERE slug like 'exportpay%'");
		$this->db->query("DROP TABLE paypal_order");
		$this->db->trans_complete();
    }
}
?>