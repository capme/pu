<?php
class Rpx_lib
{
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
        return $ret;
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
}
?>