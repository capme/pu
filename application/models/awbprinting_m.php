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
		$this->group = array("ordernr", "client_id");
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
					'<input type="checkbox" name="id[]" value="'.$_result->ordernr.'">',
					$no=$no+1,
					$_result->client_code,
					$_result->ordernr,
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					$_result->receiver,
					$_result->address,
					$_result->city,
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
	
	public function getAwbData($orderId = array()) {
		$ids = array();
		foreach($orderId as $i => $v) {
			$ids[] = "'{$v}'";
		}
		return $this->db->query("SELECT *, GROUP_CONCAT(items SEPARATOR '|') itemlist FROM ".$this->table." WHERE ordernr IN (".implode(",", $ids).") GROUP BY ordernr, client_id ORDER BY id DESC");
	}
	
	public function setAsPrinted($ids) {
		$this->db->where_in("ordernr", $ids);
		$this->db->update($this->table, array("status" => 1));
	}
	
	public function newData($data)
	{
		$this->db->insert_batch($this->table, $data); 
	}

	public function getOrderNoAmount($clientId) {
		return $this->db->get_where($this->table, array("client_id" => $clientId, "amount" => 0))->result_array();
	}
	
	public function updateAmount($datas) {
		$this->db->update_batch($this->table, $datas, "ordernr");
	}

	public function awbUploadFile($post)
	{
		$msg = array();		
		if(!empty($post['name'])) {
			$data['name'] = $post['name'];
		} else {
			$msg['name'] = "Invalid full name";
		}
		if(!empty($post['userfile'])) {
			$data['filename'] = $post['userfile'];
		} else {
			$msg['userfile'] = "File Max 2 Mb";
		}
		if(empty($msg)) {
			$this->db->insert($this->tableUpload, $data);
			return $this->db->insert_id();
		} 
		else {
			return $msg;
		}
	}
}
?>