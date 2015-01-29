<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Mageapi $mageapi
 *
 */
class Clientoptions extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->load->model("clientoptions_m");
		$this->load->model("users_m");
		$this->load->model("client_m");
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Client Options";
		$this->data['breadcrumb'] = array("Clients Options" => "clientoptions");
		
		$this->clientoptions_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->setListName("Clients Options")->setAddLabel("New Client Option")
		->setHeadingTitle(array("Record #", "Client Name"))
		->setHeadingWidth(array(2,3));
		
		$this->va_list->setDropdownFilter(1, array("name" => $this->clientoptions_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));;
	
		$this->data['script'] = $this->load->view("script/ClientOptionsList", array("ajaxSource" => site_url("clientoptions/ClientOptionsList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function ClientOptionsList()
	{	
		$data = $this->clientoptions_m->getClientOptions();	
		echo json_encode($data);
	}
	
	public function view($id){
		$data = $this->clientoptions_m->getClientById($id);		
		if($data->num_rows() < 1) {
			redirect("clientoptions");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Client Options";
		$this->data['breadcrumb'] = array("Client Options"=> "clientoptions", "View Client Options" => "");
		$this->data['formTitle'] = "View Client Options";
	
		$this->load->library("va_input", array("group" => "clientoptions"));
				
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value=$data->row_array();
		}
		
		$id= $value['id'];
		$clientop = $this->clientoptions_m->getOptions($id);	
		$this->va_input->addHidden( array("name" => "method", "value" => "save") );		
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value"=>$value['client_code'], "msg" => @$msg['client_code'], "disabled"=>"disabled"));
		$this->va_input->addCustomField( array("name" =>"options", "placeholder" => "Client Options", "label" => "Option Name", "value" =>$clientop, "view"=>"form/customClient"));
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
		
	}
	
	public function save()
	{
	$post = $this->input->post("clientoptions");
	if(empty($post)) {
			redirect("clientoptions");
		}
		else if($post['method'] == "save") {
		foreach ($_POST as $f =>$options){	
		}
		
		if (isset($options['cek'])){	
			foreach($options['cek'] as $d=>$h){					
			$iddelete= $options['cek'][$d];				
			unset($options['option_value'][$iddelete]);	
			$data = array ('delete'=>$iddelete);
			
			$this->clientoptions_m->clientOptionDelete($data);
			}
			
			foreach ($options['option_value'] as $idupdate => $s ){
			$val = $options['option_value'][$idupdate];			
			$data = array ('value'=>$val, 'update'=>$idupdate);	
			$this->clientoptions_m->clientOptionSave($data);	
			}
			 redirect("clientoptions");
			}
		else {
			foreach ($options['option_value'] as $s => $g){
			$data = array ('id'=> $s, 'value'=>$g);
			$this->clientoptions_m->clientOptionUpdate($data);
			}			
			redirect("clientoptions");			
			}
			
		}
		else if ($post['method'] == "new"){
			$result = $this->clientoptions_m->newClientOptions( $post );		
			if(is_numeric($result)) {
					redirect("clientoptions");
				} else {
					$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
					redirect("clientoptions/add");
				}
		}
		
	}
	
	public function add(){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Add Client Options";
		$this->data['breadcrumb'] = array("Client Options"=> "clientoptions", "Add Client Options" => "");
		$this->data['formTitle'] = "Add Client Ooptions";
		$this->load->library("va_input", array("group" => "clientoptions"));
		
		$flashData = $this->session->flashdata("awbError");
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
		$this->va_input->addInput( array("name" => "option_name", "placeholder" => "Option Name", "help" => "Option Name", "label" => "Option Name *", "value" => @$value['option_name'], "msg" => @$msg['option_name']) );
		$this->va_input->addInput( array("name" => "option_value", "placeholder" => "Option Value", "help" => "Option Value", "label" => "Option Value *", "value" => @$value['option_value'], "msg" => @$msg['option_value']) );
		$this->data['script'] = $this->load->view("script/clientoptions_add", array(), true);
		$this->load->view('template', $this->data);
	}
}