<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * @author 		Technology <tech@velaasia.com>
 * @property 	Auth_m $auth_m
 * @property	CI_Loader $load
 * @property Modulemanagement_m $modulemanagement_m
 * @property 	Ga_m $ga_m
 * @property	CI_Session $session
 *
 */
class MY_Controller extends CI_Controller {

	var $data = array();
	var $allows = array();

    function __construct()
    {
        parent::__construct();
		$this->load->model('auth_m');
		$this->load->model("modulemanagement_m");

		// check loggedin
		if(!$this->auth_m->is_logged_in()) {
			redirect('login?redirect=' . rawurlencode(current_url()));
		}
		
		$this->data['structuredModule'] = $this->modulemanagement_m->getStructuredModule();
		$this->data['parentMap'] = $this->modulemanagement_m->getStructuredModule(TRUE);
		$this->data['currentModule'] = $this->modulemanagement_m->getCurrentModule();
		
		// check permission for all pages
		if(empty($this->data['currentModule'])) {
			echo "Invalid request! Please add your module first..<br />";
			echo "<a href='javascript:history.back()'>Back</a>";
			die;
		}
		
		$parents = array();
		$parent = $this->data['parentMap'][$this->data['currentModule']['id']];
		while($parent > 0) {
			$parents[] = $parent;
			$parent = $this->data['parentMap'][$parent];
		}
		
		krsort($parents);
		$this->data['parents'] = $parents;
		
		// check permission for all pages
		if(!$this->auth_m->is_allowed($this->data['currentModule'])) {
			echo "You don't have permission to view this page.<br />";
			echo "<a href='javascript:history.back()'>Back</a>";
			die;
		}
		
		$this->data['session'] = $this->auth_m->get_session_data();
		$this->data['user'] = $this->auth_m->get_user(@$this->data['session']['username']);
		$this->data['allowedModule'] = $this->auth_m->getAllowedModule();
		
		$success = $this->session->flashdata('success');
		$error = $this->session->flashdata('error');
		if($success){
			$this->data['success'] = $success;
		}
		if($error) {
			$this->data['error'] = $error;
		}
		
    }

	function allow($permission, $group=null) {
		$this->allows[] = array('permission'=>$permission, 'group'=>$group);
	}

	function allow_perm($permission){
		$this->allows[] = array('permission'=>$permission);
	}
	function allow_group($group){
		$this->allows[] = array('group'=>$group);
	}
}