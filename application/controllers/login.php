<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

	var $data = '';

	public function __construct()
	{
		$this->allow_roles = array('public');
		parent::__construct();
		$this->load->model('auth_m');

	}

	public function index()
	{
		if(isset($_GET['false']) ) {
			$this->session->sess_destroy();
			$this->data['alert_type'] = 'error';
			$this->data['alert_msg'] = 'Invalid username/password.';
		}
		$this->load->view('login_v', $this->data);
	}

	public function do_login()
	{
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		($this->input->post('keep')) ? $remember=1 : $remember=0;
		$redirect = $this->input->post('redirect');

		$user = $this->auth_m->do_login($username, $password, $remember);
		if($user) {
			// login success
			if($redirect) {
				redirect($redirect);
			}else {
				redirect('/');
			}

		} else {
			$this->session->set_flashdata('error','Invalid username/password');
			redirect('login?false&redirect=' . rawurlencode($redirect));
		}

	}

	public function logout()
	{
		$this->auth_m->logout();
		$this->data['alert_type'] = 'success';
		$this->data['alert_msg'] = 'You have logged out';
		$this->load->view('login_v', $this->data);
	}

}