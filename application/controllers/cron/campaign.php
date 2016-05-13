<?php
class Campaign extends CI_Controller{

    public function send(){
        $this->load->model('managecampaign_m');
        $this->load->library('Threepl_lib');
        $data = $this->managecampaign_m->getData();
        $response = $this->threepl_lib->sendCampaign($data);
        if($response->status = 1){
            $this->managecampaign_m->updateCampaign($response->data);
        }
    }
}