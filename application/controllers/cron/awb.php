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
	
	public function fetch() {
		$this->load->library("threepl_lib");
		$this->load->model( array("awbprinting_m", "client_m") );
		
		$param['all'] = "1";
		$param['order'] = "creation_date";
		$param['order_dir'] = "desc";
		
		$param['creation_date_from'] = date('Y-m-d 00-00-00',strtotime("-1 days"));
		$param['creation_date_to'] = date('Y-m-d 00-00-00',strtotime("+1 days"));
		
		$this->load->library("threepl_lib");
		
		$records = (array) $this->threepl_lib->getOrderByDate( $param );
		$clientOrder = $this->awbprinting_m->addIgnore($records);
		print_r(array_keys($clientOrder));
		if(!empty($clientOrder)) {
			foreach($clientOrder as $cId => $orders) {
				// get amount order
                $command = "cron/awb getAmountOrder/".$cId;
                execProcess($command);

				//echo $cId;
				$this->_getOrderItems($cId, $orders);
			}
		}
	}
	
	protected function _getOrderItems($clientId, $orders) {
		$orders = $this->awbprinting_m->getOrderNoItems($clientId);
		if(empty($orders)) {
			return;
		}
		if(empty($orders)) {
			log_message("debug", self::TAG . " order without items found");
		}

		$this->load->library("mageapi");
		$client = $this->client_m->getClientById($clientId);
		if(!$client->num_rows()) {
			log_message("debug", self::TAG . " Client data (".$clientId.") not found");
		}
		
		$client = $client->row_array();
		
		if(!$client['mage_auth'] && !$client['mage_wsdl']) {
			log_message("debug", self::TAG . " Client doesn't had mage detail");
		}
			
		$config = array(
				"auth" => $client['mage_auth'],
				"url" => $client['mage_wsdl']
		);
		
		if( $this->mageapi->initSoap($config) ) {
			$mageOrders = $this->mageapi->getOrderItems($orders);
			$this->awbprinting_m->setOrderItems($mageOrders['info']);
			
			$this->awbprinting_m->setAsFetched($clientId, $mageOrders['info']);
		}
	}
}
?>