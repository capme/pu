<?php
class Autocancel extends CI_Controller {
    public function run(){
        $this->load->model(array("client_m","paymentconfirmation_m","codpaymentconfirmation_m","autocancel_m"));
        $this->load->library("mageapi");
        $this->db = $this->load->database('mysql', TRUE);
        $order = $this->autocancel_m->getOrder();
        $curdate = date('Y-m-d', time());

        if (!empty($order)){
               foreach($order as $data){
                   $expired = $data['expired_date'];
                   $date=$this->db->query("select date('$expired') as date")->row_array();

                   if($date['date'] == $curdate){
                       $client = $this->client_m->getClientById($data['client_id'])->row_array();

                       if (!$client['mage_auth'] && !$client['mage_wsdl']) {
                           continue;
                       }
                       $config = array(
                           "auth" => $client['mage_auth'],
                           "url" => $client['mage_wsdl']
                       );
                       if( $this->mageapi->initSoap($config) ) {
                           if($data['order_method'] == 'bank'){
                                $this->paymentconfirmation_m->cancelOrder($data['order_number']);
                           }
                           else{
                               $this->codpaymentconfirmation_m->cancelOrder($data['order_number']);
                           }

                          $this->autocancel_m->canceled($data['order_number'], $id =$data['id']);
                          $this->mageapi->setOrderToCancel($data['order_number'], $commet="expired order");
                       }
                   }
               }
           }
    }
}