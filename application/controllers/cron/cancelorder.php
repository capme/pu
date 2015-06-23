<?php
class Cancelorder extends CI_Controller {
    public function run(){
        $this->load->model(array("client_m","paymentconfirmation_m","codpaymentconfirmation_m"));
        $this->load->library("mageapi");
        $btorder=$this->paymentconfirmation_m->getNewOrder();
        $codorder=$this->codpaymentconfirmation_m->getCodOrder();

        if (!empty($btorder)){
               foreach($btorder as $banktransfer){
                   $client = $this->client_m->getClientById($banktransfer['client_id'])->row_array();
                   if (!$client['mage_auth'] && !$client['mage_wsdl']) {
                       continue;
                   }
                   $config = array(
                       "auth" => $client['mage_auth'],
                       "url" => $client['mage_wsdl']
                   );
                   if( $this->mageapi->initSoap($config) ) {
                       $this->paymentconfirmation_m->cancelOrder($banktransfer['order_number']);
                       $this->mageapi->setOrderToCancel($banktransfer['order_number'], $commet="expired order");
                   }
               }
           }

        if(!empty($codorder)){
            foreach($codorder as $cod){
                $client = $this->client_m->getClientById($cod['client_id'])->row_array();
                if (!$client['mage_auth'] && !$client['mage_wsdl']) {
                    continue;
                }
                $config = array(
                    "auth" => $client['mage_auth'],
                    "url" => $client['mage_wsdl']
                );
                if( $this->mageapi->initSoap($config) ) {
                    $this->codpaymentconfirmation_m->cancelOrder($cod['order_number']);
                    $this->mageapi->setOrderToCancel($cod['order_number'], $commet="expired order");
                }
            }
        }
    }
}