<?php 
/**
 * 
 * @property Mageapi $mageapi
 *
 */
class Creditcardorder extends CI_Controller {
	
	public function fetch($code = '') {
		$this->load->model("client_m");
		$this->load->model("creditcardorder_m");
		$this->load->library("mageapi");
		
		$clients = $this->client_m->getClients();
		
		foreach($clients as $client) {
            if($code && $code != $client['client_code']) {
                continue;
            }
			if(!$client['mage_auth'] && !$client['mage_wsdl']) {
				continue;
			}
				
			$config = array(
					"auth" => $client['mage_auth'],
					"url" => $client['mage_wsdl']
			);

			if( $this->mageapi->initSoap($config) ) {
				$clientId = $client['id'];
				$dateFrom = date("Y-m-d", strtotime("-14 days"));
				$dateTo = date("Y-m-d");
				$orders = $this->mageapi->getCreditCardOrder($dateFrom, $dateTo);
				if(is_array($orders)){
					$dataCreditCard = array();
                    $histories = array();
					if(!empty($orders)){
						foreach($orders as $order){
                            $_status = array_search($order['status'], $this->creditcardorder_m->getOrderStatusmap());
                            $_order = array(
                                'client_id' => $clientId, 'order_number' => $order['increment_id'], 'name' => $order['customer_name'], 'shipping_address' => $order['shipping_address'],
                                'items' => json_encode($order['items']), 'email' => $order['email'], 'amount' => $order['amount'], 'status' => $_status,
                                'cc_info' => json_encode($order['cc_info']), 'created_at' => $order['created_at'], 'updated_at' => $order['updated_at']
                            );
                            $dataCreditCard[$order['increment_id']] = $_order;
                            $histories[$order['increment_id']] = $order['histories'];
						}

                        /*print_r($histories);
                        print_r($dataCreditCard);
                        die;*/
						$return = $this->creditcardorder_m->saveCreditCardOrder($clientId, $dataCreditCard, $histories);
						echo "Credit card order for client ".$client['client_code']." between ".$dateFrom." and ".$dateTo." fetched<br>";
					}else{
						echo "No credit card order for client ".$client['client_code']." between ".$dateFrom." and ".$dateTo."<br>";
					}
				}else{
					echo "Something wrong with get credit card order. See the log file";
				}
			}
						
		}
	}
}
?>
