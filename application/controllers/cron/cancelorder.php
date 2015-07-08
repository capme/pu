<?php
class Cancelorder extends CI_Controller {
    public function run(){
        $this->load->model(array("paymentconfirmation_m","codpaymentconfirmation_m"));
        $this->paymentconfirmation_m->getNewOrder();
        $this->codpaymentconfirmation_m->getCodOrder();
    }
}