<?php
class Migration_Sorting_Config extends Base_Migration {
    public function up() {
        parent::up();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();

        $this->db->query("DROP TABLE IF EXISTS `sorting_config`");

        $this->db->query('CREATE TABLE `sorting_config` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `client_id` int(11) NOT NULL,
                `category_id` int(11) NOT NULL,
                `name` varchar(200) NOT NULL,
                `value` text NOT NULL,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        foreach($clients as $client) {
            $sql = "SELECT * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='catalog_category_product_".$client['id']."' AND COLUMN_NAME='status'";
            $cek = $this->db->query($sql)->num_rows();
            if($cek > 0){
                $this->db->query('ALTER TABLE `catalog_category_product_'.$client['id'].'` DROP COLUMN `status`');
            }

            $this->db->query('ALTER TABLE `catalog_category_product_'.$client['id'].'` ADD COLUMN `status` smallint');
        }


        //add module
        $parent = $this->db->query("SELECT *  FROM module WHERE slug = 'sortingtool'")->row_array();

        if(!empty($parent)){
            $newModule = array(
                array("name" => "Sorting - Config", "slug" => "sortingtool/config", "hidden" => 1, "status" => 1, "parent" => $parent['id']),
                array("name" => "Sorting - Save Config", "slug" => "sortingtool/saveconfig", "hidden" => 1, "status" => 1, "parent" => $parent['id']),
                array("name" => "Sorting - Catalog Product", "slug" => "sortingtool/catalogproduct", "hidden" => 1, "status" => 1, "parent" => $parent['id']),
                array("name" => "Sorting - Product List", "slug" => "sortingtool/productlist", "hidden" => 1, "status" => 1, "parent" => $parent['id']),
                array("name" => "Sorting - Category List", "slug" => "sortingtool/categorylist", "hidden" => 1, "status" => 1, "parent" => $parent['id'])
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
        }

        $this->db->trans_complete();
    }

    public function down() {
        parent::down();


        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        $this->db->query("DROP TABLE IF EXISTS `sorting_config`");

        $this->db->query("DELETE FROM module WHERE slug = 'sortingtool/config'");
        $this->db->query("DELETE FROM module WHERE slug = 'sortingtool/saveconfig'");
        $this->db->query("DELETE FROM module WHERE slug = 'sortingtool/catalogproduct'");
        $this->db->query("DELETE FROM module WHERE slug = 'sortingtool/productlist'");
        $this->db->query("DELETE FROM module WHERE slug = 'sortingtool/categorylist'");

        $this->db->trans_complete();
    }
}
?>