<?php
class Cancelorder extends CI_Controller {
    public function run(){
        $this->load->model(array("paymentconfirmation_m","codpaymentconfirmation_m"));

        $this->paymentconfirmation_m->getNewOrder();
        $this->paymentconfirmation_m->getPlouOrder();
        $this->codpaymentconfirmation_m->getCodOrder();
        $this->codpaymentconfirmation_m->getPluCodOrder();
        $this->paymentconfirmation_m->getPopOrder();
        $this->codpaymentconfirmation_m->getPopCodOrder();
    }
}