<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Changepassword extends MY_Controller {
	var $data = array();	
	public function __construct()
	{	
		parent::__construct();		
		$this->load->model("users_m");		
	}
	
	public function index()
	{
		$userdata=$this->session->userdata('pkUserId');
		$data = $this->users_m->getUser($userdata);
		if($data->num_rows() < 1) {
			redirect("logout");
		}
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "Change Password";
		$this->data['breadcrumb'] = array("Change Password"=> "");
		$this->data['formTitle'] = "Change Password";
	
		$this->load->library("va_input", array("group" => "user"));
		$flashData = $this->session->flashdata("userError");
		if($flashData !== false) {
			$flashData = json_decode($flashData, true);
			$value = $flashData['data'];
			
			$msg = $flashData['msg'];
		} else {
			$msg = $value = array();
		}
	
		$this->va_input->addHidden( array("name" => "method", "value" => "update") );
		$this->va_input->addHidden( array("name" => "pkUserId", "value" => $userdata) );		
		$this->va_input->addPassword( array("name" => "password", "placeholder" => "password", "label" => "Password *", "msg" => @$msg['password'], "value" => @$value['password']) );
		$this->va_input->addPassword( array("name" => "retypepassword", "placeholder" => "password", "label" => "Re-type Password *", "msg" => @$msg['retypepassword'], "value" => @$value['password']) );		
		$this->data['script'] = $this->load->view("script/change_password", array(), true);
		$this->load->view('template', $this->data);
	}
	
	public function save() 
	{
	$post = $this->input->post("user");
		if($post['method'] == "update") 
		{
			$result = $this->users_m->changePassword($post);
			if(is_numeric($result)) 
			{
				redirect("logout");
			} 
			else 
			{
			$this->session->set_flashdata( array("userError" => json_encode(array("msg" => $result, "data" => $post))) );
			redirect("changepassword");
			}
		}
	}
	
	
	}