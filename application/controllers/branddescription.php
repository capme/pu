<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @property Va_list $va_list
 * @property client_m
 * @property Clientoptions_m $clientoptions_m
 * @property Branddescription_m $branddescription_m
 *
 */
class Branddescription extends MY_Controller {
    var $data = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->model( array("client_m", "branddescription_m","clientoptions_m") );
        $this->load->library('va_excel');
    }

    public function index(){
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Brand Description";
        $this->data['breadcrumb'] = array("Content"=>"","Brand Description"=>"branddescription");

        $this->branddescription_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("Brand Description")->setAddLabel("Upload")
            ->setHeadingTitle(array("Record #", "Client Name","Upadated At"))
            ->setHeadingWidth(array(2,2,2,2));
        $this->va_list->setDropdownFilter(1, array("name" => $this->branddescription_m->filters['client_id'], "option" => $this->client_m->getBrandDescriptionList(TRUE)));;

        $this->data['script'] = $this->load->view("script/branddescription_list", array("ajaxSource" => site_url("branddescription/brandDescriptionList")), true);
        $this->load->view("template", $this->data);
    }

    public function brandDescriptionList(){
        $sAction = $this->input->post("sAction");
        if($sAction == "group_action") {
            $id = $this->input->post("id");
            if(sizeof($id) > 0) {
                $action = $this->input->post("sGroupActionName");
            }
        }
        $data = $this->branddescription_m->getBrandDescriptionList();
        echo json_encode($data);
    }

    public function add(){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Upload File";
        $this->data['breadcrumb'] = array("Brand Description" => "branddescription", "Upload File" => "");
        $this->data['formTitle'] = "Upload File";
        $this->load->library("va_input", array("group" => "branddescription"));

        $flashData = $this->session->flashdata("branddescriptionError");
        if ($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = $value = array();
        }

        $this->va_input->addHidden(array("name" => "method", "value" => "new"));
        $this->va_input->addSelect(array("name" => "client", "label" => "Client *", "list" => $this->client_m->getBrandDescriptionList(), "value" => @$value['client']));
        $this->va_input->addSelect( array("name" => "type", "label" => "Action type *", "list" => array("0" => "Append", "1" => "Replace"), "value" => @$value['type'], "msg" => @$msg['type']) );
        $this->va_input->addCustomField( array("name" =>"userfile", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" =>@$msg['userfile'][0]?:@$msg['userfile'][1], "label" => "Upload File *", "view"=>"form/upload_brands"));
        $this->va_input->addCustomField( array("name" =>"","msg" =>$msg, "view"=>"form/span"));

        $this->data['script'] = $this->load->view("script/branddescription_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function save(){
        if($_SERVER['REQUEST_METHOD'] != "POST") {
            redirect("branddescription/add");
        }
        $post = $this->input->post("branddescription");
        if(empty($post)) {
            redirect("branddescription/add");
        }
        if($post['method'] == "new"){
            $this->saveBrands($post);
        }
    }

    public function view($id){
        $data = $this->branddescription_m->getBrandDescriptionById($id);
        if($data->num_rows() < 1) {
            redirect("branddescription");
        }
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "View Brand Description";
        $this->data['breadcrumb'] = array("Brand Description" => "branddescription", "View Brand Description" => "");
        $this->data['formTitle'] = "View Brand Description";
        $this->load->library("va_input", array("group" => "branddescription"));
        $value = $data->result_array();

        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value" => @$value[0]['client_code'], "disabled"=>"disabled"));
        $this->va_input->addCustomField( array("name" =>"", "value" =>$value, "view"=>"form/customBrandDescription"));

        $this->data['script'] = $this->load->view("script/branddescription_add", array(), true);
        $this->load->view('template', $this->data);
    }

    private function saveBrands($post) {
        $msg = null;
        $msg = $this->doUploadFile();
        if($msg['error'] == true) {
            // upload failed
            $result['userfile'][0] = $msg['data'];
            $this->session->set_flashdata( array("branddescriptionError" => json_encode(array("msg"=>$result, "data" => $post))));
            redirect("branddescription/add");
        } else {
            $fileData = $msg['data'];
        }

        $brnddesc=$this->clientoptions_m->getBrandCode($post['client']);
        $bcode=(array)json_decode($brnddesc[0]['option_value']);
        $branddesc = $this->_validate($fileData);
           foreach($branddesc as $brand => $brandcode){
               $code[]=ucfirst(strtolower(trim($brandcode['B'])));
               $description[]=$brandcode['C'];
               $description_en[]=$brandcode['D'];
           }

        if($post['type'] == 0) {
            $failed = array_values(array_intersect($bcode, $code));
            if (count($failed) > 0) {
                for ($fail = 0; $fail < count($failed); $fail++) {
                    $errorMsg[] = 'error brand ' . $failed[$fail];
                }
                $this->session->set_flashdata(array("branddescriptionError" => json_encode(array("msg" => $errorMsg, "data" => $post))));
                unlink($fileData['full_path']);
                redirect("branddescription/add");
            }
            else{
                $success= array_diff($code,$bcode);
                for($i=0; $i < count($success); $i++){
                    $intersec=array_values($success);
                    $post['brand_code']=$intersec[$i];
                    $post['userfile'] = $fileData['file_name'] ;
                    $post['description']=$description[$i];
                    $post['description_en']=$description_en[$i];
                    $result=$this->branddescription_m->saveFile($post);
                }
            }
        }
        else {
            $failed = array_diff($code, $bcode);
            //upload validation
            if (count($failed) > 0) {
                for ($fail = 0; $fail < count($failed); $fail++) {
                    $errorMsg[] = 'error brand ' . $failed[$fail];
                }
                $this->session->set_flashdata(array("branddescriptionError" => json_encode(array("msg" => $errorMsg, "data" => $post))));
                unlink($fileData['full_path']);
                redirect("branddescription/add");
            } else {
                $success = array_intersect($bcode, $code);
                for ($i = 0; $i < count($success); $i++) {
                    $intersec = array_keys($success);
                    $post['brand_code'] = $intersec[$i];
                    $post['userfile'] = $fileData['file_name'];
                    $post['description'] = $description[$i];
                    $post['description_en']=$description_en[$i];
                    $result = $this->branddescription_m->saveFile($post);
                }
            }
        }

       if(is_numeric($result)){
            redirect('branddescription');
        }
        else {
            $result['userfile'][0]= $this->upload->display_errors();
            $this->session->set_flashdata( array("branddescriptionError" => json_encode(array("msg"=>$result, "data" => $post))));
            redirect("branddescription/add");
        }
    }

    private function doUploadFile() {
        $return = array('error' => false, 'data' => array());

        $config['upload_path'] = '../public/content/brand_description/';
        $config['allowed_types'] = 'xls|xlsx';
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

    private function _validate($data){
        $ext = explode('.', $data['file_name']);
        if( end($ext) == 'xlsx' ){
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($data['full_path']);
        } else {
            $objPHPExcel = PHPExcel_IOFactory::load($data['full_path']);
        }

        $cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();

        foreach ($cell_collection as $cell) {
            $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
            $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
            $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
            if($row >1){
            $arr_data[$row][$column] = $data_value;}
        }

        return $arr_data;
    }
}
?>
