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
            }

            $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` ADD COLUMN `product_id` int(11), ADD COLUMN `price` int(11), ADD COLUMN `created_at` timestamp');
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
            }
        }
        $this->db->trans_complete();
    }
}
?>