<?php
class Inbound_threepl {
	const SOAP_ACTION = "http://www.JOI.com/schemas/ViaSub.WMS/";
	const API_ENDPOINT = "https://secure-wms.com/webserviceexternal/contracts.asmx";
	const API_CREATE_ITEMS = "CreateItems";
	const API_GET_ITEMS = "ReportStockStatus";
	
	public $config;
	
	function __construct($config = array()) {
		require dirname(__FILE__).'/Config.php';
		$this->config = new Config();
		
		return $this;
	}

	public function setConfig($items = array()) {
		if(!is_array($items)) {
			$items = array($items);
		}
		//print_r($items);
		foreach($items as $key => $item) {
			$this->config->set($key, $item);
		}
	}

	public function getConfig($key="", $default="") {
		return $this->config->get($key, $default);
	}
	
	private function _sendRequest($soapRequest, $method) {
		$header = array(
			"Content-type: text/xml;charset=\"utf-8\"",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"SOAPAction: \"".self::SOAP_ACTION.$method."\"",
			"Content-length: ".strlen($soapRequest),
		);

		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, self::API_ENDPOINT );
		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 1000);
		curl_setopt($soap_do, CURLOPT_TIMEOUT,        1000);
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soapRequest);
		curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
		
		$response = curl_exec($soap_do);
		if($response === false) {
			$err = 'Curl error: ' . curl_error($soap_do);
			log_message('debug', '[3PL] errpr while send '.$method.' error msg: '.$err);
		}
		
		curl_close($soap_do);
		return $response;
	}

	/**
	 * get 3pl's items for each client
	 * @return array list items
	 **/
	 public function getItems(){
	 	$soapRequest = "
			<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:vias=\"http://www.JOI.com/schemas/ViaSub.WMS/\">
			   <soapenv:Header/>
			   <soapenv:Body>
			      <vias:userLoginData>
			         <vias:ThreePLID>".$this->config->get("apiid")."</vias:ThreePLID>
			         <!--Optional:-->
			         <vias:Login>".$this->config->get("username")."</vias:Login>
			         <!--Optional:-->
			         <vias:Password>".$this->config->get("password")."</vias:Password>
			      </vias:userLoginData>
			   </soapenv:Body>
			</soapenv:Envelope>	 	
	 	";
		
		$result = $this->_sendRequest($soapRequest, self::API_GET_ITEMS);
		$return = array();
		if($result !== false) {
			$result = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
			log_message("debug", $result);
			try{
				$xml = new SimpleXMLElement($result);
				if($xml->soapBody->string){
					$return = (string) $xml->soapBody->string;
					$return = "<?xml version='1.0' encoding='UTF-8'?>".$return;
					$xml = simplexml_load_string($return) or die("Error: Cannot create object");
					$return = (array)$xml;
					$items = array();
					$lup = 0;
					foreach($return['Q'] as $itemReturn){
						$arrItemReturn = (array)$itemReturn;
						$items[$lup]['SKU'] = $arrItemReturn['SKU'];
						$items[$lup]['I_DESCRIPTION'] = $arrItemReturn['I_DESCRIPTION'];
						$lup++;
					}
					return $items;
				}else{
					return (string) $xml->soapBody->soapFault->faultstring;
				}
				
			} catch(Exception $e) {
				log_message("error", "{3PL} ".$e->__toString());
				log_message("error", "{3PL} something wrong when call " . self::API_GET_ITEMS ." action.");
				log_message("error", "{3PL} config values: " . serialize($this->config->getAll()));
				return "{3PL} something wrong when call " . self::API_GET_ITEMS ." action. See the log file.";
			}				
		}
		
	 }

	/**
	 * create 3pl's items for each client
	 * @param array $ItemsToCreate
	 * @return array created items
	 */
	public function createItems($datas){
		$loginData = "
	      <vias:extLoginData>
	         <!--Optional:-->
	         <vias:ThreePLKey>".$this->config->get("apikey")."</vias:ThreePLKey>
	         <!--Optional:-->
	         <vias:Login>".$this->config->get("username")."</vias:Login>
	         <!--Optional:-->
	         <vias:Password>".$this->config->get("password")."</vias:Password>
	         <vias:FacilityID>".$this->config->get("apiid")."</vias:FacilityID>
	      </vias:extLoginData>
		";
		$header = "
			<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:vias=\"http://www.JOI.com/schemas/ViaSub.WMS/\">
			   <soapenv:Header/>
			   <soapenv:Body>		
		";
		$footer = "
				<vias:warnings>?</vias:warnings>
			   </soapenv:Body>
			</soapenv:Envelope>		
		";
		
		$soapRequest = $header.$loginData.$datas.$footer;
		$result = $this->_sendRequest($soapRequest, self::API_CREATE_ITEMS);
		$return = array();
		if($result !== false) {
			$result = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
			log_message("debug", $result);
			try{
				$xml = new SimpleXMLElement($result);
				//var_dump($xml->soapBody->CreateItemsResult->ItemID);
				if($xml->soapBody->CreateItemsResult->ItemID){
					foreach($xml->soapBody->CreateItemsResult->ItemID as $item){
						$return[] = (string)$item;
					}
				}else{
                    echo "<pre>";
                    echo $xml->soapBody->soapFault->faultstring[0];
                    echo "</pre>";
					return false;
				}
				return $return;
			} catch(Exception $e) {
				log_message("error", "{3PL} ".$e->__toString());
				log_message("error", "{3PL} something wrong when call " . self::API_CREATE_ITEMS ." action.");
				log_message("error", "{3PL} config values: " . serialize($this->config->getAll()));
				return false;
			}				
		}
		 
	}
}
?>