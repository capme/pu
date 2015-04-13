<?php 
/**
 * 
 * @property Mageapi $mageapi
 *
 */
class Creditcardorder extends CI_Controller {
	
	public function fetch() {
		$this->load->model("client_m");
		$this->load->model("creditcardorder_m");
		$this->load->library("mageapi");
		
		$clients = $this->client_m->getClients();
		
		foreach($clients as $client) {
			if(!$client['mage_auth'] && !$client['mage_wsdl']) {
				continue;
			}
				
			$config = array(
					"auth" => $client['mage_auth'],
					"url" => $client['mage_wsdl']
			);

			//debug
			$config = array(
				"auth" => "dart:Vela123!",
				"url" => "http://leecooper.localhost/api/soap/?wsdl"
			);
			if( $this->mageapi->initSoap($config) ) {
				$clientId = $client['id'];
				//debug
				$dateFrom = '2015-01-01';
				$dateTo = date("Y-m-d");
				$return = $this->mageapi->getCreditCardOrder($dateFrom, $dateTo);
				if(is_array($return)){
					$dataCreditCard = array();
					if(!empty($return)){
						foreach($return as $itemReturn){
							$dataCreditCard[$itemReturn['entity_id']]['comment'] = $itemReturn['comment'];
							$dataCreditCard[$itemReturn['entity_id']]['status'] = $itemReturn['status'];
							$dataCreditCard[$itemReturn['entity_id']]['type'] = 3;
							$dataCreditCard[$itemReturn['entity_id']]['created_by'] = 2;
							$dataCreditCard[$itemReturn['entity_id']]['amount'] = $itemReturn['amount_ordered'];
							$dataCreditCard[$itemReturn['entity_id']]['customer_email'] = $itemReturn['customer_email'];
							$dataCreditCard[$itemReturn['entity_id']]['customer_name'] = $itemReturn['customer_firstname']." ".$itemReturn['customer_middlename']." ".$itemReturn['customer_lastname'];
							$dataCreditCard[$itemReturn['entity_id']]['address'] = $itemReturn['street']." ".$itemReturn['city']." ".$itemReturn['region']." ".$itemReturn['postcode'];
							$dataCreditCard[$itemReturn['entity_id']]['items'] = $itemReturn['name']." ".$itemReturn['sku'];
							$dataCreditCard[$itemReturn['entity_id']]['amount'] = $itemReturn['amount_ordered'];
						}
						$return = $this->creditcardorder_m->saveCreditCardOrder($clientId, $dataCreditCard);
						echo "No credit card order for client ".$client['client_code']." between ".$dateFrom." and ".$dateTo."<br>";
					}else{
						echo "No credit card order for client ".$client['client_code']." between ".$dateFrom." and ".$dateTo."<br>";
					}
				}else{
					echo "Something wrong with get credit card order. See the log file";
				}
			}
						
			//debug
			break;
		}
	}
}
?>