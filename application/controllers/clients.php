<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Clients extends MY_Controller {
	var $data = array();

	public function __construct()
	{
		parent::__construct();
		$this->load->model("client_m");
	}

	public function index() 
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Client Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "Client Management" => "");
	
		$this->client_m->clearCurrentFilter();
	
		$this->load->library("va_list");
		$this->va_list->setListName("Client Listing")->setAddLabel("New Client")->setMassAction(
				array("2" => "Remove")
		)->setHeadingTitle(
				array("Record #", "Client Name", "Mage API","Mage WSDL")
		)->setHeadingWidth(
				array(5, 10, 10,10)
		);
		
		$this->va_list->setInputFilter(1, array("name" => $this->client_m->filters['client_code']));			
		$this->data['script'] = $this->load->view("script/client_list", array("ajaxSource" => site_url("clients/clientList")), true);
	
		$this->load->view("template", $this->data);
	}
	
	public function add()
	{
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Client Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "Client Management" => "");
		$this->data['formTitle'] = "Add Client";

		$this->load->library("va_input", array("group" => "client"));
		$flashData = $this->session->flashdata("clientError");		
		
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
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Name of new client", "label" => "Client Name *", "value" => @$value['client_code'], "msg" => @$msg['client_code']) );
		$this->va_input->addInput( array("name" => "mage_wsdl", "placeholder" => "Magento Wsdl", "help" => "Name of magento wsdl", "label" => "Magento Wsdl *", "value" => @$value['mage_wsdl'], "msg" => @$msg['mage_wsdl']) );
		$this->va_input->addInput( array("name" => "mage_auth", "placeholder" => "mageuser:magepass", "label" => "Magento API Auth", "value" => @$value['mage_auth'], "msg" => @$msg['mage_auth'], "help" => "Magento API auth (user:pass)") );
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save() 
	{
		if($_SERVER['REQUEST_METHOD'] != "POST") {
			redirect("clients/add");
		}
		
		$post = $this->input->post("client");
		if(empty($post)) {
			redirect("clients/add");
		}
		
		if($post['method'] == "new") {
			$result = $this->client_m->newClient( $post );
			
			if(is_numeric($result)) {
				redirect("clients");
			} else {
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("clients/add");
			}
		} else if($post['method'] == "update") {
			$result = $this->client_m->updateClient( $post );
			if(is_numeric($result)) 
			{
				redirect("clients");
			} 
			else 
			{
				$this->session->set_flashdata( array("clientError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("clients/view/".$post['id']);
			}
		}
	}
	
	public function view( $id )
	{
		$data = $this->client_m->getClientById($id);
		if($data->num_rows() < 1) {
			redirect("clients");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Client Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "Client Management" => "");
		$this->data['formTitle'] = "View Client";
	
		$this->load->library("va_input", array("group" => "client"));
		$flashData = $this->session->flashdata("clientError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
	
		$this->va_input->addHidden( array("name" => "method", "value" => "update") );
		$this->va_input->addHidden( array("name" => "id", "value" => $value['id']) );
		$this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Name of new client", "label" => "Client Name *", "value" => @$value['client_code'], "msg" => @$msg['client_code']) );
		$this->va_input->addInput( array("name" => "mage_auth", "placeholder" => "mageuser:magepass", "label" => "Magento API Auth", "value" => @$value['mage_auth'], "msg" => @$msg['mage_auth'], "help" => "Magento API auth (user:pass)") );
		$this->va_input->addInput( array("name" => "mage_wsdl", "placeholder" => "Magento Wsdl", "help" => "Name of magento wsdl", "label" => "Magento Wsdl *", "value" => @$value['mage_wsdl'], "msg" => @$msg['mage_wsdl']) );
		$this->data['script'] = $this->load->view("script/client_add", array(), true);

		$this->load->view('template', $this->data);
	}
	
	public function clientList() 
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action") {
			$id = $this->input->post("id");
			if(sizeof($id) > 0) {
				$action = $this->input->post("sGroupActionName");
				$this->client_m->removeClient($id, $action);
			}
		}	
		$data = $this->client_m->getClientList();	
		echo json_encode($data);
	}
	
	
	
}