<?php
class Autocancel extends CI_Controller {
    public function run(){
        date_default_timezone_set('Asia/Jakarta');
        $this->load->model(array("client_m","paymentconfirmation_m","codpaymentconfirmation_m","autocancel_m"));
        $this->load->library("mageapi");
        $this->db = $this->load->database('mysql', TRUE);
        $order = $this->autocancel_m->getOrder();
        $time=date('Y-m-d H:i:s');

        log_message("debug","[autocancel] total order to process : ".count($order));
        if (!empty($order)){
               foreach($order as $data){
                   $expired = $data['expired_date'];

                   if(strtotime($expired)  <= strtotime($time)){
                       log_message("debug","[autocancel] try to cancel : ".$data['order_number']." # ".$data['expired_date']);
                       $client = $this->client_m->getClientById($data['client_id'])->row_array();

                       if (!$client['mage_auth'] && !$client['mage_wsdl']) {
                           continue;
                       }

                       $config = array(
                           "auth" => $client['mage_auth'],
                           "url" => $client['mage_wsdl']
                       );
                       if( $this->mageapi->initSoap($config) ) {
                           // try to update magento first , if success update baymax
                               if ($data['order_method'] == 'bank') {
                                   if($this->mageapi->cancelPayment($data['order_number'], $commet="expired order")){
                                       $this->paymentconfirmation_m->cancelOrder($data['order_number']);
                                       $sethistory = array("order_id" => $data['id'], "type" => 2, "created_at" => $time, "status" => 2, "note" => "expired order (autocancel)", "created_by" => 2);
                                       $this->paymentconfirmation_m->setHistory($sethistory);
                                   }
                               } else {
                                   if($this->mageapi->setOrderToCancel($data['order_number'], $commet="expired order")) {
                                       $this->codpaymentconfirmation_m->cancelOrder($data['order_number']);
                                       $sethistory = array("order_id" => $data['id'], "type" => 1, "created_at" => $time, "status" => 2, "note" => "expired order (autocancel)", "created_by" => 2);
                                       $this->codpaymentconfirmation_m->setHistory($sethistory);
                                   }
                               }
                               $this->autocancel_m->canceled($data['order_number'], $id = $data['id']);
                           }
                   } else {
                       log_message("debug","[autocancel] ignore : ".$data['order_number']." # ".$data['expired_date']);
                   }
               }
           }
    }
}
