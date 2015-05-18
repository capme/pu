<?php
class Paypalorder_m extends MY_Model {
    var $filterSession = "DB_USER_FILTER";
    var $db = null;
    var $table = 'paypal_order';
    var $sorts = array(1 => "id");
    var $pkField = "id";
    var $status=array("cancel"=>2,"approve"=>1);
    var $tableClient ='client';
    var $tableOrderHistory = "order_history";
    var $orderStatusMap = array(0 => "pending payment", 1 => "processing", 2 => "complete", 
    							3 => "fraud", 4 => "payment_review", 5 => "canceled",
    							6 => "closed", 7 => "waiting_payment");
    var $tableAwb = 'awb_queue_printing';
    
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);

        $this->relation = array(
            array("type" => "left", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}"),
            array("type" => "left", "table" => $this->tableAwb, "link" => "{$this->table}.order_number  = {$this->tableAwb}.ordernr")
        );
        $this->select = array(
                            "{$this->table}.{$this->pkField}",
                            "{$this->table}.order_number",
                            "{$this->table}.created_at",
                            "{$this->table}.amount",
                            "{$this->table}.name",
                            "{$this->table}.updated_at",
                            "{$this->table}.status `".$this->table.".status`",
                            "{$this->table}.updated_by",
                            "{$this->tableClient}.client_code",
                            "{$this->table}.amount `".$this->table.".amount`",
                            "{$this->tableAwb}.status `".$this->tableAwb.".status`"
                        );
        $this->filters = array(
                            $this->table.".status"=>$this->table."_status",
                            "order_number"=>"order_number",
                            $this->table.".client_id"=>$this->table."_client_id",
                            $this->table.".amount"=>$this->table."_amount",
                            $this->tableAwb.".status"=>$this->tableAwb."_status",
                            "name"=>"name"
                        );
        $this->listWhere['equal'] = array();
        $this->listWhere['like'] = array("order_number", "name");
    }

    public function getOrderStatusmap() {
        return $this->orderStatusMap;
    }

    public function getPaypalOrderList()
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
            0 =>array("Pending Payment", "info"),
            1 => array("Processing","success"),
            2 => array("Complete","primary"),
			3 => array("Fraud","default"),
			4 => array("Payment_Review","warning"),
            5 => array("Canceled","danger"),
			6 => array("Closed","danger"),
			7 => array("Waiting_payment","info")
        );

        $statListAWB= array(
            0 =>array("New Request", "warning"),
            1 =>array("Printed", "success"),
            3 =>array("Cron Process", "danger")
        );

        $end = $iDisplayStart + $iDisplayLength;
        $end = $end > $iTotalRecords ? $iTotalRecords : $end;

        $_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
        $no=0;
        foreach($_row->result() as $_result) {
            $_resultArr = (array)$_result;
            //$status=$statList[$_result->status];
            $status=$statList[$_resultArr['paypal_order.status']];
            if(!isset($statListAWB[$_resultArr['awb_queue_printing.status']])){
                $statusAWB = "New Request";
            }else {
                $statusAWB = $statListAWB[$_resultArr['awb_queue_printing.status']];
            }
			//if ($_result->status == 0){
            if ($_resultArr['paypal_order.status']==0){
			$action ='<a href="'.site_url("paypalorder/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>
				<a href="'.site_url("paypalorder/cancel/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>
				<a href="'.site_url("paypalorder/approve/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>';
			}
			/*else if($_result->status == 1) {
			$action ='<a href="'.site_url("paypalorder/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>
					<a href="'.site_url("paypalorder/approve/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>';
			}*/
			else {
			$action ='<a href="'.site_url("paypalorder/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>';
			}
						
            $date = explode(' ', $_result->created_at);
            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $date[0],
                $_result->client_code,
                $_result->order_number,
                $_result->name,
                "Rp. ".number_format($_resultArr['paypal_order.amount']),
                '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                '<span class="label label-sm label-'.($statusAWB[1]).'">'.($statusAWB[0]).'</span>',
                $action
        );
        }

        $records["sEcho"] = $sEcho;
        $records["iTotalRecords"] = $iTotalRecords;
        $records["iTotalDisplayRecords"] = $iTotalRecords;
        return $records;
    }

    public function getPaypalOrderById($id)
    {
        $this->db = $this->load->database('mysql', TRUE);
        $this->db->select('*,
		paypal_order.items as items,
		paypal_order.status as creditcard_status, 		
		paypal_order.email as email, 
		paypal_order.created_at as creditcardcreated_at, 
		paypal_order.updated_at as creditcardupdate_at, 
		auth_users.username, 
		order_history.type, 
		order_history.created_at as history_date');
        
		$this->db->from($this->table);
        $this->db->join('client','client.id=paypal_order.client_id');
        $this->db->join('order_history', 'order_history.order_id=paypal_order.id and order_history.type=4','left');
        $this->db->join('auth_users', 'auth_users.pkUserId=order_history.created_by');
        $this->db->where('paypal_order.id', $id);
        $this->db->order_by('order_history.id','asc');
        return $this->db->get();
    }
    
    public function savePaypalOrder($clientid, $datas, $histories){
    	$this->db->trans_start();
		
    	foreach($datas as $key => $data){		
            $check = $this->db->get_where($this->table, array('order_number' => (string) $key));
			
			if(!$check->num_rows()) {
                $this->db->insert($this->table, $data);
				$id = $this->db->insert_id();
				
            } else {
				$row = $check->row_array();			
				$id = $row['id'];
                $this->db->where($this->pkField,$id);
				$this->db->update($this->table, $data);

                $this->db->delete($this->tableOrderHistory, array('order_id' => $id));
            }
			
            $_history = $histories[$key];
            $history = array();
            foreach($_history as $h) {
                $history[] = array(
                    'order_id' => $id, 'type' => 4, 'note' => '',
                    'status' => array_search($h['status'], $this->paypalorder_m->getOrderStatusmap()), 'created_at' => $h['created_at'], 'created_by' => 2);
            }

            $this->db->insert_batch($this->tableOrderHistory, $history);
			
    	}
    	$this->db->trans_complete();
    }
	
	public function setStatusApprove($id, $order_id, $type){
		$user=$this->session->userdata('pkUserId');	
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->where('id', $id);
		$this->db->update($this->table,array('status'=>1));
		
		$this->db->insert('order_history',array('order_id'=>$order_id,'note'=>'Approved','status'=>1,'type'=>$type,'created_by'=>$user));	
	}
	
	public function setStatusCancel($id, $order_id, $type){
		$user=$this->session->userdata('pkUserId');	
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->where('id', $id);
		$this->db->update($this->table,array('status'=>5));
		
		$this->db->insert('order_history',array('order_id'=>$order_id,'note'=>'canceled','status'=>5,'type'=>$type,'created_by'=>$user));	
	}

}
