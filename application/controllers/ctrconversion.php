<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ctrconversion extends MY_Controller
{
    var $data = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array("client_m", "ctrconversion_m"));
        $this->load->library('va_csv');
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "CTR ";
        $this->data['breadcrumb'] = array("Merchandising"=>"","CTR" => "");

        $this->ctrconversion_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("CTR")->setAddLabel("Upload CTR")
            ->setHeadingTitle(array("Record #","Product ID","CTR", "Conversion", "Created At"))
            ->setHeadingWidth(array(2, 2,2,4, 4, 2, 2));

        $this->va_list->setInputFilter(1, array("name" => $this->ctrconversion_m->filters["product_id"]));

        $this->data['script'] = $this->load->view("script/ctrconversion_list", array("ajaxSource" => site_url("ctrconversion/ctrConversionList")), true);
        $this->load->view("template", $this->data);
    }

    public function ctrConversionList()
    {
        $sAction = $this->input->post("sAction");
        if ($sAction == "group_action") {
            $id = $this->input->post("id");
            if (sizeof($id) > 0) {
                $action = $this->input->post("sGroupActionName");
            }
        }
        $data = $this->ctrconversion_m->getCtrList();
        echo json_encode($data);
    }

    public function add()
    {
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Upload CTR";
        $this->data['breadcrumb'] = array("Upload CTR File" => "");
        $this->data['formTitle'] = "Upload CTR File";
        $this->load->library("va_input", array("group" => "ctrconversion"));

        $flashData = $this->session->flashdata("ctrconversionError");
        if ($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = $value = array();
        }
        $this->va_input->addHidden(array("name" => "method", "value" => "new"));
        $this->va_input->addCustomField(array("name" => "userfile", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" => @$msg['userfile'][0] ?: @$msg['userfile'][1], "label" => "Upload File *", "view" => "form/upload_ctr.php"));
        $this->data['script'] = $this->load->view("script/ctr_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            redirect("ctrconversion/add");
        }
        $post = $this->input->post("ctrconversion");
        if (empty($post)) {
            redirect("ctrconversion/add");
        }

        if ($post['method'] == "new") {
            //validate file csv first
            $this->_saveNew($post);
        }
    }

    private function _saveNew($post){
        $msg = null;
        $msg = $this->uploadFile();

        if ($msg['error'] == true) {
            // upload failed
            $result['userfile'][0] = $msg['data'];
            $this->session->set_flashdata(array("ctrconversionError" => json_encode(array("msg" => $result, "data" => $post))));
            redirect("ctrconversion/add");
        } else {
            $fileData = $msg['data'];
        }

        if ($this->va_csv->get_array($fileData['full_path'])) {
            $post['userfile'] = $fileData['file_name'];
            $csv_array = $this->va_csv->get_array($fileData['full_path']);

            foreach ($csv_array as $row) {
                $post['product_id'] = $row['product_id'];
                $post['ctr'] = $row['ctr'];
                $post['conversion'] = $row['conversion'];

               $result= $this->ctrconversion_m->saveFile($post);
            }

            if (is_numeric($result)) {
                redirect("ctrconversion");
            } else {
                $this->session->set_flashdata(array("ctrconversionError" => json_encode(array("msg" => $result, "data" => $post))));
                redirect("ctrconversion/add");
            }
        }
    }

    public function uploadFile(){
        $return = array('error' => false, 'data' => array());

        $config['upload_path'] = '../public/merchandising/ctr/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = '2000';
        $config['file_name'] = date("YmdHis");

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload()) {
            $return['error'] = true;
            $return['data'] = $this->upload->display_errors();
        } else {
            $data = $this->upload->data();
            $return['data'] = array('file_name' => $data['file_name'], 'full_path' => $data['full_path']);
        }

        return $return;
    }
}

