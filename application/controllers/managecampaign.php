<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Managecampaign extends MY_Controller {
    var $data = array();

    public function __construct(){
        parent::__construct();
        $this->load->model("managecampaign_m");
        $this->load->model("client_m");
    }

    public function index(){
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Manage Campaign";
        $this->data['breadcrumb'] = array("Merchandising"=>"","Manage Campaign" => "");

        $this->managecampaign_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("Manage Campaign")
            ->setAddLabel("Add Campaign")
            ->setHeadingTitle(array("#","Client Name","Brand Code","SKU Simple","Start Date","End Date","Discount Absord","Campaign","Status"))
            ->setHeadingWidth(array(2,2,2,2,2,3,2,3,4));

        $this->va_list->setDropdownFilter(1, array("name" => $this->managecampaign_m->filters[$this->managecampaign_m->table.'.client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->va_list->setDropdownFilter(8, array("name" => $this->managecampaign_m->filters[$this->managecampaign_m->table.'.status'], "option" => $this->getStatus()));
        $this->va_list->setInputFilter(3, array("name" => $this->managecampaign_m->filters['sku_simple']));

        $this->data['script'] = $this->load->view("script/managecampaign_list", array("ajaxSource" => site_url("managecampaign/manageCampaignList")), true);
        $this->load->view("template", $this->data);
    }

    private function getStatus() {
        return array(-1=>"",0=>"not sent to DART yet",1 => "sent to DART");
    }

    public function manageCampaignList(){
        $data = $this->managecampaign_m->getManageCampaign();
        echo json_encode($data);
    }

    public function add (){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Add Campaign";
        $this->data['breadcrumb'] = array("Merchandising"=> "", "Manage Campaign" => "");
        $this->data['formTitle'] = "Add Campaign";

        $this->load->library("va_input", array("group" => "campaign"));
        $flashData = $this->session->flashdata("campaignError");

        if($flashData !== false){
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        }else{
            $msg = $value = array();
        }
        $val=array();

        $this->va_input->addHidden( array("name" => "method", "value" => "new") );
        $this->va_input->addSelect( array("name" => "client","label" => "Client *", "list" => $this->client_m->getClientCodeList(), "value" => @$value['client'], "msg" => @$msg['client']) );
        $this->va_input->addInput( array("name" => "sku", "placeholder" => "SKU Simple", "help" => "SKU Simple", "label" => "SKU Simple *", "value" => @$value['sku'], "msg" => @$msg['sku']));
        $this->va_input->addInput( array("name" => "absorb", "placeholder" => "Discount Absorb", "help" => "Discount Abasorb", "label" => "Discount Absorb *", "value" => @$value['absorb'], "msg" => @$msg['absorb']));
        $this->va_input->addInput( array("name" => "campaign", "placeholder" => "Campaign", "help" => "Campaign", "label" => "Campaign *", "value" => @$value['status'], "msg" => @$msg['campaign']));
        $this->va_input->addCustomField( array("name" =>"options", "placeholder" => "Input Period", "label" => "Input Period *", "value" =>$val, "view"=>"form/campaignPeriod"));
        $this->data['script'] = $this->load->view("script/managecampaign_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        if($_SERVER['REQUEST_METHOD'] != "POST") {
            redirect("managecampaign/add");
        }
        $post = $this->input->post("campaign");
        if(empty($post)) {
            redirect("managecampaign/add");
        }

        if($post['method'] == "new") {
            $result = $this->managecampaign_m->newCampaign( $post );
            if(is_numeric($result)) {
                redirect("managecampaign");
            } else {
                $this->session->set_flashdata( array("campaignError" => json_encode(array("msg" => $result, "data" => $post))) );
                redirect("managecampaign/add");
            }

        }
    }

    public function delete($id){
        $this->managecampaign_m->delete($id);
        redirect('managecampaign');
    }
}