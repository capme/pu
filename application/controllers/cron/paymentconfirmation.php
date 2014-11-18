<?php 
/**
 * 
 * @property Client_m $client_m
 * @property Mageapi $mageapi
 * @property Returnorder_m $returnorder_m
 * @property Paymentconfirmation_m $paymentconfirmation_m
 *
 */
class Paymentconfirmation extends CI_Controller {
	
	public function fetch() {
		$this->load->model( array("client_m", "paymentconfirmation_m"));
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
				
			if( $this->mageapi->initSoap($config) ) {
				$confirmations = $this->mageapi->getUnexportedConfirmations();
				if(!empty($confirmations)) {
					$addedIds = $this->paymentconfirmation_m->add($client, $confirmations);
					
					if(!empty($addedIds)) {
						$this->mageapi->setConfirmationsAsExported($addedIds);
					}
				}
			}
		}
	}
}
?>