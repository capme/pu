<?php
class Migration_Catalog_Category extends Base_Migration {
    public function up() {
        parent::up();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            $this->db->query("DROP TABLE IF EXISTS `catalog_category_".$client['id']."`");
            $this->db->query("DROP TABLE IF EXISTS `catalog_category_product_".$client['id']."`");

            $this->db->query('CREATE TABLE `catalog_category_'.$client['id'].'` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(200) NOT NULL,
              `category_id` int(11) NOT NULL,
              `path` varchar(300),
              `url_path` varchar(300),
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

            $this->db->query('CREATE TABLE `catalog_category_product_'.$client['id'].'` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `category_id` int(11) NOT NULL,
              `product_id` int(11),
              `sku` varchar(50),
              `position` smallint,
              `store_id` smallint,
              `manual_weight` tinyint DEFAULT 0,
              `result_index` int(11),
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        }
        $this->db->trans_complete();
    }

    public function down() {
        parent::down();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            $this->db->query("DROP TABLE IF EXISTS `catalog_category_".$client['id']."`");
            $this->db->query("DROP TABLE IF EXISTS `catalog_category_product_".$client['id']."`");
        }
        $this->db->trans_complete();
    }
}
?>