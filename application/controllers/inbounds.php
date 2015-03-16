<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property inbound_m
 * @property Va_list $va_list
 * @property client_m
 *
 */
class Inbounds extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("client_m", "inbound_m","clientoptions_m") );
        $this->load->library('va_excel');
	}
	
	public function index(){
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Inbound";
		$this->data['breadcrumb'] = array("Akuma" => "","Inbound"=>"inbound");
		
		$this->inbound_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->setListName("Inbound")->setAddLabel("Upload")		
			->setHeadingTitle(array("Record #", "Client Name","DO Number","Note","Created At"))
			->setHeadingWidth(array(2, 2,2,3,2,3,4));
		
		$this->va_list->setInputFilter(2, array("name" => $this->inbound_m->filters['doc_number']))
			->setDropdownFilter(1, array("name" => $this->inbound_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
	
		
		$this->data['script'] = $this->load->view("script/Inbound_list", array("ajaxSource" => site_url("inbounds/InboundList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function InboundList(){
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}	
		$data = $this->inbound_m->getInboundList();	
		echo json_encode($data);
	}
    
    public function delete($id){
        $data = $this->inbound_m->deleteInbound($id);
    	redirect('inbounds');
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
	
	public function save ()	{
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			redirect("inbound/add");
		}		
		$post = $this->input->post("inbound");
		if(empty($post)) {
			redirect("inbound/add");
		}		
		if($post['method'] == "new"){		  
			$filename=$this->_uploadFile();                        
            $post['userfile']= $filename['file_name'] ;
            $post['full_path']=$filename['full_path'];
            $result=$this->inbound_m->uploadFile($post, $filename);            
            if(is_numeric($result)){
                redirect("inbounds");
            }
            else{
                $result['userfile'][0]= $this->upload->display_errors();
                $this->session->set_flashdata( array("inboundError" => json_encode(array("msg"=>$result, "data" => $post))));
                redirect("inbounds/add");								 
			}   		
		}
	}
	
	private function _uploadFile() {
        $datestring = date("YmdHis");
 		$return = array('error' => false, 'data' => array());
		$config['upload_path'] = '../public/inbound/catalog_product/';
		$config['allowed_types'] = 'xls|xlsx';
		$config['max_size']	= '2000';
        $config['file_name'] = $datestring;
		
		$this->load->library('upload', $config);		
		if ( ! $this->upload->do_upload()) {			
			return null;
		} else {			
			$data=$this->upload->data();                       
			$dataupload=array('file_name'=>$data['file_name'], 'full_path'=>$data['full_path']);			
            return $dataupload;			
		}
	}
	
    public function download($id){
        $name=$this->inbound_m->getInboundById($id);
        $base=site_url();
        $data = file_get_contents($base."/inbound/catalog_product/".$name['filename']);
        force_download($name['filename'],$data);       
    }

	public function getClient(){
		$grup=$this->client_m->getClients();
		$opsi=array(""=>"Select Client");
		foreach($grup as $id=>$row)
		{
		$opsi[$row['id']] = $row['client_code'];
		}
		return $opsi;
	}
	
}
?>