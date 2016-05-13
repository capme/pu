<?php
class bbmmoneyorder_m extends MY_Model {
    var $filterSession = "DB_USER_FILTER";
    var $db = null;
    var $table = 'bbmmoney_order';
    var $sorts = array(2 => "created_at", 3 => "updated_at");
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
            array("type" => "left", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}"),
            array("type" => "left", "table" => $this->tableAwb, "link" => "{$this->table}.order_number  = {$this->tableAwb}.ordernr and {$this->tableAwb}.status != 3" )
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

    public function getBbmMoneyOrderList()
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
            $status=$statList[$_resultArr['bbmmoney_order.status']];

            if(!isset($statListAWB[$_resultArr['awb_queue_printing.status']])){
                $statusAWB = "New Request";
            }else {
                $statusAWB = $statListAWB[$_resultArr['awb_queue_printing.status']];
            }
            $date = explode(' ', $_result->updated_at);
            $cDate = explode(' ', $_result->created_at);

            if(($_resultArr['bbmmoney_order.status'] !=2) && ($_resultArr['bbmmoney_order.status']!=5)){
                $action = '<a href="'.site_url("bbmmoneyorder/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a> |
                 <a href="'.site_url("bbmmoneyorder/cancel/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>';
            }
            else{
                $action = '<a href="'.site_url("bbmmoneyorder/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>';
            }
            $records["aaData"][] = array(
                '<input type="checkbox" name="id[]" value="'.$_result->id.'">',
                $no=$no+1,
                $cDate[0],
                $date[0],
                $_result->client_code,
                $_result->order_number,
                $_result->name,
                "Rp. ".number_format($_resultArr['bbmmoney_order.amount']),
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

    public function getBbmMoneyOrderById($id)
    {
        $this->db = $this->load->database('mysql', TRUE);
        $this->db->select('*,
		bbmmoney_order.items as items,
		bbmmoney_order.status as bbmmoney_status,
		bbmmoney_order.email as email,
		bbmmoney_order.created_at as bbmmoneycreated_at,
		bbmmoney_order.updated_at as bbmmoneyupdate_at,
		auth_users.username, 
		order_history.type, 
		order_history.created_at as history_date');
        
		$this->db->from($this->table);
        $this->db->join('client','client.id=bbmmoney_order.client_id');
        $this->db->join('order_history', 'order_history.order_id=bbmmoney_order.id and order_history.type=3','left');
        $this->db->join('auth_users', 'auth_users.pkUserId=order_history.created_by');
        $this->db->where('bbmmoney_order.id', $id);
        $this->db->order_by('order_history.id','asc');
        return $this->db->get();
    }

    public function Reason($post){

        $this->load->model("client_m");
        $this->load->library("mageapi");

        $msg = array();
        $user=$this->session->userdata('pkUserId');
        $time=date('Y-m-d H:i:s', now());

        if(!empty($post['reason'])) {

            $history['note'] = $post['reason'];
            $history['type']=3;
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
    
    public function saveBbmMoneyOrder($clientid, $datas, $histories){
    	$this->db->trans_start();
		//print_r($datas);die();
    	foreach($datas as $key => $data){
            $check = null;
            $check = $this->db->get_where($this->table, array('order_number' => (string) $key));
            //$n = $check->result_array();

            //echo "order number::".$key."::rows::".$check->num_rows()."\n";
            //continue;
            if(!$check->num_rows()) {
                $this->db->insert($this->table, $data);
                $id = $this->db->insert_id();
                if($data['status'] == "2"){
                    $this->mageapi->sendNotifBrand($clientid, $data['order_number'], "bbm");
                }
            } else {
                $dataBefore = $check->row_array();
                if($dataBefore['status'] != $data['status'] and $data['status'] == "2"){
                    $this->mageapi->sendNotifBrand($clientid, $data['order_number'], "bbm");
                }
                $this->db->where('order_number', (string) $key);
                $this->db->update($this->table, $data);

                $row = $check->row_array();
                $id = $row['id'];
                $this->db->delete($this->tableOrderHistory, array('order_id' => $id, 'type' => 3));
            }

            $_history = $histories[$key];
            $history = array();
            foreach($_history as $h) {
                if(is_null($h['comment'])) $h['comment'] =  "";
                $history[] = array(
                    'order_id' => $id, 'type' => 3, 'note' => $h['comment'],
                    'status' => array_search($h['status'], $this->bbmmoneyorder_m->getOrderStatusmap()), 'created_at' => $h['created_at'], 'created_by' => 2);
            }

            $this->db->insert_batch($this->tableOrderHistory, $history);

    	}
    	$this->db->trans_complete();
    }

}
