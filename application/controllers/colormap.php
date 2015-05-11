<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 *
 * @property Va_list $va_list
 * @property client_m
 * @property Clientoptions_m $clientoptions_m
 * @property Colormap_m $colormap_m
 *
 */
class Colormap extends MY_Controller {
    var $data = array();
    public function __construct(){
        parent::__construct();
        $this->load->model( array('colormap_m', 'inbounddocument_m'));
        $this->load->library('va_excel');
    }

    public function index(){
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Color Map";
        $this->data['breadcrumb'] = array("Merchandising"=>"","Color Map"=>"colormap");

        $this->colormap_m->clearCurrentFilter();
        $this->load->library("va_list");

        $this->va_list->disableAddPlugin()->setListName("Color Map")
            ->setMassAction(array("1" => "Import", "2" => "Export"))
            ->setHeadingTitle(array("Original Color","Map Color","Color Code"))
            ->setHeadingWidth(array(2,2,2,2,2));

        $this->va_list->setInputFilter(0, array("name" => $this->colormap_m->filters['original_color']))
            ->setInputFilter(1, array("name" => $this->colormap_m->filters['color_map']))
            ->setInputFilter(2, array("name" => $this->colormap_m->filters['color_code']));

        $this->data['script'] = $this->load->view("script/colormap_list", array("ajaxSource" => site_url("colormap/colorMapList")), true);
        $this->load->view("template", $this->data);
    }

    public function colorMapList(){
        $sAction = $this->input->post("sAction");
        if($sAction == "group_action") {
           $action = $this->input->post("sGroupActionName");
           if($action == 1){
              $this->add();
           }
            else{
                $this->export();
            }
        }

        $data = $this->colormap_m->getColorMapList();
        echo json_encode($data);
    }

    public function add(){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Upload File";
        $this->data['breadcrumb'] = array("Merchandising" => "", "Upload File" => "");
        $this->data['formTitle'] = "Upload File";
        $this->load->library("va_input", array("group" => "colormap"));

        $flashData = $this->session->flashdata("colormapError");

        if ($flashData !== false) {
            $flashData = json_decode($flashData, true);
            $value = $flashData['data'];
            $msg = $flashData['msg'];
        } else {
            $msg = $value = array();
        }

        $this->va_input->addHidden(array("name" => "method", "value" => "new"));
        $this->va_input->addCustomField( array("name" =>"userfile", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" =>@$msg['userfile'][0]?:@$msg['userfile'][1], "label" => "Upload File *", "view"=>"form/upload_colormap"));
        $this->va_input->addCustomField( array("name" =>"","msg" =>$msg, "view"=>"form/span"));

        $this->data['script'] = $this->load->view("script/colormap_add", array(), true);
        $this->load->view('template', $this->data);
    }

    public function export(){
       $getdata = $this->colormap_m->getDataColor();
       $header=array(array("original color","color map","color code"));
       $add=array(array("1","2","3"));
       $data = array_merge($add,$header,$getdata);

    // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $a=1;
        $sheet=$objPHPExcel->getActiveSheet(0);
        for($i=2; $i < count($data); $i++){
            $sheet->setCellValue('B1', $data[1][0])
                ->setCellValue('C1', $data[1][1])
                ->setCellValue('D1', $data[1][2])
                ->setCellValue('A1', 'No')
                ->setCellValue('A'.$i, $a++)
                ->setCellValue('B'.$i, $data[$i]['original_color'])
                ->setCellValue('C'.$i, $data[$i]['color_map'])
                ->setCellValue('D'.$i, $data[$i]['color_code']);
        }

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="color map.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;

    }

    public function save(){
        if($_SERVER['REQUEST_METHOD'] != "POST") {
            redirect("colormap/add");
        }
        $post = $this->input->post("colormap");
        if(empty($post)) {
            redirect("colormap/add");
        }
        if($post['method'] == "new"){
            $this->saveColor($post);
        }
    }

    private function saveColor($post) {
        $msg = null;
        $msg = $this->doUploadFile();

        if($msg['error'] == true) {
            // upload failed
            $result['userfile'][0] = $msg['data'];
            $this->session->set_flashdata( array("colormapError" => json_encode(array("msg"=>$result, "data" => $post))));
            redirect("colormap/add");
        } else {
            $fileData = $msg['data'];
        }

        $truncate=$this->colormap_m->truncate();
        $colormap = $this->_validate($fileData);
        $basicColor = $this->inbounddocument_m->getMapColor();
        $errorMsg = array();

        foreach($colormap as $colorex){
            $colorex['C'] = trim(strtoupper($colorex['C']));
            if(!isset($basicColor[$colorex['C']])) {
                $errorMsg[] = "Color map " . $colorex['C'] .' is unsupported';
            }
           $original_color[]=strtoupper(trim($colorex['B']));
           $mapping_color[]=strtoupper(trim($colorex['C']));
           $color_code[]=strtoupper(trim($colorex['D']));

        }

        if(empty($errorMsg)) {
            for($i=0; $i < count($original_color); $i++){
                $result =$this->colormap_m->savefile($original_color[$i], $mapping_color[$i], $color_code[$i]);
            }
        } else {
            $result = false;
        }

        if(is_numeric($result)){
            redirect('colormap');
        }
        else {
            $result['userfile'][0]= $this->upload->display_errors();
            $this->session->set_flashdata( array("colormapError" => json_encode(array("msg"=>@$errorMsg, "data" => $post))));
            redirect("colormap/add");
        }
    }

    private function doUploadFile() {
        $return = array('error' => false, 'data' => array());
        $config['upload_path'] = '../public/merchandising/color_map/';
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
