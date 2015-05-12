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
    var $tableAwb = 'awb_queue_printing';

    
    function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database('mysql', TRUE);

        $this->relation = array(
            array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}"),
            array("type" => "inner", "table" => $this->tableAwb, "link" => "{$this->table}.order_number  = {$this->tableAwb}.ordernr")
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
                            "client_id"=>"client_id",
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
            $status=$statList[$_resultArr['creditcard_order.status']];
            $statusAWB=$statListAWB[$_resultArr['awb_queue_printing.status']];
            $date = explode(' ', $_result->created_at);
            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $date[0],
                $_result->client_code,
                $_result->order_number,
                $_result->name,
                "Rp. ".number_format($_resultArr['creditcard_order.amount']),
                '<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                '<span class="label label-sm label-'.($statusAWB[1]).'">'.($statusAWB[0]).'</span>',
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
        $this->db->select('*,
		creditcard_order.items as items,
		creditcard_order.status as creditcard_status, 		
		creditcard_order.email as email, 
		creditcard_order.created_at as creditcardcreated_at, 
		creditcard_order.updated_at as creditcardupdate_at, 
		auth_users.username, 
		order_history.type, 
		order_history.created_at as history_date');
        
		$this->db->from($this->table);
        $this->db->join('client','client.id=creditcard_order.client_id');
        $this->db->join('order_history', 'order_history.order_id=creditcard_order.id and order_history.type=3','left');
        $this->db->join('auth_users', 'auth_users.pkUserId=order_history.created_by');
        $this->db->where('creditcard_order.id', $id);
        $this->db->order_by('order_history.id','asc');
        return $this->db->get();
    }
    
    public function saveCreditCardOrder($clientid, $datas, $histories){
    	$this->db->trans_start();
		
    	foreach($datas as $key => $data){
            $check = $this->db->get_where($this->table, array('order_number' => $key));
            if(!$check->num_rows()) {
                $this->db->insert($this->table, $data);
                $id = $this->db->insert_id();
            } else {
                $row = $check->row_array();
                $this->db->update($this->table, $data);
                $id = $row['id'];
                $this->db->delete($this->tableOrderHistory, array('order_id' => $id));
            }

            $_history = $histories[$key];
            $history = array();
            foreach($_history as $h) {
                $history[] = array(
                    'order_id' => $id, 'type' => 3, 'note' => $h['comment'],
                    'status' => array_search($h['status'], $this->creditcardorder_m->getOrderStatusmap()), 'created_at' => $h['created_at'], 'created_by' => 2);
            }

            $this->db->insert_batch($this->tableOrderHistory, $history);

    	}
    	$this->db->trans_complete();
    }

}
