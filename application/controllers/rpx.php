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
            ->setHeadingTitle(array("Record #", "AWB Number","Order Number","Created At"))
            ->setHeadingWidth(array(2, 2,2,3,2,4));

        $this->va_list->setInputFilter(1, array("name" => $this->rpx_m->filters['awb_number']));
        $this->va_list->setInputFilter(2, array("name" => $this->rpx_m->filters['order_no']));

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
        $this->data['script'] = $this->load->view("script/rpx_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function pickup(){
        $pickup_ready_time = array("pickup_ready_time" => date("Y-m-d H:i"));
        $shipper_address1 = array("shipper_address1" => "Komplek Taman Tekno Blok H2 No. 27");
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
        $this->va_input->addInput( array("name" => "pickup_ready_time", "placeholder" => "Pickup Date and Time", "label" => "Pickup Date and Time", "value" => @$pickup_ready_time['pickup_ready_time'], "msg" => @$msg['pickup_ready_time']) );
        $this->va_input->addInput( array("name" => "pickup_request_by", "placeholder" => "Pickup Request By", "label" => "Pickup Request By", "value" => @$pickup_request_by['pickup_request_by'], "msg" => @$msg['pickup_request_by']) );
        $this->va_input->addInput( array("name" => "pickup_account_number", "placeholder" => "Pickup Account Number", "label" => "Pickup Account Number", "value" => @$pickup_account_number['pickup_account_number'], "msg" => @$msg['pickup_account_number']) );
        $this->va_input->addInput( array("name" => "pickup_company_name", "placeholder" => "Pickup Company Name", "label" => "Pickup Company Name", "value" => @$pickup_company_name['pickup_company_name'], "msg" => @$msg['pickup_company_name']) );
        $this->va_input->addTextarea( array("name" => "pickup_company_address", "placeholder" => "Pickup Company Address", "help" => "Pickup Company Address", "label" => "Pickup Company Address", "value" => @$pickup_company_address['pickup_company_address'], "msg" => @$msg['pickup_company_address']) );
        $this->va_input->addInput( array("name" => "pickup_shipper_name", "placeholder" => "Pickup Shipper Name", "label" => "Pickup Shipper Name", "value" => @$pickup_shipper_name['pickup_shipper_name'], "msg" => @$msg['pickup_shipper_name']) );
        $this->va_input->addInput( array("name" => "pickup_phone", "placeholder" => "Pickup Phone", "label" => "Pickup Phone", "value" => @$pickup_phone['pickup_phone'], "msg" => @$msg['pickup_phone']) );
        $this->va_input->addInput( array("name" => "pickup_city", "placeholder" => "Pickup City", "label" => "Pickup City", "value" => @$pickup_city['pickup_city'], "msg" => @$msg['pickup_city']) );
        $this->va_input->addSelect( array("name" => "pickup_postal_code","label" => "Pickup Postal Code", "list" => $this->_getPostalCode('TAN'), "value" => @$value['pickup_postal_code'], "msg" => @$msg['pickup_postal_code']) );
        $this->va_input->addSelect( array("name" => "service_type","label" => "Service Type", "list" => $this->_getService(), "value" => @$value['service_type'], "msg" => @$msg['service_type']) );
        $this->va_input->addSelect( array("name" => "destin_city","label" => "Destination City", "list" => $this->_getCity(), "value" => @$value['destin_city'], "msg" => @$msg['destin_city']) );

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

}