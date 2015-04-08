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
		$this->select = array("{$this->table}.{$this->pkField}", "{$this->table}.order_number", "{$this->table}.customer_name", "{$this->table}.created_at", "{$this->table}.updated_at", "{$this->table}.status", "{$this->table}.updated_by", "{$this->tableClient}.client_code", "{$this->table}.phone_number", "{$this->table}.email");
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
				$action='<a href="'.site_url("codconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a><br /><br /><a href="'.site_url("codconfirmation/approve/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>|
				<a href="'.site_url("codconfirmation/cancel/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>';
			}else{
				$action='<a href="'.site_url("codconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>';
			}
			
			$date = explode(" ", $_result->created_at);
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$date[0],
					$_result->client_code,
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					$_result->order_number,
					$_result->customer_name,
					$_result->phone_number . " / " . $_result->email,	
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
		$this->db->select('*, cod_confirmation.created_at,cod_confirmation.email, cod_confirmation.id, cod_confirmation.status, auth_users.username, order_history.type');
		$this->db->from($this->table);
		$this->db->join('client','client.id=cod_confirmation.client_id');
		$this->db->join('order_history', 'order_history.order_id=cod_confirmation.id and type=1', 'left');
        $this->db->join('auth_users', 'auth_users.pkUserId=order_history.created_by','left');
		$this->db->where('cod_confirmation.id', $id);
        $this->db->order_by('order_history.id','asc');
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

			$note['note'] = $post['approve'];
			$note['status'] = $this->status['approve'];
			$note['type']=1;
			$note['created_by']=$user;
			$note['created_at']=$time;
            $note['order_id']=$post['id'];
			
			$data['status'] = $this->status['approve'];
			$data['updated_by']=$user;
			$data['updated_at']=$time;	
			
			$this->db->where($this->pkField, $post['id']);			
			$this->db->update($this->table, $data);

			$this->db->insert('order_history', $note);
			return $post['id'];		
	}
	
	public function Cancel($post) 
	{
        $this->load->model("client_m");
        $this->load->library("mageapi");
        $msg = array();
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		if (!empty($post['cancel'])){

				$note['note'] = $post['cancel'];
				$note['status'] = $this->status['cancel'];
				$note['type']=1;
				$note['created_by']=$user;
				$note['created_at']=$time;
			    $note['order_id']=$post['id'];

				$data['status'] = $this->status['cancel'];
				$data['updated_by']=$user;
				$data['updated_at']=$time;	
				
				$this->db->where($this->pkField, $post['id']);			
				$this->db->update($this->table, $data);

				$this->db->insert('order_history', $note);

                $client = $this->client_m->getClientById($post['client_id'])->row_array();
                $config = array("auth" => $client['mage_auth'],"url" => $client['mage_wsdl']);

                if( $this->mageapi->initSoap($config) ) {
                    $this->mageapi->cancelCod($post['order_number'], $post['reason']);
                }

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
				array("client_id" => $client['id'], "order_number" => $order['order_number'], "customer_name" => $order['customer_fullname'],
						"shipping_address" => $order['full_shipping_address'], "amount" => $order['total_amount'], "items" => $order['items'], "updated_by" => "2",
						"created_at" => date("Y-m-d H:i:s"), "email" => $order['email'], "phone_number" => $order['phone_number'] )
			);

			if( $codId = $this->db->insert_id() ){
				$this->db->insert(
					'order_history',
					array('order_id' => $codId, 'note' => '== Order coming to oms', 'status' => 0, 'type' => 1, 'created_by' => '2', 'created_at' => date('Y-m-d H:i:s', now()))
				);
				log_message('debug','inserted cod history: data '.$codId);
			}

			$insertedIds[] = $order['id'];
		}
		$this->db->trans_complete();

		return $insertedIds;
	}
}
?>