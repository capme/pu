<?php
class Migration_Updateinventory extends Base_Migration {
    public function up() {
        parent::up();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            $sql = "SELECT * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='inv_items_".$client['id']."' AND COLUMN_NAME='product_id'";
            $cek = $this->db->query($sql)->num_rows();
            if($cek > 0){
                $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` DROP COLUMN `product_id`');
            }

            $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` ADD COLUMN `product_id` int(11)');
        }
        $this->db->trans_complete();
    }

    public function down() {
        parent::down();

        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        foreach($clients as $client) {
            $sql = "SELECT * from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='inv_items_".$client['id']."' AND COLUMN_NAME='product_id'";
            $cek = $this->db->query($sql)->num_rows();

            if($cek > 0){
                $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` DROP COLUMN `product_id`');
            }

        }
        $this->db->trans_complete();
    }
}
?>