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
		
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->table}.order_number", "{$this->tableReturnItem}.sku", "{$this->tableReturnItem}.status", "{$this->tableReturnItem}.updated_at", "{$this->tableReturnItem}.updated_by", "{$this->tableClient}.client_code", "{$this->tableReturnItem}.id as item_id");
		
		$this->filters = array("status"=>"status", "order_number"=>"order_number", "client_id" => "client_id");		
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
					$_result->sku,
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					$_result->updated_at,
					@$opsiarray[$_result->updated_by],
					
					'<a href="'.site_url("returnorder/view/".$_result->item_id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>|			
					<a href="'.site_url("returnorder/approve/".$_result->item_id).'" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>|
					<a href="'.site_url("returnorder/cancel/".$_result->item_id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>'
									
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

		} 
		else {
		}
						
		if(empty($msg)) 
		{			
			$this->db->where($this->tableReturnItem.'.id', $post['id']);			
			$this->db->update($this->tableReturnItem, $data);
			
			return $post['id'];
		} 
		else {
			return $msg;
		}	
	}
	
	public function Approve ($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$user=$this->session->userdata('pkUserId');	
		$time=date('Y-m-d H:i:s', now());
		
		$data['status'] = $this->status['approve'];
		$data['updated_by']=$user;
		$data['updated_at']= $time;	
				
		$this->db->where($this->tableReturnItem.'.id', $id);			
		$this->db->update($this->tableReturnItem, $data);
		return $id;		
	}
	
	public function getOrderById($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->select('*');
		$this->db->from('return');
		$this->db->join('return_item', 'return.id=return_item.return_id');
		$this->db->join('client','client.id=return.client_id');
		$this->db->where($this->tableReturnItem.".id", $id);
		$query=$this->db->get();
		return $query;
	}
	
	public function add($client, $items) {
		$this->db = $this->load->database('mysql', TRUE);
		
		$this->db->trans_start();
		$insertedIds = array();
		
		foreach($items as $item) {
			$existing = $this->db->get_where($this->table, array("order_number" => $item['increment_id']))->row_array();
			if(empty($existing)) {
				$this->db->insert(
						$this->table,
						array("order_number" => $item['increment_id'], "email_address" => $item['email'], "phone_number" => $item['phone_number'], "client_id" => $client['id'], "updated_by" => "2", "created_at" => date("Y-m-d H:i:s"))
				);
				$id = $this->db->insert_id();
			} else {
				$id = $existing['id'];
			}
			
			$this->db->insert(
				$this->tableReturnItem,
				array("return_id" => $id, "sku" => $item['sku'], "return_reason" => $item['reason'], "updated_by" => 2)
			);
			$insertedIds[] = $item['item_id'];
		}
		
		$this->db->trans_complete();
		
		return $insertedIds;
	}
	
}
