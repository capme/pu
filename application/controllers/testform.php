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
		//$this->_mageCreateItem();
		//echo "<br><br>";
		//create items in 3PL
		//$this->_3PLCreateItem();			
		$this->_creditcard();
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
	
}
?>