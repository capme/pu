<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Paypalorder extends MY_Controller {
    var $data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->model("paypalorder_m");
        $this->load->model("users_m");
        $this->load->model("client_m");
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Paypal Order";
        $this->data['breadcrumb'] = array("Paypal Order" => "");

        $this->paypalorder_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->disableAddPlugin()->setListName("Paypal Order")
            ->setHeadingTitle(array("#", "Created Date", "Client Name","Order Number","Name","Amount","Status","Status AWB"))
            ->setHeadingWidth(array(2,2,2,2,3,3,3,4));

        $this->va_list->setInputFilter(3, array("name" => $this->paypalorder_m->filters['order_number']))
            ->setDropdownFilter(2, array("name" => $this->paypalorder_m->filters[$this->paypalorder_m->table.'.client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->va_list->setInputFilter(4, array("name" => $this->paypalorder_m->filters['name']));
        $this->va_list->setInputFilter(5, array("name" => $this->paypalorder_m->filters[$this->paypalorder_m->table.'.amount']));
        $this->va_list->setDropdownFilter(6, array("name" => $this->paypalorder_m->filters[$this->paypalorder_m->table.'.status'], "option" => $this->getStatus()));
        $this->va_list->setDropdownFilter(7, array("name" => $this->paypalorder_m->filters[$this->paypalorder_m->tableAwb.'.status'], "option" => $this->getStatusAwb()));

        $this->data['script'] = $this->load->view("script/paypalorder_list", array("ajaxSource" => site_url("paypalorder/paypalOrderList")), true);
        $this->load->view("template", $this->data);
    }

    public function paypalOrderList()
    {
        $sAction = $this->input->post("sAction");
        $data = $this->paypalorder_m->getPaypalOrderList();
        echo json_encode($data);
    }
	
	public function view($id)
    {
        $data = $this->paypalorder_m->getPaypalOrderById($id);
        if($data->num_rows() < 1) {
            redirect("paypalorder");
        }

        $this->data['content'] = "group_form_v.php";
        $this->data['pageTitle'] = "View Paypal Order";
        $this->data['breadcrumb'] = array("Paypal Order"=> "", "View Paypal Order" => "");
        $this->data['formTitle'] = "View Paypal Order";

        $this->load->library("va_input", array("group" => "paypal"));
        $this->va_input->setJustView();
        $this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Order Info", 1 => "Comment History") )->setActiveGroup(0);

        $value = $data->result_array();
		
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value[0]['client_code'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "sku", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value[0]['order_number'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value[0]['creditcard_status']], "label" => "Status", "help" => "Order Status", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['name'],"label" => "Name", "help" => "Name", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => number_format(@$value[0]['amount'], 2),  "label" => "Amount", "help" => "Amount", "disabled"=>"disabled") );
        $this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value[0]['items'], "view"=>"form/customItemsCod"));
        $this->va_input->addInput( array("name" => "email", "placeholder" => "Email", "help" => "Email", "label" => "Email", "value" => @$value[0]['email'],"disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['creditcardupdate_at'],"label" => "Updated At", "help" => "Updated At", "disabled"=>"disabled") );
        $this->va_input->addInput( array("name" => "updated_at", "value" => @$value[0]['creditcardcreated_at'],"label" => "Created At", "help" => "Created At", "disabled"=>"disabled") );
        $this->va_input->commitForm(0);

        $this->va_input->addCustomField( array("name" =>"","value" =>$value, "view"=>"form/customCommentHistory"));
        $this->va_input->commitForm(1);

        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }
	
	public function approve ($id){
			$this->load->library("mageapi");
			$data = $this->paypalorder_m->getPaypalOrderById($id)->row_array();				
			$client = $this->client_m->getClientById($data['client_id'])->row_array();
			$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
			);
			if( $this->mageapi->initSoap($config) ) {
				$this->mageapi->paypalApprove($data['order_number']);
			}
			$this->paypalorder_m->setStatusApprove($id, $data['order_id'],$data['type']);
			redirect('paypalorder');
	}
	
	public function cancel ($id){
		$this->load->library("mageapi");
		$data = $this->paypalorder_m->getPaypalOrderById($id)->row_array();				
		$client = $this->client_m->getClientById($data['client_id'])->row_array();
		$config = array(
		"auth" => $client['mage_auth'],
		"url" => $client['mage_wsdl']
		);
		if( $this->mageapi->initSoap($config) ) {
			$this->mageapi->cancelPayment($data['order_number'],'canceled');
		}
		$this->paypalorder_m->setStatusCancel($id, $data['order_id'],$data['type']);
		redirect('paypalorder');	
	}

    private function getStatus() {
		return array(-1=>"",0 => "pending", 1 => "processing", 2 => "complete", 3 => "fraud", 4 => "payment_review", 5 => "canceled",6 => "closed", 7 => "waiting_payment");
    }
    private function getStatusAwb() {
        return array(-1=>"",0=>"New Request",1 => "Printed");
    }

}
