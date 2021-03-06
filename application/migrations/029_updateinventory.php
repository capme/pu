<?php
class Migration_Updateinventory extends Base_Migration {
    var $updateColumn = array('product_id','price','created_at');

    public function up() {
        parent::up();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            foreach($this->updateColumn as $_column){
                $sql = "SELECT * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='inv_items_".$client['id']."' AND COLUMN_NAME='".$_column."'";
                $cek = $this->db->query($sql)->num_rows();
                if($cek > 0){
                    $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` DROP COLUMN `'.$_column.'`');
                }
                $sql = "SHOW INDEX FROM `inv_items_".$client['id']."` WHERE KEY_NAME = 'sku_config'";
                $cek = $this->db->query($sql)->num_rows();
                if($cek > 0){
                    $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` DROP INDEX sku_config');
                }
            }

            $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` ADD COLUMN `product_id` int(11), ADD COLUMN `price` int(11), ADD COLUMN `created_at` timestamp , ADD INDEX (`sku_config`, `sku_simple`)');
        }
        $this->db->trans_complete();
    }

    public function down() {
        parent::down();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            foreach($this->updateColumn as $_column){
                $sql = "SELECT * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='inv_items_".$client['id']."' AND COLUMN_NAME='".$_column."'";
                $cek = $this->db->query($sql)->num_rows();
                if($cek > 0){
                    $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` DROP COLUMN `'.$_column.'`');
                }
                $sql = "SHOW INDEX FROM `inv_items_".$client['id']."` WHERE KEY_NAME = 'sku_config'";
                $cek = $this->db->query($sql)->num_rows();
                if($cek > 0){
                    $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` DROP INDEX sku_config');
                }
            }
        }
        $this->db->trans_complete();
    }
}
?>