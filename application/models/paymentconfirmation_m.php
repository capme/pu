<?php
class Paymentconfirmation_m extends MY_Model {
	var $filterSession = "DB_USER_FILTER";
	var $db = null;
	var $table = 'bank_confirmation';
	var $sorts = array(1 => "id");
	var $pkField = "id";
	var $status=array("cancel"=>2,"approve"=>1);
	var $tableClient ='client';
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
		
		$this->relation = array(
			array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}")
		);
		
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->table}.order_number", "{$this->table}.origin_bank", "{$this->table}.dest_bank", "{$this->table}.transaction_method", "{$this->table}.amount", "{$this->table}.name", "{$this->table}.transaction_date", "{$this->table}.updated_at", "{$this->table}.status", "{$this->table}.receipt_url","{$this->table}.updated_by", "{$this->tableClient}.client_code");
		
		$this->filters = array("status"=>"status","order_number"=>"order_number");
	}
	
		public function getPaymentConfirmationList() 
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
				1 =>array("Approve", "success"),
				2 =>array("Cancel","danger")
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
					$_result->order_number,
					$_result->name,
					$_result->origin_bank,
					$_result->transaction_method,					
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					$_result->transaction_date,					
					'<a '.anchor($_result->receipt_url,'View','target="_blank" enabled="enabled" class="fa fa-search btn btn-xs default"').'</a>',		
					
					@$opsiarray[$_result->updated_by],					
					'<a href="'.site_url("paymentconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>|			
					<a href="'.site_url("paymentconfirmation/approve/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>|
					<a href="'.site_url("paymentconfirmation/cancel/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>'
									
			);
		}
	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
	}
	
	public function getConfirmationById($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->select('*, bank_confirmation.id, bank_confirmation.updated_at');
		$this->db->from($this->table);
		$this->db->join('client','client.id=bank_confirmation.client_id');
		$this->db->where('bank_confirmation.id', $id);		
		return $this->db->get();  
	}
	
	public function Approve ($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$user=$this->session->userdata('pkUserId');	
		$time=date('Y-m-d H:i:s', now());
		
		$data['status'] = $this->status['approve'];
		$data['updated_by']=$user;
		$data['updated_at']= $time;	
		
		$this->db->where($this->pkField, $id);
		$this->db->update($this->table, $data);
		return $id;		
	}
	
	public function Reason($post) 
	{
		$msg = array();			
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		
		if(!empty($post['reason'])) {
			$data['reason'] = $post['reason'];
			$data['status'] = $this->status['cancel'];
			$data['updated_by']=$user;
			$data['updated_at']=$time;	
			
			$this->db->where($this->pkField, $post['id']);			
			$this->db->update($this->table, $data);
			return $post['id'];
		} 
		else {
			return $msg;
		}	
	}
	
	}
?>