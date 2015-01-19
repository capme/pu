<?php
class Codconfirmation_m extends MY_Model {
	var $filterSession = "DB_USER_FILTER";
	var $db = null;
	var $table = 'cod_confirmation';
	var $sorts = array(1 => "id");
	var $pkField = "id";
	var $status=array("cancel"=>2,"approve"=>1);
	var $tableClient ='client';
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);		
		$this->relation = array(array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} and {$this->table}.status = 0 "));
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->table}.order_number", "{$this->table}.customer_name", "{$this->table}.updated_at", "{$this->table}.status", "{$this->table}.updated_by", "{$this->tableClient}.client_code");
		$this->filters = array("order_number"=>"order_number","client_id"=>"client_id");
	}
	
	public function getCodConfirmationList() 
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
			if ($_result->status==0)
			{
				$action='<a href="'.site_url("codconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>|<a href="'.site_url("codconfirmation/approve/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>|
				<a href="'.site_url("codconfirmation/cancel/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>';
			}else{
				$action='<a href="'.site_url("codconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>';
			}
			
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->client_code,
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					$_result->order_number,
					$_result->customer_name,
					@$opsiarray[$_result->updated_by],
					$action				
			);
		}
	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
	}
	
	public function getCodConfirmationById($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->select('*, cod_confirmation.id', 'cod_confirmation.status');
		$this->db->from($this->table);
		$this->db->join('client','client.id=cod_confirmation.client_id');
//		$this->db->join('cod_history', 'cod_history.cod_id=cod_confirmation.id');
		$this->db->where('cod_confirmation.id', $id);
		return $this->db->get();  
	}

	public function getCodConfirmationByOrderNumber($orderNumber)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->select('*');
		$this->db->from($this->table);
		$this->db->where('order_number', $orderNumber);
		return $this->db->get();
	}
	
	public function Approve($post) 
	{		
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		
			$note['cod_id']=$post['id'];
			$note['note'] = $post['approve'];
			$note['status'] = $this->status['approve'];
			$note['type']=1;
			$note['created_by']=$user;
			$note['created_at']=$time;
			
			$data['status'] = $this->status['approve'];
			$data['updated_by']=$user;
			$data['updated_at']=$time;	
			
			$this->db->where($this->pkField, $post['id']);			
			$this->db->update($this->table, $data);
			
			$this->db->where('cod_id', $post['id']);
			$this->db->insert('cod_history', $note);
			return $post['id'];		
	}
	
	public function Cancel($post) 
	{	
		$msg = array();	
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		if (!empty($post['cancel'])){
				$note['cod_id']=$post['id'];
				$note['note'] = $post['cancel'];
				$note['status'] = $this->status['cancel'];
				$note['type']=1;
				$note['created_by']=$user;
				$note['created_at']=$time;
				
				$data['status'] = $this->status['cancel'];
				$data['updated_by']=$user;
				$data['updated_at']=$time;	
				
				$this->db->where($this->pkField, $post['id']);			
				$this->db->update($this->table, $data);
				
				$this->db->where('cod_id', $post['id']);
				$this->db->insert('cod_history', $note);
				return $post['id'];	
			}
		else {
		return $msg;
		}
	}
	
	public function Comment($post)
	{
		$msg = array();	
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		if (!empty($post['comment'])){
				$note['cod_id']=$post['id'];
				$note['note'] = $post['comment'];
				$note['status'] = $post['status'];
				$note['type']=1;
				$note['created_by']=$user;
				$note['created_at']=$time;
				
				$data['status'] = $post['status'];
				$data['updated_by']=$user;
				$data['updated_at']=$time;	
				
				$this->db->where($this->pkField, $post['id']);			
				$this->db->update($this->table, $data);
				
				$this->db->where('cod_id', $post['id']);
				$this->db->update('cod_history', $note);
				return $post['id'];	
			}
		else {
		return $msg;
		}	
	}

	public function add($client, $orders) {
		$this->db = $this->load->database('mysql', TRUE);

		$this->db->trans_start();
		$insertedIds = array();
		foreach($orders as $order) {

			$cekCodConfirmation = $this->getCodConfirmationByOrderNumber($order['order_number']);

			if($cekCodConfirmation->num_rows() > 0) continue;

			$this->db->insert(
				$this->table,
				array("client_id" => $client['id'], "order_number" => $order['order_number'], "customer_name" => $order['customer_fullname'], "shipping_address" => $order['full_shipping_address'], "amount" => $order['total_amount'], "items" => $order['items'], "updated_by" => "2", "created_at" => date("Y-m-d H:i:s") )
			);

			$insertedIds[] = $order['id'];
		}
		$this->db->trans_complete();

		return $insertedIds;
	}
}
?>