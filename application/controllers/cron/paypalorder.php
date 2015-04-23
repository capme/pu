<?php 
/**
 * 
 * @property Mageapi $mageapi
 *
 */
class Paypalorder extends CI_Controller {
	
	public function fetch($code = '') {		
		$this->load->model("paypalorder_m");
		$this->load->library("mageapi");
						
			$config = array(
					"auth" => 'dart:Vela123!',
					"url" => 'http://ekretek.dev/api/soap/?wsdl'
			);

			if( $this->mageapi->initSoap($config) ) {
				$clientId = 10;
				$dateFrom = date("Y-m-d", strtotime("-10 days"));
				$dateTo = date("Y-m-d");
				$orders = $this->mageapi->getPaypalOrder($dateFrom, $dateTo);
				
				if(is_array($orders)){
					$dataCreditCard = array();
                    $histories = array();
					if(!empty($orders)){
						foreach($orders as $order){
                            $_status = array_search($order['status'], $this->paypalorder_m->getOrderStatusmap());
                            $_order = array(
                                'client_id' => $clientId, 'order_number' => $order['increment_id'], 'name' => $order['customer_name'], 'shipping_address' => $order['shipping_address'],
                                'items' => json_encode($order['items']), 'email' => $order['email'], 'amount' => $order['amount'], 'status' => $_status,
                                );
                            $dataCreditCard[$order['increment_id']] = $_order;
                            $histories[$order['increment_id']] = $order['histories'];
						}

						$return = $this->paypalorder_m->savePaypalOrder($clientId, $dataCreditCard, $histories);
						echo "Payapal order for client Ekretek between ".$dateFrom." and ".$dateTo." fetched<br>";
					}else{
						echo "No Payapal order for client Ekretek between ".$dateFrom." and ".$dateTo."<br>";
					}
				}else{
					echo "Something wrong with get Paypal order. See the log file";
				}
			}
	}
}
?>