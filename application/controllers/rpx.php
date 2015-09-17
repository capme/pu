<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* @property Rpx_m $rpx_m
 */
class Rpx extends MY_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->model( array("rpx_m") );
        $this->load->library("rpx_lib");
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "RPX AWB";
        $this->data['breadcrumb'] = array("RPX AWB List"=>"rpx");

        $this->rpx_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("RPX")->setAddLabel("Upload RPX AWB")
            ->setHeadingTitle(array("Record #", "AWB Number","Order Number","AWB Return","Pickup Request No.","Created At"))
            ->setHeadingWidth(array(2, 2,2,3,2,2,2,4));

        $this->va_list->setInputFilter(1, array("name" => $this->rpx_m->filters['awb_number']));
        $this->va_list->setInputFilter(2, array("name" => $this->rpx_m->filters['order_no']));
        $this->va_list->setInputFilter(3, array("name" => $this->rpx_m->filters['awb_return']));
        $this->va_list->setInputFilter(4, array("name" => $this->rpx_m->filters['pickup_request_no']));

        $this->data['script'] = $this->load->view("script/Rpx_list", array("ajaxSource" => site_url("rpx/RpxList")), true);
        $this->load->view("template", $this->data);
    }

    public function RpxList(){
        $sAction = $this->input->post("sAction");
        if($sAction == "group_action") {
            $id = $this->input->post("id");
            if(sizeof($id) > 0) {
                $action = $this->input->post("sGroupActionName");
            }
        }
        $data = $this->rpx_m->getRpxList();
        echo json_encode($data);
    }

    public function add(){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Upload File";
        $this->data['breadcrumb'] = array("RPX AWB List"=>"rpx", "Upload File" => "");
        $this->data['formTitle'] = "Upload File";
        $this->load->library("va_input", array("group" => "rpx"));

        $flashData = $this->session->flashdata("rpxError");
        if($flashData !== false)
        {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        }
        else
        {
            $msg = $value = array();
        }
        $this->va_input->addHidden( array("name" => "method", "value" => "new") );
        $this->va_input->addCustomField( array("name" =>"userfile", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" => @$msg['userfile'][0]?:@$msg['userfile'][1], "label" => "Upload File *", "view"=>"form/upload_rpx"));
        $this->data['script'] = $this->load->view("script/rpx_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save ()	{
        if($_SERVER['REQUEST_METHOD'] != "POST") {
            redirect("rpx/add");
        }
        $post = $this->input->post("rpx");
        if(empty($post)) {
            redirect("rpx/add");
        }

        if($post['method'] == "new"){
            //validate file xls first
            $this->_saveNew($post);
        }elseif($post['method'] == "sendshipment"){
            //proses post method sendShipmentData
            $this->_sendShipmentData($post);
        }elseif($post['method'] == "pickuprequest"){
            //proses post method sendPickupRequest
            $this->_sendPickupRequest($post);
        }
    }

    private function _saveNew($post) {
        $msg = null;
        $msg = $this->_doUploadFile();

        if($msg['error'] == true) {
            // upload failed
            $result['userfile'][0] = $msg['data'];
            $this->session->set_flashdata( array("rpxError" => json_encode(array("msg"=>$result, "data" => $post))));
            redirect("rpx/add");
        } else {
            $fileData = $msg['data'];
        }


        $realPost = $post;
        $post['userfile'] = $fileData['file_name'] ;
        $post['full_path'] = $fileData['full_path'];

        $result=$this->rpx_m->saveFile($post);

        redirect("rpx");
    }

    private function _doUploadFile() {
        $return = array('error' => false, 'data' => array());

        $config['upload_path'] = $this->rpx_m->path;
        $config['allowed_types'] = 'csv';
        $config['max_size']	= '2000';
        $config['file_name'] = date("YmdHis");

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload()) {
            $return['error'] = true;
            $return['data'] = $this->upload->display_errors();
        } else {
            $data = $this->upload->data();
            $return['data'] = array('file_name'=>$data['file_name'], 'full_path'=>$data['full_path']);
        }

        return $return;
    }

    public function delete($id){
        $data = $this->rpx_m->deleteRpx($id);
        redirect('rpx');
    }

    public function shipment(){
        $awb = array("awb" => $_GET['awb']);
        $order_number = array("order_number" => $_GET['orderno']);
        $shipper_account = array("shipper_account" => $this->rpx_lib->getRpxAccount());
        $shipper_address1 = array("shipper_address1" => "Komplek Taman Tekno Blok H2 No. 27");
        $shipper_zip = array("shipper_zip" => "15314");
        $shipper_phone = array("shipper_phone" => "021-75876427");
        $shipper_name = array("shipper_name" => "PT. Vela Asia");
        $shipper_company = array("shipper_company" => "PT. Vela Asia");
        $shipper_kelurahan = array("shipper_kelurahan" => "Setu");
        $shipper_kecamatan = array("shipper_kecamatan" => "BSD");
        $shipper_city = array("shipper_city" => "Tangerang");
        $shipper_state = array("shipper_state" => "Tangerang");

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Send Shipment";
        $this->data['breadcrumb'] = array("RPX AWB List"=>"rpx", "Send Shipment Data" => "");
        $this->data['formTitle'] = "Send Shipment Data";
        $this->load->library("va_input", array("group" => "rpx"));

        $flashData = $this->session->flashdata("rpxError");
        if($flashData !== false)
        {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        }
        else
        {
            $msg = $value = array();
        }
        $this->va_input->addHidden( array("name" => "method", "value" => "sendshipment") );
        $this->va_input->addSelect( array("name" => "awb","label" => "AWB", "list" => $this->rpx_m->getAWBList(@$awb['awb']), "value" => @$awb['awb'], "msg" => @$msg['awb']) );
        $this->va_input->addInput( array("name" => "order_number", "placeholder" => "Order Number", "label" => "Order Number", "value" => @$order_number['order_number'], "msg" => @$msg['order_number']) );
        $this->va_input->addSelect( array("name" => "service","label" => "Service", "list" => $this->_getService(), "value" => @$value['service'], "msg" => @$msg['service']) );
        //shipper
        $this->va_input->addInput( array("name" => "shipper_account", "placeholder" => "Shipper Account", "label" => "Shipper Account", "value" => @$shipper_account['shipper_account'], "msg" => @$msg['shipper_account']) );
        $this->va_input->addInput( array("name" => "shipper_name", "placeholder" => "Shipper Name", "label" => "Shipper Name", "value" => @$shipper_name['shipper_name'], "msg" => @$msg['shipper_name']) );
        $this->va_input->addInput( array("name" => "shipper_company", "placeholder" => "Shipper Company", "label" => "Shipper Company", "value" => @$shipper_company['shipper_company'], "msg" => @$msg['shipper_company']) );
        $this->va_input->addTextarea( array("name" => "shipper_address1", "placeholder" => "Shipper Address", "help" => "Shipper Address", "label" => "Shipper Address", "value" => @$shipper_address1['shipper_address1'], "msg" => @$msg['shipper_address1']) );
        $this->va_input->addInput( array("name" => "shipper_kelurahan", "placeholder" => "Shipper Kelurahan", "label" => "Shipper Kelurahan", "value" => @$shipper_kelurahan['shipper_kelurahan'], "msg" => @$msg['shipper_kelurahan']) );
        $this->va_input->addInput( array("name" => "shipper_kecamatan", "placeholder" => "Shipper Kecamatan", "label" => "Shipper Kecamatan", "value" => @$shipper_kecamatan['shipper_kecamatan'], "msg" => @$msg['shipper_kecamatan']) );
        $this->va_input->addInput( array("name" => "shipper_city", "placeholder" => "Shipper City", "label" => "Shipper City", "value" => @$shipper_city['shipper_city'], "msg" => @$msg['shipper_city']) );
        $this->va_input->addInput( array("name" => "shipper_state", "placeholder" => "Shipper State", "label" => "Shipper State", "value" => @$shipper_state['shipper_state'], "msg" => @$msg['shipper_state']) );
        $this->va_input->addInput( array("name" => "shipper_zip", "placeholder" => "Shipper ZIP", "label" => "Shipper ZIP", "value" => @$shipper_zip['shipper_zip'], "msg" => @$msg['shipper_zip']) );
        $this->va_input->addInput( array("name" => "shipper_phone", "placeholder" => "Shipper Phone", "label" => "Shipper Phone", "value" => @$shipper_phone['shipper_phone'], "msg" => @$msg['shipper_phone']) );
        $this->va_input->addInput( array("name" => "shipper_mobile_no", "placeholder" => "Shipper Mobile Number", "label" => "Shipper Mobile Number", "value" => @$value['shipper_mobile_no'], "msg" => @$msg['shipper_mobile_no']) );
        //consignee
        $this->va_input->addInput( array("name" => "consignee_name", "placeholder" => "Consignee Name", "label" => "Consignee Name", "value" => @$value['consignee_name'], "msg" => @$msg['consignee_name']) );
        $this->va_input->addInput( array("name" => "consignee_company", "placeholder" => "Consignee Company", "label" => "Consignee Company", "value" => @$value['consignee_company'], "msg" => @$msg['consignee_company']) );
        $this->va_input->addTextarea( array("name" => "consignee_address1", "placeholder" => "Consignee Address", "help" => "Consignee Address", "label" => "Consignee Address", "value" => @$value['consignee_address1'], "msg" => @$msg['consignee_address1']) );
        $this->va_input->addInput( array("name" => "consignee_kelurahan", "placeholder" => "Consignee Kelurahan", "label" => "Consignee Kelurahan", "value" => @$value['consignee_kelurahan'], "msg" => @$msg['consignee_kelurahan']) );
        $this->va_input->addInput( array("name" => "consignee_kecamatan", "placeholder" => "Consignee Kecamatan", "label" => "Consignee Kecamatan", "value" => @$value['consignee_kecamatan'], "msg" => @$msg['consignee_kecamatan']) );
        $this->va_input->addInput( array("name" => "consignee_city", "placeholder" => "Consignee City", "label" => "Consignee City", "value" => @$value['consignee_city'], "msg" => @$msg['consignee_city']) );
        $this->va_input->addInput( array("name" => "consignee_state", "placeholder" => "Consignee State", "label" => "Consignee State", "value" => @$value['consignee_state'], "msg" => @$msg['consignee_state']) );
        $this->va_input->addInput( array("name" => "consignee_zip", "placeholder" => "Consignee ZIP", "label" => "Consignee ZIP", "value" => @$value['consignee_zip'], "msg" => @$msg['consignee_zip']) );
        $this->va_input->addInput( array("name" => "consignee_phone", "placeholder" => "Consignee Phone", "label" => "Consignee Phone", "value" => @$value['consignee_phone'], "msg" => @$msg['consignee_phone']) );
        $this->va_input->addInput( array("name" => "consignee_mobile_no", "placeholder" => "Consignee Mobile Number", "label" => "Consignee Mobile Number", "value" => @$value['consignee_mobile_no'], "msg" => @$msg['consignee_mobile_no']) );
        $this->va_input->addTextarea( array("name" => "desc_of_goods", "placeholder" => "Description of goods/package", "help" => "Description of goods/package", "label" => "Description of goods/package", "value" => @$value['desc_of_goods'], "msg" => @$msg['desc_of_goods']) );
        $this->va_input->addInput( array("name" => "tot_package", "placeholder" => "Total package", "label" => "Total package", "value" => "1", "msg" => @$msg['tot_package']) );
        $this->va_input->addInput( array("name" => "actual_weight", "placeholder" => "Actual weight of package", "label" => "Actual weight of package", "value" => @$value['actual_weight'], "msg" => @$msg['actual_weight']) );
        $this->va_input->addInput( array("name" => "tot_weight", "placeholder" => "Total weight of package", "label" => "Total weight of package", "value" => @$value['tot_weight'], "msg" => @$msg['tot_weight']) );

        $this->data['script'] = $this->load->view("script/rpx_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function pickup(){
        $awb = array("awb" => $_GET['awb']);
        if(isset($_GET['destin_province']) and $_GET['destin_province'] != "") {
            $destin_province = array("destin_province" => $_GET['destin_province']);
        }else{
            $destin_province = array("destin_province" => "BALI");
        }
        if(isset($_GET['destin_city']) and $_GET['destin_city'] != ""){
            $destin_city = array("destin_city" => $_GET['destin_city']);
        }else{
            $destin_city = array("destin_city" => "APR");
        }
        $pickup_ready_time = array("pickup_ready_time" => date("Y-m-d H:i"));
        $pickup_request_by = array("pickup_request_by" => "PT. Vela Asia");
        $pickup_account_number = array("pickup_account_number" => $this->rpx_lib->getRpxAccount());
        $pickup_company_name = array("pickup_company_name" => "PT. Vela Asia");
        $pickup_company_address = array("pickup_company_address" => "Komplek Taman Tekno Blok H2 No. 27, BSD Serpong, Tangerang");
        $pickup_shipper_name = array("pickup_shipper_name" => "PT. Vela Asia");
        $pickup_phone = array("pickup_phone" => "021-75876427");
        $pickup_city = array("pickup_city" => $this->_getRouteOrigin("15314"));

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Send Shipment";
        $this->data['breadcrumb'] = array("RPX AWB List"=>"rpx", "Send Shipment Data" => "");
        $this->data['formTitle'] = "Send Shipment Data";
        $this->load->library("va_input", array("group" => "rpx"));

        $flashData = $this->session->flashdata("rpxError");
        if($flashData !== false)
        {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        }
        else
        {
            $msg = $value = array();
        }
        $this->va_input->addHidden( array("name" => "method", "value" => "pickuprequest") );
        $this->va_input->addHidden( array("name" => "awb", "value" => $awb['awb']) );
        $this->va_input->addInput( array("name" => "pickup_ready_time", "placeholder" => "Pickup Date and Time", "label" => "Pickup Date and Time", "value" => $pickup_ready_time['pickup_ready_time'], "msg" => @$msg['pickup_ready_time']) );
        $this->va_input->addInput( array("name" => "pickup_request_by", "placeholder" => "Pickup Request By", "label" => "Pickup Request By", "value" => @$pickup_request_by['pickup_request_by'], "msg" => @$msg['pickup_request_by']) );
        $this->va_input->addInput( array("name" => "pickup_account_number", "placeholder" => "Pickup Account Number", "label" => "Pickup Account Number", "value" => @$pickup_account_number['pickup_account_number'], "msg" => @$msg['pickup_account_number']) );
        $this->va_input->addInput( array("name" => "pickup_company_name", "placeholder" => "Pickup Company Name", "label" => "Pickup Company Name", "value" => @$pickup_company_name['pickup_company_name'], "msg" => @$msg['pickup_company_name']) );
        $this->va_input->addTextarea( array("name" => "pickup_company_address", "placeholder" => "Pickup Company Address", "help" => "Pickup Company Address", "label" => "Pickup Company Address", "value" => @$pickup_company_address['pickup_company_address'], "msg" => @$msg['pickup_company_address']) );
        $this->va_input->addInput( array("name" => "pickup_shipper_name", "placeholder" => "Pickup Shipper Name", "label" => "Pickup Shipper Name", "value" => @$pickup_shipper_name['pickup_shipper_name'], "msg" => @$msg['pickup_shipper_name']) );
        $this->va_input->addInput( array("name" => "pickup_phone", "placeholder" => "Pickup Phone", "label" => "Pickup Phone", "value" => @$pickup_phone['pickup_phone'], "msg" => @$msg['pickup_phone']) );
        $this->va_input->addInput( array("name" => "pickup_city", "placeholder" => "Pickup City", "label" => "Pickup City", "value" => @$pickup_city['pickup_city'], "msg" => @$msg['pickup_city']) );
        $this->va_input->addSelect( array("name" => "pickup_postal_code","label" => "Pickup Postal Code", "list" => $this->_getPostalCode('TAN'), "value" => @$value['pickup_postal_code'], "msg" => @$msg['pickup_postal_code']) );
        $this->va_input->addSelect( array("name" => "service_type","label" => "Service Type", "list" => $this->_getService(), "value" => @$value['service_type'], "msg" => @$msg['service_type']) );
        $this->va_input->addSelect( array("name" => "destin_province","label" => "Destination Province", "list" => $this->_getProvince(), "value" => @$destin_province['destin_province'], "msg" => @$msg['destin_province']) );
        $this->va_input->addSelect( array("name" => "destin_city","label" => "Destination City", "list" => $this->_getCity(), "value" => @$destin_city['destin_city'], "msg" => @$msg['destin_city']) );
        $this->va_input->addSelect( array("name" => "destin_postal_code","label" => "Destination Postal Code", "list" => $this->_getPostalCode(@$destin_city['destin_city']), "value" => @$value['destin_postal_code'], "msg" => @$msg['destin_postal_code']) );
        $this->data['script'] = $this->load->view("script/rpx_add", array(), true);
        $this->load->view('template', $this->data);

    }

    private function _getService(){
        //$ret = $this->rpx_lib->getService();
        $ret = $this->rpx_lib->getServiceSOAP();
        return $ret;
    }

    private function _getRouteOrigin($postalcode){
        $arrPostalcode = array($postalcode);
        $ret = $this->rpx_lib->getRouteOriginSOAP($arrPostalcode);
        return $ret;
    }

    private function _getPostalCode($cityid){
        $arrCityid = array($cityid);
        $ret = $this->rpx_lib->getPostalCodeSOAP($arrCityid);
        return $ret;
    }

    private function _getCity(){
        $ret = $this->rpx_lib->getCitySOAP();
        return $ret;
    }

    private function _getProvince(){
        $ret = $this->rpx_lib->getProvinceSOAP();
        return $ret;
    }

    private function _sendShipmentData($param){
        $data = array(
            $param['awb'],
            "",
            "",
            $param['order_number'],
            $param['service'],
            $param['shipper_account'],
            $param['shipper_name'],
            $param['shipper_company'],
            $param['shipper_address1'],
            "",
            $param['shipper_kelurahan'],
            $param['shipper_kecamatan'],
            $param['shipper_city'],
            $param['shipper_state'],
            $param['shipper_zip'],
            $param['shipper_phone'],
            "",
            $param['shipper_mobile_no'],
            "",
            "",
            $param['consignee_name'],
            $param['consignee_company'],
            $param['consignee_address1'],
            "",
            $param['consignee_kelurahan'],
            $param['consignee_kecamatan'],
            $param['consignee_city'],
            $param['consignee_state'],
            $param['consignee_zip'],
            $param['consignee_phone'],
            $param['consignee_mobile_no'],
            "",
            $param['desc_of_goods'],
            $param['tot_package'],
            $param['actual_weight'],
            $param['tot_weight'],
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            ""
        );
        $return = $this->rpx_lib->sendShipmentDataSOAP($data);
        if(empty($return['AWB_RETURN'])){
            redirect("rpx?res=failed&msg=".$return['RESULT']);
        }else{
            $this->rpx_m->saveAwbReturn($return['AWB_RETURN'], $param['awb'], $param['order_number']);
            redirect("rpx?res=success&awb_return=".$return['AWB_RETURN']);
        }
    }

    private function _sendPickupRequest($param){
        $data = array(
            "",
            "",
            $param['pickup_ready_time'],
            $param['pickup_request_by'],
            $param['pickup_account_number'],
            $param['pickup_company_name'],
            $param['pickup_company_address'],
            $param['pickup_city'],
            $param['pickup_postal_code'],
            $param['service_type'],
            "",
            "",
            "",
            $param['pickup_shipper_name'],
            "",
            "",
            $param['pickup_phone'],
            $param['destin_postal_code'],
            $param['destin_city'],
            $param['destin_province'],
            "",
            "",
            ""
        );
        $return = $this->rpx_lib->sendPickupRequestSOAP($data);
        if(empty($return['PICKUP_REQUEST_NO'])){
            redirect("rpx?res=failed&msg=".$return['RESULT']);
        }else{
            $this->rpx_m->savePickupReturn($return['PICKUP_REQUEST_NO'], $param['awb']);
            redirect("rpx?res=success&pickup_request_no=".$return['PICKUP_REQUEST_NO']);
        }
    }

}