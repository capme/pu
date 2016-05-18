<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property leger_m
 * @property Va_list $va_list
 * @property Leger_m $leger_m
 *
 */
class Ringkasandata extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("leger_m") );
        $this->load->library('va_excel');
	}
	
	public function index(){
		$this->data['content'] = "list_v.php";
		//$this->data['pageTitle'] = "Leger Jalan";
		$this->data['breadcrumb'] = array("List Ringkasan Data"=>"ringkasandata");
		
		$this->leger_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("Ringkasan Data")
			->setHeadingTitle(array("Record #", "Distribusi Ke","No Lembar","Nama Provinsi"))
			->setHeadingWidth(array(2, 2,2,4));
		
		$this->va_list->setInputFilter(1, array("name" => $this->leger_m->filters['distribusi_ke']));
        $this->va_list->setInputFilter(2, array("name" => $this->leger_m->filters['no_lembar']));
        $this->va_list->setInputFilter(3, array("name" => $this->leger_m->filters['nama_prov']));

		$this->data['script'] = $this->load->view("script/RingkasanData_list", array("ajaxSource" => site_url("ringkasandata/RingkasanDataList")), true);
		$this->load->view("template", $this->data);
	}
	
	public function RingkasanDataList(){
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}	
		$data = $this->leger_m->getRingkasanDataList();
		echo json_encode($data);
	}
    
    public function delete($id){
        $data = $this->leger_m->deleteRingkasanData($id);
    	redirect('ringkasandata');
    }
    
    public function download($id){
        $name=$this->leger_m->getRingkasanDataById($id);
        $base=site_url();
        $data = file_get_contents($base."/inbound/catalog_product/".$name['filename']);
        force_download($name['filename'],$data);       
    }

    public function add(){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Upload File";
        $this->data['breadcrumb'] = array("Inbound"=> "inbound", "Upload File" => "");
        $this->data['formTitle'] = "Upload File";
        $this->load->library("va_input", array("group" => "inbound"));

        $flashData = $this->session->flashdata("inboundError");
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
        $this->va_input->addSelect( array("name" => "client","label" => "Client *", "list" => $this->client_m->getClientCodeList(), "value" => @$value['client'], "msg" => @$msg['client']) );
        $this->va_input->addTextarea( array("name" => "note", "placeholder" => "Note", "help" => "Note", "label" => "Note", "value" => '', "msg" => @$msg['note']) );
        $this->va_input->addCustomField( array("name" =>"userfile", "placeholder" => "Upload File ", "value" => @$value['userfile'], "msg" => @$msg['userfile'][0]?:@$msg['userfile'][1], "label" => "Upload File *", "view"=>"form/upload_inbound"));
        $this->data['script'] = $this->load->view("script/inbound_add", array(), true);
        $this->load->view('template', $this->data);
    }
}
?>
