<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *   @property Va_list $va_list
**/
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
		->setHeadingTitle(array("#", "Created Date", "Client Name","Order Number","Name","Origin Bank","Amount","Status","Status AWB"))
		->setHeadingWidth(array(2,2,2,2,2,3,2,3,4));

		$this->va_list->setInputFilter(3, array("name" => $this->paymentconfirmation_m->filters['order_number']))
			->setDropdownFilter(2, array("name" => $this->paymentconfirmation_m->filters[$this->paymentconfirmation_m->table.'.client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
		$this->va_list->setDropdownFilter(7, array("name" => $this->paymentconfirmation_m->filters[$this->paymentconfirmation_m->table.'.status'], "option" => $this->getStatus()));
        $this->va_list->setDropdownFilter(8, array("name" => $this->paymentconfirmation_m->filters[$this->paymentconfirmation_m->tableAwb.'.status'], "option" => $this->getStatusAwb()));
        $this->va_list->setInputFilter(6, array("name" => $this->paymentconfirmation_m->filters[$this->paymentconfirmation_m->table.'.amount']));
        $this->va_list->setInputFilter(4, array("name" => $this->paymentconfirmation_m->filters['name']));
		
        $this->va_list->setDateFilter(1, array("name"=>$this->paymentconfirmation_m->filters['created_at']));

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

        $this->data['content'] = "group_form_v.php";
		$this->data['pageTitle'] = "Payment Confirmation";
		$this->data['breadcrumb'] = array("Payment Confirmation"=> "", "View Payment Confirmation" => "");
		$this->data['formTitle'] = "View Payment Confirmation";

		$this->load->library("va_input", array("group" => "returnorder"));
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
        $address= $value[0]['address'].", ".$value[0]['city']." - ".$value[0]['province']." ".$value[0]['zipcode'];

		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value[0]['client_code'], "msg" => @$msg['client_code'], "disabled"=>"disabled") );
		$this->va_input->addInput( array("name" => "sku", "placeholder" => "Order Number", "help" => "Order Number", "label" => "Order Number", "value" => @$value[0]['order_number'], "msg" => @$msg['order_number'], "disabled"=>"disabled") );
		$this->va_input->addInput( array("name" => "status", "value" => $this->getStatus()[@$value[0]['status']], "msg" => @$msg['status'], "label" => "Status", "help" => "Order Status", "disabled"=>"disabled") );
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
	
	private function getStatus() {
		return array(-1=>"",0=>"New Request",1 => "Approve",2 => "Cancel");
	}

    private function getStatusAwb() {
        return array(-1=>"",0=>"New Request",1 => "Printed");
    }

	public function approve ($id)
	{
		$this->paymentconfirmation_m->Approve($id);
		$this->load->model(array("client_m","autocancel_m"));
		$this->load->library("mageapi");
		$data = $this->paymentconfirmation_m->getConfirmationById($id)->row_array();
		$client = $this->client_m->getClientById($data['client_id'])->row_array();

		$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
		);
		
		if( $this->mageapi->initSoap($config) ) {
			$this->mageapi->processOrder($data['order_number']);
            $this->mageapi->sendNotifBrand($data['client_id'], $data['order_number'], "banktransfer");
            $this->autocancel_m->remove($data['order_number']);
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
        $this->va_input->addHidden( array("name" => "client_id", "value" => $value['client_id']) );
        $this->va_input->addHidden( array("name" => "order_number", "value" => $value['order_number']) );
		$this->va_input->addTextarea( array("name" => "reason", "placeholder" => "Cancel reason", "help" => "Cancel reason", "label" => "Cancel Reason", "value" => @$value['reason'], "msg" => @$msg['reason']) );
		
		$this->data['script'] = $this->load->view("script/paymentconfirmation_add", array(), true);
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
            $this->autocancel_m->remove($post['order_number']);
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
