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
        $data = $this->rpx_m->getInboundList();
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

}