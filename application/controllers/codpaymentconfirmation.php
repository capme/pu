<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Codpaymentconfirmation extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model("codpaymentconfirmation_m");
		$this->load->model("users_m");
		$this->load->model("client_m");
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "COD Payment Confirmation";
		$this->data['breadcrumb'] = array("COD Payment Confirmation" => "");
		
		$this->codpaymentconfirmation_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("COD Payment Confirmation")
		->setHeadingTitle(array("#","Created Date", "Client Name","Status","Order Number","Cust. Name","Phone / Email"))
		->setHeadingWidth(array(2,2,2,2,2,2,2));
		
		$this->va_list->setInputFilter(4, array("name" => $this->codpaymentconfirmation_m->filters['order_number']))
			->setDropdownFilter(2, array("name" => $this->codpaymentconfirmation_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
		$this->va_list->setDropdownFilter(3, array("name" => $this->codpaymentconfirmation_m->filters['status'], "option" => $this->getStatus()));
		
		$this->data['script'] = $this->load->view("script/codconfirmation_list", array("ajaxSource" => site_url("codpaymentconfirmation/CodPaymentConfirmationList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function CodPaymentConfirmationList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
				$this->codpaymentconfirmation_m->removePayment($id, $action);
			}
		}	
		$data = $this->codpaymentconfirmation_m->getCodPaymentConfirmationList();	
		echo json_encode($data);
	}
	
	public function view($id)
	{
		$data = $this->codpaymentconfirmation_m->getCodPaymentConfirmationById($id);
		if($data->num_rows() < 1) {
			redirect("codconfirmation");
		}
		
		$this->data['content'] = "group_form_v.php";
		$this->data['pageTitle'] = "COD Payment Confirmation";
		$this->data['breadcrumb'] = array("View COD Payment Confirmation" => "");
		$this->data['formTitle'] = "View COD Payment Confirmation";
		
		$this->load->model("codconfirmation_m");
		$this->load->library("va_input", array("group" => "codpaymentconfirmation"));
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
		
		$this->va_input->addCustomField( array("name"=>"","value" =>$value, "view"=>"form/customCommentHistory"));
		$this->va_input->commitForm(1);
		
		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function receive($id)
	{
		$data = $this->codpaymentconfirmation_m->getCodPaymentConfirmationById($id);		
		if($data->num_rows() < 1) {
			redirect("codpaymentconfirmation");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Receive COD Payment";
		$this->data['breadcrumb'] = array("COD Payment Confirmation "=> "codpaymentconfirmation", "Receive COD Payment" => "");
		$this->data['formTitle'] = "Receive COD Payment";
	
		$this->load->library("va_input", array("group" => "codpaymentconfirmation"));
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
		
		$this->va_input->addHidden( array("name" => "method", "value" => "receive") );
		$this->va_input->addHidden( array("name" => "id", "value" => $value['id']) );
		$this->va_input->addHidden( array("name" => "client_id", "value" => $value['client_id']) );
		$this->va_input->addTextarea( array("name" => "receive", "placeholder" => "Note", "help" => "Note", "label" => "Note", "value" => '', "msg" => @$msg['note']) );
		
		$this->data['script'] = $this->load->view("script/codconfirmation_add", array(), true);
		$this->load->view('template', $this->data);					
	}
	
	public function cancel($id)
	{
		$data = $this->codpaymentconfirmation_m->getCodPaymentConfirmationById($id);
		if($data->num_rows() < 1) {
			redirect("codpaymentconfirmation");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Cancel COD Payment";
		$this->data['breadcrumb'] = array("COD Payment Confirmation "=> "codpaymentconfirmation", "Cancel COD Payment" => "");
		$this->data['formTitle'] = "Cancel COD Payment";
	
		$this->load->library("va_input", array("group" => "codpaymentconfirmation"));
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
	
		$this->va_input->addHidden( array("name" => "method", "value" => "cancel") );
		$this->va_input->addHidden( array("name" => "id", "value" => $value['id']) );
		$this->va_input->addTextarea( array("name" => "cancel", "placeholder" => "Note", "help" => "Note", "label" => "Note", "value" => '', "msg" => @$msg['note']) );
		
		$this->data['script'] = $this->load->view("script/codconfirmation_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save() 
	{
		$this->load->library("mageapi");
		$post = $this->input->post("codpaymentconfirmation");		
		if(empty($post)) {
			redirect("codpaymentconfirmation");
		}
		
		else if($post['method'] == "receive") {			
			$data = $this->codpaymentconfirmation_m->getCodPaymentConfirmationById($post['id'])->row_array();						
			$client = $this->client_m->getClientById($post['client_id'])->row_array();
			$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
			);
			
			if( $this->mageapi->initSoap($config) ) {
				$this->mageapi->setOrderToreceived($data['order_number']);
			}
			
			$result = $this->codpaymentconfirmation_m->Receive($post);
		
			if(is_numeric($result)) 
			{
				redirect("codpaymentconfirmation");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("codpaymentconfirmation/receive/".$post['id']);
			}
		}
		
		else if($post['method'] == "cancel") {
			$result = $this->codpaymentconfirmation_m->Cancel($post);
			if(is_numeric($result)) 
			{
				redirect("codpaymentconfirmation");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("codpaymentconfirmation/cancel/".$post['id']);
			}
		}
	}
	
	private function getStatus() {
		return array(-1=>"",1 => "Processing",3 => "Received", 4 => "Cancel");
	}	
}
