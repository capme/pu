<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller to manage user login list, only admin and admin-client should be able to access this page
 * 
 * @author Ferry Ardhana<ferry.ardhana@velaasia.com>
 * @property Va_input $va_input
 * @property Va_list $va_list
 * @property Users_m $users_m
 */
class Users extends MY_Controller {
	var $data = array();
	
	public function __construct()
	{
		$this->allow_group("1");
		$this->allow_group("cadmin");
		
		parent::__construct();
		$this->data['pagegroup'] = 4;
		$this->load->model("users_m");
		$this->load->model('usergroup_m');
		$this->data['page'] = "users";
	}
	
	public function index() {
		$this->data['content'] = "list_v.php";
		$this->data['pageTitle'] = "User Login Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "User Login Management" => "");
		
		$this->users_m->clearCurrentFilter();
		
		$this->load->library("va_list");
		$this->va_list->setListName("User Listing")->setAddLabel("New User")
		->setMassAction(
				array("1" => "Active", "0" => "Not Active", "2" => "Remove")
		)->setHeadingTitle(
				array("Record #", "Name", "Username", "Email", "Group", "Status")
		)->setHeadingWidth(
				array(5, 20, 20, 10, 10, 10)
		);
		$this->va_list->setInputFilter(2, array("name" => $this->users_m->filters['username']))
			->setDropdownFilter(4, array("name" => $this->users_m->filters['group'], "option" => $this->_getUserGroupList()));
		
		$this->data['script'] = $this->load->view("script/user_list", array("ajaxSource" => site_url("users/userList")), true);
		
		$this->load->view("template", $this->data);
	}
	
	/**
	 * List of registered API
	 */
	public function add()
	{
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "User Login Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "User Login Management" => "");
		$this->data['formTitle'] = "Add User Login";
	
		$this->load->library("va_input", array("group" => "user"));
		$flashData = $this->session->flashdata("userError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = $value = array();
		}
	
		$this->va_input->addHidden( array("name" => "method", "value" => "new") );
		$this->va_input->addInput( array("name" => "username", "placeholder" => "username", "help" => "Username for login", "label" => "User Name *", "value" => @$value['username'], "msg" => @$msg['username']) );
		$this->va_input->addPassword( array("name" => "password", "placeholder" => "password", "label" => "Password *", "msg" => @$msg['password']) );
		$this->va_input->addPassword( array("name" => "retypepassword", "placeholder" => "password", "label" => "Re-type Password *", "msg" => @$msg['retypepassword']) );
		$this->va_input->addSelect( array("name" => "group", "list" => $this->_getUserGroupList(), "value" => @$value['group'], "msg" => @$msg['group'], "label" => "Group *") );
		$this->va_input->addInput( array("name" => "fullname", "placeholder" => "Full name", "label" => "Full Name *", "value" => @$value['fullname'], "msg" => @$msg['fullname']) );
		$this->va_input->addInput( array("name" => "email", "placeholder" => "email@example.com", "label" => "Email Address *", "value" => @$value['email'], "msg" => @$msg['email']) );
		$this->va_input->addSelect( array("name" => "active", "label" => "User Status *", "list" => array("0" => "Not Active", "1" => "Active"), "value" => @$value['active'], "msg" => @$msg['active']) );
	
		$this->data['script'] = $this->load->view("script/user_add", array(), true);
		// $this->load->view('customers_v', $this->data);
		$this->load->view('template', $this->data);
	}
	
	public function view( $id) 
	{
		$data = $this->users_m->getUser($id);
		if($data->num_rows() < 1) {
			redirect("api");
		}
		
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "User Login Management";
		$this->data['breadcrumb'] = array("Application Management"=> "", "User Login Management" => "");
		$this->data['formTitle'] = "View User Login";
		
		$this->load->library("va_input", array("group" => "user"));
		$flashData = $this->session->flashdata("userError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			$msg = $flashData['msg'];
		} else {
			$msg = array();
			$value = $data->row_array();
		}
		
		$this->va_input->addHidden( array("name" => "method", "value" => "update") );
		$this->va_input->addHidden( array("name" => "pkUserId", "value" => $value['pkUserId']) );
		$this->va_input->addInput( array("name" => "username", "placeholder" => "username", "help" => "Username for login", "label" => "User Name *", "value" => @$value['username'], "msg" => @$msg['username']) );
		$this->va_input->addCheckbox( array("name" => "changepass", "label" => "Change Password", "help" => "Check this field when you want to change password", "list" => array("1" => "I want to change password")) );
		$this->va_input->addPassword( array("name" => "password", "placeholder" => "password", "label" => "Password *", "msg" => @$msg['password'], "value" => @$value['password']) );
		$this->va_input->addPassword( array("name" => "retypepassword", "placeholder" => "password", "label" => "Re-type Password *", "msg" => @$msg['retypepassword'], "value" => @$value['password']) );
		$this->va_input->addSelect( array("name" => "group", "list" => $this->_getUserGroupList(), "value" => @$value['group'], "msg" => @$msg['group'], "label" => "Group *") );
		$this->va_input->addInput( array("name" => "fullname", "placeholder" => "Full name", "label" => "Full Name *", "value" => @$value['fullname'], "msg" => @$msg['fullname']) );
		$this->va_input->addInput( array("name" => "email", "placeholder" => "email@example.com", "label" => "Email Address *", "value" => @$value['email'], "msg" => @$msg['email']) );
		$this->va_input->addSelect( array("name" => "active", "label" => "User Status *", "list" => array("0" => "Not Active", "1" => "Active"), "value" => @$value['active'], "msg" => @$msg['active']) );
		
		$this->data['script'] = $this->load->view("script/user_add", array(), true);
		
		
		// $this->load->view('customers_v', $this->data);
		$this->load->view('template', $this->data);
	}
	
	public function save() 
	{
		if($_SERVER['REQUEST_METHOD'] != "POST") 
		{
			redirect("users/add");
		}
		
		$post = $this->input->post("user");
		
		if(empty($post)) 
		{
			redirect("users/add");
		}
		
		if($post['method'] == "new") 
		{
			$result = $this->users_m->newUser( $post );
			if(is_numeric($result)) 
			{
				redirect("users");
			} 
			else 
			{
				$this->session->set_flashdata( array("userError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("users/add");
			}
		} 
		else if($post['method'] == "update") 
		{
			$result = $this->users_m->updateUser( $post );

			if(is_numeric($result)) 
			{
				redirect("users");
			} 
			else 
			{
			$this->session->set_flashdata( array("userError" => json_encode(array("msg" => $result, "data" => $post))) );
				redirect("users/view/".$post['pkUserId']);
			}
		}
	}
	
	public function userList() 
	{
		$sAction = $this->input->post("sAction");
		if($sAction == "group_action")
		{
			$ids = $this->input->post("id");
			if(sizeof($ids) > 0) 
			{
				$action = $this->input->post("sGroupActionName");
				$this->users_m->massUpdate($ids, $action);
			}
		}	
		$data = $this->users_m->getUserList();
	
		echo json_encode($data);
	}
	
	private function _getUserGroupList() 
	{
	//	return array("" => "Select Group", "admin" => "Admin", "client" => "Client", "cs" => "Customer Service", "operator" => "Operator");
	
	$grup=$this->usergroup_m->groupList();
	$opsi=array(""=>"Select Group");
	foreach($grup as $id=>$row)
		{
		$opsi[$row['id']] = $row['name'];
		}
	return $opsi;
	}
	
	public function deleteUser($pkUserId)
	{
	$this->users_m->deleteUser($pkUserId);
	redirect('users');
	}
}
?>