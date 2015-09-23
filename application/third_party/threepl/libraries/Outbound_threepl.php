<?php
class Outbound_threepl {
    const SOAP_ACTION = "http://www.JOI.com/schemas/ViaSub.WMS/";
    const API_ENDPOINT = "https://secure-wms.com/webserviceexternal/contracts.asmx";
    const API_CREATE_ITEMS = "CreateItems";
    const API_GET_ITEMS = "ReportStockStatus";
    const API_UPDATE_ORDERS = "UpdateOrders";

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

    public function confirmOrders($whId, $awb){
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

        $datas = "
      <vias:updateOrders>
         <!--Zero or more repetitions:-->
         <vias:UpdateOrder>
            <vias:WarehouseTransactionID>".$whId."</vias:WarehouseTransactionID>
            <!--Optional:-->
            <vias:BillOfLading>".$awb."</vias:BillOfLading>
            <!--Optional:-->
            <vias:TrackingNumber>".$awb."</vias:TrackingNumber>
            <vias:ConfirmationDate>".date("Y-m-d")."</vias:ConfirmationDate>
         </vias:UpdateOrder>
      </vias:updateOrders>
        ";

        $soapRequest = $header.$loginData.$datas.$footer;
        $result = $this->_sendRequest($soapRequest, self::API_UPDATE_ORDERS);
        if($result !== false) {
            $result = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
            log_message("debug", $result);
            try{
                $xml = new SimpleXMLElement($result);
                //var_dump($xml->soapBody->CreateItemsResult->ItemID);
                if($xml->soapBody->Int32 == "1"){
                    return true;
                }else{
                    echo "<pre>";
                    echo $xml->soapBody->soapFault->faultstring[0];
                    echo "</pre>";
                    return false;
                }
                return $return;
            } catch(Exception $e) {
                log_message("error", "{3PL} ".$e->__toString());
                log_message("error", "{3PL} something wrong when call " . self::API_UPDATE_ORDERS ." action.");
                log_message("error", "{3PL} config values: " . serialize($this->config->getAll()));
                return false;
            }
        }
    }
}
?>