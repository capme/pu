<?php
class Rpx_lib
{
    const API_ENDPOINT = "http://api.rpxholding.com/wsdl/rpxwsdl.php";
    const API_ENDPOINT_SOAP = "http://api.rpxholding.com/wsdl/rpxwsdl.php?wsdl";

    private $_rpxUser = "";
    private $_rpxPassword = "";
    private $_rpxAccount = "";
    private $_params = "";
    private $_listServices = array();
    private $_helper;



    const METHOD_SEND_SHIPMENT_INFO = "sendShipmentData";
    const METHOD_SEND_PICKUP_REQ = "sendPickupRequest";

    function __construct() {
        $config = config_item("rpx");
        $this->_rpxUser = $config["rpx_user"];
        $this->_rpxPassword = $config["rpx_password"];
        $this->_rpxAccount = $config["rpx_account"];
        $this->_helper = & get_instance();
        $this->_helper->load->helper('xml');
    }

    public function getRpxAccount(){
        return $this->_rpxAccount;
    }

    private function _sendRequestSOAP($method, $param) {
        $client = new SoapClient(self::API_ENDPOINT_SOAP);
        $username = $this->_rpxUser;
        $password  = $this->_rpxPassword;
        try {
            if($method == "getService") {
                $result = $client->getService($username, $password);
            }elseif($method == "getPostalCode") {
                //param[0] -> city id
                $result = $client->getPostalCode($username, $password, $param[0]);
            }elseif($method == "getRouteOrigin") {
                //param[0] -> postal code
                $result = $client->getRouteOrigin($username, $password, $param[0]);
            }elseif($method == "getCity") {
                $result = $client->getCity($username, $password);
            }elseif($method == "getProvince") {
                $result = $client->getProvince($username, $password);
            }elseif($method == "sendPickupRequest") {
                //param[0] -> order_type
                //param[1] -> pickup_agent_id
                //param[2] -> pickup_ready_time
                //param[3] -> pickup_request_by
                //param[4] -> pickup_account_number
                //param[5] -> pickup_company_name
                //param[6] -> pickup_company_address
                //param[7] -> pickup_city
                //param[8] -> pickup_postal_code
                //param[9] -> service_type
                //param[10] -> desc_of_goods
                //param[11] -> tot_declare_value
                //param[12] -> office_closed_time
                //param[13] -> pickup_shipper_name
                //param[14] -> pickup_company_email
                //param[15] -> pickup_cellphone
                //param[16] -> pickup_phone
                //param[17] -> destin_postal_code
                //param[18] -> destin_city
                //param[19] -> destin_province
                //param[20] -> total_weight
                //param[21] -> total_package
                //param[22] -> format
                $result = $client->sendPickupRequest($username, $password, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9], $param[10], $param[11], $param[12], $param[13], $param[14], $param[15], $param[16], $param[17], $param[18], $param[19], $param[20], $param[21], $param[22]);
            }elseif($method == "sendShipmentData") {
                //param[0] -> awb
                //param[1] -> order_number
                //param[2] -> package_id
                //param[3] -> order_type
                //param[4] -> order_number
                //param[5] -> service_type_id
                //param[6] -> shipper_account
                //param[7] -> shipper_name
                //param[8] -> shipper_company
                //param[9] -> shipper_address1
                //param[10] -> shipper_address2
                //param[11] -> shipper_kelurahan
                //param[12] -> shipper_kecamatan
                //param[13] -> shipper_city
                //param[14] -> shipper_state
                //param[15] -> shipper_zip
                //param[16] -> shipper_phone
                //param[17] -> identity_no
                //param[18] -> shipper_mobile_no
                //param[19] -> shipper_email
                //param[20] -> consignee_account
                //param[21] -> consignee_name
                //param[22] -> consignee_company
                //param[23] -> consignee_address1
                //param[24] -> consignee_address2
                //param[25] -> consignee_kelurahan
                //param[26] -> consignee_kecamatan
                //param[27] -> consignee_city
                //param[28] -> consignee_state
                //param[29] -> consignee_zip
                //param[30] -> consignee_phone
                //param[31] -> consignee_mobile_no
                //param[32] -> consignee_email
                //param[33] -> desc_of_goods
                //param[34] -> tot_package
                //param[35] -> actual_weight
                //param[36] -> tot_weight
                //param[37] -> tot_declare_value
                //param[38] -> tot_dimensi
                //param[39] -> flag_mp_spec_handling
                //param[40] -> insurance
                //param[41] -> surcharge
                //param[42] -> high_value
                //param[43] -> value_docs
                //param[44] -> electronic
                //param[45] -> format
                $result = $client->sendShipmentData($username, $password, $param[0], $param[1], $param[2], $param[3], $param[4], $param[5], $param[6], $param[7], $param[8], $param[9], $param[10], $param[11], $param[12], $param[13], $param[14], $param[15], $param[16], $param[17], $param[18], $param[19], $param[20], $param[21], $param[22], $param[23], $param[24], $param[25], $param[26], $param[27], $param[28], $param[29], $param[30], $param[31], $param[32], $param[33], $param[34], $param[35], $param[36], $param[37], $param[38], $param[39], $param[40], $param[41], $param[42], $param[43], $param[44], $param[45]);
            }
            return $result;
        }
        catch ( Exception $e ) {
            return false;
        }
    }


    private function _sendRequest($method, $param) {
        libxml_use_internal_errors(true);
        try {
            $chp = curl_init();
            $cookiesjar = "cookiejar_rpx.txt";
            curl_setopt($chp, CURLOPT_COOKIEJAR, $cookiesjar);
            curl_setopt($chp, CURLOPT_COOKIEFILE, $cookiesjar);
            curl_setopt($chp, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.76 Safari/537.36");
            curl_setopt($chp, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($chp, CURLOPT_RETURNTRANSFER, 1);

            $url = 'http://api.rpxholding.com/wsdl/client/' . $method . '.php?user=' . rawurlencode($this->_rpxUser) . '&password=' . rawurlencode($this->_rpxPassword) . $param;
            curl_setopt($chp, CURLOPT_URL, $url);
            $content = curl_exec($chp);
            curl_close($chp);

            if(simplexml_load_string($content) !== FALSE) {
                return $content;
            }else{
                return false;
            }
        }catch (Exception $e){
            return false;
        }
    }

    private function _generateParam($arrParam){
        $this->_params = "&";
        foreach($arrParam as $keyItemArrParam => $itemArrParam){
            $this->_params .= $keyItemArrParam."=".$itemArrParam."&";
        }
        $this->_params = substr($this->_params, 0, strlen($this->_params)-1);

        return $this->_params;
    }

    public function getService(){
        $ret = $this->_sendRequest("getService","");
        if(!$ret) return false;
        $_data = simplexml_load_string($ret);
        $DATA = $_data->DATA;
        $arrRet = array();
        foreach($DATA as $itemData){
            $temp = explode("-",$itemData->SERVICE);
            $arrRet[$temp[0]] = $temp[1];
        }
        return $arrRet;
    }

    public function getServiceSOAP(){
        $ret = $this->_sendRequestSOAP("getService","");
        if(!$ret) return false;
        $arr = json_decode(json_encode((array) simplexml_load_string($ret)),1);
        $DATA = $arr['DATA'];
        $arrRet = array();
        foreach($DATA as $itemData){
            $temp = explode("-",$itemData['SERVICE']);
            $arrRet[$temp[0]] = $temp[1];
        }
        return $arrRet;
    }

    public function sendShipmentInformation($arrParam){
        $retParam = $this->_generateParam($arrParam);
        $ret = $this->_sendRequest("sendShipmentData",$retParam);
        if(!$ret) return false;
        $_data = simplexml_load_string($ret);
        $DATA = $_data->DATA;

        if(trim($DATA->AWB_RETURN) == ""){
            return false;
        }else{
            return $DATA;
        }
    }

    public function sendPickupRequest($arrParam){
        $retParam = $this->_generateParam($arrParam);
        $ret = $this->_sendRequest("sendPickupRequest",$retParam);
        if(!$ret) return false;
        $_data = simplexml_load_string($ret);
        $DATA = $_data->DATA;

        if(trim($DATA->PICKUP_REQUEST_NO) == ""){
            return false;
        }else{
            return $DATA;
        }
    }

    public function getRouteOrigin($arrParam){
        $retParam = $this->_generateParam($arrParam);
        $ret = $this->_sendRequest("getRouteOrigin",$retParam);
        if(!$ret) return false;
        $_data = simplexml_load_string($ret);
        $DATA = $_data->DATA;

        if(trim($DATA->ORIGIN) == ""){
            return false;
        }else{
            return $DATA;
        }
    }

    public function getRouteOriginSOAP($postalcode){
        $ret = $this->_sendRequestSOAP("getRouteOrigin",$postalcode);
        if(!$ret) return false;
        $arr = json_decode(json_encode((array) simplexml_load_string($ret)),1);
        if(isset($arr['DATA'])) {
            $DATA = $arr['DATA'];
            $origin = $DATA['ORIGIN'];
            $originBillCity = $DATA['ORIGIN_BILL_CITY'];
            $originCityName = $DATA['ORIGIN_CITY_NAME'];

            return $origin;
        }else{
            return $ret['RESULT'];
        }
    }

    public function getPostalCode($arrParam){
        $retParam = $this->_generateParam($arrParam);
        $ret = $this->_sendRequest("getPostalCode",$retParam);
        if(!$ret) return false;
        $_data = simplexml_load_string($ret);
        $DATA = $_data->DATA;
        $arrRet = array();
        foreach($DATA as $itemData){
            $cityId = $itemData->CITY_ID;
            $cityName = $itemData->CITY_NAME;
            $postalCode = $itemData->POSTAL_CODE;
            $postalName = $itemData->POSTAL_NAME;
            $stationId = $itemData->STATION_ID;

            $arrRet[$postalCode] = $postalCode." - ".$postalName ;
        }
        return $arrRet;
    }

    public function getPostalCodeSOAP($cityid){
        $ret = $this->_sendRequestSOAP("getPostalCode",$cityid);
        if(!$ret) return false;
        $arr = json_decode(json_encode((array) simplexml_load_string($ret)),1);
        $DATA = $arr['DATA'];
        $arrRet = array();
        if(!is_array($DATA)) return array("" => "No Data Found");
        foreach($DATA as $itemData){
            if(isset($itemData['CITY_ID']) and isset($itemData['CITY_NAME']) and isset($itemData['POSTAL_CODE']) and isset($itemData['POSTAL_NAME'])){
                $cityId = $itemData['CITY_ID'];
                $cityName = $itemData['CITY_NAME'];
                $postalCode = $itemData['POSTAL_CODE'];
                $postalName = $itemData['POSTAL_NAME'];
                $arrRet[$postalCode] = $postalCode . " - " . $postalName;
            }
        }
        return $arrRet;
    }

    public function getCity(){
        $ret = $this->_sendRequest("getCity","");
        if(!$ret) return false;
        $_data = simplexml_load_string($ret);
        $DATA = $_data->DATA;
        $arrRet = array();
        foreach($DATA as $itemData){
            $cityId = $itemData->CITY_ID;
            $cityName = $itemData->CITY_NAME;
            $stationId = $itemData->STATION_ID;

            $arrRet[$cityId] = $cityName;
        }
        return $arrRet;
    }

    public function getCitySOAP(){
        $ret = $this->_sendRequestSOAP("getCity","");
        if(!$ret) return false;
        $arr = json_decode(json_encode((array) simplexml_load_string($ret)),1);
        $DATA = $arr['DATA'];
        $arrRet = array();
        foreach($DATA as $itemData){
            $cityId = $itemData['CITY_ID'];
            $cityName = $itemData['CITY_NAME'];
            $stationId = $itemData['STATION_ID'];
            $arrRet[$cityId] = $cityName;
        }
        return $arrRet;
    }

    public function getProvince(){
        $ret = $this->_sendRequest("getProvince","");
        if(!$ret) return false;
        $_data = simplexml_load_string($ret);
        $DATA = $_data->DATA;
        $arrRet = array();
        foreach($DATA as $itemData){
            $province = $itemData->PROVINCE;
            $arrRet[$province] = $province;
        }
        return $arrRet;
    }

    public function getProvinceSOAP(){
        $ret = $this->_sendRequestSOAP("getProvince","");
        if(!$ret) return false;
        $arr = json_decode(json_encode((array) simplexml_load_string($ret)),1);
        $DATA = $arr['DATA'];
        $arrRet = array();
        foreach($DATA as $itemData){
            $province = $itemData['PROVINCE'];
            $arrRet[$province] = $province;
        }
        return $arrRet;
    }

    public function sendShipmentDataSOAP($arrParam){
        $ret = $this->_sendRequestSOAP("sendShipmentData",$arrParam);
        if(!$ret) return false;
        $arr = json_decode(json_encode((array) simplexml_load_string($ret)),1);
        $DATA = $arr['DATA'];

        //$dataResult = $DATA['RESULT'];
        //$dataAwbReturn = $DATA['AWB_RETURN'][0];

        return $DATA;
    }

    public function sendPickupRequestSOAP($arrParam){
        $ret = $this->_sendRequestSOAP("sendPickupRequest",$arrParam);
        if(!$ret) return false;
        $arr = json_decode(json_encode((array) simplexml_load_string($ret)),1);
        if(isset($arr['DATA'])) {
            $DATA = $arr['DATA'];
        }else{
            $DATA = $arr['RESULT'];
        }
        //$dataResult = $DATA['RESULT'];
        //$dataPickupRequestNo = $DATA['PICKUP_REQUEST_NO'];
        return $DATA;

    }

}
?>