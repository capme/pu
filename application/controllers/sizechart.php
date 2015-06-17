<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Sizechart extends MY_Controller{
    var $data = array();

    public function __construct(){
        parent::__construct();
        $this->load->model(array("client_m", "sizechart_m","clientoptions_m"));
        $this->load->library('va_csv');
    }

    public function index(){
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Size Chart";
        $this->data['breadcrumb'] = array("Size Chart" => "");

        $this->sizechart_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("Size Chart")->setAddLabel("Upload")
            ->setHeadingTitle(array("Record #", "Client Name", "Note", "Created At"))
            ->setHeadingWidth(array(2, 2, 3, 2, 2));
        $this->va_list->setDropdownFilter(1, array("name" => $this->sizechart_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
        $this->data['script'] = $this->load->view("script/sizechart_list", array("ajaxSource" => site_url("sizechart/sizeChartList")), true);
        $this->load->view("template", $this->data);
    }

    public function sizeChartList(){
        $data = $this->sizechart_m->getSizeChartList();
        echo json_encode($data);
    }

    public function add(){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Upload File";
        $this->data['breadcrumb'] = array("Size Chart" => "sizechart", "Upload File" => "");
        $this->data['formTitle'] = "Upload File";
        $this->load->library("va_input", array("group" => "sizechart"));

        $flashData = $this->session->flashdata("sizechartError");
        if ($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];

        } else {
            $msg = $value = array();
        }

        $this->va_input->addHidden(array("name" => "method", "value" => "new"));
        $this->va_input->addSelect(array("name" => "client_id", "label" => "Client *", "list" => $this->client_m->getClientCodeList()));
        $this->va_input->addTextarea(array("name" => "note", "placeholder" => "Note", "help" => "Note", "label" => "Note"));
        $this->va_input->addCustomField(array("name" => "userfile", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" => @$msg ?: @$msg['userfile'][0] ?: @$msg['userfile'][1], "label" => "Upload File *", "view" => "form/upload_sizechart.php"));

        $this->data['script'] = $this->load->view("script/ctr_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            redirect("sizechart/add");
        }
        $post = $this->input->post("sizechart");

        if (empty($post)) {
            redirect("sizechart/add");
        }

        if ($post['method'] == "new") {
            //validate file csv first
            $this->_saveNew($post);
        }
        else {
            redirect('sizechart');
        }
    }

    private function _saveNew($post){
        $msg = null;
        $msg = $this->uploadFile();

        if ($msg['error'] == true) {
            // upload failed
            $result['userfile'][0] = $msg['data'];
            $this->session->set_flashdata(array("sizechartError" => json_encode(array("msg" => $result, "data" => $post))));
            unlink($msg['data']['full_path']);
            redirect("sizechart/add");
        } else {
            $fileData = $msg['data'];
        }

        if ($this->va_csv->get_array($fileData['full_path'])) {
            $post['userfile'] = $fileData['file_name'];
            $post['note'] = $post['note'];

            $brand_code=$this->clientoptions_m->get($post['client_id'], 'brand_initial');
            $cekAvailable= $this->sizechart_m->cekAvailable($brand_code['option_value']);
            $cekMap=$this->sizechart_m->cekMap($post['client_id']);

            if(!empty($cekAvailable) && !empty($cekMap)){
                $avail="client has availabe";
                $this->session->set_flashdata(array("sizechartError" => json_encode(array("msg" => $avail, "data" => $post))));
                unlink($fileData['full_path']);
                redirect('sizechart/add');
            }
            else{
                $csv_array = $this->va_csv->get_array($fileData['full_path']);
                foreach ($csv_array as $row) {
                    $post['attribute_set'] = $row['attribute_set'];
                    $post['brand_size'] = $row['brand_size'];
                    $post['brand_size_system'] = $row['brand_size_system'];
                    $post['paraplou_size'] = $row['paraplou_size'];
                    $post['position'] = $row['position'];
                    $post['brand_code']=$brand_code['option_value'];
                    $result = $this->sizechart_m->saveFile($post);
                }

                if (is_numeric($result)) {
                    $this->sizechart_m->saveImport($post, $brand_code['option_value']);
                    redirect("sizechart");
                } else {
                    $this->sizechart_m->deleteTemp($post['client_id']);
                    $this->session->set_flashdata(array("sizechartError" => json_encode(array("msg" => $result, "data" => $post))));
                    unlink($fileData['full_path']);
                    redirect("sizechart/add");
                }
            }
        }
    }

    public function uploadFile(){
        $return = array('error' => false, 'data' => array());

        $config['upload_path'] = '../public/merchandising/size_chart/';
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

    public function export($client_id){
        $clientname=$this->client_m->getClientById($client_id)->row_array();
        $data = $this->sizechart_m->export($client_id);
        if (empty($data)) {
            redirect('sizechart');
        } else {
            $delimiter = ",";
            $newline = "\r\n";
            $this->load->dbutil();
            $datas = $this->dbutil->csv_from_result($data, $delimiter, $newline);
            force_download('paraplou_size_chart_'.$clientname['client_code'].'.csv', $datas);
        }
    }

    public function delete($client_id){
        $this->sizechart_m->delete($client_id);
        redirect('sizechart');
    }

    public function view ($client_id) {
        $data = $this->sizechart_m->getSizeChartById($client_id);
        if($data->num_rows() < 1) {
            redirect("sizechart");
        }
        $clientname=$this->client_m->getClientById($client_id)->row_array();

        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Client Options";
        $this->data['breadcrumb'] = array("Size Chart"=> "sizechart", "View Size Chart" => "");
        $this->data['formTitle'] = "View Size Chart";

        $this->load->library("va_input", array("group" => "sizechart"));

        $flashData = $this->session->flashdata("clientError");
        if($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = array();
            $value=$data->result_array();
        }

        $this->va_input->addHidden( array("name" => "method", "value" => "save") );
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value"=>$clientname['client_code'],"disabled"=>"disabled"));
        $this->va_input->addCustomField( array("name" =>"size_chart", "placeholder" => "Size Chart", "label" => "Size Chart", "value" =>$value, "view"=>"form/customSizeChart"));
        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }
}