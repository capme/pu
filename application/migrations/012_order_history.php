<?php
class Migration_Order_history extends Base_migration {
    public function up() {
        parent::up();
        $this->db->trans_start();
        $this->db->query("rename table cod_history to order_history");
        $this->db->query("ALTER TABLE order_history CHANGE  cod_id order_id INT");
        $query=$this->db->get('bank_confirmation');
        $hasil = $query->result_array();
        foreach($hasil as $result){
            $history['note']= $result['reason'];
            $history['status']=$result['status'];
            $history['type']=2;
            $history['created_by']=$result['updated_by'];
            $history['order_id']=$result['id'];

            $this->db->insert('order_history', $history);
        }
        $this->db->query("ALTER TABLE bank_confirmation DROP COLUMN reason");
        $this->db->trans_complete();
    }

    public function down() {
        parent::down();
        $this->db->trans_start();
        $this->db->query("rename table order_history to cod_history");
        $this->db->query("ALTER TABLE cod_history CHANGE order_id cod_id INT");
        $this->db->query("ALTER TABLE bank_confirmation ADD reason text");
        $this->db->trans_complete();
    }
}
?>