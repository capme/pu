<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Testform extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		
        $content = $this->rpx();
        $xml = $content;
        print_r($xml);

	}

    public function rpx(){
        $this->load->library("rpx_lib");
        //method get services
        $ret = $this->rpx_lib->getService();
        if(!$ret){
            echo "RPX getService failed. Please see log file.";
        }else{
            return $ret;
        }

        //method sendShipmentData
        /*
        $testArrParam = array(
            "awb" => "RPX00000001",
            "package_id" => "",
            "order_type" => "",
            "order_number" => "PLOU00012345",
            "service_type_id" => "RGP",
            "shipper_account" => "234098705",
            "shipper_name" => "Purbo",
            "shipper_company" => "PT. PARAPLOU",
            "shipper_address1" => "Graha Tirtadi lt. 2",
            "shipper_address2" => "Jl. Senopati no 71-73",
            "shipper_kelurahan" => "",
            "shipper_kecamatan" => "",
            "shipper_city" => "Jakarta Selatan",
            "shipper_state" => "DKI Jakarta",
            "shipper_zip" => "",
            "shipper_phone" => "081318759311",
            "identity_no" => "",
            "shipper_mobile_no" => "081318759311",
            "shipper_email" => "",
            "consignee_account" => "",
            "consignee_mobile_no" => "",
            "consignee_email" => "",
            "consignee_name" => "",
            "consignee_company" => "",
            "consignee_address1" => "",
            "consignee_address2" => "",
            "consignee_kelurahan" => "",
            "consignee_kecamatan" => "",
            "consignee_city" => "",
            "consignee_state" => "",
            "consignee_zip" => "",
            "consignee_phone" => "",
            "desc_of_goods" => "",
            "tot_package" => "",
            "actual_weight" => "",
            "tot_weight" => "",
            "tot_declare_value" => "",
            "tot_dimensi" => "",
            "format" => ""
        );
        $ret = $this->rpx_lib->sendShipmentInformation($testArrParam);
        if(!$ret){
            echo "RPX sendShipmentInformation failed. Please see log file.";
        }else{
            return $ret;
        }
*/
        //method sendPickupRequest
        /*
        $testArrParam = array(
            "order_type" => "",
            "pickup_agent_id" => "",
            "pickup_ready_time" => "",
            "pickup_request_by" => "",
            "pickup_account_number" => "234098705",
            "pickup_company_name" => "PT. PARAPLOU",
            "pickup_company_address" => "Graha Tirtadi lt. 2",
            "pickup_city" => "Jakarta Selatan",
            "pickup_postal_code" => "",
            "service_type" => "RGP",
            "desc_of_goods" => "",
            "tot_declare_value" => "",
            "office_closed_time" => "",
            "pickup_shipper_name" => "",
            "pickup_company_email" => "",
            "pickup_cellphone" => "",
            "pickup_phone" => "",
            "destin_postal_code" => "",
            "destin_city" => "",
            "destin_province" => "",
            "total_weight" => "",
            "total_package" => "",
            "format" => ""
        );
        $ret = $this->rpx_lib->sendPickupRequest($testArrParam);
        if(!$ret){
            echo "RPX sendPickupRequest failed. Please see log file.";
        }else{
            return $ret;
        }
        */

    }
}
?>