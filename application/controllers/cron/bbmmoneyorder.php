<?php 
/**
 * 
 * @property Mageapi $mageapi
 *
 */
class Bbmmoneyorder extends CI_Controller {
	
	public function fetch($code = '') {
		$this->load->model("client_m");
		$this->load->model("bbmmoneyorder_m");
		$this->load->library("mageapi");
		
		$clients = $this->client_m->getClients();
		$arrClientBbmMoney = array("POPPARAPLOU");

		foreach($clients as $client) {
            if(!in_array($client['client_code'], $arrClientBbmMoney)) continue;
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
				$orders = $this->mageapi->getBbmMoneyOrder($dateFrom, $dateTo);
				if(is_array($orders)){
					$dataBbmMoney = array();
                    $histories = array();
					if(!empty($orders)){
						foreach($orders as $order){
                            $_status = array_search($order['status'], $this->bbmmoneyorder_m->getOrderStatusmap());
                            $_order = array(
                                'client_id' => $clientId, 'order_number' => $order['increment_id'], 'name' => $order['customer_name'], 'shipping_address' => $order['shipping_address'],
                                'items' => json_encode($order['items']), 'email' => $order['email'], 'amount' => $order['amount'], 'status' => $_status,
                                'created_at' => $order['created_at'], 'updated_at' => $order['updated_at']
                            );
                            $dataBbmMoney[$order['increment_id']] = $_order;
                            $histories[$order['increment_id']] = $order['histories'];
						}

                        /*print_r($histories);
                        print_r($dataCreditCard);
                        die;*/
                        //print_r($histories);
                        //die();
						$return = $this->bbmmoneyorder_m->saveBbmMoneyOrder($clientId, $dataBbmMoney, $histories);
						echo "BBM Money order for client ".$client['client_code']." between ".$dateFrom." and ".$dateTo." fetched<br>";
					}else{
						echo "No BBM Money order for client ".$client['client_code']." between ".$dateFrom." and ".$dateTo."<br>";
					}
				}else{
					echo "Something wrong with get BBM Money order. See the log file";
				}
			}
						
		}
	}
}
?>
