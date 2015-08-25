<?php
class Threepl_lib {
	private $_dartkey = "";
	private $_dartUrl = "";
	private $_carrier = array(
		'F71B22D2-F3A3-4C38-A702-E9071BFEC6D5' => 'DPEX',
		'14DAC981-651A-4DB5-8869-331D4DEDC664' => 'DROP',
		'8F05FA7C-1DB4-4D92-BE2E-07BC816B3382' => 'FEDEX',
		'F6EE9ABF-2CC0-47E4-BCBE-93C90CDEB288' => 'JNE',
		'2EBEB3B3-ACE8-43AB-A8D6-E478B09D80B3' => 'KIRIM',
		'0920F850-608B-4112-9EF2-2382F5598F7E' => 'REX',
		'C7357119-0894-4887-990D-07BC5130F0FD' => 'RPX',
		'00000000-0000-0000-0000-000000000000' => 'VELA',
	);
	
	const METHOD_GET_ORDERS = "get_orders";
	const METHOD_GET_ORDER = "get_order";
	const METHOD_SET_ORDERS = "set_orders";
	const PARAM_DART_KEY = "dart_key";
	const METHOD_SET_DISPATCH = "set_dispatch";
	const METHOD_SET_INVALID = "set_invalid";
	const METHOD_SET_PROCESS = "set_process";
	const METHOD_UPDATE_TRACKING = "update_tracking";
	const METHOD_GET_ORDER_BY_DATE = "get_order_by_date";
    const METHOD_GET_ACTIVE_INVENTORY = "get_active_inventory";
	const METHOD_GET_CAMPIAIGN="receiveCampaign";
	
	function __construct() {
		$config = config_item("dart");
		$this->_dartkey = $config["dart_key"];
		$this->_dartUrl = $config["dart_url"];
	}
	
	public function getOrders($filters, $param) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_GET_ORDERS;
		$postData = array_merge($filters, $param);
		$response = json_decode($this->postData($url, $postData));
		

		$orders = array();
		$refNumbers = array();
		
		if( !isset($param['count']) ) {
			foreach($response->data as $res) {
				if(in_array($res->reference_num, $refNumbers)){continue;}

				$refNumbers[] = $res->reference_num;
				if($res->delivered_date ){
					$fkStatusId = 4;
				} else if(isset($res->ship_method) && $res->ship_method == 'INVALID') {
					$fkStatusId = 5;					
				} else if($res->dispatched_date) {
					$fkStatusId = 3;
				} else {
					$fkStatusId = 2;
				}
					
				$action_buttons = "";
					
				if( $fkStatusId == '2' ) { // only processed order allowed to be dispatched
					$action_buttons .= '<div class="action">
				<a href="'.site_url('admin/orders/dispatch?orderid=' . $res->reference_num).'" class="dispatch_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" role="button">
						<span class="ui-button-icon-primary ui-icon ui-icon-image D0fbcf45e"></span><span class="ui-button-text">&nbsp;Dispatch</span>
					</a>';
				}
					
				$action_buttons .= '<a href="'.site_url('admin/orders/view?orderid=' . $res->reference_num).'" class="view_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" role="button">
				<span class="ui-button-icon-primary ui-icon ui-icon-document"></span>
				<span class="ui-button-text">&nbsp;View</span>
			</a>
			
							<a href="'.site_url('admin/orders/edit/' . $res->reference_num).'" class="edit_button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" role="button">
				<span class="ui-button-icon-primary ui-icon ui-icon-pencil"></span>
				<span class="ui-button-text">&nbsp;Edit</span>
			</a></div>';
				if($res->ship_to_city) {
					if(strstr($res->ship_to_city, ",") !== false) {
						list($town, $region) = explode(",", $res->ship_to_city);
					} else {
						$town = $res->ship_to_city;
						$region = "";
					}
				} else {
					$town = "";
					$region = "";
				}
				
					
					
				$orders[] = array(
						"pkOrderID" => $res->reference_num,
						"cFullName" => $res->ship_to_name,
						"cEmailAddress" => $res->ship_to_email,
						"cShippingAddress" => $res->ship_to_address1 . "<br />" . $res->ship_to_address2,
						"Address1" => $res->ship_to_address1,
						"Address2" => $res->ship_to_address2,
						"Address3" => "",
						"cPostCode" => $res->ship_to_zip,
						"Company" => $res->ship_to_company_name,
						"BuyerPhoneNumber" => $res->ship_to_phone,
						"dReceievedDate" => $res->creation_date ? date("Y-m-d H:i:s", strtotime($res->creation_date)) : "",
						"dProcessedOn" => $res->process_date ? date("Y-m-d H:i:s", strtotime($res->process_date)) : "",
						"dDispatchedOn" => $res->dispatched_date ? date("Y-m-d H:i:s", strtotime($res->dispatched_date)) : "",
						"dDeliveredOn" => $res->delivered_date ? date("Y-m-d H:i:s", strtotime($res->delivered_date)) : "",
						"PostalServiceName" => $res->load_number,
						"PostalServiceTag" => $res->load_number,
						"Source" => $res->source,
						"ReferenceNum" => $res->reference_num,
						"CreatedDate" => date("d/m/Y H:i", strtotime($res->creation_date)),
						"PostalTrackingNumber" => $res->tracking_number,
						"StatusText" => ($fkStatusId == 2 ? "PROCESSED" : ($fkStatusId == 4 ? "DELIVERED" : ($fkStatusId==5 ? "INVALID" : "DISPATCHED" ))),
						"fkStatusId" => $fkStatusId,
						"Town" => $town,
						"Region" => $region,
						"nOrderId" => $res->warehouse_transaction_id,
						"Recipient" => 	$res->received_by
				);
			}
		} else {
			$orders = $response->data;
		}
		
		
		return $orders;
	}
	
	public function setOrders($orderId, $data) {
		
	}
	
	public function setDispatchOrder($orderId, $dispatchedDate) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_SET_DISPATCH;
		$postData = array("data" => serialize(array("reference_num" => $orderId, "dispatched_date" => $dispatchedDate)));
		$response = json_decode($this->postData($url, $postData));
		
		if($response->status == 1) {
			return;
		} else {
			return $response->data;
		}
	}
	
	public function setInvalidOrder($orderId) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_SET_INVALID;
		$postData = array("data" => serialize(array("reference_num" => $orderId)));
		$response = json_decode($this->postData($url, $postData));

		if($response->status == 1) {
			return;
		} else {
			return $response->data;
		}
	}

	public function setProcessedOrder($orderId) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_SET_PROCESS;
		$postData = array("data" => serialize(array("reference_num" => $orderId)));
		$response = json_decode($this->postData($url, $postData));
		
		if($response->status == 1) {
			return;
		} else {
			return $response->data;
		}
	}
	
	public function getOrderByRefnumber($refNumber, $awb = false) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_GET_ORDER;
		$postData = array("reference_num" => $refNumber);
		if($awb) {
			$postData['tracking_number'] = $awb;
		}
		
		$response = json_decode($this->postData($url, $postData));
		$order = array();
		
		if($response->status == 1 && !empty($response->data)) {
			$res = $response->data;
			
			if($res->delivered_date){
				$fkStatusId = 4;
			} else if(isset($res->ship_method) && $res->ship_method == 'INVALID') {
				$fkStatusId = 5;
			} else if($res->dispatched_date) {
				$fkStatusId = 3;
			} else {
				$fkStatusId = 2;
			}
			
			if(strstr($res->ship_to_city, ",") !== false)
				list($town, $region) = explode(",", $res->ship_to_city);
			else {$town = $res->ship_to_city; $region = "";}
			
			$order = array(
				// 'StatusText' => ($fkStatusId == 2 ? "PROCESSED" : ($fkStatusId == 4 ? "DELIVERED" : "DISPATCHED")),
				'StatusText' => ($fkStatusId == 2 ? "PROCESSED" : ($fkStatusId == 4 ? "DELIVERED" : ($fkStatusId == 5 ? "INVALID" : "DISPATCHED"))),
				'Source' => $res->source,
				'ReferenceNum' => $res->reference_num,
				'cFullName' => $res->ship_to_name,
				'BuyerPhoneNumber' => $res->ship_to_phone,
				'Address1' => $res->ship_to_address1,
				'Address2' => $res->ship_to_address2,
				'Town' => $town,
				'Region' => $region,
				'dReceievedDate' => date("m/d/Y H:i", strtotime($res->creation_date)),
				'dProcessedOn' => date("m/d/Y H:i", strtotime($res->creation_date)),
				'dDispatchedOn' => ($fkStatusId == 3 ? date("m/d/Y H:i", strtotime($res->process_date)) : ""),
				'dDeliveredOn' => ($fkStatusId == 4 ? date("m/d/Y H:i", strtotime($res->delivered_date)) : ""),
				'fkPostalServiceId' => array_search($res->load_number, $this->_carrier),
				'PostalTrackingNumber' => $res->tracking_number,
				"fkStatusId" => $fkStatusId
			);
		}
		
		return $order;
	}
	
	public function getOrderByCarrier($carrier, $filter) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_GET_ORDERS;
		$postData = array("load_number" => $carrier);
		foreach($filter as $key => $val){
			$postData[$key] = $val;
		}
		/*if(isset($filter['status'])) {
			$postData['status'] = $filter['status'];
		}*/
		
		$response = json_decode($this->postData($url, $postData));
// 		$response = $this->postData($url, $postData);
// 		print_r($response);
		if($response->status == 1 && !empty($response->data)) {
			return $response->data;
		} else {
			return array();
		}
	}
	
	public function updateOrderAsDelivered($orders = array()) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_SET_ORDERS;
		$postData = array();
		foreach($orders as $order) {
			$postData[$order['reference_num']] = array("delivered_date" => $order['delivered_date'], "received_by" => $order['received_by']);
		}
		
		$response = json_decode($this->postData($url, array("data" => serialize($postData))));
	}
	
	public function updateTrackingInfo($orderId) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_UPDATE_TRACKING;
		$postData = array("reference_num" => $orderId);
		
		$response = json_decode($this->postData($url, array("data" => serialize($postData))));
	}
	
	public function getOrderByDate($postData) {
		$url = trim($this->_dartUrl, "/") . "/" . self::METHOD_GET_ORDER_BY_DATE;
		$response = json_decode($this->postData($url, $postData));
		
		if($response->status == 1 && !empty($response->data)) {
			return $response->data;
		} else {
			return array();
		}
		
	}

    public function getActiveInventory($clientName) {
        $url = trim($this->_dartUrl, "/") . "/" . self::METHOD_GET_ACTIVE_INVENTORY;
        $postData = array('filters' => json_encode(array('filter' => array('source' => $clientName), 'option' => array('unique' => 1))));
        $response = json_decode($this->postData($url, $postData));

        if($response->status == 1 && !empty($response->data)) {
            return $response->data;
        } else {
            return array();
        }
    }

    public function sendCampaign($data){
        $url = trim($this->_dartUrl, "/") . "/" . self::METHOD_GET_CAMPIAIGN;
        $postData = array('data' => json_encode($data));
        $response = json_decode($this->postData($url, $postData));
        return $response;
    }
	
	private function postData($url, $data) {
		$data[self::PARAM_DART_KEY] = $this->_dartkey;
		$dataString = array();
		foreach($data as $key=>$value) { $dataString[] = $key.'='.$value; }
		$ch = curl_init();
		
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($data));
		curl_setopt($ch,CURLOPT_POSTFIELDS, implode("&", $dataString));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
    	$result = curl_exec($ch);

		curl_close($ch);

		return $result;
	}
}
?>
