<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//sampai sini
class Creditcardorder extends MY_Controller {
    var $data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->model("creditcardorder_m");
        $this->load->model("users_m");
        $this->load->model("client_m");
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Credit Card Order";
        $this->data['breadcrumb'] = array("Credit Card Order" => "");

        $this->creditcardorder_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->disableAddPlugin()->setListName("Credit Card Order")
            ->setHeadingTitle(array("#", "Created Date", "Client Name","Order Number","Name","Amount","Status","Status AWB"))
            ->setHeadingWidth(array(2,2,2,2,3,3,3,4));

        $this->va_list->setInputFilter(3, array("name" => $this->creditcardorder_m->filters['order_number']))
            ->setDropdownFilter(2, array("name" => $this->creditcardorder_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->va_list->setInputFilter(4, array("name" => $this->creditcardorder_m->filters['name']));
        $this->va_list->setInputFilter(5, array("name" => $this->creditcardorder_m->filters[$this->creditcardorder_m->table.'.amount']));
        $this->va_list->setDropdownFilter(6, array("name" => $this->creditcardorder_m->filters[$this->creditcardorder_m->table.'.status'], "option" => $this->getStatus()));
        $this->va_list->setDropdownFilter(7, array("name" => $this->creditcardorder_m->filters[$this->creditcardorder_m->tableAwb.'.status'], "option" => $this->getStatusAwb()));

        $this->data['script'] = $this->load->view("script/creditcardorder_list", array("ajaxSource" => site_url("creditcardorder/creditCardOrderList")), true);
        $this->load->view("template", $this->data);
    }

    public function creditCardOrderList()
    {
        $sAction = $this->input->post("sAction");
        $data = $this->creditcardorder_m->getCreditCardOrderList();
        echo json_encode($data);
    }

    public function view($id)
    {
        $data = $this->creditcardorder_m->getCreditCardOrderById($id);
        if($data->num_rows() < 1) {
            redirect("creditcardorder");
        }

        $this->data['content'] = "group_form_v.php";
        $this->data['pageTitle'] = "View Credit Card Order";
        $this->data['breadcrumb'] = array("Credit Card Order"=> "", "View Credit Card Order" => "");
        $this->data['formTitle'] = "View Credit Card Order";

        $this->load->library("va_input", array("group" => "creditcard"));
        $this->va_input->setJustView();
        $this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Order Info", 1 => "Comment History") )->setActiveGroup(0);

        $value = $data->result_array();
		
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value[0]['client_code'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "sku", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value[0]['order_number'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value[0]['creditcard_status']], "label" => "Status", "help" => "Order Status", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['name'],"label" => "Name", "help" => "Name", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => number_format(@$value[0]['amount'], 2),  "label" => "Amount", "help" => "Amount", "disabled"=>"disabled") );
        $this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value[0]['items'], "view"=>"form/customItemsCod"));
        $this->va_input->addCustomField( array("name" =>"ccinfo", "placeholder" => "Items", "label" => "Items", "value" => @$value[0]['cc_info'], "view"=>"form/custom_cc_info"));
        $this->va_input->addInput( array("name" => "email", "placeholder" => "Email", "help" => "Email", "label" => "Email", "value" => @$value[0]['email'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['creditcardupdate_at'],"label" => "Updated At", "help" => "Updated At", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['creditcardcreated_at'],"label" => "Created At", "help" => "Created At", "disabled"=>"disabled") );
        $this->va_input->commitForm(0);

        $this->va_input->addCustomField( array("name" =>"","value" =>$value, "view"=>"form/customCommentHistory"));
        $this->va_input->commitForm(1);

        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }

    private function getStatus() {
		return array(-1=>"",0 => "pending", 1 => "processing", 2 => "complete", 3 => "fraud", 4 => "payment_review", 5 => "canceled",6 => "closed", 7 => "waiting_payment");
    }
    private function getStatusAwb() {
        return array(-1=>"",0=>"New Request",1 => "Printed");
    }

}
