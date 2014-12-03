<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Awbprinting_m $awbprinting_m
 * @property Va_list $va_list
 *
 */
class Awbprinting extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("users_m", "client_m", "awbprinting_m") );
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "AWB Printing";
		$this->data['breadcrumb'] = array("AWB Printing" => "");
		
		$this->awbprinting_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->setListName("AWB Listing")->setAddLabel("Upload new AWB")
			->setMassAction(array("0" => "Print JNE Format", "1" => "Print NEX Format"))
			->setHeadingTitle(array("Record #", "Client Name","Order Number","Name","Origin Bank","Amount","Status","Transfer Date","Receipt","Updated By"))
			->setHeadingWidth(array(2, 2,2,3,2,3,4,2,2,4));
		
		$this->va_list->setInputFilter(2, array("name" => $this->awbprinting_m->filters['order_number']))
			->setDropdownFilter(1, array("name" => $this->awbprinting_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
		$this->va_list->setDropdownFilter(6, array("name" => $this->awbprinting_m->filters['status'], "option" => $this->getStatus()));
		
		$this->data['script'] = $this->load->view("script/paymentconfirmation_list", array("ajaxSource" => site_url("paymentconfirmation/paymentConfirmationList")), true);	
		$this->load->view("template", $this->data);
	}
	
	private function getStatus() {
		return array(-1 => "", 0 => "New Request", 1 => "Printed");
	}
}
?>