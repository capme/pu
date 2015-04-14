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
		
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->table}.order_number", "{$this->table}.created_at", "{$this->table}.origin_bank", "{$this->table}.dest_bank", "{$this->table}.transaction_method", "{$this->table}.amount", "{$this->table}.name", "{$this->table}.transaction_date", "{$this->table}.updated_at", "{$this->table}.status", "{$this->table}.receipt_url","{$this->table}.updated_by", "{$this->tableClient}.client_code");
		
		$this->filters = array("status"=>"status","order_number"=>"order_number","client_id"=>"client_id");
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
			if ($_result->status==0)
			{
				$action='<a href="'.site_url("paymentconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>|<a href="'.site_url("paymentconfirmation/approve/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>|
				<a href="'.site_url("paymentconfirmation/cancel/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>';
			}else{
				$action='<a href="'.site_url("paymentconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>';
			}
			
			$date = explode(' ', $_result->created_at);
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$date[0],
					$_result->client_code,
					$_result->order_number,
					$_result->name,
					$_result->origin_bank . ($_result->origin_bank ? '<br /> (<a '.anchor($_result->receipt_url, 'Receipt', 'style="font-size:12px;" target="_blank" enabled="enabled" class="fa fa-search btn btn-xs default"').'</a>)' : ''),
					"Rp. ".number_format($_result->amount),					
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					$action				
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
		$this->db->select('*, bank_confirmation.created_at,bank_confirmation.id, bank_confirmation.updated_at, auth_users.username, order_history.type, order_history.created_at as history_date');
		$this->db->from($this->table);
		$this->db->join('client','client.id=bank_confirmation.client_id');
        $this->db->join('order_history', 'order_history.order_id=bank_confirmation.id and type=2','left');
        $this->db->join('auth_users', 'auth_users.pkUserId=order_history.created_by','left');
        $this->db->where('bank_confirmation.id', $id);
        $this->db->order_by('order_history.id','desc');
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

        $history['created_by']=$user;
        $history['status']=$this->status['approve'];
		$history['order_id']=$id;
        $history['created_at']= $time;
        $history['type']=2;

		$this->db->where($this->pkField, $id);
		$this->db->update($this->table, $data);

        $this->db->insert('order_history', $history);
		return $id;		
	}
	
	public function Reason($post) 
	{
        $this->load->model("client_m");
        $this->load->library("mageapi");

		$msg = array();			
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		
		if(!empty($post['reason'])) {

			$history['note'] = $post['reason'];
			$history['type']=2;
            $history['order_id']=$post['id'];
            $history['created_by']=$user;
            $history['created_at']=$time;
            $history['status']=$this->status['cancel'];

            $data['status'] = $this->status['cancel'];
			$data['updated_by']=$user;
			$data['updated_at']=$time;	
			
			$this->db->where($this->pkField, $post['id']);			
			$this->db->update($this->table, $data);

            $this->db->insert('order_history', $history);

            $client = $this->client_m->getClientById($post['client_id'])->row_array();
            $config = array(
                "auth" => $client['mage_auth'],
                "url" => $client['mage_wsdl']
            );

            if( $this->mageapi->initSoap($config) ) {
                $this->mageapi->cancelPayment($post['order_number'], $post['reason']);
            }

			return $post['id'];
		} 
		else {
			return $msg;
		}	
	}
	
	public function add($client, $payments) {
		$this->db = $this->load->database('mysql', TRUE);
		
		$this->db->trans_start();
		$insertedIds = array();
		foreach($payments as $payment) {
			$exist = $this->db->get_where($this->table, array("order_number" => $payment['order_number'], "client_id" => $client['id']))->row_array();
			if(isset($exist['id']) && $exist['id']) {
				$this->db->where(array("order_number" => $payment['order_number'], "client_id" => $client['id']));
				$this->db->update(
					$this->table, 
					array("client_id" => $client['id'], "order_number" => $payment['order_number'], "origin_bank" => $payment['origin_bank'], "dest_bank" => $payment['dest_bank'], "transaction_method" => $payment['transaction_method'], "name" => $payment['name'], "transaction_date" => $payment['transaction_date'], "amount" => $payment['amount'], "receipt_url" => $payment['receipt_url'], "updated_by" => "2", "updated_at" => date("Y-m-d H:i:s") )
				);
			} else {
				$this->db->insert( 
					$this->table, 
					array("client_id" => $client['id'], "order_number" => $payment['order_number'], "origin_bank" => $payment['origin_bank'], "dest_bank" => $payment['dest_bank'], "transaction_method" => $payment['transaction_method'], "name" => $payment['name'], "transaction_date" => $payment['transaction_date'], "amount" => $payment['amount'], "receipt_url" => $payment['receipt_url'], "updated_by" => "2", "created_at" => date("Y-m-d H:i:s") )
				);
			}
			
			$insertedIds[] = $payment['id'];
		}
		$this->db->trans_complete();
		
		return $insertedIds;
	}
	
}
?>
