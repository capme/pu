<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//sampai sini
class Bbmmoneyorder extends MY_Controller {
    var $data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->model("bbmmoneyorder_m");
        $this->load->model("users_m");
        $this->load->model("client_m");
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "BBM Money Order";
        $this->data['breadcrumb'] = array("BBM Money Order" => "");

        $this->bbmmoneyorder_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->disableAddPlugin()->setListName("BBM Money Order")
            ->setHeadingTitle(array("#", "Order Date", "Updated At", "Client Name","Order Number","Name","Amount","Status","Status AWB"))
            ->setHeadingWidth(array(2,2,2,2,2,3,3,3,4));

        $this->va_list->setInputFilter(4, array("name" => $this->bbmmoneyorder_m->filters['order_number']))
            ->setDropdownFilter(3, array("name" => $this->bbmmoneyorder_m->filters[$this->bbmmoneyorder_m->table.'.client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->va_list->setInputFilter(5, array("name" => $this->bbmmoneyorder_m->filters['name']));
        $this->va_list->setInputFilter(6, array("name" => $this->bbmmoneyorder_m->filters[$this->bbmmoneyorder_m->table.'.amount']));
        $this->va_list->setDropdownFilter(7, array("name" => $this->bbmmoneyorder_m->filters[$this->bbmmoneyorder_m->table.'.status'], "option" => $this->getStatus()));
        $this->va_list->setDropdownFilter(8, array("name" => $this->bbmmoneyorder_m->filters[$this->bbmmoneyorder_m->tableAwb.'.status'], "option" => $this->getStatusAwb()));

        $this->data['script'] = $this->load->view("script/bbmmoneyorder_list", array("ajaxSource" => site_url("bbmmoneyorder/bbmMoneyOrderList")), true);
        $this->load->view("template", $this->data);
    }

    public function bbmMoneyOrderList()
    {
        $sAction = $this->input->post("sAction");
        $data = $this->bbmmoneyorder_m->getBbmMoneyOrderList();
        echo json_encode($data);
    }

    public function view($id)
    {
        $data = $this->bbmmoneyorder_m->getBbmMoneyOrderById($id);
        if($data->num_rows() < 1) {
            redirect("bbmmoneyorder");
        }

        $this->data['content'] = "group_form_v.php";
        $this->data['pageTitle'] = "View BBM Money Order";
        $this->data['breadcrumb'] = array("BBM Money Order"=> "", "View BBM Money Order" => "");
        $this->data['formTitle'] = "View BBM Money Order";

        $this->load->library("va_input", array("group" => "bbmmoney"));
        $this->va_input->setJustView();
        $this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Order Info", 1 => "Comment History") )->setActiveGroup(0);

        $value = $data->result_array();
		
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value[0]['client_code'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "sku", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value[0]['order_number'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value[0]['bbmmoney_status']], "label" => "Status", "help" => "Order Status", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['name'],"label" => "Name", "help" => "Name", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => number_format(@$value[0]['amount'], 2),  "label" => "Amount", "help" => "Amount", "disabled"=>"disabled") );
        $this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value[0]['items'], "view"=>"form/customItemsCod"));
        $this->va_input->addInput( array("name" => "email", "placeholder" => "Email", "help" => "Email", "label" => "Email", "value" => @$value[0]['email'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['bbmmoneyupdate_at'],"label" => "Updated At", "help" => "Updated At", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['bbmmoneycreated_at'],"label" => "Created At", "help" => "Created At", "disabled"=>"disabled") );
        $this->va_input->commitForm(0);

        $this->va_input->addCustomField( array("name" =>"","value" =>$value, "view"=>"form/customCommentHistory"));
        $this->va_input->commitForm(1);

        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function cancel($id){
        $data = $this->bbmmoneyorder_m->getBbmMoneyOrderById($id);
        if($data->num_rows() < 1) {
            redirect("bbmmoneyorder");
        }

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Cancel BBM Money Order";
        $this->data['breadcrumb'] = array("BBM Money Order "=> "bbmmoneyorder", "Cancel BBM Money Order" => "");
        $this->data['formTitle'] = "Cancel BBM Money Order";

        $this->load->library("va_input", array("group" => "bbmmoney"));
        $flashData = $this->session->flashdata("bbmmoneyError");
        if($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = array();
            $value = $data->row_array();
        }

        $this->va_input->addHidden( array("name" => "method", "value" => "cancel") );
        $this->va_input->addHidden( array("name" => "id", "value" => $value['order_id']) );
        $this->va_input->addHidden( array("name" => "client_id", "value" => $value['client_id']) );
        $this->va_input->addHidden( array("name" => "order_number", "value" => $value['order_number']) );
        $this->va_input->addTextarea( array("name" => "reason", "placeholder" => "Cancel reason", "help" => "Cancel reason", "label" => "Cancel Reason", "value" => @$value['reason'], "msg" => @$msg['reason']) );

        $this->data['script'] = $this->load->view("script/bbmmoneyorder_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        $post = $this->input->post("bbmmoney");
        if(empty($post)) {
            redirect("bbmmoneyorder");
        }

        else if($post['method'] == "cancel") {
            $result = $this->bbmmoneyorder_m->Reason($post);
            if(is_numeric($result)) {
                redirect("bbmmoneyorder");
            }
            else{
                $this->session->set_flashdata( array("bbmmoneyError" => json_encode(array("msg" => $result, "data" => $post))) );
                redirect("bbmmoneyorder/cancel/".$post['id']);
            }
        }
    }

    private function getStatus() {
		return array(-1=>"",0 => "pending", 1 => "processing", 2 => "complete", 3 => "fraud", 4 => "payment_review", 5 => "canceled",6 => "closed", 7 => "waiting_payment");
    }
    private function getStatusAwb() {
        return array(-1=>"",0=>"New Request",1 => "Printed");
    }

}
