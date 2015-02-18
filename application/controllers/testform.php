<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Testform extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		
		$this->_page();		

	}
	
	private function _page(){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "test ajah form custom";
		$this->data['formTitle'] = "test ajah form custom";
		$this->data['breadcrumb'] = array("AWB Printing"=> "", "View AWB Printing" => "");
		
		$this->load->library("va_input", array("group" => "returnorder"));
		$this->va_input->setJustView();
		
		  
		$arrayObject =  array(
			"objectname" => "table",
			"id" => "table1",
			"sub" =>
				array(
					"objectname" => "tr",
					"id" => "tr1",
					"sub" =>
						array(
							"objectname" => "td",
							"id" => "td1",
							"sub" => 
								array(
									"objectname" => "input",
									"id" => "input1",
									"value" => "isi text 1"
								)
						)
				)		
		);
		 /*
		$arrayObject = array(
			"objectname" => "span",
			"name" => "container1",
			"id" => "container1",
			"sub" => array(
						"objectname" => "input",
						"id" => "input1",
						"name" => "input1",
						"value" => "isi input1"
					)
			);
		
		*/
		/*
		$arrayObject = array(
			"objectname" => "input",
			"type" => "text",
			"value" => "isi container1",
			"name" => "container1",
			"id" => "container1"
			);
			*/
		
		$this->va_input->addCustomInput( $arrayObject );
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
		
	}
	
	private function _notgrouped() 
	{
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "test ajah form custom";
		$this->data['formTitle'] = "test ajah form custom";
		$this->data['breadcrumb'] = array("AWB Printing"=> "", "View AWB Printing" => "");
		
		$this->load->library("va_input", array("group" => "returnorder"));
		$this->va_input->setJustView();
		
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value['client_code'], "msg" => @$msg['client_code']) );
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
		

	}

	private function _grouped() 
	{
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "test ajah form custom";
		$this->data['formTitle'] = "test ajah form custom";
		$this->data['breadcrumb'] = array("AWB Printing"=> "", "View AWB Printing" => "");
		
		$this->load->library("va_input", array("group" => "returnorder"));
		$this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Order Info", 1 => "Status / Comment") )->setActiveGroup(1);
		$this->va_input->setJustView();

		
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value['client_code'], "msg" => @$msg['client_code'], "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "ordernumber", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value['order_number'], "msg" => @$msg['order_number'], "disabled"=>"disabled") );
		$this->va_input->addInput( array("name" => "customer_name", "value" => @$value['customer_name'], "msg" => @$msg['customer_name'], "label" => "Customer Name", "help" => "Customer Name", "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "email", "value" => @$value['email'], "msg" => @$msg['email'], "label" => "Email Address", "help" => "Customer Email", "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "phone_number", "value" => @$value['phone_number'], "msg" => @$msg['phone_number'], "label" => "Customer Phone", "help" => "Customer Phone", "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "amount", "value" => number_format(@$value['amount'], 2), "msg" => @$msg['amount'], "label" => "Amount", "help" => "Amount", "disabled"=>"disabled"));
		$this->va_input->addTextarea( array("name" => "shipping_address","placeholder" => "Shipping Addres","value" => @$value['shipping_address'], "msg" => @$msg['shipping_address'], "label" => "Shipping Address", "help" => "Shipping Address","disabled"=>"disabled"));
		$this->va_input->addCustomField( array("name" =>"items", "placeholder" => "Items", "label" => "Items", "value" => @$value['items'], "msg" => @$msg['items'], "view"=>"form/customItemsCod"));		
		$this->va_input->addInput( array("name" => "updated_at", "value" => @$value['updated_at'], "msg" => @$msg['updated_at'], "label" => "Updated At", "help" => "Updated At", "disabled"=>"disabled"));
		$this->va_input->addInput( array("name" => "created_at", "value" => @$value['created_at'], "msg" => @$msg['created_at'], "label" => "Created At", "help" => "Created At", "disabled"=>"disabled"));
		$this->va_input->commitForm(0);
		
		$this->va_input->addHidden( array("name" => "method", "value" => "comment") );
		$this->va_input->addSelect( array("name" => "status", "label" => "Status *", "list" => array("0" => "New Request", "1" => "Approve","2"=>"Cancel"), "value" => @$value['status'], "msg" => @$msg['status']));	
		$this->va_input->addTextarea( array("name" => "comment", "value" => '', "msg" => @$msg['note'], "label" => "Comment *", "help" => "Comment") );
		$this->va_input->addCustomField( array("name"=>"","value" =>'submit', "view"=>"form/customSubmit"));		
		$this->va_input->commitForm(1);
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
		

	}
	
}
?>