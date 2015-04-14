<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Testform extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		
		//create items in magento
		$this->_mageCreateItem();
		echo "<br><br>";
		//create items in 3PL
		$this->_3PLCreateItem();			

	}

	private function _mageCreateItem(){
		$this->load->library("Mageapi");
		$this->load->model( array("client_m", "inbounddocument_m") );
		
			$config = array(
				"auth" => "dart:Vela123!",
				"url" => "http://leecooper.localhost/api/soap/?wsdl"
			);
			
		$client = $_GET['client'];
		$doc = $_GET['doc'];
		
		$param = $this->inbounddocument_m->getParamInboundMage($client, $doc);		
							
		if( $this->mageapi->initSoap($config) ) {
				$return = $this->mageapi->inboundCreateItem($param);
				if(is_array($return)){
					print_r($return);
				}else{
					echo $return;
				}
		}
		
	}
	
	private function _3PLCreateItem(){
		$this->load->add_package_path(APPPATH."third_party/threepl/");
		$this->load->library("inbound_threepl", null, "inbound_threepl");
		$this->load->model( array("client_m", "inbounddocument_m") );
		
		$client = $_GET['client'];
		$doc = $_GET['doc'];
		
		$c['threepluser'] = "dev_test";
		$c['threeplpass'] = "Vela123!";
		
		$this->inbound_threepl->setConfig( array("username" => $c['threepluser'], "password" => $c['threeplpass']) );
		$returnMsgItem = $this->inbounddocument_m->getParamInbound3PL($client, $doc);
		$return = $this->inbound_threepl->createItems($returnMsgItem);
				if(is_array($return)){
					print_r($return);
				}else{
					echo $return;
				}
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
	
	private function _creditcard(){
		$this->load->library("Mageapi");
			$config = array(
				"auth" => "dart:Vela123!",
				"url" => "http://leecooper.localhost/api/soap/?wsdl"
			);
			
			if( $this->mageapi->initSoap($config) ) {
				echo "test";
				$return = $this->mageapi->getCreditCardOrder('2015-01-01', '2015-12-31');
				if(is_array($return)){
					print_r($return);
				}else{
					echo $return;
				}
			}
	}
	
	
}
?>