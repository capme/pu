<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Listinbounddoc extends MY_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model( array("users_m", "client_m", "inbounddocument_m") );
	}
	
	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Inbound Document";
		$this->data['breadcrumb'] = array("Inbound Document" => "");
		
		$this->inbounddocument_m->clearCurrentFilter();
				
		$this->load->library("va_list");
		$this->va_list->setListName("Inbound Document Listing")->disableAddPlugin()
			->setHeadingTitle(array("No #", "Client Name","DO Number","Note"))
			->setHeadingWidth(array(2, 2,2,3,2));
		$this->va_list->setDropdownFilter(1, array("name" => $this->inbounddocument_m->filters['client_id'], "option" => $this->client_m->getClientCodeList(TRUE)));
		
		$this->data['script'] = $this->load->view("script/inbounddocument_list", array("ajaxSource" => site_url("listinbounddoc/inboundDocList")), true);	
		$this->load->view("template", $this->data);
	}
	
	public function inboundDocList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
			}
		}	
		$data = $this->inbounddocument_m->getInboundDocumentList();	
		echo json_encode($data);
	}
	
}
?>