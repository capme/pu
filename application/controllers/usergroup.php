<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * 
 * @property Va_input $va_input
 * @property Modulemanagement_m $modulemanagement_m
 * @property Usergroup_m $usergroup_m
 *
 */
class UserGroup extends MY_Controller 
{
	var $data = array();
	public function __construct()
	{
		$this->allow_group("1");
		$this->allow_group("cadmin");
		
		parent::__construct();
		$this->data['pagegroup'] = 4;
		$this->load->model("usergroup_m");
		$this->data['page'] = "usergroup";
	}
	
	public function index()
	{
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "Group Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "User Group Management" => "");
		
		$this->usergroup_m->clearCurrentFilter();		
		$this->load->library("va_list");
		$this->va_list->setListName("User Group Management")
		->setAddLabel("New Group")
		->setMassAction(array("1" => "Active", "0" => "Not Active", "2" => "Remove"))
		->setHeadingTitle(array("No", "Group Name","Status"))
		->setHeadingWidth(array(5, 20,5));
		
		$this->va_list->setDropdownFilter(2, array("name" => $this->usergroup_m->filters['status'], "option" => $this->getStatus()));			
		$this->data['script'] = $this->load->view("script/group_list", array("ajaxSource" => site_url("usergroup/GroupList")), true);		
		$this->load->view("template", $this->data);
	}
	
	public function GroupList()
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action")
		{
			$ids = $this->input->post("id");
			if(sizeof($ids) > 0) 
			{
				$action = $this->input->post("sGroupActionName");
				$this->usergroup_m->massUpdate($ids, $action);
			}
		}	
		$data = $this->usergroup_m->getGroupList();	
		echo json_encode($data);
	}
	
	public function viewusergroup($id)
	{
		$data = $this->usergroup_m->getUserGroup($id);
		if($data->num_rows() < 1) 
		{
			redirect("api");
		}		
		$this->data['content'] = "group_form_v.php";
		$this->data['pageTitle'] = "User Group Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "User Group Management" => "");
		$this->data['formTitle'] = "View User Group";
		
		$this->load->model("modulemanagement_m");
		$this->load->library("va_input", array("group" => "user"));
		$this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "User Group Info", 1 => "User Group Role") )->setActiveGroup(0);
		
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
		$this->va_input->addInput( array("name" => "name", "placeholder" => "group name", "help" => "Name of Group", "label" => "Group Name *", "value" => @$value['name'], "msg" => @$msg['name']) );
		$this->va_input->addSelect( array("name" => "status", "label" => "Group Status *", "list" => array("0" => "Not Active", "1" => "Active"), "value" => @$value['status'], "msg" => @$msg['status']) );
		$this->va_input->commitForm(0);
		$groupList = $this->modulemanagement_m->getStructuredModule();
		$this->va_input->addCustomField( array("name" => "module", "help" => "Select module for user group", "label" => "Modules", "msg" => @$msg['module'], "value" => @$value['auth_module'], "list" => $groupList, "view" => "form/custom_module") );
		$this->va_input->commitForm(1);
		
		$this->data['script'] = $this->load->view("script/group_add", array(), true);		
		// $this->load->view('customers_v', $this->data);
		$this->load->view('template', $this->data);
	}
	
	public function deleteusergroup($id)
	{
	$data = $this->usergroup_m->deleteUserGroup($id);
	redirect('usergroup');
	}
	
	public function add()
	{
		$this->data['content'] = "group_form_v.php";
		$this->data['pageTitle'] = "User Group Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "User Group Management" => "");
		$this->data['formTitle'] = "Add User Group";
		$this->load->model("modulemanagement_m");
		$this->load->library("va_input", array("group" => "user"));
		$this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "User Group Info", 1 => "User Group Role") )->setActiveGroup(0);

		
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
		$this->va_input->addInput( array("name" => "group", "placeholder" => "name of group", "help" => "Group Name", "label" => "Group Name *", "value" => @$value['group'], "msg" => @$msg['group']) );
		$this->va_input->addSelect( array("name" => "status", "label" => "Group Status *", "list" => array("0" => "Not Active", "1" => "Active"), "value" => @$value['active'], "msg" => @$msg['active']) );
		$this->va_input->commitForm(0);
		$groupList = $this->modulemanagement_m->getStructuredModule();
		$this->va_input->addCustomField( array("name" => "module", "help" => "Select module for user group", "label" => "Modules", "msg" => @$msg['module'], "value" => @$value['module'], "list" => $groupList, "view" => "form/custom_module") );
		$this->va_input->commitForm(1);
		$this->data['script'] = $this->load->view("script/group_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save ()
	{
		if($_SERVER['REQUEST_METHOD'] != "POST") 
		{
			redirect("usergroup/add");
		}		
		$post = $this->input->post("user");
		
		if(empty($post)) 
		{
			redirect("usergroup/add");
		}
		
		if($post['method'] == "new") 
		{
			$result = $this->usergroup_m->newGroup( $post );
			if(is_numeric($result)) 
			{
				redirect("usergroup");
			} 
			else 
			{
				$this->session->set_flashdata( array("userError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("usergroup/add");
			}
		} 
		else if($post['method'] == "update") 
		{
			$result = $this->usergroup_m->updateGroup($post);
			redirect("usergroup");
			
		}
	}	
	public function getGroupStatus()
	{
		$grup=$this->usergroup_m->groupList();
		$opsi=array(""=>"Select Status");
		foreach($grup as $id=>$row)
		{
		$opsi[$row['status']] = $row['status'];
		}
		return $opsi;
	}
	public function getStatus()
	{
	$status=array(""=>"Select Status",1=>"Active",0=>"Not active");
	return $status;
	}
		
}