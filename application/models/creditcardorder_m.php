<?php
class creditcardorder_m extends MY_Model {
    var $filterSession = "DB_USER_FILTER";
    var $db = null;
    var $table = 'creditcard_order';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $status=array("cancel"=>2,"approve"=>1);
    var $tableClient ='client';
    var $tableOrderHistory = "order_history";
    var $orderStatusMap = array(0 => "pending", 1 => "processing", 2 => "complete", 
    							3 => "fraud", 4 => "payment_review", 5 => "canceled",
    							6 => "closed", 7 => "waiting_payment");
    
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);

        $this->relation = array(
            array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}")
        );
        $this->select = array("{$this->table}.{$this->pkField}", "{$this->table}.order_number", "{$this->table}.created_at",  "{$this->table}.amount", "{$this->table}.name","{$this->table}.updated_at", "{$this->table}.status", "{$this->table}.updated_by", "{$this->tableClient}.client_code");
        $this->filters = array("status"=>"status","order_number"=>"order_number","client_id"=>"client_id");
    }

    public function getCreditCardOrderList()
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
            0 =>array("Pending", "info"),
            1 => array("Processing","success"),
            2 => array("Complete","primary"),
			3 => array("Fraud","default"),
			4 => array("Payment_Review","warning"),
            5 => array("Canceled","danger"),
			6 => array("Closed","danger"),
			7 => array("Waiting_payment","info"),
        );
		
        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
        $no=0;
        foreach($_row->result() as $_result) {
            $status=$statList[$_result->status];
                 $date = explode(' ', $_result->created_at);
            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $date[0],
                $_result->client_code,
                $_result->order_number,
                $_result->name,
                "Rp. ".number_format($_result->amount),
                '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                '<a href="'.site_url("creditcardorder/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>'

        );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function getCreditCardOrderById($id)
    {
        $this->db = $this->load->database('mysql', TRUE);
        $this->db->select('*, creditcard_order.email, creditcard_order.created_at, creditcard_order.id, creditcard_order.updated_at, auth_users.username, order_history.type');
        $this->db->from($this->table);
        $this->db->join('client','client.id=creditcard_order.client_id');
        $this->db->join('order_history', 'order_history.order_id=creditcard_order.id and type=3','left');
        $this->db->join('auth_users', 'auth_users.pkUserId=order_history.created_by','left');
        $this->db->where('creditcard_order.id', $id);
        $this->db->order_by('order_history.id','asc');
        return $this->db->get();
    }
    
    public function saveCreditCardOrder($clientid, $datas){
    	$this->db->trans_start();
		
    	foreach($datas as $key => $data){
    		$order_id = $key;
    			if(!is_null($data['comment'])){
    				$note = $data['comment'];
    			}else{
    				$note = "";
    			}
    			$status = $data['status'];
    			$type = $data['type'];
    			$customerName = $data['customer_name'];
    			$shippingAddress = $data['address'];
    			$items = $data['items'];
    			$customerEmail = $data['customer_email'];
    			$amount = $data['amount'];
    			$status = array_search($status, $this->orderStatusMap);

    			//check if data order ccc is exist
				$this->db->select('*');
				$this->db->where('order_id', $order_id);
				$query = $this->db->get('order_history');
				$num = $query->num_rows();
				
				if($num > 0){
					//update table order_history
					$data = array("note" => $note, "status" => $status, "created_by" => "2", "created_at" => date("Y-m-d H:i:s"));
					$dataWhere = array('order_id' => $order_id, 'type' => 3);
					$this->db->where($dataWhere);
					$this->db->update($this->tableOrderHistory, $data); 
					//update table creditcard_order
					$data = array("name" => $customerName, "shipping_address" => $shippingAddress, "items" => $items, "email" => $customerEmail, "amount" => $amount, "status" => $status, "updated_by" => "2", "updated_at" => date("Y-m-d H:i:s"));
					$dataWhere = array('client_id' => $clientid, 'order_number' => $order_id);
					$this->db->where($dataWhere);
					$this->db->update($this->table, $data); 
				}else{
	    			//insert into table order_history
			    	$this->db->insert(
							$this->tableOrderHistory,
							array("order_id" => $order_id, "note" => $note, "status" => $status, "type" => $type, "created_by" => "2", "created_at" => date("Y-m-d H:i:s"))
					);
					$idOrderHistory = $this->db->insert_id();
					//insert into table creditcard_order
			    	$this->db->insert(
							$this->table,
							array("client_id" => $clientid, "order_number" => $order_id, "name" => $customerName, "shipping_address" => $shippingAddress, "items" => $items, "email" => $customerEmail, "amount" => $amount, "status" => $status, "updated_by" => "2", "created_at" => date("Y-m-d H:i:s"))
					);
					$idCreditCardOrder = $this->db->insert_id();
				}	
    	}
    	$this->db->trans_complete();
    }

}
