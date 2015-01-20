<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Mageapi $mageapi
 *
 */
class Codconfirmation extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model("codconfirmation_m");
		$this->load->model("users_m");
		$this->load->model("client_m");
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "COD Order Confirmation";
		$this->data['breadcrumb'] = array("COD Order Confirmation" => "");
		
		$this->codconfirmation_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("COD Order Confirmation")
		->setHeadingTitle(array("Record #", "Client Name","Status","Order Number","Cust. Name","Updated By"))
		->setHeadingWidth(array(2,2,2,2,2,2));
		
		$this->va_list->setInputFilter(3, array("name" => $this->codconfirmation_m->filters['order_number']))
			->setDropdownFilter(1, array("name" => $this->codconfirmation_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
				
		$this->data['script'] = $this->load->view("script/codconfirmation_list", array("ajaxSource" => site_url("codconfirmation/CodConfirmationList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function CodConfirmationList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
				$this->codconfirmation_m->removePayment($id, $action);
			}
		}	
		$data = $this->codconfirmation_m->getCodConfirmationList();	
		echo json_encode($data);
	}
	
	public function view($id)
	{
		$data = $this->codconfirmation_m->getCodConfirmationById($id);
		if($data->num_rows() < 1) {
			redirect("codconfirmation");
		}
		
		$this->data['content'] = "group_form_v.php";
		$this->data['pageTitle'] = "COD Order Confirmation";
		$this->data['breadcrumb'] = array("View COD Order Confirmation" => "");
		$this->data['formTitle'] = "View COD Order Confirmation";
		
		$this->load->model("codconfirmation_m");
		$this->load->library("va_input", array("group" => "codconfirmation"));
		$this->va_input->setJustView();
		$this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Order Info", 1 => "Status / Comment") )->setActiveGroup(0);
		
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
		
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value['client_code'], "msg" => @$msg['client_code'], "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "ordernumber", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value['order_number'], "msg" => @$msg['order_number'], "disabled"=>"disabled") );
		$this->va_input->addInput( array("name" => "customer_name", "value" => @$value['customer_name'], "msg" => @$msg['customer_name'], "label" => "Customer Name", "help" => "Customer Name", "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "amount", "value" => number_format(@$value['amount'], 2), "msg" => @$msg['amount'], "label" => "Amount", "help" => "Amount", "disabled"=>"disabled"));
		$this->va_input->addTextarea( array("name" => "shipping_address","placeholder" => "Shipping Addres","value" => @$value['shipping_address'], "msg" => @$msg['shipping_address'], "label" => "Shipping Address", "help" => "Shipping Address","disabled"=>"disabled"));
		$this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value['items'], "msg" => @$msg['items'], "view"=>"form/customItemsCod"));		
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['updated_at'], "msg" => @$msg['updated_at'], "label" => "Updated At", "help" => "Updated At", "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "created_at", "value" => @$value['created_at'], "msg" => @$msg['created_at'], "label" => "Created At", "help" => "Created At", "disabled"=>"disabled"));
		$this->va_input->commitForm(0);
		
		$this->va_input->addHidden( array("name" => "method", "value" => "comment") );
		$this->va_input->addHidden( array("name" => "client_id", "value" => $value['client_id']) );
		$this->va_input->addHidden( array("name" => "order_number", "value" => $value['order_number']) );
		$this->va_input->addHidden( array("name" => "id", "value" => $value['id']) );
		$this->va_input->addSelect( array("name" => "status", "label" => "Status *", "list" => array("0" => "New Request", "1" => "Approve","2"=>"Cancel"), "value" => @$value['status'], "msg" => @$msg['status']));	
		$this->va_input->addTextarea( array("name" => "comment", "value" => @$value['note'], "msg" => @$msg['note'], "label" => "Comment *", "help" => "Comment") );
		$this->va_input->addCustomField( array("name"=>"","value" =>'submit', "view"=>"form/customSubmit"));		
		$this->va_input->commitForm(1);
		
		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function approve($id)
	{
		$data = $this->codconfirmation_m->getCodConfirmationById($id);		
		if($data->num_rows() < 1) {
			redirect("codconfirmation");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Approve COD Order";
		$this->data['breadcrumb'] = array("COD Order Confirmation "=> "codconfirmation", "Approve COD Order" => "");
		$this->data['formTitle'] = "Approve COD Order";
	
		$this->load->library("va_input", array("group" => "codconfirmation"));
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
		
		$this->va_input->addHidden( array("name" => "method", "value" => "approve") );
		$this->va_input->addHidden( array("name" => "id", "value" => $value['id']) );
		$this->va_input->addHidden( array("name" => "client_id", "value" => $value['client_id']) );
		$this->va_input->addTextarea( array("name" => "approve", "placeholder" => "Note", "help" => "Note", "label" => "Note", "value" => @$value['note'], "msg" => @$msg['note']) );
		
		$this->data['script'] = $this->load->view("script/codconfirmation_add", array(), true);
		$this->load->view('template', $this->data);					
	}
	
	public function cancel($id)
	{
		$data = $this->codconfirmation_m->getCodConfirmationById($id);
		if($data->num_rows() < 1) {
			redirect("codconfirmation");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Cancel COD Order";
		$this->data['breadcrumb'] = array("COD Order Confirmation "=> "codconfirmation", "Cancel COD Order" => "");
		$this->data['formTitle'] = "Cancel COD Order";
	
		$this->load->library("va_input", array("group" => "codconfirmation"));
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
		$this->va_input->addHidden( array("name" => "client_id", "value" => $value['client_id']) );
		$this->va_input->addTextarea( array("name" => "cancel", "placeholder" => "Note", "help" => "Note", "label" => "Note", "value" => @$value['note'], "msg" => @$msg['note']) );
		
		$this->data['script'] = $this->load->view("script/codconfirmation_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save() 
	{
		$this->load->library("mageapi");
		$post = $this->input->post("codconfirmation");	
		
		if(empty($post)) {
			redirect("codconfirmation");
		}
		
		else if($post['method'] == "approve") {			
			$data = $this->codconfirmation_m->getCodConfirmationById($post['id'])->row_array();						
			$client = $this->client_m->getClientById($post['client_id'])->row_array();
			$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
			);
			
			if( $this->mageapi->initSoap($config) ) {
				$this->mageapi->setOrderToVerified($data['order_number'], $post['approve']);
			}
			
			$result = $this->codconfirmation_m->Approve($post);
			
			if(is_numeric($result)) 
			{
				redirect("codconfirmation");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("codconfirmation/approve/".$post['id']);
			}
		}
		
		else if($post['method'] == "cancel") {
			$data = $this->codconfirmation_m->getCodConfirmationById($post['id'])->row_array();						
			$client = $this->client_m->getClientById($post['client_id'])->row_array();
			$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
			);
			
			if( $this->mageapi->initSoap($config) ) {
				$this->mageapi->setOrderToCancel($data['order_number'], $post['cancel']);
			}
			
			$result = $this->codconfirmation_m->Cancel($post);
			if(is_numeric($result)) 
			{
				redirect("codconfirmation");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("codconfirmation/cancel/".$post['id']);
			}
		}
		
		else if($post['method'] == "comment") {
			$client = $this->client_m->getClientById($post['client_id'])->row_array();
			$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
			);
				
			if( $this->mageapi->initSoap($config) ) {
				if($post['status'] == 2) {
					$apiResponse = $this->mageapi->setOrderToCancel($post['order_number'], $post['comment']);
				} else if($post['status'] == 1) {
					$apiResponse = $this->mageapi->setOrderToVerified($post['order_number'], $post['comment']);
				}
			}
			
			if(!$apiResponse) {
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => array("order_number" => "Magento client error"), "data" => $post))) );
				redirect("codconfirmation/view/".$post['id']);
			}
			
			$result = $this->codconfirmation_m->Comment($post);
			if(is_numeric($result)) 
			{
				redirect("codconfirmation");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("codconfirmation/view/".$post['id']);
			}
		}
	}
		
	private function getStatus() {
		return array(-1=>"",0=>"New Request",1 => "Approve",2 => "Cancel");
	}	
}