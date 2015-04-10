<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Testform extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		
		$this->_page4();		

	}
	
	private function _page4(){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "test ajah form custom";
		$this->data['formTitle'] = "test ajah form custom";
		$this->data['breadcrumb'] = array("Test Form 2"=> "");
		$this->load->library("va_input", array("group" => "codconfirmation"));
		
		$this->va_input->addSelect( array("name" => "status", "label" => "Status *", "list" => array("1"=>"Processing","3" => "Receive","4"=>"Cancel"), "value" => "isi value", "msg" => "isi msg"));	
		$this->va_input->addInput( array("name" => "client_code_1", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => "value 2", "msg" => "test", "disabled"=>"disabled"));
		
		$this->va_input->setCustomLayout(TRUE)->setCustomLayoutFile("layout/custom1.php");
		$this->data['script'] = $this->load->view("script/codgroup_view", array(), true);
		$this->load->view('template', $this->data);
				
	}
	
}
?>