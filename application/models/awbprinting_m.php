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
		$this->filters = array("status"=>"status","ordernr"=>"ordernr","client_id"=>"client_id");
		$this->group = array("ordernr");
	}
	
	public function getAwbPrintingList()
	{
		$this->db = $this->load->database('mysql', TRUE); 
		$iTotalRecords = $this->_doGetTotalRow();
		$iDisplayLength = intval($this->input->post('iDisplayLength'));
		$iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
		$iDisplayStart = intval($this->input->post('iDisplayStart'));
		$sEcho = intval($this->input->post('sEcho'));
	
		$records = array();
		$records["aaData"] = array();
		
		$grup=$this->users_m->userList();
		$opsiarray=array();
		foreach($grup as $pkUserId=>$row)
		{
		$opsiarray[$row['pkUserId']]=$row['username'];
		}
		
		$statList= array(
				0 =>array("New Request", "warning"),
				1 =>array("Printed", "success"),
				
		);
		
		$end = $iDisplayStart + $iDisplayLength;
		$end = $end > $iTotalRecords ? $iTotalRecords : $end;
		
		$_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
		$no=0;
		
		foreach($_row->result() as $_result) {
			$status=$statList[$_result->status];
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->client_code,
					$_result->ordernr,
					$_result->receiver,
					$_result->address,
					$_result->city,
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					$_result->package_type,
					$_result->shipping_type,					
					'<a href="'.site_url("awbprinting/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>'
					
			);
		}
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
		
	}
	
	public function getAwbPrintingById($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->join('client','client.id=awb_queue_printing.client_id');
		$this->db->where('awb_queue_printing.id', $id);		
		return $this->db->get();  
	}
	
	public function printAwb($ids = array(), $status) {
		$this->db->where_in($this->pkField, $ids);
		$this->db->select('*');		
		$query = $this->db->get($this->table);
		$data['list'] = $query;
		if($status == 0) 
		{
		$this->load->view("print_template_jne", $data);
		} 
		else {
		$this->load->view("print_template_nex", $data);
		}
	}
	
	public function newData($data)
	{
	$this->db->insert_batch($this->table, $data); 
	}
	
	public function insertBatchData($data) {
		$this->db->insert_batch($this->tableName, $data); 
	}
	
}
?>