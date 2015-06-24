<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Canceledorder extends MY_Controller {
    var $data = array();
    public function __construct(){
        parent::__construct();
        $this->load->model("canceledorder_m");
        $this->load->model("users_m");
        $this->load->model("client_m");
    }

    public function index(){
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "COD Order Canceled";
        $this->data['breadcrumb'] = array("COD Order Canceled" => "canceledorder");

        $this->canceledorder_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->disableAddPlugin()->setListName("COD Order Canceled")
            ->setHeadingTitle(array("#", "Created Date", "Client Name","Order Number","Cust. Name", "Amount","Phone / Email","Status"))
            ->setHeadingWidth(array(2,2,2,2,2,2,2,2));

        $this->va_list->setInputFilter(3, array("name" => $this->canceledorder_m->filters['order_number']))
            ->setDropdownFilter(2, array("name" => $this->canceledorder_m->filters[$this->canceledorder_m->table.'.client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->va_list->setInputFilter(4, array("name" => $this->canceledorder_m->filters['customer_name']));
        $this->va_list->setInputFilter(5, array("name" => $this->canceledorder_m->filters[$this->canceledorder_m->table.'.amount']));
        $this->va_list->setDateFilter(1, array("name"=>$this->canceledorder_m->filters['created_at']));

        $this->data['script'] = $this->load->view("script/canceledorder_List", array("ajaxSource" => site_url("canceledorder/canceledOrderList")), true);
        $this->load->view("template", $this->data);
    }

    public function canceledOrderList(){
        $data = $this->canceledorder_m->getCanceledOrderList();
        echo json_encode($data);
    }

    public function view($id){
        $data = $this->canceledorder_m->getCanceledOrderById($id);
        if($data->num_rows() < 1) {
            redirect("canceledorder");
        }

        $this->data['content'] = "group_form_v.php";
        $this->data['pageTitle'] = "COD Order Canceled";
        $this->data['breadcrumb'] = array("View COD Order Canceled" => "");
        $this->data['formTitle'] = "View COD Order Canceled";

        $this->load->library("va_input", array("group" => "codconfirmation"));
        $this->va_input->setJustView();
        $this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Order Info", 1 => "Comment History") )->setActiveGroup(0);

        $flashData = $this->session->flashdata("clientError");
        if($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = array();
            $value = $data->result_array();
        }

        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value[0]['client_code'], "msg" => @$msg['client_code'], "disabled"=>"disabled"));
        $this->va_input->addInput( array("name" => "ordernumber", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value[0]['order_number'], "msg" => @$msg['order_number'], "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "customer_name", "value" => @$value[0]['customer_name'], "msg" => @$msg['customer_name'], "label" => "Customer Name", "help" => "Customer Name", "disabled"=>"disabled"));
        $this->va_input->addInput( array("name" => "email", "value" => @$value[0]['email'], "msg" => @$msg['email'], "label" => "Email Address", "help" => "Customer Email", "disabled"=>"disabled"));
        $this->va_input->addInput( array("name" => "phone_number", "value" => @$value[0]['phone_number'], "msg" => @$msg['phone_number'], "label" => "Customer Phone", "help" => "Customer Phone", "disabled"=>"disabled"));
        $this->va_input->addInput( array("name" => "amount", "value" => number_format(@$value[0]['amount'], 2), "msg" => @$msg['amount'], "label" => "Amount", "help" => "Amount", "disabled"=>"disabled"));
        $this->va_input->addTextarea( array("name" => "shipping_address","placeholder" => "Shipping Addres","value" => @$value[0]['shipping_address'], "msg" => @$msg['shipping_address'], "label" => "Shipping Address", "help" => "Shipping Address","disabled"=>"disabled"));
        $this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value[0]['items'], "msg" => @$msg['items'], "view"=>"form/customItemsCod"));
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['updated_at'], "msg" => @$msg['updated_at'], "label" => "Updated At", "help" => "Updated At", "disabled"=>"disabled"));
        $this->va_input->addInput( array("name" => "created_at", "value" => @$value[0]['created_at'], "msg" => @$msg['created_at'], "label" => "Created At", "help" => "Created At", "disabled"=>"disabled"));
        $this->va_input->commitForm(0);

        $this->va_input->addCustomField( array("name" =>"","value" =>$value, "view"=>"form/customCommentHistory"));
        $this->va_input->commitForm(1);

        $this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
        $this->load->view('template', $this->data);
    }
}
