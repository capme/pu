<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Brandcode extends MY_Controller {
	var $data = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model("brandcode_m");
		$this->load->model("clientoptions_m");
	}

	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Update Brand Code";
		$this->data['breadcrumb'] = array("Operation"=> "", "Update Brand Code" => "");
	
		$this->brandcode_m->clearCurrentFilter();
	
		$this->load->library("va_list");
		$this->va_list->disableAddPlugin()->setListName("Brand Code")->setHeadingTitle(
				array("Record #", "Client"))
				->setHeadingWidth(array(5, 10));
		
		$this->va_list->setInputFilter(1, array("name" => $this->brandcode_m->filters['client_code']));			
		$this->data['script'] = $this->load->view("script/brandcode_list", array("ajaxSource" => site_url("brandcode/brandCodeList")), true);
	
		$this->load->view("template", $this->data);
	}
	
	public function brandCodeList() 
	{
		$data = $this->brandcode_m->getBrandCodeList();	
		echo json_encode($data);
	}	
	
	public function update($id){	
		$data = $this->clientoptions_m->getClientById($id);		
		if($data->num_rows() < 1) {
			redirect("brandcode");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Update Brand Code";
		$this->data['breadcrumb'] = array("Operation"=> "", "Update Brand Code" => "");
		$this->data['formTitle'] = "Update Brand Code";
	
		$this->load->library("va_input", array("group" => "brandcode"));
				
		$flashData = $this->session->flashdata("brandcodeError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value=$data->row_array();
		}
		
		$id= $value['id'];
		$clientop = $this->brandcode_m->getOptions($id);	
		$this->va_input->addHidden( array("name" => "method", "value" => "save") );		
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value"=>$value['client_code'], "msg" => @$msg['client_code'], "disabled"=>"disabled"));
		$this->va_input->addCustomField( array("name" =>"", "placeholder" => "Client Options", "value" =>$clientop, "view"=>"form/customBrandCode"));
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save(){
	$post = $this->input->post("brandcode");
		if(empty($post)) {
			redirect("brandcode");
		}
		else if($post['method'] == "save") {
		$id=$post['id'];
			$cod=array();
			$brnd=array();
		if (isset($post['cek'])){			
			for($i=0; $i < count($post['brands']); $i++ ){				
				$code=strtoupper($post['key'][$i]);
				$brand=strtolower(str_replace(" ","",$post['brands'][$i]));
				$cod = array_merge($cod, array($code));
				$brnd=array_merge($brnd, array($brand));
			}
			
			for($cek=0; $cek < count($post['cek']); $cek++){
					$key_cek=array_keys($post['cek']);
					unset($cod[$key_cek[$cek]]);
					unset($brnd[$key_cek[$cek]]);
					}
			$data=array_combine($cod,$brnd);
			}
		else{
			for($i=0; $i < count($post['brands']); $i++ ){
				$code=strtoupper($post['key'][$i]);
				$brand=strtolower(str_replace(" ","",$post['brands'][$i]));
				$cod = array_merge($cod, array($code));
				$brnd=array_merge($brnd, array($brand));				
				}			
				$data=array_combine($cod,$brnd);			
			}
		$this->brandcode_m->updateBrand($id,$data);
		redirect('brandcode');
		}
	}
}