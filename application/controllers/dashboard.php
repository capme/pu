<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		$this->data['content'] = "dashboard_v.php";
		$this->data['pageTitle'] = "Dashboard <small>statistics and more</small>";
		$this->data['breadcrumb'] = array("Dashboard" => "");
		
		$this->load->view("template", $this->data);
	}

}