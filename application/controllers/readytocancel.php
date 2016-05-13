<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Readytocancel extends MY_Controller {
    var $data = array();
    public function __construct(){
        parent::__construct();
        $this->load->model(array("autocancel_m","paymentconfirmation_m","codpaymentconfirmation_m"));
        $this->load->model("users_m");
        $this->load->model("client_m");
    }

    public function index(){
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Expire Order";
        $this->data['breadcrumb'] = array("Expire Order" => "readytocancel");

        $this->autocancel_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->disableAddPlugin()->setListName("Expire Order")
            ->setHeadingTitle(array("No", "Client Name","Order Number","Created Date","Expired Date","Order Method","Status"))
            ->setHeadingWidth(array(2,2,2,2,2,2,2));

        $this->va_list->setInputFilter(2, array("name" => $this->autocancel_m->filters['order_number']))
            ->setDropdownFilter(1, array("name" => $this->autocancel_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->va_list->setDropdownFilter(6, array("name" => $this->autocancel_m->filters['status'], "option" => $this->getStatus()));

        $this->data['script'] = $this->load->view("script/readytocancel_List", array("ajaxSource" => site_url("readytocancel/readyToCancelList")), true);
        $this->load->view("template", $this->data);
    }

    public function readyToCancelList(){
        $data = $this->autocancel_m->getReadyToCancelList();
        echo json_encode($data);
    }

    public function view(){
        $id=$this->input->get('id');
        $method=$this->input->get('method');

        $this->data['content'] = "group_form_v.php";
        $this->data['pageTitle'] = "Expire Order";
        $this->data['breadcrumb'] = array("Expire Order"=> "", "View Expire Order" => "");
        $this->data['formTitle'] = "View Expire Order";

        $this->load->library("va_input", array("group" => "readytocancel"));
        $this->va_input->setJustView();
        $this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Order Info", 1 => "Comment History") )->setActiveGroup(0);

        if($method == 'bank'){
            $data = $this->paymentconfirmation_m->getConfirmationById($id);
            if($data->num_rows() < 1) {
                redirect("readytocancel");
            }

            $flashData = $this->session->flashdata("clientError");
            if($flashData !== false) {
                $flashData = json_decode($flashData, true);
                $value = $flashData['data'];
                $msg = $flashData['msg'];
            } else {
                $msg = array();
                $value = $data->result_array();
            }
            $address= $value[0]['address'].", ".$value[0]['city']." - ".$value[0]['province']." ".$value[0]['zipcode'];

            $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value[0]['client_code'], "msg" => @$msg['client_code'], "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "sku", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value[0]['order_number'], "msg" => @$msg['order_number'], "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value[0]['status_bank']], "msg" => @$msg['status'], "label" => "Status", "help" => "Order Status", "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "name", "value" => @$value[0]['name'], "msg" => @$msg['name'], "label" => "Name", "help" => "Name", "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "transaction_date", "value" => @$value[0]['transaction_date'], "msg" => @$msg['transaction_date'], "label" => "Transfer Date", "help" => "Transaction Date", "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "amount", "value" => number_format(@$value[0]['amount'], 2), "msg" => @$msg['amount'], "label" => "Amount", "help" => "Amount", "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "origin", "value" => @$value[0]['origin_bank'], "msg" => @$msg['origin_bank'], "label" => "Original Bank", "help" => "Original Bank", "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "method", "value" => @$value[0]['transaction_method'], "msg" => @$msg['transaction_method'], "label" => "Transaction Method", "help" => "Transaction Method", "disabled"=>"disabled") );
            $this->va_input->addTextarea( array("name" => "shipping", "value" => @$address, "msg" => @$msg['address'], "label" => "Shipping Address", "help" => "Shipping Address", "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "shipping_type", "value" => @$value[0]['shipping_type'], "msg" => @$msg['shipping_type'], "label" => "Shipping Type", "help" => "Shipping Type", "disabled"=>"disabled") );
            $this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value[0]['items'], "view"=>"form/customItemsBank"));
            $this->va_input->addCustomField( array("name" =>"receipt_url", "placeholder" => "xx", "label" => "Receipt URL", "value" => @$value[0]['receipt_url'], "msg" => @$msg['receipt_url'], "view"=>"form/customFoto"));
            $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['updated_at'], "msg" => @$msg['updated_at'], "label" => "Updated At", "help" => "Updated At", "disabled"=>"disabled") );
            $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['created_at'], "msg" => @$msg['created_at'], "label" => "Created At", "help" => "Created At", "disabled"=>"disabled") );
            $this->va_input->commitForm(0);

            $this->va_input->addCustomField( array("name" =>"","value" =>$value, "view"=>"form/customCommentHistory"));
            $this->va_input->commitForm(1);

            $this->data['script'] = $this->load->view("script/client_add", array(), true);
            $this->load->view('template', $this->data);
        }
        else{
            $data = $this->codpaymentconfirmation_m->getCodPaymentConfirmationById($id);
            if($data->num_rows() < 1) {
                redirect("readytocancel");
            }

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

            $this->va_input->addCustomField(array("name" => "", "value" =>$value, "view" => "form/customCommentHistory"));
            $this->va_input->commitForm(1);

            $this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
            $this->load->view('template', $this->data);
        }

    }

    public function update(){
        $id=$this->input->get('id');
        $method=$this->input->get('method');
        $client=$this->input->get('client');
        $ordernr=$this->input->get('order_number');

        $value=$this->autocancel_m->getData($id, $method);

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Edit Expire Order";
        $this->data['breadcrumb'] = array("Expire Order"=> "readytocancel", "Expire Order" => "");
        $this->data['formTitle'] = "Edit Expired Order";

        $this->load->library("va_input", array("group" => "expiredorder"));
        $clientname=$this->client_m->getClientById($client)->row_array();

        $this->va_input->addHidden( array("name" => "method", "value" => "update"));
        $this->va_input->addHidden( array("name" => "id", "value" => $id));
        $this->va_input->addHidden( array("name" => "order_method", "value" => $method));
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value"=>$clientname['client_code'],"disabled"=>"disabled"));
        $this->va_input->addInput( array("name" => "order_number", "placeholder" => "Order Number", "help" => "Client Name", "label" => "Client Name", "value"=>$ordernr,"disabled"=>"disabled"));
        $this->va_input->addCustomField( array("name" =>"date", "placeholder" => "Expired Date", "label" => "Expired Date", "value" =>$value, "view"=>"form/customExpire"));
        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        $post = $this->input->post("expiredorder");
        if(empty($post)) {
            redirect("readytocancel");
        }

        if($post['method'] == "update") {
            $result = $this->autocancel_m->update($post);
            if(is_numeric($result)) {
                redirect("readytocancel");
            }
        }
    }

    private function getStatus() {
        return array(-1=>"",0 =>"Pending Cancel", 2 => "Canceled");
    }
}
