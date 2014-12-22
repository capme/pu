<?php 
/**
 * 
 * @property Client_m $client_m
 * @property Mageapi $mageapi
 * @property Returnorder_m $returnorder_m
 * @property Awbprinting_m $awbprinting_m
 *
 */
class Awb extends CI_Controller {
	const TAG = "[AWB CRON]";
	
	public function getAmountOrder($clientId) {
		$this->load->model( array("awbprinting_m", "returnorder_m", "client_m"));
		$this->load->library("mageapi");
		
		$client = $this->client_m->getClientById($clientId);
		if(!$client->num_rows()) {
			log_message("debug", self::TAG . " Client data not found");
			die;
		}
		
		$client = $client->row_array();
		
		if(!$client['mage_auth'] && !$client['mage_wsdl']) {
			log_message("debug", self::TAG . " Client doesn't had mage detail");
			die;
		}
			
		$config = array(
			"auth" => $client['mage_auth'],
			"url" => $client['mage_wsdl']
		);
		
		print_r($config);
			
		if( $this->mageapi->initSoap($config) ) {
			$orders = $this->awbprinting_m->getOrderNoAmount($clientId);
			print_r($orders);
			if(empty($orders)) {
				log_message("debug", self::TAG . " order not found");
				die;
			}
			
			$amountOrders = $this->mageapi->getOrderAmount($orders);
			if(!empty($amountOrders)) {
				$this->awbprinting_m->updateAmount($amountOrders);
			}
		}
	}
}
?>