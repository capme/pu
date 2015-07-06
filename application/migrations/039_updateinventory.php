<?php
class Migration_Updateinventory_Stock extends Base_Migration {
    var $updateColumn = array('magestock');

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
                $this->db->query('ALTER TABLE `inv_items_'.$client['id'].'` ADD COLUMN `'.$_column.'` int(11)');
            }
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