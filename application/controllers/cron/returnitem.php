<?php 
/**
 * 
 * @property Client_m $client_m
 * @property Mageapi $mageapi
 * @property Returnorder_m $returnorder_m
 *
 */
class Returnitem extends CI_Controller {
	
	public function fetch() {
		$this->load->model( array("client_m", "returnorder_m"));
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
				$returnItems = $this->mageapi->getUnexportedReturnItem();
				if(!empty($returnItems)) {
					$addedIds = $this->returnorder_m->add($client, $returnItems);
					
					if(!empty($addedIds)) {
						$this->mageapi->setAsExported($addedIds);
					}
				}
			}
		}
	}
}
?>