<?php
class Migration_Inventory_List extends Base_Migration {
    public function up() {
        parent::up();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            $this->db->query('CREATE TABLE `inv_items_'.$client['id'].'` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku_simple` varchar(200) NOT NULL,
  `sku_description` varchar(300) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        }
    }

    public function down() {
        parent::down();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            $this->db->query("DROP TABLE IF EXISTS `inv_items_".$client['id']."`");
        }
        $this->db->trans_complete();
    }
}
?>