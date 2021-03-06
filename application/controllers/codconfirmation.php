<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Mageapi $mageapi
 * @property Codconfirmation_m $codconfirmation_m
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
		->setHeadingTitle(array("#", "Created Date", "Client Name","Order Number","Cust. Name", "Amount","Phone / Email","Status","Status AWB"))
		->setHeadingWidth(array(2,2,2,2,2,2,2,2,2));
		
		$this->va_list->setInputFilter(3, array("name" => $this->codconfirmation_m->filters['order_number']))
			->setDropdownFilter(2, array("name" => $this->codconfirmation_m->filters[$this->codconfirmation_m->table.'.client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->va_list->setInputFilter(4, array("name" => $this->codconfirmation_m->filters['customer_name']));
        $this->va_list->setInputFilter(5, array("name" => $this->codconfirmation_m->filters[$this->codconfirmation_m->table.'.amount']));
        $this->va_list->setDropdownFilter(7, array("name" => $this->codconfirmation_m->filters[$this->codconfirmation_m->table.'.status'], "option" => $this->getStatus()));
        $this->va_list->setDropdownFilter(8, array("name" => $this->codconfirmation_m->filters[$this->codconfirmation_m->tableAwb.'.status'], "option" => $this->getStatusAwb()));
        $this->va_list->setDateFilter(1, array("name"=>$this->codconfirmation_m->filters['created_at']));

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
		$this->va_input->addTextarea( array("name" => "approve", "placeholder" => "Note", "help" => "Note", "label" => "Note", "value" => '', "msg" => @$msg['note']) );
		
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
        $this->va_input->addHidden( array("name" => "order_number", "value" => $value['order_number']) );
		$this->va_input->addTextarea( array("name" => "cancel", "placeholder" => "Note", "help" => "Note", "label" => "Note", "value" => '', "msg" => @$msg['note']) );
		
		$this->data['script'] = $this->load->view("script/codconfirmation_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save() 
	{
		$this->load->library("mageapi");
        $this->load->model("autocancel_m");
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
                $this->mageapi->sendNotifBrand($data['client_id'], $data['order_number'], "cod");
			}
			
			$result = $this->codconfirmation_m->Approve($post);
            $this->autocancel_m->remove($data['order_number']);

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
            $this->autocancel_m->remove($data['order_number']);
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

	}
		
	private function getStatus() {
		return array(-1=>"",0=>"New Request",1 => "Approve",2 => "Cancel");
	}

    private function getStatusAwb() {
        return array(-1=>"",0=>"New Request",1 => "Printed");
    }
}
