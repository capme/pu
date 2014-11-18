<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Paymentconfirmation extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model("paymentconfirmation_m");
		$this->load->model("users_m");
		$this->load->model("client_m");
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Payment Confirmation";
		$this->data['breadcrumb'] = array("Payment Confirmation" => "");
		
		$this->paymentconfirmation_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("Payment Confirmation")
		->setHeadingTitle(array("Record #", "Client Name","Order Number","Name","Origin Bank","Amount","Status","Transfer Date","Receipt","Updated By"))
		->setHeadingWidth(array(2, 2,2,3,2,3,4,2,2,4));
		
		$this->va_list->setInputFilter(2, array("name" => $this->paymentconfirmation_m->filters['order_number']));
		$this->va_list->setDropdownFilter(6, array("name" => $this->paymentconfirmation_m->filters['status'], "option" => $this->getStatus()));
		
		$this->data['script'] = $this->load->view("script/paymentconfirmation_list", array("ajaxSource" => site_url("paymentconfirmation/paymentConfirmationList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function paymentConfirmationList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
				$this->paymentconfirmation_m->removePayment($id, $action);
			}
		}	
		$data = $this->paymentconfirmation_m->getPaymentConfirmationList();	
		echo json_encode($data);
	}
	
	public function view($id)
	{
		$data = $this->paymentconfirmation_m->getConfirmationById($id);
		if($data->num_rows() < 1) {
			redirect("paymentconfirmation");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Payment Confirmation";
		$this->data['breadcrumb'] = array("Payment Confirmation"=> "", "View Payment Confirmation" => "");
		$this->data['formTitle'] = "View Payment Confirmation";
	
		$this->load->library("va_input", array("group" => "returnorder"));
		$this->va_input->setJustView();
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
		
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value['client_code'], "msg" => @$msg['client_code']) );
		
		$this->va_input->addInput( array("name" => "sku", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value['order_number'], "msg" => @$msg['order_number']) );
		$this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value['status']], "msg" => @$msg['status'], "label" => "Status", "help" => "Order Status") );		
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['name'], "msg" => @$msg['name'], "label" => "Name", "help" => "Name") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['transaction_date'], "msg" => @$msg['transaction_date'], "label" => "Transfer Date", "help" => "Transaction Date") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => number_format(@$value['amount'], 2), "msg" => @$msg['amount'], "label" => "Amount", "help" => "Amount") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['origin_bank'], "msg" => @$msg['origin_bank'], "label" => "Original Bank", "help" => "Original Bank") );
		//$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['dest_bank'], "msg" => @$msg['dest_bank'], "label" => "Destination Bank", "help" => "Destination Bank") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['transaction_method'], "msg" => @$msg['transaction_method'], "label" => "Transaction Method", "help" => "Transaction Method") );
		$this->va_input->addCustomField( array("name" =>"receipt_url", "placeholder" => "xx", "label" => "Receipt URL", "value" => @$value['receipt_url'], "msg" => @$msg['receipt_url'], "view"=>"form/customFoto"));
		
		$this->va_input->addTextarea( array("name" => "cancel_reason", "value" => @$value['reason'], "msg" => @$msg['reason'], "label" => "Reason", "help" => "Reason") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['updated_at'], "msg" => @$msg['updated_at'], "label" => "Updated At", "help" => "Updated At") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['created_at'], "msg" => @$msg['created_at'], "label" => "Created At", "help" => "Created At") );
		
				
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	private function getStatus() {
		return array(-1=>"",0=>"New Request",1 => "Approve",2 => "Cancel");
	}
	
	public function approve ($id)
	{
		$this->paymentconfirmation_m->Approve($id);
		$this->load->model("client_m");
		$this->load->library("mageapi");
		$data = $this->paymentconfirmation_m->getConfirmationById($id)->row_array();
		$client = $this->client_m->getClientById($data['client_id'])->row_array();

		$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
		);
		
		if( $this->mageapi->initSoap($config) ) {
			$this->mageapi->processOrder($data['order_number']);
		}
		redirect("paymentconfirmation");
	}
	
	public function cancel($id)
	{
		$data = $this->paymentconfirmation_m->getConfirmationById($id);
		if($data->num_rows() < 1) {
			redirect("paymentconfirmation");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Cancel Item";
		$this->data['breadcrumb'] = array("Payment Confirmation "=> "", "Cancel Payment" => "");
		$this->data['formTitle'] = "cancel payment";
	
		$this->load->library("va_input", array("group" => "returnorder"));
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
	
		$this->va_input->addHidden( array("name" => "method", "value" => "update") );
		$this->va_input->addHidden( array("name" => "id", "value" => $value['id']) );
		$this->va_input->addTextarea( array("name" => "reason", "placeholder" => "Cancel reason", "help" => "Cancel reason", "label" => "Cancel Reason", "value" => @$value['reason'], "msg" => @$msg['reason']) );
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save() 
	{
		$post = $this->input->post("returnorder");
		if(empty($post)) {
			redirect("paymentconfirmation");
		}
		
		else if($post['method'] == "update") {
			$result = $this->paymentconfirmation_m->Reason($post);
			if(is_numeric($result)) 
			{
				redirect("paymentconfirmation");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("paymentconfirmation/cancel/".$post['id']);
			}
		}
	}
	
	
}