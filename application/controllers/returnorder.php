<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Va_list $va_list
 *
 */
class Returnorder extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("returnorder_m", "users_m", "client_m"));
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Return Order";
		$this->data['breadcrumb'] = array("Return Order" => "");
		
		$this->returnorder_m->clearCurrentFilter();
		
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("Return Order")
		->setHeadingTitle(array("Record #", "Client Name","Order Number","SKU","Status","Updated At","Updated By"))
		->setHeadingWidth(array(2, 2, 2,8,5,10,10));
		
		$this->va_list->setInputFilter(2, array("name" => $this->returnorder_m->filters['order_number']))
			->setDropdownFilter(1, array("name" => $this->returnorder_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));
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
		$this->va_input->addHidden( array("name" => "id", "value" => $value['return_id']) );
		$this->va_input->addTextarea( array("name" => "cancel_reason", "placeholder" => "Cancel reason", "help" => "Cancel reason", "label" => "Cancel Reason *", "value" => @$value['cancel_reason'], "msg" => @$msg['cancel_reason']) );
		
		$this->data['script'] = $this->load->view("script/returnorder_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function approve ($id)
	{
		$this->returnorder_m->Approve($id);
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
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client Code", "help" => "Client Code", "label" => "Client Code", "value" => @$value['client_code'], "msg" => @$msg['client_code']) );
		$this->va_input->addInput( array("name" => "order_number", "placeholder" => "Order Number", "label" => "Order Number", "value" => @$value['order_number'], "msg" => @$msg['order_number'], "help" => "Order Number") );
		$this->va_input->addInput( array("name" => "sku", "placeholder" => "SKU", "help" => "SKU", "label" => "SKU", "value" => @$value['sku'], "msg" => @$msg['sku']) );
		$this->va_input->addTextarea( array("name" => "reason", "value" => @$value['return_reason'], "msg" => @$msg['return_reason'], "label" => "Return Reason", "help" => "Cancellation reason") );
		$this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value['status']], "msg" => @$msg['status'], "label" => "Status", "help" => "Order Status") );
		$this->va_input->addInput( array("name" => "phone_number", "value" => @$value['phone_number'], "msg" => @$msg['phone_number'], "label" => "Phone Number", "help" => "Phone Number") );
		$this->va_input->addInput( array("name" => "email_address", "value" => @$value['email_address'], "msg" => @$msg['email_address'], "label" => "Email Address", "help" => "Email Address") );
		$this->va_input->addTextarea( array("name" => "cancel_reason", "value" => @$value['cancel_reason'], "msg" => @$msg['cancel_reason'], "label" => "Cancel Reason", "help" => "Ops. Cancel Reason") );
		
		$this->data['script'] = $this->load->view("script/returnorder_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	private function getStatus() {
		return array(-1=>"",0=>"New Request",1 => "Approve",2 => "Cancel");
	}
	
	}
