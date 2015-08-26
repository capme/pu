<?php
class Migration_Campaign extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("DROP TABLE IF EXISTS manage_campaign");
        $this->db->query("CREATE TABLE `manage_campaign`
            (`id` int(5) AUTO_INCREMENT,
            `client_id` int(3),
            `brand_code` varchar(5),
			`sku_simple` varchar(30),
            `campaign` varchar(30),
            `discount_absorb` int(30),
            `status` int(3),
            `start_date` TIMESTAMP,
            `end_date` TIMESTAMP,
			primary key (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("DELETE FROM module WHERE slug like 'ctrcon%'");
        $new= array(
            "managecampaign" => array("name" => "Manage Campaign", "slug" => "managecampaign", "icon" => "fa-cog", "hidden" => 0, "status" => 1, "parent" => 108),
        );
        $newIds = array();

        $parentTags = array();
        foreach($new as $tag => $module) {
            $this->db->insert("module", $module);
            $newIds[] = $parentTags[$tag] = $this->db->insert_id();
        }

        $newModule = array(
            array("name" => "Manage Campaign List", "slug" => "managecampaign/manageCampaignList", "hidden" => 1, "status" => 1, "parent" => $parentTags['managecampaign']),
            array("name" => "Manage Campaign Add", "slug" => "managecampaign/add", "hidden" => 1, "status" => 1, "parent" => $parentTags['managecampaign']),
            array("name" => "Manage Campaign Save", "slug" => "managecampaign/save", "hidden" => 1, "status" => 1, "parent" => $parentTags['managecampaign']),
            array("name" => "Manage Campaign Delete", "slug" => "managecampaign/delete", "hidden" => 1, "status" => 1, "parent" => $parentTags['managecampaign'])
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
        $this->db->query("DELETE FROM module WHERE slug like 'campa%'");
        $this->db->query("DROP TABLE IF EXISTS manage_campaign");
        $this->db->trans_complete();
    }
}
?>
