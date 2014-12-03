<?php
class Awbprinting_m extends MY_Model {
	var $filterSession = "DB_AWB_FILTER";
	var $db = null;
	var $table = 'awb_queue_printing';
	var $tableUpload = 'awb_upload_file';
	var $tableClient ='client';
	var $sorts = array(1 => "id");
	var $pkField = "id";
	var $status=array("cancel"=>2,"approve"=>1);
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
		
		$this->relation = array(
			array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}")
		);
		
		$this->select = array("{$this->table}.*", "{$this->tableClient}.client_code");
		$this->filters = array("status"=>"status","order_number"=>"order_number","client_id"=>"client_id");
	}
}
?>