<?php
class Autocancel_m extends MY_Model {
    var $expired ='expired_order';

    function __construct(){
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);
    }

    public function getOrder(){
        return $this->db->get($this->expired, array("status"=>0))->result_array();
    }

    public function canceled($ordernr, $id){
        $this->db->where('order_number', $ordernr);
        $this->db->where('id', $id);
        $this->db->update($this->expired, array('status'=>2));
    }

    public function cekOrder($ordernr, $client_id){
        return $this->db->query("select * from expired_order where order_number= '$ordernr' and client_id = '$client_id'")->row_array();
    }

    public function delete($ordernr, $id){
        $this->db->delete($this->expired, array('order_number'=>$ordernr, 'id'=>$id));
    }
}
