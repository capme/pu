<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Returnorder extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model("returnorder_m");
		$this->load->model("users_m");
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Return Order";
		$this->data['breadcrumb'] = array("Return Order" => "");
	
		$this->returnorder_m->clearCurrentFilter();
		
		$this->load->library("va_list");
		$this->va_list->setListName("Return Order")->setMassAction(array("2" => "Remove"))
		->setHeadingTitle(array("Record #", "Client Code","Order Number","SKU","Status","Updated","Updated By"))
		->setHeadingWidth(array(2, 2, 2,8,5,10,10));
		
		$this->va_list->setInputFilter(2, array("name" => $this->returnorder_m->filters['order_number']));
		$this->va_list->setDropdownFilter(4, array("name" => $this->returnorder_m->filters['status'], "option" => $this->getStatus()));	
		
		$this->data['script'] = $this->load->view("script/returnorder_list", array("ajaxSource" => site_url("returnorder/returnOrderList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function returnOrderList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
				$this->returnorder_m->removeClient($id, $action);
			}
		}	
		$data = $this->returnorder_m->getReturnOrderList();	
		echo json_encode($data);
	}
	
	public function cancel( $id )
	{
		$data = $this->returnorder_m->getOrderById($id);
		if($data->num_rows() < 1) {
			redirect("returnorder");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Cancel Order";
		$this->data['breadcrumb'] = array("Return Oder"=> "", "Cancel Order" => "");
		$this->data['formTitle'] = "cancel order";
	
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
		$this->va_input->addTextarea( array("name" => "cancel_reason", "placeholder" => "Cancel reason", "help" => "Cancel reason", "label" => "Cancel Reason *", "value" => @$value['cancel_reason'], "msg" => @$msg['cancel_reason']) );
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);

		$this->load->view('template', $this->data);
	}
	
	public function approve ($id)
	{
		$data = $this->returnorder_m->getOrderById($id);
		redirect("returnorder");
	}
	
	
	
	public function save() 
	{
	
		$post = $this->input->post("returnorder");
		if(empty($post)) {
			redirect("returnorder");
		}
		
		else if($post['method'] == "update") {
			$result = $this->returnorder_m->Reason($post);
			if(is_numeric($result)) 
			{
				redirect("returnorder");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("returnorder/cancel/".$post['id']);
			}
		}
	}
	
	public function view($id)
	{
		$data = $this->returnorder_m->getOrderById($id);
		if($data->num_rows() < 1) {
			redirect("returnorder");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Return Order";
		$this->data['breadcrumb'] = array("Return Order"=> "", "View Oder" => "");
		$this->data['formTitle'] = "View Order";
	
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
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client Code", "help" => "Client Code", "label" => "Client Code *", "value" => @$value['client_code'], "msg" => @$msg['client_code']) );
		$this->va_input->addInput( array("name" => "order_number", "placeholder" => "Order Number", "label" => "Order Number", "value" => @$value['order_number'], "msg" => @$msg['order_number'], "help" => "Order Number") );
		$this->va_input->addInput( array("name" => "sku", "placeholder" => "SKU", "help" => "SKU", "label" => "SKU *", "value" => @$value['sku'], "msg" => @$msg['sku']) );
		$this->va_input->addSelect( array("name" => "status", "list" => $this->getStatus(), "value" => @$value['status'], "msg" => @$msg['status'], "label" => "Order Status *", "help" => "Order Status") );
		$this->va_input->addInput( array("name" => "created_at", "value" => @$value['created_at'], "msg" => @$msg['created_at'], "label" => "Created At *", "help" => "Created At") );
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['updated_at'], "msg" => @$msg['updated_at'], "label" => "Updated At *", "help" => "Updated At") );
		$this->va_input->addInput( array("name" => "phone_number", "value" => @$value['phone_number'], "msg" => @$msg['phone_number'], "label" => "Phone Number *", "help" => "Phone Number") );
		$this->va_input->addInput( array("name" => "email_address", "value" => @$value['email_address'], "msg" => @$msg['email_address'], "label" => "Email Address *", "help" => "Email Address") );
		$this->va_input->addTextarea( array("name" => "cancel_reason", "value" => @$value['cancel_reason'], "msg" => @$msg['cancel_reason'], "label" => "Cancel Reason*", "help" => "Cancel Reason") );
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	private function getStatus() {
		return array(0=>"Default",1 => "Approve",2 => "Cancel");
	}
	
	}