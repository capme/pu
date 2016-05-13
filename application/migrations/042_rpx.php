<?php
class Migration_rpx extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("CREATE TABLE `rpx_awb`
            (`id` int(11) AUTO_INCREMENT,
            `awb_number` text,
            `account_number` text,
            `order_no` text,
            `awb_return` text,
            `pickup_request_no` text,
            `status` char(1),
            `total_weight` text,
            `rpx_consignee_name` text,
            `rpx_consignee_company` text,
            `rpx_consignee_address1` text,
            `rpx_consignee_kelurahan` text,
            `rpx_consignee_kecamatan` text,
            `rpx_consignee_city` text,
            `rpx_consignee_state` text,
            `rpx_consignee_zip` text,
            `rpx_consignee_phone` text,
            `rpx_consignee_mobile_no` text,
            `rpx_desc_of_goods` text,
            `rpx_tot_package` text,
            `rpx_actual_weight` text,
            `rpx_tot_weight` text,
            `created_at` TIMESTAMP,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("DELETE FROM module WHERE slug like 'rpx%'");
        $new= array(
            "rpx" => array("name" => "Rpx AWB", "slug" => "rpx", "icon" => "fa-cubes", "hidden" => 0, "status" => 1, "parent" => 74),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Rpx AWB Upload", "slug" => "rpx/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['rpx']),
            array("name" => "Rpx AWB Delete", "slug" => "rpx/delete", "hidden" => 1, "status" => 1, "parent" => $parentTags['rpx']),
            array("name" => "Rpx AWB Save", "slug" => "rpx/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['rpx']),
            array("name" => "Rpx AWB List", "slug" => "rpx/RpxList", "hidden" => 1, "status" => 1, "parent" => $parentTags['rpx']),
            array("name" => "Rpx AWB - Shipment", "slug" => "rpx/shipment", "hidden" => 1, "status" => 1, "parent" => $parentTags['rpx']),
            array("name" => "Rpx AWB - Pickup", "slug" => "rpx/pickup", "hidden" => 1, "status" => 1, "parent" => $parentTags['rpx']),
            array("name" => "Rpx AWB - View Shipment", "slug" => "rpx/view", "hidden" => 1, "status" => 1, "parent" => $parentTags['rpx']),
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
        $this->db->query("DELETE FROM module WHERE slug like 'rpx%'");
        $this->db->query("DROP TABLE rpx_awb");
        $this->db->trans_complete();
    }
}
?>