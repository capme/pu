<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Modulemanagement_m $modulemanagement_m
 *
 */
class ModuleManagement extends MY_Controller 
{
var $data = array();
	public function __construct()
	{
		$this->allow_group("1");
		$this->allow_group("cadmin");
		
		parent::__construct();
		$this->data['pagegroup'] = 4;
		$this->load->model("modulemanagement_m");
		$this->data['page'] = "modulemanagement";
	}
	public function index()
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Module Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "Module Management" => "");
		
		$this->modulemanagement_m->clearCurrentFilter();		
		$this->load->library("va_list");
		$this->va_list->setListName("Module Management")
		->setAddLabel("New Module")
		->setMassAction(array("1" => "Active", "0" => "Not Active", "2" => "Remove"))
		->setHeadingTitle(array("No", "Module Name","URL","Sort","Parent","Status","Visibility"))
		->setHeadingWidth(array(5, 15,5,5,5,5,5));
		
		$this->va_list->setInputFilter(1, array("name" => $this->modulemanagement_m->filters['name']));
		$this->va_list->setDropdownFilter(4, array("name" => $this->modulemanagement_m->filters['parent'], "option" => $this->getParentList()));
		$this->va_list->setDropdownFilter(5, array("name" => $this->modulemanagement_m->filters['status'], "option" => $this->getStatus()));
		$this->va_list->setDropdownFilter(6, array("name" => $this->modulemanagement_m->filters['hidden'], "option" => $this->getVisibility()));
		$this->data['script'] = $this->load->view("script/modulemanagement_list", array("ajaxSource" => site_url("modulemanagement/ModuleList")), true);		
		$this->load->view("template", $this->data);
	}
	
	public function ModuleList()
	{
	$sAction = $this->input->post("sAction");
		if($sAction == "group_action")
		{
			$ids = $this->input->post("id");
			if(sizeof($ids) > 0) 
			{
				$action = $this->input->post("sGroupActionName");
				$this->modulemanagement_m->massUpdate($ids, $action);
			}
		}	
		$data = $this->modulemanagement_m->getModuleManagementList();	
		echo json_encode($data);
	}
	public function add()
	{
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Module Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "Module Management" => "");
		$this->data['formTitle'] = "Add Module";
		$this->load->library("va_input", array("group" => "user"));
		
		$flashData = $this->session->flashdata("userError");
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
		$this->va_input->addInput( array("name" => "module", "placeholder" => "name of module", "help" => "Module Name", "label" => "Module Name *", "value" => @$value['module'], "msg" => @$msg['module']) );
		$this->va_input->addSelect( array("name" => "parent", "list" => $this->getParentList(), "value" => @$value['parent'], "msg" => @$msg['parent'], "label" => "Parent *") );
		$this->va_input->addInput( array("name" => "icon", "placeholder" => "fa-user", "help" => "class name for the icon see <a href='http://fortawesome.github.io/Font-Awesome/icons/' target='_blank'>http://fortawesome.github.io/Font-Awesome/icons/</a>", "label" => "Icon", "value" => @$value['icon'], "msg" => @$msg['icon']) );
		$this->va_input->addInput( array("name" => "url", "placeholder" => "url", "help" => "URL", "label" => "URL *", "value" => @$value['url'], "msg" => @$msg['url']) );
		$this->va_input->addInput( array("name" => "sort", "placeholder" => "Sort", "help" => "Sort", "label" => "Sort *", "value" => @$value['sort'], "msg" => @$msg['sort']) );
		$this->va_input->addSelect( array("name" => "hidden", "label" => "Visibility *", "list" => array("0" => "Visible", "1" => "Hidden"), "value" => @$value['hidden'], "msg" => @$msg['hidden']) );
		$this->va_input->addSelect( array("name" => "status", "label" => "Status *", "list" => array("0" => "Not Active", "1" => "Active"), "value" => @$value['status'], "msg" => @$msg['status']) );
		$this->data['script'] = $this->load->view("script/modulemanagement_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function getParentList($list = array(), $addjust = "", &$option = array())
	{
		if(empty($list)) {
			$list = $this->modulemanagement_m->getStructuredModule();
			if(!isset($option)) $option = array();
			
			$option = array( "" => "Select Parent");
		}
		
		foreach($list as $k => $l) {
			$option[$k] = $addjust . $l['name'];
			if( isset($l['child']) && sizeof($l['child']) ) {
				$addjust .= "&nbsp;&nbsp;&nbsp;";
				$this->getParentList($l['child'], $addjust, $option);
				$addjust = substr($addjust, 0, -18);
			}
			
		}
		
		return $option;
	}
	
	public function save ()
	{
	if($_SERVER['REQUEST_METHOD'] != "POST") 
		{
			redirect("modulemanagement/add");
		}		
		$post = $this->input->post("user");
		
		if(empty($post)) 
		{
			redirect("modulemanagement/add");
		}
		
		if($post['method'] == "new") 
		{
			$result = $this->modulemanagement_m->newModule( $post );
			if(is_numeric($result)) 
			{
				redirect("modulemanagement");
			} 
			else 
			{
				$this->session->set_flashdata( array("userError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("modulemanagement/add");
			}
		}
		else if($post['method'] == "update") 
		{
			$result = $this->modulemanagement_m->updateModule( $post );

			if(is_numeric($result)) 
			{
				redirect("modulemanagement");
			} 
			else 
			{
			$this->session->set_flashdata( array("userError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("modulemanagement");
			}
		}
	}
	
	public function viewmodulemanagement($id)
	{
	$data = $this->modulemanagement_m->getModuleManagement($id);
		if($data->num_rows() < 1) 
		{
			redirect("api");
		}		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Module Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "Module Management" => "modulemanagement");
		$this->data['formTitle'] = "View Module";
		
		$this->load->library("va_input", array("group" => "user"));
		
		$flashData = $this->session->flashdata("userError");
		if($flashData !== false) 
		{
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} 
		else
		{
			$msg = array();
			$value = $data->row_array();
		}
		
		$this->va_input->addHidden( array("name" => "method", "value" => "update") );
		$this->va_input->addHidden( array("name" => "id", "value" => $value['id']) );
		$this->va_input->addInput( array("name" => "module", "placeholder" => "module name", "help" => "Name of Module", "label" => "Module Name *", "value" => @$value['name'], "msg" => @$msg['name']) );
		$this->va_input->addSelect( array("name" => "parent", "list" => $this->getParentList(), "value" => @$value['parent'], "msg" => @$msg['parent'], "label" => "Parent *") );
		$this->va_input->addInput( array("name" => "icon", "placeholder" => "fa-user", "help" => "class name for the icon see <a href='http://fortawesome.github.io/Font-Awesome/icons/' target='_blank'>http://fortawesome.github.io/Font-Awesome/icons/</a>", "label" => "Icon", "value" => @$value['icon'], "msg" => @$msg['icon']) );
		$this->va_input->addInput( array("name" => "url", "placeholder" => "URL", "help" => "URL", "label" => "URL *", "value" => @$value['slug'], "msg" => @$msg['slug']) );
		$this->va_input->addInput( array("name" => "sort", "placeholder" => "Sort", "help" => "Sort", "label" => "Sort *", "value" => @$value['sort'], "msg" => @$msg['sort']) );
		$this->va_input->addSelect( array("name" => "hidden", "label" => "Visibility *", "list" => array("0" => "Visible", "1" => "Hidden"), "value" => @$value['hidden'], "msg" => @$msg['hidden']) );
		$this->va_input->addSelect( array("name" => "status", "label" => "Status *", "list" => array("0" => "Not Active", "1" => "Active"), "value" => @$value['status'], "msg" => @$msg['status']) );
		
		$this->data['script'] = $this->load->view("script/user_add", array(), true);		
		// $this->load->view('customers_v', $this->data);
		$this->load->view('template', $this->data);
	}
	
	public function deletemodulemanagement($id)
	{
	$data = $this->modulemanagement_m->deleteModulemManagement($id);
	redirect('modulemanagement');
	}
	
	public function getStatus()
	{
	$status=array(""=>"Select Status",1=>"Active",0=>"Not active");
	return $status;
	}
	
	public function getVisibility()
	{
		$visible=array(""=>"Select Visibility",1=>"Visible",0=>"Hidden");
		return $visible;
	}
	

}


