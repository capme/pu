<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Testform extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		
		$this->_mageCreateItem();		

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
	
}
?>