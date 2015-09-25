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
            ->setMassAction(array("0" => "Pickup Request"))
            ->setHeadingTitle(array("Record #", "AWB Number","Order Number","AWB Return","Pickup Request No.","Status","Created At"))
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
            $value['awb'] = $_GET['awb'];
            $value['order_number'] = "";
            if(trim($_GET['account_number']) == ""){
                $value['shipper_account'] = $this->rpx_lib->getRpxAccount();
            }else{
                $value['shipper_account'] = trim($_GET['account_number']);
            }
            $value['shipper_address1'] = "Komplek Taman Tekno Blok H2 No. 27";
            $value['shipper_zip'] = "15314";
            $value['shipper_phone'] = "021-75876427";
            $value['shipper_name'] = "PT. Vela Asia";
            $value['shipper_company'] = "PT. Vela Asia";
            $value['shipper_kelurahan'] = "Setu";
            $value['shipper_kecamatan'] = "BSD";
            $value['shipper_city'] = "Tangerang";
            $value['shipper_state'] = "Tangerang";
        }
        $this->va_input->addHidden( array("name" => "method", "value" => "sendshipment") );
        $this->va_input->addSelect( array("name" => "awb","label" => "AWB *", "list" => $this->rpx_m->getAWBList(@$value['awb']), "value" => @$value['awb'], "msg" => @$msg['awb']) );
        $this->va_input->addInput( array("name" => "order_number", "placeholder" => "Order Number", "label" => "Order Number", "value" => @$value['order_number'], "msg" => @$msg['order_number']) );
        $this->va_input->addSelect( array("name" => "service","label" => "Service *", "list" => $this->_getService(), "value" => @$value['service'], "msg" => @$msg['service']) );
        //shipper
        $this->va_input->addInput( array("name" => "shipper_account", "placeholder" => "Shipper Account", "label" => "Shipper Account *", "value" => @$value['shipper_account'], "msg" => @$msg['shipper_account']) );
        $this->va_input->addInput( array("name" => "shipper_name", "placeholder" => "Shipper Name", "label" => "Shipper Name *", "value" => @$value['shipper_name'], "msg" => @$msg['shipper_name']) );
        $this->va_input->addInput( array("name" => "shipper_company", "placeholder" => "Shipper Company", "label" => "Shipper Company *", "value" => @$value['shipper_company'], "msg" => @$msg['shipper_company']) );
        $this->va_input->addTextarea( array("name" => "shipper_address1", "placeholder" => "Shipper Address", "help" => "Shipper Address", "label" => "Shipper Address *", "value" => @$value['shipper_address1'], "msg" => @$msg['shipper_address1']) );
        $this->va_input->addInput( array("name" => "shipper_kelurahan", "placeholder" => "Shipper Kelurahan", "label" => "Shipper Kelurahan", "value" => @$value['shipper_kelurahan'], "msg" => @$msg['shipper_kelurahan']) );
        $this->va_input->addInput( array("name" => "shipper_kecamatan", "placeholder" => "Shipper Kecamatan", "label" => "Shipper Kecamatan", "value" => @$value['shipper_kecamatan'], "msg" => @$msg['shipper_kecamatan']) );
        $this->va_input->addInput( array("name" => "shipper_city", "placeholder" => "Shipper City", "label" => "Shipper City *", "value" => @$value['shipper_city'], "msg" => @$msg['shipper_city']) );
        $this->va_input->addInput( array("name" => "shipper_state", "placeholder" => "Shipper State", "label" => "Shipper State *", "value" => @$value['shipper_state'], "msg" => @$msg['shipper_state']) );
        $this->va_input->addInput( array("name" => "shipper_zip", "placeholder" => "Shipper ZIP", "label" => "Shipper ZIP", "value" => @$value['shipper_zip'], "msg" => @$msg['shipper_zip']) );
        $this->va_input->addInput( array("name" => "shipper_phone", "placeholder" => "Shipper Phone", "label" => "Shipper Phone *", "value" => @$value['shipper_phone'], "msg" => @$msg['shipper_phone']) );
        $this->va_input->addInput( array("name" => "shipper_mobile_no", "placeholder" => "Shipper Mobile Number", "label" => "Shipper Mobile Number", "value" => @$value['shipper_mobile_no'], "msg" => @$msg['shipper_mobile_no']) );
        //consignee
        $this->va_input->addInput( array("name" => "consignee_name", "placeholder" => "Consignee Name", "label" => "Consignee Name *", "value" => @$value['consignee_name'], "msg" => @$msg['consignee_name']) );
        $this->va_input->addInput( array("name" => "consignee_company", "placeholder" => "Consignee Company", "label" => "Consignee Company", "value" => @$value['consignee_company'], "msg" => @$msg['consignee_company']) );
        $this->va_input->addTextarea( array("name" => "consignee_address1", "placeholder" => "Consignee Address", "help" => "Consignee Address", "label" => "Consignee Address *", "value" => @$value['consignee_address1'], "msg" => @$msg['consignee_address1']) );
        $this->va_input->addInput( array("name" => "consignee_kelurahan", "placeholder" => "Consignee Kelurahan", "label" => "Consignee Kelurahan", "value" => @$value['consignee_kelurahan'], "msg" => @$msg['consignee_kelurahan']) );
        $this->va_input->addInput( array("name" => "consignee_kecamatan", "placeholder" => "Consignee Kecamatan", "label" => "Consignee Kecamatan", "value" => @$value['consignee_kecamatan'], "msg" => @$msg['consignee_kecamatan']) );
        $this->va_input->addInput( array("name" => "consignee_city", "placeholder" => "Consignee City", "label" => "Consignee City *", "value" => @$value['consignee_city'], "msg" => @$msg['consignee_city']) );
        $this->va_input->addInput( array("name" => "consignee_state", "placeholder" => "Consignee State", "label" => "Consignee State *", "value" => @$value['consignee_state'], "msg" => @$msg['consignee_state']) );
        $this->va_input->addInput( array("name" => "consignee_zip", "placeholder" => "Consignee ZIP", "label" => "Consignee ZIP", "value" => @$value['consignee_zip'], "msg" => @$msg['consignee_zip']) );
        $this->va_input->addInput( array("name" => "consignee_phone", "placeholder" => "Consignee Phone", "label" => "Consignee Phone *", "value" => @$value['consignee_phone'], "msg" => @$msg['consignee_phone']) );
        $this->va_input->addInput( array("name" => "consignee_mobile_no", "placeholder" => "Consignee Mobile Number", "label" => "Consignee Mobile Number", "value" => @$value['consignee_mobile_no'], "msg" => @$msg['consignee_mobile_no']) );
        $this->va_input->addTextarea( array("name" => "desc_of_goods", "placeholder" => "Description of goods/package", "help" => "Description of goods/package", "label" => "Description of goods/package", "value" => @$value['desc_of_goods'], "msg" => @$msg['desc_of_goods']) );
        $this->va_input->addInput( array("name" => "tot_package", "placeholder" => "Total package", "label" => "Total package *", "value" => "1", "msg" => @$msg['tot_package']) );
        $this->va_input->addInput( array("name" => "actual_weight", "placeholder" => "Actual weight of package", "label" => "Actual weight of package", "value" => @$value['actual_weight'], "msg" => @$msg['actual_weight']) );
        $this->va_input->addInput( array("name" => "tot_weight", "placeholder" => "Total weight of package", "label" => "Total weight of package *", "value" => @$value['tot_weight'], "msg" => @$msg['tot_weight']) );

        $this->data['script'] = $this->load->view("script/rpx_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function pickup(){
        if(!isset($_GET['ids'])) redirect("rpx");
        //check whether more than one shipment for one pickup
        if(strpos($_GET['ids'],",") >= 0){
            $arrids = explode(",", $_GET['ids']);
            $totalPackage = count($arrids);
        }else{
            $totalPackage = 1;
        }

        $totalWeight = $this->rpx_m->getSumTotalWeight($_GET['ids']);

        $ret = $this->rpx_m->getAwbStatus($_GET['ids']);
        if(!$ret) redirect("rpx");

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Send Pickup Request";
        $this->data['breadcrumb'] = array("RPX AWB List"=>"rpx", "Send Pickup Request" => "");
        $this->data['formTitle'] = "Send Pickup Request";
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
            if(isset($_GET['service_type']) and $_GET['service_type'] != "") {
                $value['service_type'] = $_GET['service_type'];
            }else{
                $value['service_type'] = "RGP";
            }
            if(isset($_GET['destin_province']) and $_GET['destin_province'] != "") {
                $value['destin_province'] = $_GET['destin_province'];
            }else{
                $value['destin_province'] = "BALI";
            }
            if(isset($_GET['destin_city']) and $_GET['destin_city'] != ""){
                $value['destin_city'] = $_GET['destin_city'];
            }else{
                $value['destin_city'] = "APR";
            }
            $value['pickup_ready_time'] = date("H:i");
            $value['pickup_request_by'] = "PT. Vela Asia";
            $value['pickup_account_number'] = $this->rpx_lib->getRpxAccount();
            $value['pickup_company_name'] = "PT. Vela Asia";
            $value['pickup_company_email'] = "opswh@paraplou.com";
            $value['pickup_company_address'] = "Komplek Taman Tekno Blok H2 No. 27, BSD Serpong, Tangerang";
            $value['pickup_shipper_name'] = "PT. Vela Asia";
            $value['pickup_phone'] = "021-75876427";
            $value['pickup_city'] = $this->_getRouteOrigin("15314");
        }
        $this->va_input->addHidden( array("name" => "method", "value" => "pickuprequest") );
        $this->va_input->addHidden( array("name" => "ids", "value" => $_GET['ids']) );
        $this->va_input->addHidden( array("name" => "total_package", "value" => $totalPackage) );
        $this->va_input->addHidden( array("name" => "total_weight", "value" => $totalWeight) );
        $this->va_input->addHidden( array("name" => "office_closed_time", "value" => "18:00") );
        $this->va_input->addHidden( array("name" => "desc_of_goods", "value" => "Paraplou shipment ".date("d:m:Y")) );
        $this->va_input->addHidden( array("name" => "pickup_cellphone", "value" => $value['pickup_phone']) );
        $this->va_input->addInput( array("name" => "pickup_ready_time", "placeholder" => "Pickup Ready Time", "label" => "Pickup Ready Time", "value" => $value['pickup_ready_time'], "msg" => @$msg['pickup_ready_time']) );
        $this->va_input->addInput( array("name" => "pickup_request_by", "placeholder" => "Pickup Request By", "label" => "Pickup Request By", "value" => @$value['pickup_request_by'], "msg" => @$msg['pickup_request_by']) );
        $this->va_input->addInput( array("name" => "pickup_account_number", "placeholder" => "Pickup Account Number", "label" => "Pickup Account Number", "value" => @$value['pickup_account_number'], "msg" => @$msg['pickup_account_number']) );
        $this->va_input->addInput( array("name" => "pickup_company_name", "placeholder" => "Pickup Company Name", "label" => "Pickup Company Name", "value" => @$value['pickup_company_name'], "msg" => @$msg['pickup_company_name']) );
        $this->va_input->addTextarea( array("name" => "pickup_company_address", "placeholder" => "Pickup Company Address", "help" => "Pickup Company Address", "label" => "Pickup Company Address", "value" => @$value['pickup_company_address'], "msg" => @$msg['pickup_company_address']) );
        $this->va_input->addInput( array("name" => "pickup_shipper_name", "placeholder" => "Pickup Shipper Name", "label" => "Pickup Shipper Name", "value" => @$value['pickup_shipper_name'], "msg" => @$msg['pickup_shipper_name']) );
        $this->va_input->addInput( array("name" => "pickup_phone", "placeholder" => "Pickup Phone", "label" => "Pickup Phone", "value" => @$value['pickup_phone'], "msg" => @$msg['pickup_phone']) );
        $this->va_input->addInput( array("name" => "pickup_company_email", "placeholder" => "Pickup Company Email", "label" => "Pickup Company Email", "value" => @$value['pickup_company_email'], "msg" => @$msg['pickup_company_email']) );
        $this->va_input->addInput( array("name" => "pickup_city", "placeholder" => "Pickup City", "label" => "Pickup City", "value" => @$value['pickup_city'], "msg" => @$msg['pickup_city']) );
        $this->va_input->addSelect( array("name" => "pickup_postal_code","label" => "Pickup Postal Code", "list" => $this->_getPostalCode('TAN'), "value" => @$value['pickup_postal_code'], "msg" => @$msg['pickup_postal_code']) );
        $this->va_input->addSelect( array("name" => "service_type","label" => "Service Type", "list" => $this->_getService(), "value" => @$value['service_type'], "msg" => @$msg['service_type']) );
        //$this->va_input->addSelect( array("name" => "destin_province","label" => "Destination Province", "list" => $this->_getProvince(), "value" => @$value['destin_province'], "msg" => @$msg['destin_province']) );
        //$this->va_input->addSelect( array("name" => "destin_city","label" => "Destination City", "list" => $this->_getCity(), "value" => @$value['destin_city'], "msg" => @$msg['destin_city']) );
        //$this->va_input->addSelect( array("name" => "destin_postal_code","label" => "Destination Postal Code", "list" => $this->_getPostalCode(@$value['destin_city']), "value" => @$value['destin_postal_code'], "msg" => @$msg['destin_postal_code']) );
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
        $this->load->add_package_path(APPPATH."third_party/threepl/");
        $this->load->library("outbound_threepl", null, "outbound_threepl");
        $this->load->library("threepl_lib");
        $this->load->model( array("client_m") );

        //========== proses confirm order ke 3pl ==============
        /*
         * proses ini sementara ditiadakan
         *
        $records = (array) $this->threepl_lib->getOrderByRefnumber($param['order_number']);
        $client = $this->client_m->getClientByClientCode($records['Source']);
        $client = $client->row_array();
        if(!$client['threepl_user'] && !$client['threepl_pass']) {
            log_message("debug", self::TAG . " Client doesn't had 3PL detail");
            die;
        }
        $c['threepluser'] = $client['threepl_user'];
        $c['threeplpass'] = $client['threepl_pass'];
        $this->outbound_threepl->setConfig( array("username" => $c['threepluser'], "password" => $c['threeplpass']) );

        $return = $this->outbound_threepl->confirmOrders($records['nOrderId'], $param['awb']);
        if(!$return){
            $result['awb'] = "Failed confirmed order on 3PL";
            $this->session->set_flashdata( array("rpxError" => json_encode(array("msg"=>$result, "data" => $param))));
            redirect("rpx/shipment?res=failed&msg=".$result['awb']);
        }
        */
        //============================================================
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
            ""
        );
        $return = $this->rpx_lib->sendShipmentDataSOAP($data);
        if(empty($return['AWB_RETURN'])){
                $result['awb'] = $return['RESULT'];
                $this->session->set_flashdata( array("rpxError" => json_encode(array("msg"=>$result, "data" => $param))));
                redirect("rpx/shipment?res=failed&msg=".$return['RESULT']);
        }else{
            $this->rpx_m->saveAwbReturn($return['AWB_RETURN'], $param['awb'], $param['order_number'], $param['tot_weight']);
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
            $param['desc_of_goods'],
            "",
            $param['office_closed_time'],
            $param['pickup_shipper_name'],
            $param['pickup_company_email'],
            $param['pickup_cellphone'],
            $param['pickup_phone'],
            "",
            "",
            "",
            $param['total_weight'],
            $param['total_package'],
            ""
        );
        $return = $this->rpx_lib->sendPickupRequestSOAP($data);
        if(empty($return['PICKUP_REQUEST_NO'])){
            $result['pickup_ready_time'] = $return;
            $this->session->set_flashdata( array("rpxError" => json_encode(array("msg"=>$result, "data" => $param))));
            redirect("rpx/pickup?res=failed&msg=".$return."&awb=".$param['awb']."&orderno=".$param['orderno']);
        }else{
            $this->rpx_m->savePickupReturn($return['PICKUP_REQUEST_NO'], $param['ids']);
            redirect("rpx?res=success&pickup_request_no=".$return['PICKUP_REQUEST_NO']);
        }
    }

}