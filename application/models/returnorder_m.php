<?php
class Returnorder_m extends MY_Model {

	var $filterSession = "DB_CLIENT_FILTER";
	var $db = null;
	var $table = 'return';
	var $tableReturnItem ='return_item';
	var $tableClient ='client';
	var $pkField = "id";
	var $status=array("cancel"=>2,"approve"=>1);	
	
	function __construct()
    {
        parent::__construct();
		
		$this->relation = array(
			array("type" => "inner", "table" => $this->tableReturnItem, "link" => $this->table . "." . $this->pkField . " = " . $this->tableReturnItem . "." . "return_id"),
			array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}")
		);
		
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->table}.order_number", "{$this->tableReturnItem}.sku", "{$this->tableReturnItem}.status", "{$this->tableReturnItem}.updated_at", "{$this->tableReturnItem}.updated_by", "{$this->tableClient}.client_code");
		
		$this->filters = array("client_code" => $this->table.".client_code","status"=>"status","order_number"=>"order_number");
		$this->sorts = array(1 => $this->table.".id");
    }
	
	public function getReturnOrderList() {
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
				0=>"Default",
				1 => "Approve",
				2 => "Cancel"
		);
		
		$end = $iDisplayStart + $iDisplayLength;
		$end = $end > $iTotalRecords ? $iTotalRecords : $end;
	
		$_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
		$no=0;
		foreach($_row->result() as $_result) {
			
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->client_code,
					$_result->order_number,
					$_result->sku,
					$statList[$_result->status],
					$_result->updated_at,
					@$opsiarray[$_result->updated_by],
					
					'<a href="'.site_url("returnorder/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>|			
					<a href="'.site_url("returnorder/approve/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>|
					<a href="'.site_url("returnorder/cancel/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>'
									
			);
		}
	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
	}
	
	public function Reason($post) 
	{
		$this->db = $this->load->database('mysql', TRUE);		
		$msg = array();			
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		
		if(!empty($post['cancel_reason'])) {
			$data['cancel_reason'] = $post['cancel_reason'];
			$data['status'] = $this->status['cancel'];
			$data['updated_by']=$user;
			$data['updated_at']= $time;
			$dat['updated_by']=$user;
			$dat['updated_at']= $time;
		} else {
		}
						
		if(empty($msg)) 
		{			
			$this->db->where($this->pkField, $post['id']);
			
			$this->db->update($this->tableReturnItem, $data);
			$this->db->update($this->table, $dat);
			return $post['id'];
		} 
		else {
			return $msg;
		}	
	}
	
	public function Approve ($post)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$user=$this->session->userdata('pkUserId');	
		$time=date('Y-m-d H:i:s', now());
		
		$data['status'] = $this->status['approve'];
		$data['updated_by']=$user;
		$data['updated_at']= $time;	
		
		$dat['updated_by']=$user;
		$dat['updated_at']= $time;
		
		$this->db->where($this->pkField, $post['id']);	
		
		$this->db->update($this->tableReturnItem, $data);
		$this->db->update($this->table, $dat);
		return $post['id'];		
	}
	
	public function getOrderById($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->select('*');
		$this->db->from('return');
		$this->db->join('return_item', 'return.id=return_item.return_id = '.$id.'');
		$this->db->join('client','client.id=return.client_id');
		$query=$this->db->get();
		return $query;
	}
	
	
}